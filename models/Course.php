<?php

declare(strict_types=1);

/**
 * Model Course
 * Mengelola tabel courses (Mata Kuliah) sekaligus menjaga agar beban SKS dosen pengampu selalu sinkron.
 */
final class Course extends BaseModel
{
    public const STATUS_ACTIVE = 'Aktif';
    public const STATUS_INACTIVE = 'Nonaktif';

    /** @var list<string> */
    public const ALLOWED_STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE];

    private Lecturer $lecturerModel;

    public function __construct(PDO $db, Lecturer $lecturerModel)
    {
        parent::__construct($db);
        $this->lecturerModel = $lecturerModel;
    }

    protected function getTableName(): string
    {
        return 'courses';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * @return list<string>
     */
    protected function getAllowedColumns(): array
    {
        return [
            'department_id',
            'lecturer_id',
            'course_code',
            'course_name',
            'credits',
            'semester',
            'class_room',
            'status',
        ];
    }

    /**
     * @return list<string>
     */
    protected function getRequiredColumns(): array
    {
        return $this->getAllowedColumns();
    }

    /**
     * Validasi input mata kuliah (kode MK, SKS, semester, status, serta relasi prodi & dosen).
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function validateData(array $input): array
    {
        $cleanData = parent::validateData($input);

        // Cek pilihan departemen dan dosen pengampu
        $departmentId = filter_var($cleanData['department_id'], FILTER_VALIDATE_INT);
        if ($departmentId === false || $departmentId <= 0) {
            throw new InvalidArgumentException('Departemen/Prodi mata kuliah wajib dipilih.');
        }
        $cleanData['department_id'] = $departmentId;

        $lecturerId = filter_var($cleanData['lecturer_id'], FILTER_VALIDATE_INT);
        if ($lecturerId === false || $lecturerId <= 0) {
            throw new InvalidArgumentException('Dosen pengampu mata kuliah wajib dipilih.');
        }
        $cleanData['lecturer_id'] = $lecturerId;

        // Ubah kode mata kuliah ke huruf besar dan cek formatnya
        $courseCode = strtoupper((string) $cleanData['course_code']);
        if (!preg_match('/^[A-Z0-9\-]{2,20}$/', $courseCode)) {
            throw new InvalidArgumentException(
                'Kode mata kuliah hanya boleh berisi huruf, angka, atau tanda hubung (2-20 karakter).'
            );
        }
        $cleanData['course_code'] = $courseCode;

        // Cek bobot SKS (1 - 6 SKS) dan semester (1 - 8)
        $credits = filter_var($cleanData['credits'], FILTER_VALIDATE_INT);
        if ($credits === false || $credits < 1 || $credits > 6) {
            throw new InvalidArgumentException('Bobot SKS mata kuliah harus bernilai antara 1 hingga 6.');
        }
        $cleanData['credits'] = $credits;

        $semester = filter_var($cleanData['semester'], FILTER_VALIDATE_INT);
        if ($semester === false || $semester < 1 || $semester > 8) {
            throw new InvalidArgumentException('Semester mata kuliah harus bernilai antara 1 hingga 8.');
        }
        $cleanData['semester'] = $semester;

        // Cek status mata kuliah
        $status = (string) $cleanData['status'];
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('Status mata kuliah tidak valid.');
        }

        return $cleanData;
    }

    /**
     * Mengambil daftar mata kuliah digabung dengan nama prodi dan nama dosen pengampu.
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $sql = 'SELECT
                    c.id,
                    c.department_id,
                    c.lecturer_id,
                    c.course_code,
                    c.course_name,
                    c.credits,
                    c.semester,
                    c.class_room,
                    c.status,
                    d.dept_code,
                    d.dept_name,
                    l.name AS lecturer_name,
                    l.nidn AS lecturer_nidn
                FROM courses c
                INNER JOIN departments d ON d.id = c.department_id
                INNER JOIN lecturers l ON l.id = c.lecturer_id
                ORDER BY c.id DESC';

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->run($sql)->fetchAll();

        return $rows;
    }

    /**
     * Menambahkan mata kuliah baru. Jika statusnya Aktif, beban SKS dosen ikut ditambah
     * di dalam satu transaksi database.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $cleanData = $this->validateData($data);
        $lecturerId = (int) $cleanData['lecturer_id'];
        $credits = (int) $cleanData['credits'];
        $status = (string) $cleanData['status'];

        $this->db->beginTransaction();

        try {
            // Langkah 1: Jika MK berstatus Aktif, cek kuota dan tambah beban SKS dosen
            if ($status === self::STATUS_ACTIVE) {
                $lecturer = $this->lecturerModel->findByIdForUpdate($lecturerId);
                if ($lecturer === null) {
                    throw new RuntimeException('Dosen pengampu tidak ditemukan.');
                }

                $currentAssigned = (int) $lecturer['assigned_credits'];
                $maxCredits = (int) $lecturer['max_credits'];

                if ($currentAssigned + $credits > $maxCredits) {
                    throw new RuntimeException(
                        'Beban SKS dosen "' . (string) $lecturer['name'] . '" melebihi kuota (terpakai: '
                        . $currentAssigned . '/' . $maxCredits . ' SKS, tambahan: ' . $credits . ' SKS).'
                    );
                }

                $this->lecturerModel->updateAssignedCredits($lecturerId, $credits);
            }

            // Langkah 2: Simpan mata kuliah baru lalu commit transaksi
            $courseId = parent::create($cleanData);
            $this->db->commit();

            return $courseId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Memperbarui mata kuliah dan menyesuaikan selisih beban SKS dosen lama maupun baru.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $cleanData = $this->validateData($data);
        $newLecturerId = (int) $cleanData['lecturer_id'];
        $newCredits = (int) $cleanData['credits'];
        $newStatus = (string) $cleanData['status'];

        $this->db->beginTransaction();

        try {
            // Langkah 1: Ambil data mata kuliah yang lama sebelum diubah
            /** @var array<string, mixed>|false $existing */
            $existing = $this->run(
                'SELECT * FROM courses WHERE id = :id LIMIT 1 FOR UPDATE',
                ['id' => $id]
            )->fetch();

            if ($existing === false) {
                throw new RuntimeException('Data mata kuliah tidak ditemukan.');
            }

            $oldLecturerId = (int) $existing['lecturer_id'];
            $oldCredits = (int) $existing['credits'];
            $oldStatus = (string) $existing['status'];

            // Langkah 2: Kembalikan SKS dosen lama jika sebelumnya mata kuliah ini Aktif
            if ($oldStatus === self::STATUS_ACTIVE) {
                $oldLecturer = $this->lecturerModel->findByIdForUpdate($oldLecturerId);
                if ($oldLecturer === null) {
                    throw new RuntimeException('Dosen pengampu sebelumnya tidak ditemukan.');
                }

                $this->lecturerModel->updateAssignedCredits($oldLecturerId, -$oldCredits);
            }

            // Langkah 3: Jika data baru berstatus Aktif, cek kuota dan tambahkan SKS ke dosen baru
            if ($newStatus === self::STATUS_ACTIVE) {
                $newLecturer = $this->lecturerModel->findByIdForUpdate($newLecturerId);
                if ($newLecturer === null) {
                    throw new RuntimeException('Dosen pengampu baru tidak ditemukan.');
                }

                $currentAssigned = (int) $newLecturer['assigned_credits'];
                $maxCredits = (int) $newLecturer['max_credits'];
                $remainingCredits = $maxCredits - $currentAssigned;

                if ($currentAssigned + $newCredits > $maxCredits) {
                    throw new RuntimeException(
                        'Beban SKS dosen "' . (string) $newLecturer['name'] . '" tidak mencukupi (sisa kuota: '
                        . $remainingCredits . ' SKS, dibutuhkan: ' . $newCredits . ' SKS).'
                    );
                }

                $this->lecturerModel->updateAssignedCredits($newLecturerId, $newCredits);
            }

            // Langkah 4: Simpan perubahan pada tabel courses lalu commit transaksi
            $updated = parent::update($id, $cleanData);
            $this->db->commit();

            return $updated;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Mengubah status mata kuliah (Aktif <-> Nonaktif) sekaligus memperbarui SKS dosen pengampu.
     */
    public function toggleStatus(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // Langkah 1: Ambil dan kunci data mata kuliah serta dosennya
            /** @var array<string, mixed>|false $course */
            $course = $this->run(
                'SELECT * FROM courses WHERE id = :id LIMIT 1 FOR UPDATE',
                ['id' => $id]
            )->fetch();

            if ($course === false) {
                throw new RuntimeException('Data mata kuliah tidak ditemukan.');
            }

            $lecturerId = (int) $course['lecturer_id'];
            $credits = (int) $course['credits'];
            $currentStatus = (string) $course['status'];

            $lecturer = $this->lecturerModel->findByIdForUpdate($lecturerId);
            if ($lecturer === null) {
                throw new RuntimeException('Dosen pengampu tidak ditemukan.');
            }

            // Langkah 2: Hitung perubahan status dan SKS dosen
            if ($currentStatus === self::STATUS_ACTIVE) {
                $nextStatus = self::STATUS_INACTIVE;
                $this->lecturerModel->updateAssignedCredits($lecturerId, -$credits);
            } else {
                $currentAssigned = (int) $lecturer['assigned_credits'];
                $maxCredits = (int) $lecturer['max_credits'];

                if ($currentAssigned + $credits > $maxCredits) {
                    throw new RuntimeException(
                        'Tidak dapat mengaktifkan mata kuliah karena beban SKS "' . (string) $lecturer['name']
                        . '" melebihi batas (' . $currentAssigned . '/' . $maxCredits . ' SKS).'
                    );
                }

                $nextStatus = self::STATUS_ACTIVE;
                $this->lecturerModel->updateAssignedCredits($lecturerId, $credits);
            }

            // Langkah 3: Simpan status baru lalu commit
            $this->run(
                'UPDATE courses SET status = :status WHERE id = :id',
                [
                    'status' => $nextStatus,
                    'id' => $id,
                ]
            );

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Menghapus mata kuliah dan mengembalikan kuota SKS dosen jika MK tersebut masih Aktif.
     */
    public function delete(int $id): bool
    {
        $this->db->beginTransaction();

        try {
            // Langkah 1: Cek data mata kuliah yang akan dihapus
            /** @var array<string, mixed>|false $course */
            $course = $this->run(
                'SELECT * FROM courses WHERE id = :id LIMIT 1 FOR UPDATE',
                ['id' => $id]
            )->fetch();

            if ($course === false) {
                throw new RuntimeException('Data mata kuliah tidak ditemukan.');
            }

            // Langkah 2: Jika masih Aktif, kurangi beban SKS dosen pengampu
            if ((string) $course['status'] === self::STATUS_ACTIVE) {
                $lecturerId = (int) $course['lecturer_id'];
                $credits = (int) $course['credits'];

                $lecturer = $this->lecturerModel->findByIdForUpdate($lecturerId);
                if ($lecturer !== null) {
                    $this->lecturerModel->updateAssignedCredits($lecturerId, -$credits);
                }
            }

            // Langkah 3: Hapus data mata kuliah lalu commit transaksi
            $deleted = parent::delete($id);
            $this->db->commit();

            return $deleted;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
