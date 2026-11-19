<?php

declare(strict_types=1);

/**
 * Model Lecturer
 * Mengelola tabel lecturers (Dosen) yang berelasi ke departments serta batas beban SKS.
 */
final class Lecturer extends BaseModel
{
    /** @var list<string> */
    public const ALLOWED_RANKS = ['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar'];

    protected function getTableName(): string
    {
        return 'lecturers';
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
            'name',
            'nidn',
            'email',
            'phone',
            'join_date',
            'academic_rank',
            'max_credits',
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
     * Mengecek apakah format tanggal benar-benar YYYY-MM-DD (misal 2024-09-01).
     */
    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    /**
     * Validasi semua input data dosen sebelum disimpan ke database.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function validateData(array $input): array
    {
        $cleanData = parent::validateData($input);

        // Cek ID departemen harus bilangan bulat positif
        $departmentId = filter_var($cleanData['department_id'], FILTER_VALIDATE_INT);
        if ($departmentId === false || $departmentId <= 0) {
            throw new InvalidArgumentException('Departemen/Prodi wajib dipilih dengan benar.');
        }
        $cleanData['department_id'] = $departmentId;

        // Cek NIDN berupa angka 6 sampai 20 digit
        $nidn = (string) $cleanData['nidn'];
        if (!preg_match('/^[0-9]{6,20}$/', $nidn)) {
            throw new InvalidArgumentException('NIDN harus berupa angka sepanjang 6 sampai 20 digit.');
        }

        // Cek format email
        $email = (string) $cleanData['email'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Format alamat email dosen tidak valid.');
        }

        // Cek format nomor telepon
        $phone = (string) $cleanData['phone'];
        if (!preg_match('/^[0-9+\-\s]{7,25}$/', $phone)) {
            throw new InvalidArgumentException('Nomor telepon hanya boleh berisi angka, spasi, +, atau - (7-25 karakter).');
        }

        // Cek tanggal bergabung
        $joinDate = (string) $cleanData['join_date'];
        if (!$this->isValidDate($joinDate)) {
            throw new InvalidArgumentException('Format tanggal bergabung harus YYYY-MM-DD.');
        }

        // Cek jabatan akademik harus termasuk dalam pilihan yang tersedia
        $academicRank = (string) $cleanData['academic_rank'];
        if (!in_array($academicRank, self::ALLOWED_RANKS, true)) {
            throw new InvalidArgumentException('Jabatan akademik dosen tidak valid.');
        }

        // Cek kuota SKS maksimal dosen (1 sampai 24 SKS)
        $maxCredits = filter_var($cleanData['max_credits'], FILTER_VALIDATE_INT);
        if ($maxCredits === false || $maxCredits < 1 || $maxCredits > 24) {
            throw new InvalidArgumentException('Kuota maksimal SKS dosen harus bernilai antara 1 hingga 24.');
        }
        $cleanData['max_credits'] = $maxCredits;

        return $cleanData;
    }

    /**
     * Mengambil data dosen digabung (INNER JOIN) dengan data departemen serta jumlah mata kuliahnya.
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $sql = 'SELECT
                    l.id,
                    l.department_id,
                    l.name,
                    l.nidn,
                    l.email,
                    l.phone,
                    l.join_date,
                    l.academic_rank,
                    l.max_credits,
                    l.assigned_credits,
                    d.dept_code,
                    d.dept_name,
                    d.faculty,
                    COUNT(c.id) AS course_count
                FROM lecturers l
                INNER JOIN departments d ON d.id = l.department_id
                LEFT JOIN courses c ON c.lecturer_id = l.id
                GROUP BY
                    l.id,
                    l.department_id,
                    l.name,
                    l.nidn,
                    l.email,
                    l.phone,
                    l.join_date,
                    l.academic_rank,
                    l.max_credits,
                    l.assigned_credits,
                    d.dept_code,
                    d.dept_name,
                    d.faculty
                ORDER BY l.id DESC';

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->run($sql)->fetchAll();

        return $rows;
    }

    /**
     * Memperbarui data dosen sambil memastikan kuota SKS maksimal tidak lebih kecil dari SKS yang sedang diajar.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $current = $this->findById($id);
        if ($current === null) {
            throw new RuntimeException('Data dosen yang ingin diperbarui tidak ditemukan.');
        }

        $cleanData = $this->validateData($data);
        $assignedCredits = (int) $current['assigned_credits'];
        $newMaxCredits = (int) $cleanData['max_credits'];

        if ($newMaxCredits < $assignedCredits) {
            throw new RuntimeException(
                'Kuota maksimal SKS (' . $newMaxCredits . ') tidak boleh lebih kecil dari beban SKS aktif saat ini (' . $assignedCredits . ' SKS).'
            );
        }

        return parent::update($id, $cleanData);
    }

    /**
     * Mengambil dan mengunci baris dosen (FOR UPDATE) selama transaksi agar jumlah SKS tetap akurat.
     *
     * @return array<string, mixed>|null
     */
    public function findByIdForUpdate(int $id): ?array
    {
        $sql = 'SELECT * FROM lecturers WHERE id = :id LIMIT 1 FOR UPDATE';

        /** @var array<string, mixed>|false $row */
        $row = $this->run($sql, ['id' => $id])->fetch();

        if ($row === false) {
            return null;
        }

        return $row;
    }

    /**
     * Menambah (angka positif) atau mengurangi (angka negatif) beban SKS aktif dosen.
     */
    public function updateAssignedCredits(int $id, int $creditChange): bool
    {
        $sql = 'UPDATE lecturers
                SET assigned_credits = assigned_credits + :credit_change
                WHERE id = :id';

        $stmt = $this->run($sql, [
            'credit_change' => $creditChange,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}
