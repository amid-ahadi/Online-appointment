<?php

require_once 'jdf.php';

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isDoctor() {
    return isLoggedIn() && $_SESSION['role'] === 'doctor';
}

function isPatient() {
    return isLoggedIn() && $_SESSION['role'] === 'patient';
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function flash($message, $type = 'info') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>