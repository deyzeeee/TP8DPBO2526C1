<?php

declare(strict_types=1);

/**
 * Interface CrudRepository
 * Kontrak antarmuka kecil dan fokus untuk operasi dasar CRUD (Interface Segregation Principle).
 */
interface CrudRepository
{
    /**
     * Mengambil seluruh baris data.
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(): array;

    /**
     * Mengambil satu baris data berdasarkan primary key.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array;

    /**
     * Menyimpan baris data baru dan mengembalikan ID yang baru dibuat.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int;

    /**
     * Memperbarui baris data berdasarkan primary key.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool;

    /**
     * Menghapus baris data berdasarkan primary key.
     */
    public function delete(int $id): bool;
}
