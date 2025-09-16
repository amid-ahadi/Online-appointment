<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isAdmin()) {
    echo '<div class="alert alert-danger">دسترسی غیرمجاز!</div>';
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM doctors");
$totalDoctors = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'confirmed'");
$totalAppointments = $stmt->fetch()['total'];
?>

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
