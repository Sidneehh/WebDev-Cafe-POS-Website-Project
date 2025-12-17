<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Cashier'){
    header('Location: ../otter_homepage.html');
    exit();
}

header('Location: order.php');
exit();
?>