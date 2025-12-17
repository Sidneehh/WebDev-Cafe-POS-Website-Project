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

$sql_delete_order_items = "DELETE FROM ORDER_ITEMS WHERE ITEM_ID = '$item_id'";
$result1 = sqlsrv_query($conn, $sql_delete_order_items);

if(!$result1){
    die("Error deleting order items: " . print_r(sqlsrv_errors(), true));
}

$sql_delete_item = "DELETE FROM MENU_ITEMS WHERE ITEM_ID = '$item_id'";
$result2 = sqlsrv_query($conn, $sql_delete_item);

if($result2){
    header('Location: manage_menu.php?msg=deleted');
    exit();
} else {
    die("Error deleting menu item: " . print_r(sqlsrv_errors(), true));
}
?>