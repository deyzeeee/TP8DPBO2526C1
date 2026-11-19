<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $lecturers */
/** @var list<array<string, mixed>> $departments */
/** @var array<string, mixed>|null $editLecturer */
/** @var list<string> $allowedRanks */

$isEdit = $editLecturer !== null;
$hasDepartments = $departments !== [];

require __DIR__ . '/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header <?= $isEdit ? 'bg-warning text-dark' : 'bg-primary text-white' ?>">
                <?= $isEdit ? 'Edit Data Dosen' : 'Tambah Dosen Baru' ?>
            </div>
            <div class="card-body">
                <?php if (!$hasDepartments): ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        Data departemen masih kosong. Silakan tambahkan departemen terlebih dahulu di halaman
                        <a href="index.php?page=departments" class="alert-link">Departemen</a>.
                    </div>
                <?php else: ?>
                    <form method="post" action="index.php?page=lecturers">
                        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= e($editLecturer['id']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="department_id" class="form-label fw-semibold">Departemen / Prodi</label>
                            <select id="department_id" name="department_id" class="form-select" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <?php $selected = $isEdit && (int) $editLecturer['department_id'] === (int) $dept['id']; ?>
                                    <option value="<?= e($dept['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                        [<?= e($dept['dept_code']) ?>] <?= e($dept['dept_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Lengkap &amp; Gelar</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                placeholder="Contoh: Dr. Eddy Prasetyo Nugroho, M.T."
                                maxlength="120"
                                value="<?= e($isEdit ? $editLecturer['name'] : '') ?>"
                                required
                            >
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label for="nidn" class="form-label fw-semibold">NIDN</label>
                                <input
                                    type="text"
                                    id="nidn"
                                    name="nidn"
                                    class="form-control"
                                    placeholder="0014098109"
                                    maxlength="20"
                                    value="<?= e($isEdit ? $editLecturer['nidn'] : '') ?>"
                                    required
                                >
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label fw-semibold">No. Telepon</label>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    placeholder="081234567890"
                                    maxlength="25"
                                    value="<?= e($isEdit ? $editLecturer['phone'] : '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Alamat Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="nama.dosen@upi.edu"
                                maxlength="120"
                                value="<?= e($isEdit ? $editLecturer['email'] : '') ?>"
                                required
                            >
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label for="join_date" class="form-label fw-semibold">Tanggal Bergabung</label>
                                <input
                                    type="date"
                                    id="join_date"
                                    name="join_date"
                                    class="form-control"
                                    value="<?= e($isEdit ? $editLecturer['join_date'] : date('Y-m-d')) ?>"
                                    required
                                >
                            </div>
                            <div class="col-sm-6">
                                <label for="max_credits" class="form-label fw-semibold">Kuota Maks. SKS</label>
                                <input
                                    type="number"
                                    id="max_credits"
                                    name="max_credits"
                                    class="form-control"
                                    min="1"
                                    max="24"
                                    value="<?= e($isEdit ? $editLecturer['max_credits'] : '12') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="academic_rank" class="form-label fw-semibold">Jabatan Akademik</label>
                            <select id="academic_rank" name="academic_rank" class="form-select" required>
                                <?php foreach ($allowedRanks as $rank): ?>
                                    <?php $selected = $isEdit && (string) $editLecturer['academic_rank'] === $rank; ?>
                                    <option value="<?= e($rank) ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= e($rank) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn <?= $isEdit ? 'btn-warning fw-semibold' : 'btn-primary' ?>">
                                <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Dosen' ?>
                            </button>
                            <?php if ($isEdit): ?>
                                <a href="index.php?page=lecturers" class="btn btn-outline-secondary">Batal Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Daftar Dosen &amp; Beban Mengajar</span>
                <span class="badge bg-secondary"><?= count($lecturers) ?> Dosen</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Dosen &amp; NIDN</th>
                                <th>Departemen</th>
                                <th>Kontak &amp; Jabatan</th>
                                <th>Beban SKS</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($lecturers === []): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada data dosen tersimpan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lecturers as $lecturer): ?>
                                    <?php
                                    $assigned = (int) $lecturer['assigned_credits'];
                                    $max = max(1, (int) $lecturer['max_credits']);
                                    $percent = min(100, (int) round(($assigned / $max) * 100));

                                    if ($percent >= 90) {
                                        $barColor = 'bg-danger';
                                    } elseif ($percent >= 65) {
                                        $barColor = 'bg-warning';
                                    } else {
                                        $barColor = 'bg-success';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e($lecturer['name']) ?></div>
                                            <small class="text-muted">NIDN: <?= e($lecturer['nidn']) ?></small>
                                            <div><small class="text-muted">Gabung: <?= e($lecturer['join_date']) ?></small></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-primary"><?= e($lecturer['dept_code']) ?></span>
                                            <div class="small mt-1"><?= e($lecturer['dept_name']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?= e($lecturer['academic_rank']) ?></div>
                                            <small class="d-block text-muted"><?= e($lecturer['email']) ?></small>
                                            <small class="text-muted"><?= e($lecturer['phone']) ?></small>
                                        </td>
                                        <td style="min-width: 130px;">
                                            <div class="d-flex justify-content-between small">
                                                <span class="fw-semibold"><?= e($assigned) ?>/<?= e($max) ?> SKS</span>
                                                <span class="text-muted"><?= e($lecturer['course_count']) ?> MK</span>
                                            </div>
                                            <div class="sks-progress">
                                                <div class="sks-progress-bar <?= e($barColor) ?>" style="width: <?= e($percent) ?>%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="index.php?page=lecturers&amp;edit=<?= e($lecturer['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                            <form
                                                method="post"
                                                action="index.php?page=lecturers"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data dosen ini?');"
                                            >
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e($lecturer['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
