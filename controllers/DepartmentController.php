<?php

declare(strict_types=1);

/**
 * Class DepartmentController
 * Menghubungkan halaman Departemen dengan model Department dan tampilan views/departments.php.
 */
final class DepartmentController extends BaseController
{
    private Department $departmentModel;

    public function __construct(Department $departmentModel)
    {
        $this->departmentModel = $departmentModel;
    }

    public function handleRequest(): void
    {
        // Jika form dikirim dengan metode POST, proses aksi simpan/ubah/hapus
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        // Jika metode GET, ambil data lalu tampilkan ke view departments
        $this->render('departments', [
            'currentPage' => 'departments',
            'pageTitle' => 'Manajemen Departemen',
            'departments' => $this->departmentModel->findAll(),
            'editDepartment' => $this->getEditData($this->departmentModel),
            'allowedAccreditations' => Department::ALLOWED_ACCREDITATIONS,
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
                $this->departmentModel->create($_POST);
                setFlash('success', 'Data departemen berhasil ditambahkan.');
            } elseif ($action === 'update') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID departemen tidak valid.');
                }

                $this->departmentModel->update($id, $_POST);
                setFlash('success', 'Data departemen berhasil diperbarui.');
            } elseif ($action === 'delete') {
                $id = $this->toPositiveId($_POST['id'] ?? 0);
                if ($id <= 0) {
                    throw new InvalidArgumentException('ID departemen tidak valid.');
                }

                $deleted = $this->departmentModel->delete($id);
                if (!$deleted) {
                    throw new RuntimeException('Data departemen tidak ditemukan.');
                }

                setFlash('success', 'Data departemen berhasil dihapus.');
            } else {
                throw new InvalidArgumentException('Aksi tidak dikenali.');
            }
        } catch (Throwable $e) {
            setFlash('danger', translateException($e));
        }

        // Redirect setelah POST (Post/Redirect/Get) agar form tidak terkirim ulang saat refresh
        redirectToPage('departments');
    }
}
