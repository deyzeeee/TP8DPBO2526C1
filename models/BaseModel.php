<?php

declare(strict_types=1);

/**
 * Abstract Class BaseModel
 * Kelas induk yang menyediakan fungsi CRUD dasar (Create, Read, Update, Delete)
 * menggunakan PDO Prepared Statement supaya aman dari SQL Injection.
 */
abstract class BaseModel implements CrudRepository
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    abstract protected function getTableName(): string;

    abstract protected function getPrimaryKey(): string;

    /**
     * Daftar nama kolom yang boleh diisi saat INSERT atau UPDATE.
     *
     * @return list<string>
     */
    abstract protected function getAllowedColumns(): array;

    /**
     * Daftar nama kolom yang wajib diisi (tidak boleh kosong).
     *
     * @return list<string>
     */
    abstract protected function getRequiredColumns(): array;

    /**
     * Semua perintah SQL di aplikasi wajib lewat method ini.
     * Memakai prepare() dan execute() agar parameter terpisah dari query SQL.
     *
     * @param array<int|string, mixed> $params
     */
    protected function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Menyaring data dari form (misalnya $_POST) berdasarkan daftar kolom yang diizinkan
     * lalu memeriksa apakah ada kolom wajib yang masih kosong.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function validateData(array $input): array
    {
        $allowedColumns = $this->getAllowedColumns();
        $requiredColumns = $this->getRequiredColumns();
        $cleanData = [];

        // Ambil hanya kolom yang memang terdaftar di getAllowedColumns()
        foreach ($allowedColumns as $column) {
            if (array_key_exists($column, $input)) {
                $value = $input[$column];
                if (is_string($value)) {
                    $cleanData[$column] = trim($value);
                } else {
                    $cleanData[$column] = $value;
                }
            }
        }

        // Pastikan tidak ada kolom wajib yang kosong
        foreach ($requiredColumns as $reqColumn) {
            if (!isset($cleanData[$reqColumn]) || $cleanData[$reqColumn] === '') {
                throw new InvalidArgumentException(
                    'Kolom "' . $reqColumn . '" wajib diisi dan tidak boleh kosong.'
                );
            }
        }

        return $cleanData;
    }

    /**
     * Mengambil semua baris dari tabel terkait.
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        $table = $this->getTableName();
        $primaryKey = $this->getPrimaryKey();
        $sql = "SELECT * FROM {$table} ORDER BY {$primaryKey} DESC";

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->run($sql)->fetchAll();

        return $rows;
    }

    /**
     * Mengambil satu baris data berdasarkan ID.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $table = $this->getTableName();
        $primaryKey = $this->getPrimaryKey();
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = :id LIMIT 1";

        /** @var array<string, mixed>|false $row */
        $row = $this->run($sql, ['id' => $id])->fetch();

        if ($row === false) {
            return null;
        }

        return $row;
    }

    /**
     * Menyimpan satu baris data baru ke tabel dan mengembalikan ID baru.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $cleanData = $this->validateData($data);
        $columns = array_keys($cleanData);

        $columnNames = [];
        $placeholders = [];
        foreach ($columns as $col) {
            $columnNames[] = $col;
            $placeholders[] = ':' . $col;
        }

        $table = $this->getTableName();
        $columnText = implode(', ', $columnNames);
        $placeholderText = implode(', ', $placeholders);

        $sql = "INSERT INTO {$table} ({$columnText}) VALUES ({$placeholderText})";
        $this->run($sql, $cleanData);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Memperbarui data pada baris dengan ID tertentu.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $cleanData = $this->validateData($data);
        $columns = array_keys($cleanData);

        $setParts = [];
        foreach ($columns as $col) {
            $setParts[] = "{$col} = :{$col}";
        }

        $table = $this->getTableName();
        $primaryKey = $this->getPrimaryKey();
        $setText = implode(', ', $setParts);

        $sql = "UPDATE {$table} SET {$setText} WHERE {$primaryKey} = :primary_id";
        $cleanData['primary_id'] = $id;
        $this->run($sql, $cleanData);

        return true;
    }

    /**
     * Menghapus satu baris data berdasarkan ID.
     */
    public function delete(int $id): bool
    {
        $table = $this->getTableName();
        $primaryKey = $this->getPrimaryKey();
        $sql = "DELETE FROM {$table} WHERE {$primaryKey} = :id";

        $stmt = $this->run($sql, ['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
