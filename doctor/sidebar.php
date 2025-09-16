<div class="position-sticky" style="top: 2rem;">
    <div class="list-group">
        <a href="dashboard.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            📊 داشبورد
        </a>
        <a href="view-appointments.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'view-appointments.php' ? 'active' : '' ?>">
            📋 نوبت‌های من
        </a>
        <a href="manage-availability.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'manage-availability.php' ? 'active' : '' ?>">
            ⏰ ساعت کاری من
        </a>
        <a href="doctor-profile.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'doctor-profile.php' ? 'active' : '' ?>">
            👥 پروفايل من
        </a>
    </div>
</div>