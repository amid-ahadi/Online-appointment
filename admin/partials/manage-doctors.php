<?php
if (!defined('DASHBOARD_ACCESS')) {
    die('دسترسی غیرمجاز!');
}

// پردازش حذف پزشک
if (isset($_GET['delete_id']) && isAdmin()) {
    $doctor_id = intval($_GET['delete_id']);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status IN ('pending','confirmed')");
    $stmt->execute([$doctor_id]);
    $hasAppointments = $stmt->fetchColumn();

    if ($hasAppointments > 0) {
        flash('⛔ امکان حذف پزشک وجود ندارد — زیرا ' . $hasAppointments . ' نوبت فعال/در انتظار دارد.', 'warning');
    } else {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        if ($stmt->execute([$doctor_id])) {
            flash('✅ پزشک با موفقیت حذف شد.', 'success');
        } else {
            flash('❌ خطا در حذف پزشک.', 'danger');
        }
    }
    redirect('dashboard.php?target=manage-doctors');
}

// پردازش تغییر وضعیت فعال/غیرفعال
if (isset($_GET['toggle_active']) && isAdmin()) {
    $doctor_id = intval($_GET['toggle_active']);
    $stmt = $pdo->prepare("UPDATE doctors SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$doctor_id])) {
        flash('✅ وضعیت پزشک با موفقیت تغییر کرد.', 'success');
    } else {
        flash('❌ خطا در تغییر وضعیت.', 'danger');
    }
    redirect('dashboard.php?target=manage-doctors');
}

// پردازش افزودن پزشک جدید
if ($_POST && isAdmin() && ($_POST['action'] ?? '') === 'add_doctor') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $specialty_id = intval($_POST['specialty_id'] ?? 0);
    $medical_license = sanitizeInput($_POST['medical_license'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');

    if ($user_id <= 0 || $specialty_id <= 0) {
        flash('لطفاً کاربر و تخصص را انتخاب کنید.', 'danger');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialty_id, medical_license, bio, is_active) VALUES (?, ?, ?, ?, 1)");
            if ($stmt->execute([$user_id, $specialty_id, $medical_license, $bio])) {
                flash('✅ پزشک جدید با موفقیت اضافه شد.', 'success');
            } else {
                flash('❌ خطا در افزودن پزشک.', 'danger');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                flash('⛔ این کاربر قبلاً به عنوان پزشک ثبت شده است.', 'warning');
            } else {
                flash('❌ خطا: ' . $e->getMessage(), 'danger');
            }
        }
    }
    redirect('dashboard.php?target=manage-doctors');
}

// پردازش ویرایش پزشک
if ($_POST && isAdmin() && ($_POST['action'] ?? '') === 'edit_doctor') {
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $specialty_id = intval($_POST['specialty_id'] ?? 0);
    $medical_license = sanitizeInput($_POST['medical_license'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($specialty_id <= 0) {
        flash('لطفاً تخصص را انتخاب کنید.', 'danger');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE doctors SET specialty_id = ?, medical_license = ?, bio = ?, is_active = ? WHERE id = ?");
            if ($stmt->execute([$specialty_id, $medical_license, $bio, $is_active, $doctor_id])) {
                flash('✅ اطلاعات پزشک با موفقیت به‌روزرسانی شد.', 'success');
            } else {
                flash('❌ خطا در به‌روزرسانی پزشک.', 'danger');
            }
        } catch (Exception $e) {
            flash('❌ خطا: ' . $e->getMessage(), 'danger');
        }
    }
    redirect('dashboard.php?target=manage-doctors');
}

// تنظیمات صفحه‌بندی
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// دریافت تعداد کل پزشکان
$stmt = $pdo->query("
    SELECT COUNT(*) as total 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id
");
$total_doctors = $stmt->fetch()['total'];
$total_pages = ceil($total_doctors / $limit);

// لیست پزشکان با LIMIT و OFFSET
$stmt = $pdo->prepare("
    SELECT d.*, u.fullname, u.email, s.name as specialty_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY u.fullname ASC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$doctors = $stmt->fetchAll();

// لیست کاربران با نقش پزشک (برای افزودن پزشک جدید)
$stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE role = 'doctor' ORDER BY fullname");
$stmt->execute();
$doctorUsers = $stmt->fetchAll();

// لیست تخصص‌ها
$stmt = $pdo->query("SELECT id, name FROM specialties ORDER BY name");
$specialtiesList = $stmt->fetchAll();
?>

<h3>👨‍⚕️ مدیریت پزشکان</h3>
<?php displayFlash(); ?>

<!-- دکمه افزودن پزشک جدید -->
<button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
    ➕ افزودن پزشک جدید
</button>

<?php if (empty($doctors)): ?>
    <div class="alert alert-info">هیچ پزشکی ثبت نشده است.</div>
<?php else: ?>
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>نام پزشک</th>
                <th>ایمیل</th>
                <th>تخصص</th>
                <th>پروانه</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doctors as $doc): ?>
            <tr>
                <td><strong><?= htmlspecialchars($doc['fullname']) ?></strong></td>
                <td><?= htmlspecialchars($doc['email']) ?></td>
                <td><?= htmlspecialchars($doc['specialty_name']) ?></td>
                <td><?= htmlspecialchars($doc['medical_license'] ?? '—') ?></td>
                <td>
                    <span class="badge bg-<?= $doc['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $doc['is_active'] ? 'فعال' : 'غیرفعال' ?>
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editDoctorModal<?= $doc['id'] ?>">
                        ✏️ ویرایش
                    </button>
                    <a href="?target=manage-doctors&toggle_active=<?= $doc['id'] ?>" 
                       class="btn btn-sm btn-outline-<?= $doc['is_active'] ? 'warning' : 'success' ?>"
                       onclick="return confirm('⚠️ آیا از تغییر وضعیت پزشک «<?= htmlspecialchars($doc['fullname']) ?>» مطمئن هستید؟')">
                        🔄 <?= $doc['is_active'] ? 'غیرفعال' : 'فعال' ?>
                    </a>
                    <a href="?target=manage-doctors&delete_id=<?= $doc['id'] ?>" 
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('⚠️ آیا از حذف پزشک «<?= htmlspecialchars($doc['fullname']) ?>» مطمئن هستید؟\n(اگر نوبت فعال داشته باشد، حذف نخواهد شد!)')">
                        ❌ حذف
                    </a>
                </td>
            </tr>

            <!-- Modal ویرایش پزشک -->
            <div class="modal fade" id="editDoctorModal<?= $doc['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">ویرایش پزشک: <?= htmlspecialchars($doc['fullname']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="doctor_id" value="<?= $doc['id'] ?>">
                            <input type="hidden" name="action" value="edit_doctor">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>تخصص</label>
                                    <select name="specialty_id" class="form-select" required>
                                        <option value="">— انتخاب کنید —</option>
                                        <?php foreach ($specialtiesList as $spec): ?>
                                            <option value="<?= $spec['id'] ?>" <?= $doc['specialty_id'] == $spec['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($spec['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>شماره پروانه پزشکی</label>
                                    <input type="text" name="medical_license" class="form-control" 
                                           value="<?= htmlspecialchars($doc['medical_license'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label>بیوگرافی/معرفی</label>
                                    <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doc['bio'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active<?= $doc['id'] ?>" <?= $doc['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active<?= $doc['id'] ?>">فعال باشد</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                <button type="submit" class="btn btn-primary">💾 ذخیره تغییرات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- صفحه‌بندی -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="صفحه‌بندی پزشکان">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?target=manage-doctors&page=<?= $page - 1 ?>" aria-label="قبلی">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?target=manage-doctors&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?target=manage-doctors&page=<?= $page + 1 ?>" aria-label="بعدی">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal افزودن پزشک جدید -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ افزودن پزشک جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_doctor">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>انتخاب کاربر (باید نقش "پزشک" داشته باشد)</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($doctorUsers as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>انتخاب تخصص</label>
                        <select name="specialty_id" class="form-select" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($specialtiesList as $spec): ?>
                                <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>شماره پروانه پزشکی (اختیاری)</label>
                        <input type="text" name="medical_license" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>بیوگرافی/معرفی (اختیاری)</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">✅ افزودن پزشک</button>
                </div>
            </form>
        </div>
    </div>
</div>