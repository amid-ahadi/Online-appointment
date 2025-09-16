<?php
if (!defined('DASHBOARD_ACCESS')) {
    die('دسترسی غیرمجاز!');
}

// پردازش حذف تخصص
if (isset($_GET['delete_id']) && isAdmin()) {
    $specialty_id = intval($_GET['delete_id']);
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE specialty_id = ?");
        $stmt->execute([$specialty_id]);
        $hasDoctors = $stmt->fetchColumn();

        if ($hasDoctors > 0) {
            flash('⛔ امکان حذف تخصص وجود ندارد — زیرا ' . $hasDoctors . ' پزشک به این تخصص متصل هستند.', 'warning');
        } else {
            $stmt = $pdo->prepare("DELETE FROM specialties WHERE id = ?");
            if ($stmt->execute([$specialty_id])) {
                flash('✅ تخصص با موفقیت حذف شد.', 'success');
            } else {
                flash('❌ خطا در حذف تخصص.', 'danger');
            }
        }
    } catch (Exception $e) {
        flash('❌ خطا: ' . $e->getMessage(), 'danger');
    }
    redirect('dashboard.php?target=manage-specialties');
}

// پردازش تغییر جایگاه — بالا
if (isset($_GET['move_up']) && isAdmin()) {
    $current_id = intval($_GET['move_up']);
    $stmt = $pdo->prepare("SELECT sort_order FROM specialties WHERE id = ?");
    $stmt->execute([$current_id]);
    $current_order = $stmt->fetchColumn();

    if ($current_order > 1) {
        $above_order = $current_order - 1;
        $stmt = $pdo->prepare("SELECT id FROM specialties WHERE sort_order = ? LIMIT 1");
        $stmt->execute([$above_order]);
        $above_id = $stmt->fetchColumn();

        if ($above_id) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE specialties SET sort_order = ? WHERE id = ?");
            $stmt->execute([$above_order, $current_id]);
            $stmt->execute([$current_order, $above_id]);
            $pdo->commit();
            flash('جایگاه تخصص با موفقیت تغییر کرد.', 'success');
        }
    }
    redirect('dashboard.php?target=manage-specialties');
}

// پردازش تغییر جایگاه — پایین
if (isset($_GET['move_down']) && isAdmin()) {
    $current_id = intval($_GET['move_down']);
    $stmt = $pdo->prepare("SELECT sort_order FROM specialties WHERE id = ?");
    $stmt->execute([$current_id]);
    $current_order = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM specialties");
    $total = $stmt->fetchColumn();

    if ($current_order < $total) {
        $below_order = $current_order + 1;
        $stmt = $pdo->prepare("SELECT id FROM specialties WHERE sort_order = ? LIMIT 1");
        $stmt->execute([$below_order]);
        $below_id = $stmt->fetchColumn();

        if ($below_id) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE specialties SET sort_order = ? WHERE id = ?");
            $stmt->execute([$below_order, $current_id]);
            $stmt->execute([$current_order, $below_id]);
            $pdo->commit();
            flash('جایگاه تخصص با موفقیت تغییر کرد.', 'success');
        }
    }
    redirect('dashboard.php?target=manage-specialties');
}

// پردازش ثبت و ویرایش تخصص
if ($_POST && isAdmin()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_specialty') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');

        if (empty($name)) {
            flash('نام تخصص نمی‌تواند خالی باشد.', 'danger');
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO specialties (name, description, sort_order) VALUES (?, ?, (SELECT IFNULL(MAX(sort_order),0)+1 FROM specialties))");
                if ($stmt->execute([$name, $description])) {
                    flash('✅ تخصص جدید با موفقیت ثبت شد.', 'success');
                } else {
                    flash('❌ خطایی در ثبت تخصص رخ داد.', 'danger');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    flash('⛔ این نام تخصص قبلاً ثبت شده است.', 'warning');
                } else {
                    flash('❌ خطا: ' . $e->getMessage(), 'danger');
                }
            }
        }
        redirect('dashboard.php?target=manage-specialties');
    } 
    elseif ($action === 'edit_specialty') {
        $specialty_id = intval($_POST['specialty_id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');

        if (empty($name)) {
            flash('نام تخصص نمی‌تواند خالی باشد.', 'danger');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE specialties SET name = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $specialty_id])) {
                    flash('✅ تخصص با موفقیت به‌روزرسانی شد.', 'success');
                } else {
                    flash('❌ خطایی در به‌روزرسانی تخصص رخ داد.', 'danger');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    flash('⛔ این نام تخصص قبلاً ثبت شده است.', 'warning');
                } else {
                    flash('❌ خطا: ' . $e->getMessage(), 'danger');
                }
            }
        }
        redirect('dashboard.php?target=manage-specialties');
    }
}

// لیست تخصص‌ها
$stmt = $pdo->query("SELECT * FROM specialties ORDER BY sort_order ASC, name ASC");
$specialties = $stmt->fetchAll();
?>

<h3>🩺 مدیریت تخصص‌ها</h3>
<?php displayFlash(); ?>

<button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addSpecialtyModal">
    ➕ ثبت تخصص جدید
</button>

<?php if (empty($specialties)): ?>
    <div class="alert alert-info">هیچ تخصصی ثبت نشده است.</div>
<?php else: ?>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>جایگاه</th>
                <th>نام تخصص</th>
                <th>توضیحات</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($specialties as $spec): ?>
            <tr>
                <td class="text-center">
                    <?php if ($spec['sort_order'] > 1): ?>
                        <a href="?target=manage-specialties&move_up=<?= $spec['id'] ?>" class="btn btn-sm btn-outline-secondary" title="بالا">
                            ⬆️
                        </a>
                    <?php endif; ?>
                    <?php if ($spec['sort_order'] < count($specialties)): ?>
                        <a href="?target=manage-specialties&move_down=<?= $spec['id'] ?>" class="btn btn-sm btn-outline-secondary" title="پایین">
                            ⬇️
                        </a>
                    <?php endif; ?>
                    <br><small class="text-muted">#<?= $spec['sort_order'] ?></small>
                </td>
                <td><strong><?= htmlspecialchars($spec['name']) ?></strong></td>
                <td><?= htmlspecialchars($spec['description'] ?? '—') ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editSpecialtyModal<?= $spec['id'] ?>">
                        ✏️ ویرایش
                    </button>
                    <a href="?target=manage-specialties&delete_id=<?= $spec['id'] ?>" 
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('⚠️ آیا از حذف تخصص «<?= htmlspecialchars($spec['name']) ?>» مطمئن هستید؟')">
                        ❌ حذف
                    </a>
                </td>
            </tr>

            <!-- Modal ویرایش -->
            <div class="modal fade" id="editSpecialtyModal<?= $spec['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">ویرایش: <?= htmlspecialchars($spec['name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="specialty_id" value="<?= $spec['id'] ?>">
                            <input type="hidden" name="action" value="edit_specialty">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>نام تخصص</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($spec['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>توضیحات</label>
                                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($spec['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                <button type="submit" class="btn btn-primary">💾 ذخیره</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Modal افزودن -->
<div class="modal fade" id="addSpecialtyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ ثبت تخصص جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_specialty">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>نام تخصص</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>توضیحات (اختیاری)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">✅ ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>