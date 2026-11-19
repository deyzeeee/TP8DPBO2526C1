<?php

declare(strict_types=1);

/**
 * Model Department
 * Mengelola tabel departments (Program Studi / Departemen).
 */
final class Department extends BaseModel
{
    /** @var list<string> */
    public const ALLOWED_ACCREDITATIONS = ['Unggul', 'Baik Sekali', 'Baik'];

    protected function getTableName(): string
    {
        return 'departments';
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
            'dept_code',
            'dept_name',
            'faculty',
            'head_room',
            'accreditation',
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
     * Validasi aturan khusus tabel departments sebelum disimpan.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function validateData(array $input): array
    {
        $cleanData = parent::validateData($input);

        // Ubah kode departemen menjadi huruf besar dan cek formatnya
        $deptCode = strtoupper((string) $cleanData['dept_code']);
        if (!preg_match('/^[A-Z0-9\-]{2,20}$/', $deptCode)) {
            throw new InvalidArgumentException(
                'Kode departemen hanya boleh berisi huruf, angka, atau tanda hubung (2-20 karakter).'
            );
        }
        $cleanData['dept_code'] = $deptCode;

        // Pastikan akreditasi sesuai dengan pilihan ENUM yang tersedia
        $accreditation = (string) $cleanData['accreditation'];
        if (!in_array($accreditation, self::ALLOWED_ACCREDITATIONS, true)) {
            throw new InvalidArgumentException('Status akreditasi departemen tidak valid.');
        }

        return $cleanData;
    }

    /**
     * Mengambil daftar departemen sekaligus menghitung jumlah dosen dan mata kuliah per departemen.
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $sql = 'SELECT
                    d.id,
                    d.dept_code,
                    d.dept_name,
                    d.faculty,
                    d.head_room,
                    d.accreditation,
                    COUNT(DISTINCT l.id) AS lecturer_count,
                    COUNT(DISTINCT c.id) AS course_count
                FROM departments d
                LEFT JOIN lecturers l ON l.department_id = d.id
                LEFT JOIN courses c ON c.department_id = d.id
                GROUP BY d.id, d.dept_code, d.dept_name, d.faculty, d.head_room, d.accreditation
                ORDER BY d.id ASC';

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->run($sql)->fetchAll();

        return $rows;
    }
}
