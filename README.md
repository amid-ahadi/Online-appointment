
# 🏥 سیستم نوبت‌دهی درمانگاه — Clinic Appointment System

یک سیستم کامل، امن و حرفه‌ای برای مدیریت نوبت‌دهی در درمانگاه‌ها — با قابلیت‌های پیشرفته برای **بیماران، پزشکان و ادمین‌ها**.

---

## 🌟 ویژگی‌های سیستم

- ✅ **لاگین و ثبت‌نام کاربران** (بیمار، پزشک، ادمین)
- ✅ **رزرو نوبت آنلاین** با انتخاب تخصص، پزشک، روز و ساعت
- ✅ **محدودیت تقویم** — فقط روزهای کاری پزشک و غیرتعطیلی قابل انتخاب
- ✅ **داشبورد اختصاصی برای هر نقش**
- ✅ **مدیریت کاربران، تخصص‌ها، پزشکان و ساعت کاری توسط ادمین**
- ✅ **تنظیم ساعت کاری توسط پزشکان**
- ✅ **لغو نوبت توسط بیمار (قبل از تأیید)**
- ✅ **تغییر وضعیت نوبت توسط پزشک** (تأیید/لغو/انجام شده)
- ✅ **تقویم شمسی** برای تمام فیلدهای تاریخ
- ✅ **صفحه‌بندی** در لیست‌های طولانی
- ✅ **پنل مدیریت کامل** با منوی سمت راست
- ✅ **پشتیبانی از زبان فارسی و راست‌چین**

---

## ⚙️ پیش‌نیازها

- ✅ **PHP 7.4 یا بالاتر**
- ✅ **MySQL 5.7+ یا MariaDB**
- ✅ **Apache یا Nginx**
- ✅ **فعال بودن `mod_rewrite` (برای URLهای تمیز)**
- ✅ **افزونه‌های PHP: `PDO`, `mbstring`**

---

## 📥 نصب و راه‌اندازی

### ۱. کلون کردن پروژه

```bash
git https://github.com/amid-ahadi/Online-appointment.git
cd Online-appointment

۲. آپلود به هاست
فایل‌ها رو در روت هاست آپلود کن — یا در یک پوشه (مثلاً /nobat).

۳. ایجاد دیتابیس
وارد phpMyAdmin یا هر ابزار مدیریت دیتابیسی شو.
یک دیتابیس جدید ایجاد کن (مثلاً clinic_appointment_system).
کد SQL اولیه رو اجرا کن (فایل database.sql — در ادامه میاد).
۴. تنظیم اتصال به دیتابیس
فایل includes/config.php رو باز کن و اطلاعات دیتابیس رو وارد کن:

$host = 'localhost';
$dbname = 'clinic_appointment_system';
$username = 'your_db_username';
$password = 'your_db_password';

۵. وارد شدن به سیستم
آدرس سیستم: http://yoursite.com/ یا http://yoursite.com/nobat/
لاگین ادمین:
ایمیل: admin@clinic.com
رمز: password

🗃️ ساختار دیتابیس
دیتابیس شامل جداول زیر هست:

users — کاربران (بیماران، پزشکان، ادمین‌ها)
specialties — تخصص‌های پزشکی
doctors — پزشکان (ارتباط با users و specialties)
availability — ساعت کاری پزشکان
appointments — نوبت‌های رزرو شده
clinic_settings — تنظیمات کلی درمانگاه
clinic_holidays — روزهای تعطیلی درمانگاه
doctor_profiles — پروفایل نمایشی پزشکان (عکس، بیوگرافی و ...)
✅ اسکریپت کامل دیتابیس در فایل database.sql موجوده. 




👥 نقش‌های کاربری
👨‍⚕️ ادمین
مدیریت کاربران (افزودن/ویرایش/حذف)
مدیریت تخصص‌ها (افزودن/ویرایش/حذف/تغییر جایگاه)
مدیریت پزشکان
تنظیم ساعت کاری کل درمانگاه
اعلام روزهای تعطیلی
مدیریت پروفایل پزشکان
👨‍⚕️ پزشک
مشاهده و مدیریت نوبت‌های خودش
تنظیم ساعت کاری شخصی
تغییر وضعیت نوبت‌ها (تأیید/لغو/انجام شده)
👩 بیمار
رزرو نوبت با انتخاب تخصص و پزشک
مشاهده و لغو نوبت‌های خودش
استفاده از تقویم شمسی برای انتخاب تاریخ

🤝 مشارکت
اگر می‌خوای به پروژه کمک کنی:

فورک کن
تغییرات رو اعمال کن
Pull Request بده
📄 لایسنس
این پروژه تحت لایسنس MIT منتشر شده — می‌تونی رایگان استفاده کنی، تغییر بدی و حتی در پروژه‌های تجاری به کار ببری.

🙏 تشکر
سپاس از تو که این سیستم رو انتخاب کردی!
اگر سوالی داشتی، در Issues مطرح کن.

✅ سیستم آماده استفاده است — موفق باشی!


---

## ✅ فایل `database.sql` — برای ایجاد دیتابیس

این فایل رو در روت پروژه ذخیره کن — و در `phpMyAdmin` اجرا کن:

```sql
-- --------------------------------------------------------
-- نام دیتابیس: clinic_appointment_system
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `clinic_appointment_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
USE `clinic_appointment_system`;

-- --------------------------------------------------------
-- جدول: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL COMMENT 'نام و نام خانوادگی',
  `email` varchar(100) NOT NULL UNIQUE COMMENT 'ایمیل منحصر به فرد',
  `password` varchar(255) NOT NULL COMMENT 'رمز عبور هش شده',
  `phone` varchar(15) DEFAULT NULL COMMENT 'شماره تلفن همراه',
  `role` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient' COMMENT 'نقش کاربر',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ثبت‌نام',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: specialties
-- --------------------------------------------------------
CREATE TABLE `specialties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE COMMENT 'نام تخصص',
  `description` text DEFAULT NULL COMMENT 'توضیحات تخصص',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'ترتیب نمایش',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: doctors
-- --------------------------------------------------------
CREATE TABLE `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ارتباط با جدول users',
  `specialty_id` int(11) NOT NULL COMMENT 'ارتباط با جدول specialties',
  `medical_license` varchar(50) DEFAULT NULL COMMENT 'شماره پروانه پزشکی',
  `bio` text DEFAULT NULL COMMENT 'بیوگرافی/معرفی پزشک',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'فعال/غیرفعال بودن پزشک',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`specialty_id`) REFERENCES `specialties`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_specialty` (`specialty_id`),
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: availability
-- --------------------------------------------------------
CREATE TABLE `availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('sat','sun','mon','tue','wed','thu','fri') NOT NULL COMMENT 'روز هفته',
  `start_time` time NOT NULL COMMENT 'ساعت شروع کار',
  `end_time` time NOT NULL COMMENT 'ساعت پایان کار',
  `slot_duration` int(11) NOT NULL DEFAULT 30 COMMENT 'مدت زمان هر نوبت به دقیقه',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_doctor_day` (`doctor_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: appointments
-- --------------------------------------------------------
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL COMMENT 'کاربر بیمار',
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL COMMENT 'تاریخ نوبت',
  `appointment_time` time NOT NULL COMMENT 'ساعت نوبت',
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending' COMMENT 'وضعیت نوبت',
  `notes` text DEFAULT NULL COMMENT 'یادداشت بیمار یا پزشک',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_doctor` (`doctor_id`),
  INDEX `idx_date_status` (`appointment_date`, `status`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: clinic_settings
-- --------------------------------------------------------
CREATE TABLE `clinic_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE COMMENT 'کلید تنظیمات',
  `setting_value` text DEFAULT NULL COMMENT 'مقدار تنظیمات',
  `description` text DEFAULT NULL COMMENT 'توضیحات',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: clinic_holidays
-- --------------------------------------------------------
CREATE TABLE `clinic_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holiday_date` date NOT NULL COMMENT 'تاریخ تعطیلی',
  `reason` varchar(255) DEFAULT NULL COMMENT 'دلیل تعطیلی',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`holiday_date`),
  INDEX `idx_date` (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- جدول: doctor_profiles
-- --------------------------------------------------------
CREATE TABLE `doctor_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL COMMENT 'ارتباط با جدول doctors',
  `photo_url` varchar(255) DEFAULT NULL COMMENT 'آدرس عکس پزشک',
  `display_bio` text DEFAULT NULL COMMENT 'بیوگرافی نمایشی در صفحه رزرو',
  `banner_text` varchar(255) DEFAULT NULL COMMENT 'متن بنر',
  `bg_color` varchar(7) DEFAULT '#ffffff' COMMENT 'رنگ پس‌زمینه کارت',
  `text_color` varchar(7) DEFAULT '#000000' COMMENT 'رنگ متن',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'آیا در صفحه اول برجسته شود؟',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'ترتیب نمایش',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `unique_doctor` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- داده‌های نمونه
-- --------------------------------------------------------
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `role`) VALUES
('ادمین سیستم', 'admin@clinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'admin'),
('دکتر علی رضایی', 'ali.rezaei@clinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09121112233', 'doctor'),
('مرضیه احمدی', 'maryam.ahmadi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09124445566', 'patient');

INSERT INTO `specialties` (`name`, `description`, `sort_order`) VALUES
('قلب و عروق', 'متخصص بیماری‌های قلبی و عروقی', 1),
('چشم پزشکی', 'تشخیص و درمان بیماری‌های چشم', 2);

INSERT INTO `doctors` (`user_id`, `specialty_id`, `medical_license`, `bio`) VALUES
(2, 1, 'MED123456', 'دکتر علی رضایی — فوق تخصص قلب و عروق — سابقه 15 سال');

INSERT INTO `clinic_settings` (`setting_key`, `setting_value`, `description`) VALUES
('opening_time', '08:00:00', 'ساعت شروع کار درمانگاه'),
('closing_time', '20:00:00', 'ساعت پایان کار درمانگاه'),
('slot_duration', '30', 'مدت زمان هر نوبت به دقیقه'),
('is_clinic_active', '1', 'آیا درمانگاه فعال است؟'),
('clinic_name', 'درمانگاه آریا', 'نام درمانگاه');

-- --------------------------------------------------------
-- تنظیم AUTO_INCREMENT
-- --------------------------------------------------------
ALTER TABLE `users` AUTO_INCREMENT = 1000;
ALTER TABLE `specialties` AUTO_INCREMENT = 10;
ALTER TABLE `doctors` AUTO_INCREMENT = 100;
ALTER TABLE `appointments` AUTO_INCREMENT = 5000;




