<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isDoctor()) {
    redirect('../index.php');
}

// پیدا کردن doctor_id از user_id
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();
if (!$doctor) {
    flash('شما به عنوان پزشک ثبت نشده‌اید.', 'danger');
    redirect('../dashboard.php');
}
$doctor_id = $doctor['id'];

// آمارهای پزشک
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$totalAppointments = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as confirmed FROM appointments WHERE doctor_id = ? AND status = 'confirmed'");
$stmt->execute([$doctor_id]);
$confirmedAppointments = $stmt->fetch()['confirmed'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM appointments WHERE doctor_id = ? AND status = 'pending'");
$stmt->execute([$doctor_id]);
$pendingAppointments = $stmt->fetch()['pending'];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- منوی سمت راست -->
        <div class="col-md-3">
            <div class="position-sticky" style="top: 2rem;">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action active">
                        📊 داشبورد
                    </a>
                    <a href="view-appointments.php" class="list-group-item list-group-item-action">
                        📋 نوبت‌های من
                    </a>
                    <a href="manage-availability.php" class="list-group-item list-group-item-action">
                        ⏰ ساعت کاری من
                    </a>
                </div>
            </div>
        </div>

        <!-- محتوا -->
        <div class="col-md-9">
            <h2>👨‍⚕️ داشبورد پزشک: <?= $_SESSION['fullname'] ?></h2>

            <div class="row g-4 mt-3">
                <div class="col-md-4">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">مجموع نوبت‌ها</h5>
                            <h2 class="card-text"><?= $totalAppointments ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <h5 class="card-title">نوبت‌های تأیید شده</h5>
                            <h2 class="card-text"><?= $confirmedAppointments ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">نوبت‌های در انتظار</h5>
                            <h2 class="card-text"><?= $pendingAppointments ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h5>👋 به داشبورد خود خوش آمدید!</h5>
                <p>در اینجا می‌توانید نوبت‌های خود را مدیریت کنید، ساعت کاری خود را تنظیم کنید و وضعیت نوبت‌ها را تغییر دهید.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
