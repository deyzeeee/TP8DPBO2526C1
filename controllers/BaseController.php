<?php

declare(strict_types=1);

/**
 * Abstract Class BaseController
 * Kelas dasar untuk semua controller agar punya cara yang sama
 * dalam membaca ID, mengambil data edit, dan menampilkan view.
 */
abstract class BaseController
{
    /**
     * Method utama untuk memproses request dari browser (GET atau POST).
     */
    abstract public function handleRequest(): void;

    /**
     * Mengubah nilai input (misal dari $_GET atau $_POST) menjadi angka ID positif.
     * Jika tidak valid atau <= 0, hasilnya adalah 0.
     */
    protected function toPositiveId(mixed $value): int
    {
        $parsed = filter_var($value, FILTER_VALIDATE_INT);

        if ($parsed !== false && $parsed > 0) {
            return $parsed;
        }

        return 0;
    }

    /**
     * Mengecek apakah URL punya parameter ?edit=<id>.
     * Jika ada, ambil data baris tersebut dari model untuk mengisi form edit.
     *
     * @return array<string, mixed>|null
     */
    protected function getEditData(CrudRepository $model): ?array
    {
        $editId = $this->toPositiveId($_GET['edit'] ?? 0);
        if ($editId <= 0) {
            return null;
        }

        return $model->findById($editId);
    }

    /**
     * Memanggil file tampilan di folder views/ sambil mengirim data dari controller.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $viewName, array $data = []): void
    {
        $safeView = basename($viewName);
        $viewPath = dirname(__DIR__) . '/views/' . $safeView . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException('File view "' . $safeView . '" tidak ditemukan.');
        }

        // Ubah isi array $data menjadi variabel biasa yang bisa langsung dipakai di file view
        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}
