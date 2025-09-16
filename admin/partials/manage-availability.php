<?php
if (!defined('DASHBOARD_ACCESS')) {
    die('دسترسی غیرمجاز!');
}

// پردازش تنظیمات کلی درمانگاه
if ($_POST && isAdmin() && ($_POST['action'] ?? '') === 'update_clinic_settings') {
    $opening_time = $_POST['opening_time'] ?? '08:00';
    $closing_time = $_POST['closing_time'] ?? '20:00';
    $slot_duration = intval($_POST['slot_duration'] ?? 30);
    $is_clinic_active = isset($_POST['is_clinic_active']) ? 1 : 0;

    // ذخیره در دیتابیس
    $settings = [
        'opening_time' => $opening_time . ':00',
        'closing_time' => $closing_time . ':00',
        'slot_duration' => $slot_duration,
        'is_clinic_active' => $is_clinic_active
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("REPLACE INTO clinic_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }

    flash('✅ تنظیمات ساعت کاری درمانگاه با موفقیت به‌روزرسانی شد.', 'success');
    redirect('dashboard.php?target=manage-availability');
}

// پردازش افزودن روز تعطیلی
if ($_POST && isAdmin() && ($_POST['action'] ?? '') === 'add_holiday') {
    $holiday_date = $_POST['holiday_date'] ?? '';
    $reason = sanitizeInput($_POST['reason'] ?? '');

    if (empty($holiday_date)) {
        flash('لطفاً تاریخ تعطیلی را انتخاب کنید.', 'danger');
    } else {
        // چک کن قبلاً ثبت نشده باشه
        $stmt = $pdo->prepare("SELECT id FROM clinic_holidays WHERE holiday_date = ?");
        $stmt->execute([$holiday_date]);
        if ($stmt->fetch()) {
            flash('⛔ این تاریخ قبلاً به عنوان تعطیلی ثبت شده است.', 'warning');
        } else {
            $stmt = $pdo->prepare("INSERT INTO clinic_holidays (holiday_date, reason) VALUES (?, ?)");
            if ($stmt->execute([$holiday_date, $reason])) {
                flash('✅ روز تعطیلی با موفقیت ثبت شد.', 'success');
            } else {
                flash('❌ خطا در ثبت روز تعطیلی.', 'danger');
            }
        }
    }
    redirect('dashboard.php?target=manage-availability');
}

// پردازش حذف روز تعطیلی
if (isset($_GET['delete_holiday']) && isAdmin()) {
    $holiday_id = intval($_GET['delete_holiday']);
    $stmt = $pdo->prepare("DELETE FROM clinic_holidays WHERE id = ?");
    if ($stmt->execute([$holiday_id])) {
        flash('✅ روز تعطیلی با موفقیت حذف شد.', 'success');
    } else {
        flash('❌ خطا در حذف روز تعطیلی.', 'danger');
    }
    redirect('dashboard.php?target=manage-availability');
}

// دریافت تنظیمات کلی
$stmt = $pdo->query("SELECT setting_key, setting_value FROM clinic_settings WHERE setting_key IN ('opening_time','closing_time','slot_duration','is_clinic_active')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// دریافت لیست روزهای تعطیلی
$stmt = $pdo->query("SELECT * FROM clinic_holidays ORDER BY holiday_date DESC");
$holidays = $stmt->fetchAll();
?>

<h3>⏰ مدیریت ساعت کاری و تعطیلات درمانگاه</h3>
<?php displayFlash(); ?>

<!-- بخش تنظیمات کلی -->
<div class="card mb-4">
    <div class="card-header">
        🕒 تنظیمات کلی ساعت کاری درمانگاه
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="update_clinic_settings">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>ساعت شروع کار</label>
                    <input type="time" name="opening_time" class="form-control" 
                           value="<?= substr($settings['opening_time'] ?? '08:00:00', 0, 5) ?>" required>
                </div>
                <div class="col-md-3">
                    <label>ساعت پایان کار</label>
                    <input type="time" name="closing_time" class="form-control" 
                           value="<?= substr($settings['closing_time'] ?? '20:00:00', 0, 5) ?>" required>
                </div>
                <div class="col-md-3">
                    <label>مدت زمان هر نوبت (دقیقه)</label>
                    <input type="number" name="slot_duration" class="form-control" 
                           value="<?= $settings['slot_duration'] ?? 30 ?>" min="10" max="120" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_clinic_active" class="form-check-input" id="is_clinic_active" 
                               <?= ($settings['is_clinic_active'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_clinic_active">درمانگاه فعال باشد</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">💾 ذخیره تنظیمات کلی</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- بخش افزودن روز تعطیلی -->
<div class="card mb-4">
    <div class="card-header">
        📅 افزودن روز تعطیلی
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="add_holiday">
            <div class="col-md-4">
                   <label>تاریخ تعطیلی</label>
                  <!-- فیلد نمایشی (شمسی) -->
                  <input type="text" id="holiday_date_display" class="form-control" placeholder="تاريخ تعطيلات را وارد كنيد" readonly>
                  <input type="hidden" name="holiday_date" id="holiday_date">
            </div>
            <div class="col-md-6">
                <label>دلیل تعطیلی (اختیاری)</label>
                <input type="text" name="reason" class="form-control" placeholder="مثلاً: تعطیلات نوروز">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-danger w-100">➕ افزودن تعطیلی</button>
            </div>
        </form>
    </div>
</div>

<!-- لیست روزهای تعطیلی -->
<?php if (!empty($holidays)): ?>
    <div class="card">
        <div class="card-header">
            📋 لیست روزهای تعطیلی
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ</th>
                        <th>دلیل</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $holiday): ?>
                    <tr>
                        <td><?= jdate('Y/m/d', strtotime($holiday['holiday_date'])) ?></td>
                        <td><?= htmlspecialchars($holiday['reason'] ?? '—') ?></td>
                        <td>
                            <a href="?target=manage-availability&delete_holiday=<?= $holiday['id'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('⚠️ آیا از حذف این روز تعطیلی مطمئن هستید؟')">
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
    <div class="alert alert-info">هیچ روز تعطیلی ثبت نشده است.</div>
<?php endif; ?>
<script>
$(document).ready(function() {
    $('#holiday_date_display').pDatepicker({
        calendarType: 'persian',
        toolbox: {
            calendarSwitch: {
                enabled: true
            }
        },
        text: {
            'nextMonth': 'ماه بعد',
            'previousMonth': 'ماه قبل',
            'selectMonth': 'انتخاب ماه',
            'selectYear': 'انتخاب سال',
            'submit': 'تأیید',
            'cancel': 'انصراف'
        },
        // مهم: ذخیره مقدار میلادی در فیلد مخفی
        altField: '#holiday_date',
        altFormat: 'YYYY-MM-DD',
        onSelect: function (unixDate) {
            if (unixDate) {
                var d = new Date(unixDate);
                var formatted = d.getFullYear() + '-' + 
                                String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(d.getDate()).padStart(2, '0');
                $('#holiday_date').val(formatted);
            }
        }
    });
});
</script>