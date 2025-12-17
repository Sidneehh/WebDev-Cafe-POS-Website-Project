<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin'){
    header('Location: ../otter_homepage.html');
    exit();
}

$serverName = "DESKTOP-IVHRL9V\SQLEXPRESS";
$connectionOptions = [
    "Database" => "OTTERLY_BREWED",
    "Uid" => "",
    "PWD" => ""
];
$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn == false){
    die(print_r(sqlsrv_errors(), true));
}

$item_id = $_GET['id'];

$sql = "UPDATE MENU_ITEMS SET IS_AVAILABLE = 1 WHERE ITEM_ID = '$item_id'";
$result = sqlsrv_query($conn, $sql);

if($result){
    header('Location: manage_menu.php?msg=activated');
    exit();
} else {
    die("Error activating item: " . print_r(sqlsrv_errors(), true));
}
?>