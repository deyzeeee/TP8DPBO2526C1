<?php

declare(strict_types=1);

/**
 * Mengamankan teks sebelum ditampilkan ke HTML agar terhindar dari XSS.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Menyimpan pesan notifikasi sementara (flash message) ke dalam session.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Mengambil pesan flash dari session lalu langsung menghapusnya agar hanya tampil sekali.
 *
 * @return array{type: string, message: string}|null
 */
function getFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = [
        'type' => (string) ($_SESSION['flash']['type'] ?? 'info'),
        'message' => (string) ($_SESSION['flash']['message'] ?? ''),
    ];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Mengarahkan browser kembali ke halaman tertentu (pola Post/Redirect/Get).
 *
 * @param array<string, scalar> $params
 */
function redirectToPage(string $page, array $params = []): void
{
    $query = array_merge(['page' => $page], $params);
    header('Location: index.php?' . http_build_query($query));
    exit;
}

/**
 * Menerjemahkan error database MySQL atau error validasi menjadi pesan bahasa Indonesia yang ramah.
 * Penting: PDOException harus dicek lebih dulu karena PDOException merupakan turunan dari RuntimeException.
 */
function translateException(Throwable $exception): string
{
    if ($exception instanceof PDOException) {
        $errorCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : 0;
        $rawMessage = $exception->getMessage();

        if ($errorCode === 1451 || str_contains($rawMessage, '1451')) {
            return 'Gagal menghapus: Data ini tidak bisa dihapus karena masih dipakai oleh data di tabel lain.';
        } elseif ($errorCode === 1452 || str_contains($rawMessage, '1452')) {
            return 'Gagal menyimpan: Data pilihan relasi (departemen atau dosen) tidak ditemukan atau tidak valid.';
        } elseif ($errorCode === 1062 || str_contains($rawMessage, '1062')) {
            return 'Gagal menyimpan: Data dengan kode, NIDN, atau email tersebut sudah terdaftar (tidak boleh sama).';
        } else {
            return 'Terjadi kesalahan saat menyimpan data ke database. Silakan cek kembali input Anda.';
        }
    }

    if ($exception instanceof InvalidArgumentException || $exception instanceof RuntimeException) {
        return $exception->getMessage();
    }

    return 'Terjadi kesalahan sistem yang tidak terduga.';
}
