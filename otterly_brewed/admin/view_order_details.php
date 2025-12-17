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

$order_id = $_GET['id'];

// Get order info
$sql_order = "SELECT O.*, U.FULL_NAME 
            FROM ORDERS O
            INNER JOIN USERS U ON O.USER_ID = U.USER_ID
            WHERE O.ORDER_ID = '$order_id'";
$result_order = sqlsrv_query($conn, $sql_order);
$order = sqlsrv_fetch_array($result_order);

if(!$order){
    header('Location: view_reports.php');
    exit();
}

// Get order items
$sql_items = "SELECT OI.*, M.ITEM_NAME 
            FROM ORDER_ITEMS OI
            INNER JOIN MENU_ITEMS M ON OI.ITEM_ID = M.ITEM_ID
            WHERE OI.ORDER_ID = '$order_id'";
$result_items = sqlsrv_query($conn, $sql_items);

$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/more-sugar" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/reports_styles.css" rel="stylesheet">
</head>
<body class="order-details-body">
    <div class="order-container">
        <div class="receipt-header">
            <div class="receipt-logo">🦦</div>
            <div class="receipt-title">Otterly Brewed</div>
            <p style="color: #8B6F47; font-style: italic;">Order Details</p>
        </div>

        <div class="order-info">
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span class="info-value">#<?php echo $order['ORDER_ID']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date & Time:</span>
                <span class="info-value"><?php echo $order['ORDER_DATE']->format('F d, Y h:i A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Processed By:</span>
                <span class="info-value"><?php echo $order['FULL_NAME']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?php echo $order['PAYMENT_METHOD']; ?></span>
            </div>
        </div>

        <div class="items-section">
            <h3 class="section-title">Order Items</h3>
            <?php
            while($item = sqlsrv_fetch_array($result_items)){
                $item_name = $item['ITEM_NAME'];
                $quantity = $item['QUANTITY'];
                $price = number_format($item['ITEM_PRICE'], 2);
                $subtotal = number_format($item['SUBTOTAL'], 2);
                
                echo '
                <div class="order-item">
                    <div>
                        <div class="item-name">'.$item_name.'</div>
                        <div class="item-details">₱'.$price.' × '.$quantity.'</div>
                    </div>
                    <div style="font-weight: bold; color: #6B4423;">₱'.$subtotal.'</div>
                </div>
                ';
            }
            ?>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₱<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?></span>
            </div>
            <div class="total-row">
                <span>Amount Paid:</span>
                <span>₱<?php echo number_format($order['AMOUNT_PAID'], 2); ?></span>
            </div>
            <div class="total-row grand">
                <span>Change:</span>
                <span>₱<?php echo number_format($order['CHANGE_AMOUNT'], 2); ?></span>
            </div>
        </div>

        <button class="btn-back" onclick="window.location.href='view_reports.php'">
            Back to Reports
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>