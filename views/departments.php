<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $departments */
/** @var array<string, mixed>|null $editDepartment */
/** @var list<string> $allowedAccreditations */

$isEdit = $editDepartment !== null;

require __DIR__ . '/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header <?= $isEdit ? 'bg-warning text-dark' : 'bg-primary text-white' ?>">
                <?= $isEdit ? 'Edit Data Departemen' : 'Tambah Departemen Baru' ?>
            </div>
            <div class="card-body">
                <form method="post" action="index.php?page=departments">
                    <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= e($editDepartment['id']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="dept_code" class="form-label fw-semibold">Kode Departemen</label>
                        <input
                            type="text"
                            id="dept_code"
                            name="dept_code"
                            class="form-control"
                            placeholder="Contoh: TK-S1"
                            maxlength="20"
                            value="<?= e($isEdit ? $editDepartment['dept_code'] : '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="dept_name" class="form-label fw-semibold">Nama Departemen / Prodi</label>
                        <input
                            type="text"
                            id="dept_name"
                            name="dept_name"
                            class="form-control"
                            placeholder="Contoh: Teknik Komputer"
                            maxlength="100"
                            value="<?= e($isEdit ? $editDepartment['dept_name'] : '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="faculty" class="form-label fw-semibold">Fakultas</label>
                        <input
                            type="text"
                            id="faculty"
                            name="faculty"
                            class="form-control"
                            placeholder="Contoh: FPMIPA UPI"
                            maxlength="100"
                            value="<?= e($isEdit ? $editDepartment['faculty'] : '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="head_room" class="form-label fw-semibold">Ruang Kantor Prodi</label>
                        <input
                            type="text"
                            id="head_room"
                            name="head_room"
                            class="form-control"
                            placeholder="Contoh: Gedung JICA FPMIPA A Lt. 3"
                            maxlength="60"
                            value="<?= e($isEdit ? $editDepartment['head_room'] : '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="accreditation" class="form-label fw-semibold">Akreditasi</label>
                        <select id="accreditation" name="accreditation" class="form-select" required>
                            <?php foreach ($allowedAccreditations as $acc): ?>
                                <?php $selected = $isEdit && (string) $editDepartment['accreditation'] === $acc; ?>
                                <option value="<?= e($acc) ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= e($acc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn <?= $isEdit ? 'btn-warning fw-semibold' : 'btn-primary' ?>">
                            <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Departemen' ?>
                        </button>
                        <?php if ($isEdit): ?>
                            <a href="index.php?page=departments" class="btn btn-outline-secondary">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Daftar Departemen / Program Studi</span>
                <span class="badge bg-secondary"><?= count($departments) ?> Departemen</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Program Studi &amp; Fakultas</th>
                                <th>Ruang Kantor</th>
                                <th>Akreditasi</th>
                                <th>Relasi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departments === []): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada data departemen. Silakan tambahkan melalui form di samping.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departments as $dept): ?>
                                    <?php
                                    $acc = (string) $dept['accreditation'];
                                    if ($acc === 'Unggul') {
                                        $accClass = 'badge-soft-success';
                                    } elseif ($acc === 'Baik Sekali') {
                                        $accClass = 'badge-soft-primary';
                                    } else {
                                        $accClass = 'badge-soft-warning';
                                    }
                                    ?>
                                    <tr>
                                        <td><span class="fw-semibold text-primary"><?= e($dept['dept_code']) ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= e($dept['dept_name']) ?></div>
                                            <small class="text-muted"><?= e($dept['faculty']) ?></small>
                                        </td>
                                        <td><?= e($dept['head_room']) ?></td>
                                        <td>
                                            <span class="badge <?= e($accClass) ?>"><?= e($acc) ?></span>
                                        </td>
                                        <td>
                                            <small class="d-block"><?= e($dept['lecturer_count']) ?> Dosen</small>
                                            <small class="text-muted"><?= e($dept['course_count']) ?> Mata Kuliah</small>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="index.php?page=departments&amp;edit=<?= e($dept['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                            <form
                                                method="post"
                                                action="index.php?page=departments"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data departemen ini?');"
                                            >
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e($dept['id']) ?>">
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
