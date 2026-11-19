<?php

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

/**
 * Daftar halaman yang diperbolehkan (whitelist) untuk mencegah akses file sembarangan.
 *
 * @var list<string> $allowedPages
 */
$allowedPages = ['lecturers', 'departments', 'courses'];

$requestedPage = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'lecturers';
$currentPage = in_array($requestedPage, $allowedPages, true) ? $requestedPage : 'lecturers';

try {
    // 1. Buka koneksi database PDO
    $database = new Database();
    $pdo = $database->getConnection();

    // 2. Buat objek Model dengan menyuntikkan koneksi $pdo lewat constructor
    $departmentModel = new Department($pdo);
    $lecturerModel = new Lecturer($pdo);
    $courseModel = new Course($pdo, $lecturerModel);

    // 3. Pilih Controller sesuai halaman ?page= yang diminta user
    if ($currentPage === 'departments') {
        $controller = new DepartmentController($departmentModel);
    } elseif ($currentPage === 'courses') {
        $controller = new CourseController($courseModel, $lecturerModel, $departmentModel);
    } else {
        $controller = new LecturerController($lecturerModel, $departmentModel);
    }

    // 4. Jalankan Controller untuk memproses request GET atau POST
    $controller->handleRequest();
} catch (Throwable $e) {
    $pageTitle = 'Koneksi Database Gagal';
    $flash = getFlash();
    $dbError = 'Koneksi ke database gagal. Pastikan Apache dan MySQL aktif serta file database/tp_mvc25.sql sudah di-import.';

    require __DIR__ . '/views/header.php';
    require __DIR__ . '/views/footer.php';
}
