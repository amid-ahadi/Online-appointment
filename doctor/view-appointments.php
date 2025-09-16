<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isDoctor()) {
    redirect('../index.php');
}

// پیدا کردن doctor_id
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();
if (!$doctor) {
    flash('شما به عنوان پزشک ثبت نشده‌اید.', 'danger');
    redirect('../dashboard.php');
}
$doctor_id = $doctor['id'];

// پردازش تغییر وضعیت نوبت
if ($_POST && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    if ($stmt->execute([$_POST['status'], $_POST['appointment_id'], $doctor_id])) {
        flash('وضعیت نوبت با موفقیت به‌روزرسانی شد.', 'success');
    } else {
        flash('خطا در به‌روزرسانی وضعیت نوبت.', 'danger');
    }
}

// دریافت لیست نوبت‌ها
$stmt = $pdo->prepare("
    SELECT a.*, u.fullname AS patient_name, u.phone
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();

// تابع تبدیل وضعیت به فارسی
function getStatusLabel($status) {
    $labels = [
        'pending' => 'در انتظار تأیید',
        'confirmed' => 'تأیید شده',
        'cancelled' => 'لغو شده',
        'completed' => 'انجام شده'
    ];
    return $labels[$status] ?? $status;
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'secondary'
    ];
    return $badges[$status] ?? 'secondary';
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h3>📋 نوبت‌های من</h3>

            <?php if (empty($appointments)): ?>
                <div class="alert alert-info">شما هیچ نوبتی ندارید.</div>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>بیمار</th>
                            <th>تلفن</th>
                            <th>تاریخ</th>
                            <th>ساعت</th>
                            <th>وضعیت</th>
                            <th>یادداشت بیمار</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><?= htmlspecialchars($appt['patient_name']) ?></td>
                            <td><?= htmlspecialchars($appt['phone']) ?></td>
<td>
    <?php 
    $date = $appt['appointment_date'];
    if ($date && $date != '0000-00-00') {
        echo jdate('Y/m/d', strtotime($date));
    } else {
        echo '<span class="text-danger">—</span>';
    }
    ?>
</td>
                            <td><?= substr($appt['appointment_time'], 0, 5) ?></td>
                            <td>
                                <span class="badge bg-<?= getStatusBadge($appt['status']) ?>">
                                    <?= getStatusLabel($appt['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($appt['notes'] ?? '') ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                                    <select name="status" class="form-select d-inline w-auto" onchange="this.form.submit()">
                                        <option value="pending" <?= $appt['status']=='pending'?'selected':'' ?>>در انتظار</option>
                                        <option value="confirmed" <?= $appt['status']=='confirmed'?'selected':'' ?>>تأیید</option>
                                        <option value="cancelled" <?= $appt['status']=='cancelled'?'selected':'' ?>>لغو</option>
                                        <option value="completed" <?= $appt['status']=='completed'?'selected':'' ?>>انجام شد</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>