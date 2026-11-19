<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $courses */
/** @var list<array<string, mixed>> $departments */
/** @var list<array<string, mixed>> $lecturers */
/** @var array<string, mixed>|null $editCourse */
/** @var list<string> $allowedStatuses */

$isEdit = $editCourse !== null;
$hasParents = $departments !== [] && $lecturers !== [];

require __DIR__ . '/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header <?= $isEdit ? 'bg-warning text-dark' : 'bg-primary text-white' ?>">
                <?= $isEdit ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah Baru' ?>
            </div>
            <div class="card-body">
                <?php if (!$hasParents): ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        Pastikan data
                        <a href="index.php?page=departments" class="alert-link">Departemen</a>
                        dan
                        <a href="index.php?page=lecturers" class="alert-link">Dosen</a>
                        sudah terisi sebelum menambahkan mata kuliah.
                    </div>
                <?php else: ?>
                    <form method="post" action="index.php?page=courses">
                        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= e($editCourse['id']) ?>">
                        <?php endif; ?>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-5">
                                <label for="course_code" class="form-label fw-semibold">Kode MK</label>
                                <input
                                    type="text"
                                    id="course_code"
                                    name="course_code"
                                    class="form-control"
                                    placeholder="IK308"
                                    maxlength="20"
                                    value="<?= e($isEdit ? $editCourse['course_code'] : '') ?>"
                                    required
                                >
                            </div>
                            <div class="col-sm-3">
                                <label for="credits" class="form-label fw-semibold">SKS</label>
                                <input
                                    type="number"
                                    id="credits"
                                    name="credits"
                                    class="form-control"
                                    min="1"
                                    max="6"
                                    value="<?= e($isEdit ? $editCourse['credits'] : '3') ?>"
                                    required
                                >
                            </div>
                            <div class="col-sm-4">
                                <label for="semester" class="form-label fw-semibold">Semester</label>
                                <input
                                    type="number"
                                    id="semester"
                                    name="semester"
                                    class="form-control"
                                    min="1"
                                    max="8"
                                    value="<?= e($isEdit ? $editCourse['semester'] : '3') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="course_name" class="form-label fw-semibold">Nama Mata Kuliah</label>
                            <input
                                type="text"
                                id="course_name"
                                name="course_name"
                                class="form-control"
                                placeholder="Contoh: Pemrograman Visual dan Piranti Bergerak"
                                maxlength="120"
                                value="<?= e($isEdit ? $editCourse['course_name'] : '') ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label fw-semibold">Departemen / Prodi</label>
                            <select id="department_id" name="department_id" class="form-select" required>
                                <option value="">-- Pilih Departemen --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <?php $selected = $isEdit && (int) $editCourse['department_id'] === (int) $dept['id']; ?>
                                    <option value="<?= e($dept['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                        [<?= e($dept['dept_code']) ?>] <?= e($dept['dept_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="lecturer_id" class="form-label fw-semibold">Dosen Pengampu</label>
                            <select id="lecturer_id" name="lecturer_id" class="form-select" required>
                                <option value="">-- Pilih Dosen Pengampu --</option>
                                <?php foreach ($lecturers as $lecturer): ?>
                                    <?php $selected = $isEdit && (int) $editCourse['lecturer_id'] === (int) $lecturer['id']; ?>
                                    <option value="<?= e($lecturer['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= e($lecturer['name']) ?> (<?= e($lecturer['assigned_credits']) ?>/<?= e($lecturer['max_credits']) ?> SKS)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="class_room" class="form-label fw-semibold">Ruang Kelas</label>
                            <input
                                type="text"
                                id="class_room"
                                name="class_room"
                                class="form-control"
                                placeholder="Contoh: Lab Pemrograman Kemakom"
                                maxlength="60"
                                value="<?= e($isEdit ? $editCourse['class_room'] : '') ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Status Perkuliahan</label>
                            <select id="status" name="status" class="form-select" required>
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <?php $selected = $isEdit && (string) $editCourse['status'] === $status; ?>
                                    <option value="<?= e($status) ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= e($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Mata kuliah berstatus <strong>Aktif</strong> otomatis menambah beban SKS dosen pengampu.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn <?= $isEdit ? 'btn-warning fw-semibold' : 'btn-primary' ?>">
                                <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Mata Kuliah' ?>
                            </button>
                            <?php if ($isEdit): ?>
                                <a href="index.php?page=courses" class="btn btn-outline-secondary">Batal Edit</a>
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
                <span>Daftar Mata Kuliah &amp; Dosen Pengampu</span>
                <span class="badge bg-secondary"><?= count($courses) ?> Mata Kuliah</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Prodi &amp; Ruang</th>
                                <th>Dosen Pengampu</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($courses === []): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada data mata kuliah tersimpan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <?php $isActive = (string) $course['status'] === Course::STATUS_ACTIVE; ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-soft-primary"><?= e($course['course_code']) ?></span>
                                            <div class="fw-semibold mt-1"><?= e($course['course_name']) ?></div>
                                            <small class="text-muted">
                                                <?= e($course['credits']) ?> SKS &bull; Semester <?= e($course['semester']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?= e($course['dept_name']) ?></div>
                                            <small class="text-muted"><?= e($course['class_room']) ?></small>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?= e($course['lecturer_name']) ?></div>
                                            <small class="text-muted">NIDN: <?= e($course['lecturer_nidn']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $isActive ? 'badge-soft-success' : 'badge-soft-secondary' ?>">
                                                <?= e($course['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <form method="post" action="index.php?page=courses" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?= e($course['id']) ?>">
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm <?= $isActive ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                                                >
                                                    <?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                </button>
                                            </form>
                                            <a href="index.php?page=courses&amp;edit=<?= e($course['id']) ?>" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                            <form
                                                method="post"
                                                action="index.php?page=courses"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini?');"
                                            >
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= e($course['id']) ?>">
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
