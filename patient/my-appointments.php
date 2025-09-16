<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isPatient()) {
    redirect('../index.php');
}

// پردازش لغو نوبت
// پردازش لغو نوبت
if (isset($_GET['cancel_id']) && is_numeric($_GET['cancel_id'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status = 'pending'");
    if ($stmt->execute([$_GET['cancel_id'], $_SESSION['user_id']])) {
        flash('نوبت شما با موفقیت لغو شد.', 'success');
    } else {
        flash('خطا در لغو نوبت. ممکن است نوبت قبلاً تأیید یا لغو شده باشد.', 'danger');
    }
    echo "<script>window.location.href = 'my-appointments.php';</script>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, u.fullname AS doctor_name, s.name AS specialty_name
    FROM appointments a
    JOIN doctors doc ON a.doctor_id = doc.id
    JOIN users u ON doc.user_id = u.id
    JOIN specialties s ON doc.specialty_id = s.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
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

<h3>📋 نوبت‌های من</h3>

<?php if (empty($appointments)): ?>
    <div class="alert alert-info">شما هیچ نوبتی ندارید.</div>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>پزشک</th>
                <th>تخصص</th>
                <th>تاریخ</th>
                <th>ساعت</th>
                <th>وضعیت</th>
                <th>یادداشت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appt): ?>
            <tr>
                <td><?= htmlspecialchars($appt['doctor_name']) ?></td>
                <td><?= htmlspecialchars($appt['specialty_name']) ?></td>
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
                    <?php if ($appt['status'] === 'pending'): ?>
                        <a href="?cancel_id=<?= $appt['id'] ?>" 
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('⚠️ آیا از لغو این نوبت مطمئن هستید؟')">
                            ❌ لغو نوبت
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>