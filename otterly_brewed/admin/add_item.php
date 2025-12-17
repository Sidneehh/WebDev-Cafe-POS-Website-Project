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

$item_name = $_POST['item_name'];
$price = $_POST['price'];
$category_id = $_POST['category_id'];

$destination = "../uploads/";
$filename = basename($_FILES['item_image']['name']);
$finalfilepath = $destination . $filename;

$allowtypes = array('jpg', 'jpeg', 'png');
$filetype = pathinfo($finalfilepath, PATHINFO_EXTENSION);

if(in_array(strtolower($filetype), $allowtypes)){
    $finalfolder = move_uploaded_file($_FILES['item_image']['tmp_name'], $finalfilepath);
    
    if($finalfolder){
        $sql = "INSERT INTO MENU_ITEMS (ITEM_NAME, CATEGORY_ID, PRICE, IMAGE_PATH, IS_AVAILABLE) 
                VALUES ('$item_name', '$category_id', '$price', 'uploads/$filename', 1)";
        $result = sqlsrv_query($conn, $sql);
        
        if($result){
            header('Location: manage_menu.php');
            exit();
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
}
?>