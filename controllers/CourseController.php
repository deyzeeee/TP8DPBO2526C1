<?php

declare(strict_types=1);

/**
 * Class CourseController
 * Menghubungkan halaman Mata Kuliah dengan model Course, Lecturer, Department dan tampilan views/courses.php.
 */
final class CourseController extends BaseController
{
    private Course $courseModel;
    private Lecturer $lecturerModel;
    private Department $departmentModel;

    public function __construct(
        Course $courseModel,
        Lecturer $lecturerModel,
        Department $departmentModel
    ) {
        $this->courseModel = $courseModel;
        $this->lecturerModel = $lecturerModel;
        $this->departmentModel = $departmentModel;
    }

    public function handleRequest(): void
    {
        // Jika form dikirim dengan metode POST, proses aksi tambah/ubah/toggle status/hapus
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        // Jika metode GET, siapkan daftar mata kuliah, departemen, dan dosen untuk tampilan
        $this->render('courses', [
            'currentPage' => 'courses',
            'pageTitle' => 'Manajemen Mata Kuliah',
            'courses' => $this->courseModel->findAll(),
            'departments' => $this->departmentModel->findAll(),
            'lecturers' => $this->lecturerModel->findAll(),
            'editCourse' => $this->getEditData($this->courseModel),
            'allowedStatuses' => Course::ALLOWED_STATUSES,
            'flash' => getFlash(),
            'dbError' => null,
        ]);
    }

    private function handlePost(): void
    {
        $action = '';
        if (isset($_POST['action']) && is_string($_POST['action'])) {
            $action = $_POST['action'];
        }

        try {
            if ($action === 'create') {
                $this->courseModel->create($_POST);
                setFlash('success', 'Mata kuliah berhasil ditambahkan dan beban SKS dosen telah diperbarui.');
            } elseif ($action === 'update') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID mata kuliah tidak valid.');
                }

                $this->courseModel->update($id, $_POST);
                setFlash('success', 'Data mata kuliah dan beban SKS dosen berhasil diperbarui.');
            } elseif ($action === 'toggle_status') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID mata kuliah tidak valid.');
                }

                $this->courseModel->toggleStatus($id);
                setFlash('success', 'Status mata kuliah dan beban SKS dosen berhasil disinkronkan.');
            } elseif ($action === 'delete') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID mata kuliah tidak valid.');
                }

                $deleted = $this->courseModel->delete($id);
                if (!$deleted) {
                    throw new RuntimeException('Data mata kuliah tidak ditemukan.');
                }

                setFlash('success', 'Data mata kuliah berhasil dihapus dan kuota SKS telah disesuaikan.');
            } else {
                throw new InvalidArgumentException('Aksi tidak dikenali.');
            }
        } catch (Throwable $e) {
            setFlash('danger', translateException($e));
        }

        // Redirect setelah POST (Post/Redirect/Get) agar form tidak terkirim ulang saat refresh
        redirectToPage('courses');
    }
}
