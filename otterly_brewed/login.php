<?php
$serverName = "DESKTOP-IVHRL9V\SQLEXPRESS";
$connectionOptions = [
    "Database" => "OTTERLY_BREWED",
    "Uid" => "",
    "PWD" => ""
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if($conn==false){
    die(print_r(sqlsrv_errors(), true));
}else{
    echo 'Connection Success';
}


$username = $_POST['username'];
$password = $_POST['password'];
$expected_role = $_POST['expected_role'];

$sql = "SELECT USER_ID, USERNAME, PASSWORD, FULL_NAME, ROLE 
        FROM USERS 
        WHERE USERNAME = '$username'";
$result = sqlsrv_query($conn, $sql);
$row = sqlsrv_fetch_array($result);

if(!$row){
    if($expected_role == 'Admin'){
        header('Location: login_admin_nouser.html');
    } else {
        header('Location: login_cashier_nouser.html');
    }
    die();
}

$realpassword = $row['PASSWORD'];
if($password != $realpassword){
    if($expected_role == 'Admin'){
        header('Location: login_admin_wrongpass.html');
    } else {
        header('Location: login_cashier_wrongpass.html');
    }
    die();
}

$actual_role = $row['ROLE'];
if($actual_role != $expected_role){
    if($expected_role == 'Admin'){
        header('Location: login_admin_wrongrole.html');
    } else {
        header('Location: login_cashier_wrongrole.html');
    }
    die();
}

$userid = $row['USER_ID'];
$fullname = $row['FULL_NAME'];

session_start();
$_SESSION['user_id'] = $userid;
$_SESSION['username'] = $username;
$_SESSION['fullname'] = $fullname;
$_SESSION['role'] = $actual_role;

if($actual_role == 'Admin'){
    header('Location: admin/dashboard.php');
} else if($actual_role == 'Cashier'){
    header('Location: cashier/dashboard.php');
}

exit();
?>