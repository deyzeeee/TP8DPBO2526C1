<?php

declare(strict_types=1);

/**
 * Class LecturerController
 * Menghubungkan halaman Dosen dengan model Lecturer & Department dan tampilan views/lecturers.php.
 */
final class LecturerController extends BaseController
{
    private Lecturer $lecturerModel;
    private Department $departmentModel;

    public function __construct(Lecturer $lecturerModel, Department $departmentModel)
    {
        $this->lecturerModel = $lecturerModel;
        $this->departmentModel = $departmentModel;
    }

    public function handleRequest(): void
    {
        // Jika form dikirim dengan metode POST, proses aksi simpan/ubah/hapus
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        // Jika metode GET, ambil daftar dosen dan daftar departemen untuk dropdown
        $this->render('lecturers', [
            'currentPage' => 'lecturers',
            'pageTitle' => 'Manajemen Dosen',
            'lecturers' => $this->lecturerModel->findAll(),
            'departments' => $this->departmentModel->findAll(),
            'editLecturer' => $this->getEditData($this->lecturerModel),
            'allowedRanks' => Lecturer::ALLOWED_RANKS,
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
                $this->lecturerModel->create($_POST);
                setFlash('success', 'Data dosen berhasil ditambahkan.');
            } elseif ($action === 'update') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID dosen tidak valid.');
                }

                $this->lecturerModel->update($id, $_POST);
                setFlash('success', 'Data dosen berhasil diperbarui.');
            } elseif ($action === 'delete') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID dosen tidak valid.');
                }

                $deleted = $this->lecturerModel->delete($id);
                if (!$deleted) {
                    throw new RuntimeException('Data dosen tidak ditemukan.');
                }

                setFlash('success', 'Data dosen berhasil dihapus.');
            } else {
                throw new InvalidArgumentException('Aksi tidak dikenali.');
            }
        } catch (Throwable $e) {
            setFlash('danger', translateException($e));
        }

        // Redirect setelah POST (Post/Redirect/Get) agar form tidak terkirim ulang saat refresh
        redirectToPage('lecturers');
    }
}
