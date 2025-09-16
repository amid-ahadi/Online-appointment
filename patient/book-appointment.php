<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isPatient()) {
    redirect('../index.php');
}

// دریافت تنظیمات درمانگاه (برای چک تعطیلات)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM clinic_settings WHERE setting_key IN ('is_clinic_active')");
$clinic_settings = [];
while ($row = $stmt->fetch()) {
    $clinic_settings[$row['setting_key']] = $row['setting_value'];
}

$is_clinic_active = ($clinic_settings['is_clinic_active'] ?? 1) == 1;

if (!$is_clinic_active) {
    flash('⛔ درمانگاه در حال حاضر غیرفعال است. لطفاً بعداً مراجعه کنید.', 'danger');
    redirect('my-appointments.php');
}

// دریافت لیست تخصص‌ها
$stmt = $pdo->query("SELECT id, name FROM specialties ORDER BY sort_order ASC, name ASC");
$specialties = $stmt->fetchAll();

$selectedSpecialty = $_GET['specialty_id'] ?? null;
$doctors = [];

if ($selectedSpecialty) {
    // دریافت پزشکان فعال این تخصص
    $stmt = $pdo->prepare("
        SELECT d.id, u.fullname, dp.photo_url, dp.display_bio, dp.banner_text, dp.bg_color, dp.text_color
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        LEFT JOIN doctor_profiles dp ON d.id = dp.doctor_id
        WHERE d.specialty_id = ? AND d.is_active = 1
        ORDER BY dp.sort_order ASC, u.fullname ASC
    ");
    $stmt->execute([$selectedSpecialty]);
    $doctors = $stmt->fetchAll();
}

$selectedDoctor = $_GET['doctor_id'] ?? null;
$availability = [];
$selectedDay = $_GET['day'] ?? null;

if ($selectedDoctor) {
    // دریافت روزهای کاری پزشک
    $stmt = $pdo->prepare("SELECT DISTINCT day_of_week FROM availability WHERE doctor_id = ?");
    $stmt->execute([$selectedDoctor]);
    $days = $stmt->fetchAll();

    if ($selectedDay) {
        // دریافت بازه زمانی پزشک در این روز
        $stmt = $pdo->prepare("
            SELECT start_time, end_time, slot_duration 
            FROM availability 
            WHERE doctor_id = ? AND day_of_week = ?
        ");
        $stmt->execute([$selectedDoctor, $selectedDay]);
        $avail = $stmt->fetch();

        if ($avail) {
            $start = new DateTime($avail['start_time']);
            $end = new DateTime($avail['end_time']);
            $interval = new DateInterval('PT' . $avail['slot_duration'] . 'M');
            $slots = [];

            while ($start < $end) {
                $slotTime = $start->format('H:i:s');
                // چک کن نوبت قبلاً رزرو نشده باشه
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM appointments 
                    WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
                ");
                $stmtCheck->execute([$selectedDoctor, $_GET['date'] ?? '', $slotTime]);
                $isBooked = $stmtCheck->fetchColumn();

                if (!$isBooked) {
                    $slots[] = $slotTime;
                }
                $start->add($interval);
            }
            $availability = $slots;
        }
    }
}

if ($_POST) {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // چک کن تاریخ انتخاب شده تعطیلی نباشه
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clinic_holidays WHERE holiday_date = ?");
    $stmt->execute([$date]);
    $isHoliday = $stmt->fetchColumn();

    if ($isHoliday > 0) {
        flash('⛔ این روز تعطیلی است و امکان رزرو نوبت وجود ندارد.', 'danger');
        echo "<script>window.location.href = 'book-appointment.php?specialty_id=" . $_GET['specialty_id'] . "&doctor_id=" . $doctor_id . "&day=" . $_GET['day'] . "';</script>";
        exit;
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
        ");
        $stmt->execute([$doctor_id, $date, $time]);
        if ($stmt->fetchColumn() > 0) {
            flash('این زمان قبلاً رزرو شده است.', 'warning');
            echo "<script>window.location.href = 'book-appointment.php?specialty_id=" . $_GET['specialty_id'] . "&doctor_id=" . $doctor_id . "&day=" . $_GET['day'] . "';</script>";
            exit;
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) 
                VALUES (?, ?, ?, ?, 'pending')
            ");
            if ($stmt->execute([$patient_id, $doctor_id, $date, $time])) {
                flash('نوبت شما با موفقیت رزرو شد. منتظر تأیید پزشک باشید.', 'success');
                echo "<script>window.location.href = 'my-appointments.php';</script>";
                exit;
            } else {
                flash('خطا در رزرو نوبت.', 'danger');
                echo "<script>window.location.href = 'book-appointment.php?specialty_id=" . $_GET['specialty_id'] . "&doctor_id=" . $doctor_id . "&day=" . $_GET['day'] . "';</script>";
                exit;
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
.doctor-card {
    border-radius: 12px;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.doctor-photo {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}
.banner {
    padding: 4px 8px;
    font-size: 0.85rem;
    font-weight: bold;
    border-radius: 0 0 12px 12px;
}
</style>

<h3 class="mb-4">🎯 رزرو نوبت جدید</h3>

<!-- انتخاب تخصص -->
<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <label class="form-label">انتخاب تخصص</label>
            <select name="specialty_id" class="form-select" onchange="this.form.submit()">
                <option value="">— لطفاً انتخاب کنید —</option>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?= $spec['id'] ?>" <?= $selectedSpecialty == $spec['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($spec['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</form>

<?php if ($selectedSpecialty && $doctors): ?>
    <h4 class="mt-5 mb-3">👨‍⚕️ پزشکان تخصص «<?= htmlspecialchars($specialties[array_search($selectedSpecialty, array_column($specialties, 'id'))]['name'] ?? '') ?>»</h4>
    
    <div class="row g-4">
        <?php foreach ($doctors as $doc): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card doctor-card" 
                     style="background-color: <?= $doc['bg_color'] ?>; color: <?= $doc['text_color'] ?>;">
                    
                    <?php if ($doc['photo_url']): ?>
                        <img src="<?= htmlspecialchars($doc['photo_url']) ?>" 
                             class="doctor-photo" 
                             alt="<?= htmlspecialchars($doc['fullname']) ?>" 
                             onerror="this.src='https://via.placeholder.com/300x200?text=No+Photo'">
                    <?php else: ?>
                        <div class="doctor-photo d-flex align-items-center justify-content-center bg-light">
                            <span class="display-4">👨‍⚕️</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2"><?= htmlspecialchars($doc['fullname']) ?></h5>
                        
                        <?php if ($doc['banner_text']): ?>
                            <div class="banner mb-2" style="background-color: <?= $doc['text_color'] ?>; color: <?= $doc['bg_color'] ?>;">
                                <?= htmlspecialchars($doc['banner_text']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($doc['display_bio']): ?>
                            <p class="card-text small flex-grow-1"><?= htmlspecialchars($doc['display_bio']) ?></p>
                        <?php endif; ?>
                        
                        <a href="?specialty_id=<?= $selectedSpecialty ?>&doctor_id=<?= $doc['id'] ?>" 
                           class="btn btn-sm btn-outline-dark mt-auto">
                           انتخاب این پزشک →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($selectedDoctor): ?>
        <!-- انتخاب روز -->
        <div class="card p-4 mt-5">
            <h5>📅 انتخاب روز هفته</h5>
            <form method="GET">
                <input type="hidden" name="specialty_id" value="<?= $selectedSpecialty ?>">
                <input type="hidden" name="doctor_id" value="<?= $selectedDoctor ?>">
                <div class="btn-group" role="group">
                    <?php
                    $daysEn = ['sat','sun','mon','tue','wed','thu','fri'];
                    $daysFa = ['ش','ی','د','س','چ','پ','ج'];
                    foreach ($days as $d):
                        $index = array_search($d['day_of_week'], $daysEn);
                    ?>
                        <button type="submit" name="day" value="<?= $d['day_of_week'] ?>" 
                                class="btn btn-outline-primary <?= $selectedDay == $d['day_of_week'] ? 'active' : '' ?>">
                            <?= $daysFa[$index] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($selectedDay && $availability): ?>
        <!-- انتخاب تاریخ و زمان -->
        <div class="card p-4 mt-4">
            <h5>⏰ انتخاب تاریخ و زمان</h5>
            <form method="POST">
                <input type="hidden" name="doctor_id" value="<?= $selectedDoctor ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">تاریخ نوبت</label>
                        <input type="text" name="date" class="form-control pdate" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ساعت نوبت</label>
                        <select name="time" class="form-select" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($availability as $slot): ?>
                                <option value="<?= $slot ?>"><?= substr($slot, 0, 5) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">✅ رزرو نوبت</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // فعال‌سازی تقویم شمسی برای فیلد تاریخ
    $('input.pdate').pDatepicker({
        format: 'YYYY-MM-DD',
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
        }
    });
});
</script>
