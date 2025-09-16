<?php
if (!defined('DASHBOARD_ACCESS')) {
    die('دسترسی غیرمجاز!');
}

// پردازش حذف کاربر
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt->execute([$_GET['delete_id']])) {
        flash('کاربر با موفقیت حذف شد.', 'success');
    } else {
        flash('خطا در حذف کاربر.', 'danger');
    }
    redirect('dashboard.php?target=manage-users');
}

// تنظیمات صفحه‌بندی
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// دریافت تعداد کل کاربران
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];
$total_pages = ceil($total_users / $limit);

// لیست کاربران با LIMIT و OFFSET
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<h3>👥 مدیریت کاربران</h3>
<?php displayFlash(); ?>

<!-- دکمه ثبت کاربر جدید -->
<button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
    ➕ ثبت کاربر جدید
</button>

<?php if (empty($users)): ?>
    <div class="alert alert-info">هیچ کاربری ثبت نشده است.</div>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>نام کامل</th>
                <th>ایمیل</th>
                <th>نقش</th>
                <th>تاریخ ثبت‌نام</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $index => $user): ?>
            <tr>
                <td><?= (($page - 1) * $limit) + $index + 1 ?></td>
                <td><?= htmlspecialchars($user['fullname']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'doctor' ? 'success' : 'info') ?>">
                        <?= $user['role'] === 'admin' ? 'ادمین' : ($user['role'] === 'doctor' ? 'پزشک' : 'بیمار') ?>
                    </span>
                </td>
                <td><?= jdate('Y/m/d H:i', strtotime($user['created_at'])) ?></td>
                <td>
                    <?php if ($user['role'] !== 'admin' || $user['id'] == $_SESSION['user_id']): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#changePassModal<?= $user['id'] ?>">
                            🔐 رمز
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#changeRoleModal<?= $user['id'] ?>">
                            ✏️ نقش
                        </button>
                        <?php if ($user['role'] !== 'admin'): ?>
                            <a href="?target=manage-users&delete_id=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('⚠️ آیا از حذف کاربر «<?= htmlspecialchars($user['fullname']) ?>» مطمئن هستید؟')">
                                ❌ حذف
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">🔒 فقط خود کاربر می‌تواند ویرایش کند</span>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Modal تغییر رمز -->
            <div class="modal fade" id="changePassModal<?= $user['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">تغییر رمز: <?= htmlspecialchars($user['fullname']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="action" value="change_password">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>رمز جدید</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label>تکرار رمز</label>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
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

            <!-- Modal تغییر نقش -->
            <div class="modal fade" id="changeRoleModal<?= $user['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">تغییر نقش: <?= htmlspecialchars($user['fullname']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="action" value="change_role">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>نقش جدید</label>
                                    <select name="new_role" class="form-select" required>
                                        <option value="patient" <?= $user['role'] == 'patient' ? 'selected' : '' ?>>بیمار</option>
                                        <option value="doctor" <?= $user['role'] == 'doctor' ? 'selected' : '' ?>>پزشک</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>ادمین</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                                <button type="submit" class="btn btn-warning">💾 ذخیره</button>
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
        <nav aria-label="صفحه‌بندی کاربران">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?target=manage-users&page=<?= $page - 1 ?>" aria-label="قبلی">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?target=manage-users&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?target=manage-users&page=<?= $page + 1 ?>" aria-label="بعدی">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal افزودن کاربر جدید -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ ثبت کاربر جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>نام و نام خانوادگی</label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>ایمیل</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>رمز عبور</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label>شماره موبایل (اختیاری)</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>نقش کاربر</label>
                        <select name="role" class="form-select" required>
                            <option value="patient">بیمار</option>
                            <option value="doctor">پزشک</option>
                            <option value="admin">ادمین</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">✅ ثبت کاربر</button>
                </div>
            </form>
        </div>
    </div>
</div>