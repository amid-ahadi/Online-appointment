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

// پردازش افزودن/ویرایش ساعت کاری
if ($_POST && ($_POST['action'] ?? '') === 'save_availability') {
    $day_of_week = $_POST['day_of_week'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $slot_duration = intval($_POST['slot_duration'] ?? 30);
    $availability_id = intval($_POST['availability_id'] ?? 0);

    if (empty($day_of_week) || empty($start_time) || empty($end_time)) {
        flash('لطفاً تمام فیلدها را پر کنید.', 'danger');
    } elseif ($start_time >= $end_time) {
        flash('ساعت شروع باید قبل از ساعت پایان باشد.', 'danger');
    } else {
        try {
            if ($availability_id > 0) {
                // ویرایش
                $stmt = $pdo->prepare("UPDATE availability SET day_of_week = ?, start_time = ?, end_time = ?, slot_duration = ? WHERE id = ? AND doctor_id = ?");
                $result = $stmt->execute([$day_of_week, $start_time, $end_time, $slot_duration, $availability_id, $doctor_id]);
            } else {
                // افزودن
                $stmt = $pdo->prepare("INSERT INTO availability (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$doctor_id, $day_of_week, $start_time, $end_time, $slot_duration]);
            }

            if ($result) {
                flash('ساعت کاری با موفقیت ذخیره شد.', 'success');
            } else {
                flash('خطا در ذخیره ساعت کاری.', 'danger');
            }
        } catch (Exception $e) {
            flash('خطا: ' . $e->getMessage(), 'danger');
        }
    }
    // به جای redirect — با JavaScript رفرش می‌کنیم
    echo "<script>window.location.href = 'manage-availability.php';</script>";
    exit;
}

// پردازش حذف ساعت کاری
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM availability WHERE id = ? AND doctor_id = ?");
    if ($stmt->execute([$_GET['delete_id'], $doctor_id])) {
        flash('ساعت کاری با موفقیت حذف شد.', 'success');
    } else {
        flash('خطا در حذف ساعت کاری.', 'danger');
    }
    echo "<script>window.location.href = 'manage-availability.php';</script>";
    exit;
}

// دریافت لیست ساعت‌های کاری فعلی
$stmt = $pdo->prepare("SELECT * FROM availability WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'sat','sun','mon','tue','wed','thu','fri'), start_time");
$stmt->execute([$doctor_id]);
$availabilities = $stmt->fetchAll();

// تابع تبدیل روز هفته به فارسی
function getDayLabel($day) {
    $days = [
        'sat' => 'شنبه',
        'sun' => 'یکشنبه',
        'mon' => 'دوشنبه',
        'tue' => 'سه‌شنبه',
        'wed' => 'چهارشنبه',
        'thu' => 'پنج‌شنبه',
        'fri' => 'جمعه'
    ];
    return $days[$day] ?? $day;
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h3>⏰ مدیریت ساعت کاری من</h3>
            <?php displayFlash(); ?>

            <!-- فرم افزودن/ویرایش ساعت کاری -->
            <div class="card mb-4">
                <div class="card-header">
                    <?= (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) ? 'ویرایش ساعت کاری' : 'افزودن ساعت کاری جدید' ?>
                </div>
                <div class="card-body">
                    <?php
                    $edit_data = null;
                    if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
                        foreach ($availabilities as $av) {
                            if ($av['id'] == $_GET['edit_id']) {
                                $edit_data = $av;
                                break;
                            }
                        }
                    }
                    ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_availability">
                        <input type="hidden" name="availability_id" value="<?= $edit_data['id'] ?? 0 ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label>روز هفته</label>
                                <select name="day_of_week" class="form-select" required>
                                    <option value="">— انتخاب کنید —</option>
                                    <option value="sat" <?= ($edit_data['day_of_week'] ?? '') == 'sat' ? 'selected' : '' ?>>شنبه</option>
                                    <option value="sun" <?= ($edit_data['day_of_week'] ?? '') == 'sun' ? 'selected' : '' ?>>یکشنبه</option>
                                    <option value="mon" <?= ($edit_data['day_of_week'] ?? '') == 'mon' ? 'selected' : '' ?>>دوشنبه</option>
                                    <option value="tue" <?= ($edit_data['day_of_week'] ?? '') == 'tue' ? 'selected' : '' ?>>سه‌شنبه</option>
                                    <option value="wed" <?= ($edit_data['day_of_week'] ?? '') == 'wed' ? 'selected' : '' ?>>چهارشنبه</option>
                                    <option value="thu" <?= ($edit_data['day_of_week'] ?? '') == 'thu' ? 'selected' : '' ?>>پنج‌شنبه</option>
                                    <option value="fri" <?= ($edit_data['day_of_week'] ?? '') == 'fri' ? 'selected' : '' ?>>جمعه</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>ساعت شروع</label>
                                <input type="time" name="start_time" class="form-control" 
                                       value="<?= $edit_data['start_time'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>ساعت پایان</label>
                                <input type="time" name="end_time" class="form-control" 
                                       value="<?= $edit_data['end_time'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>مدت زمان هر نوبت (دقیقه)</label>
                                <input type="number" name="slot_duration" class="form-control" 
                                       value="<?= $edit_data['slot_duration'] ?? 30 ?>" min="10" max="120" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <?= $edit_data ? '💾 ویرایش ساعت کاری' : '➕ افزودن ساعت کاری' ?>
                                </button>
                                <?php if ($edit_data): ?>
                                    <a href="manage-availability.php" class="btn btn-secondary">انصراف</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- لیست ساعت‌های کاری -->
            <?php if (!empty($availabilities)): ?>
                <div class="card">
                    <div class="card-header">
                        📋 لیست ساعت‌های کاری فعلی
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>روز هفته</th>
                                    <th>ساعت شروع</th>
                                    <th>ساعت پایان</th>
                                    <th>مدت نوبت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availabilities as $av): ?>
                                <tr>
                                    <td><?= getDayLabel($av['day_of_week']) ?></td>
                                    <td><?= substr($av['start_time'], 0, 5) ?></td>
                                    <td><?= substr($av['end_time'], 0, 5) ?></td>
                                    <td><?= $av['slot_duration'] ?> دقیقه</td>
                                    <td>
                                        <a href="?edit_id=<?= $av['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ ویرایش</a>
                                        <a href="?delete_id=<?= $av['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('⚠️ آیا از حذف این ساعت کاری مطمئن هستید؟')">
                                            ❌ حذف
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">شما هیچ ساعت کاری تعریف نکرده‌اید. لطفاً ساعت کاری خود را اضافه کنید.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>