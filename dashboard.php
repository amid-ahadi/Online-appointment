<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    redirect('admin/dashboard.php');
} elseif ($role === 'doctor') {
    redirect('doctor/dashboard.php');
} else {
    redirect('patient/book-appointment.php');
}
?>