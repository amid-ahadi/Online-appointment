<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// تعریف ثابت برای امنیت include
define('DASHBOARD_ACCESS', true);


if ($_POST && isAdmin()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            flash('رمز عبور و تکرار آن یکسان نیستند.', 'danger');
        } elseif (strlen($new_password) < 6) {
            flash('رمز عبور باید حداقل ۶ کاراکتر باشد.', 'warning');
        } else {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $user_id])) {
                flash('رمز عبور کاربر با موفقیت تغییر کرد.', 'success');
            } else {
                flash('خطا در تغییر رمز عبور.', 'danger');
            }
        }
        redirect('dashboard.php?target=manage-users');
    } 
    elseif ($action === 'change_role') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_role = $_POST['new_role'] ?? '';

        if (!in_array($new_role, ['patient', 'doctor', 'admin'])) {
            flash('نقش انتخاب شده معتبر نیست.', 'danger');
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$new_role, $user_id])) {
                flash('نقش کاربر با موفقیت تغییر کرد.', 'success');
            } else {
                flash('خطا در تغییر نقش.', 'danger');
            }
        }
        redirect('dashboard.php?target=manage-users');
    }
}
// ➕ پردازش ثبت کاربر جدید
if ($_POST && isAdmin() && ($_POST['action'] ?? '') === 'add_user') {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'patient';

    if (empty($fullname) || empty($email) || empty($password)) {
        flash('لطفاً تمام فیلدهای اجباری را پر کنید.', 'danger');
    } elseif (strlen($password) < 6) {
        flash('رمز عبور باید حداقل ۶ کاراکتر باشد.', 'warning');
    } else {
        // چک تکراری بودن ایمیل
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            flash('⛔ این ایمیل قبلاً ثبت شده است.', 'warning');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $email, $hashedPassword, $phone, $role])) {
                flash('✅ کاربر جدید با موفقیت ثبت شد.', 'success');
            } else {
                flash('❌ خطا در ثبت کاربر.', 'danger');
            }
        }
    }
    redirect('dashboard.php?target=manage-users');
}

$target = $_GET['target'] ?? 'dashboard';

// آمارها فقط برای داشبورد
$totalUsers = $totalDoctors = $totalAppointments = 0;
if ($target === 'dashboard') {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM doctors");
    $totalDoctors = $stmt->fetch()['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'confirmed'");
    $totalAppointments = $stmt->fetch()['total'];
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9" id="main-content">
            <?php if ($target === 'dashboard'): ?>
                <h2>📊 داشبورد ادمین</h2>
                <div class="row g-4 mt-3">
                    <div class="col-md-4">
                        <div class="card text-bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">👥 کاربران</h5>
                                <h2 class="card-text"><?= $totalUsers ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-bg-success">
                            <div class="card-body">
                                <h5 class="card-title">👨‍⚕️ پزشکان</h5>
                                <h2 class="card-text"><?= $totalDoctors ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">✅ نوبت‌های تأیید شده</h5>
                                <h2 class="card-text"><?= $totalAppointments ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($target === 'manage-users'): ?>
                <?php include 'partials/manage-users.php'; ?>

            <?php elseif ($target === 'manage-specialties'): ?>
                <?php include 'partials/manage-specialties.php'; ?>

            <?php elseif ($target === 'manage-doctors'): ?>
                 <?php include 'partials/manage-doctors.php'; ?>>

            <?php elseif ($target === 'manage-availability'): ?>
                 <?php include 'partials/manage-availability.php'; ?>
 
            <?php else: ?>
                <div class="alert alert-warning">صفحه مورد نظر یافت نشد.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#admin-menu a').click(function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        window.history.pushState({}, '', '?target=' + target);
        location.reload();
    });
});
</script>

<?php include '../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
