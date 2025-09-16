<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سیستم نوبت‌دهی درمانگاه</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://nobat.c-security.ir/assets/style.css">
    <link rel="stylesheet" href="https://nobat.c-security.ir/assets/persian-datepicker.css">
    <script src="https://nobat.c-security.ir/assets/persian-date.js"></script>
    <script src="https://nobat.c-security.ir/assets/persian-datepicker.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">درمانگاه امام خميني كرج</a>
            <div class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <span class="navbar-text me-3">خوش آمدید، <?= $_SESSION['fullname'] ?></span>
                    <a class="nav-link" href="https://nobat.c-security.ir/logout.php">خروج</a>
                <?php else: ?>
                    <a class="nav-link" href="index.php">ورود</a>
                    <a class="nav-link" href="register.php">ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php displayFlash(); ?>