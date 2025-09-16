<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_POST) {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $role = 'patient'; // فقط بیمار می‌تونه ثبت‌نام کنه

    // چک تکراری بودن ایمیل
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash('این ایمیل قبلاً ثبت شده است.', 'warning');
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$fullname, $email, $hashedPassword, $phone, $role])) {
            flash('ثبت‌نام با موفقیت انجام شد. اکنون وارد شوید.', 'success');
            redirect('index.php');
        } else {
            flash('خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.', 'danger');
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">ثبت‌نام بیمار جدید</div>
            <div class="card-body">
                <form method="POST">
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
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>شماره موبایل</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success w-100">ثبت‌نام</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="index.php">قبلاً ثبت‌نام کرده‌اید؟ وارد شوید</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>