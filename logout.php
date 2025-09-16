<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_destroy();
flash('با موفقیت خارج شدید.', 'info');
redirect('index.php');
?>