<?php
session_start();
if(!isset($_SESSION['user_id'])){
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

$cart_json = $_POST['cart_data'];
$cart = json_decode($cart_json, true);

if(empty($cart)){
    header('Location: order.php');
    exit();
}

$total = 0;
foreach($cart as $item){
    $total += $item['price'] * $item['quantity'];
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$role = $_SESSION['role'];

if(isset($_POST['payment_method'])){
    $payment_method = $_POST['payment_method'];
    $amount_paid = $_POST['amount_paid'];
    $change = $amount_paid - $total;
    
    if($change < 0){
        $error = "Insufficient payment amount!";
    } else {
        $sql_order = "INSERT INTO ORDERS (USER_ID, TOTAL_AMOUNT, PAYMENT_METHOD, AMOUNT_PAID, CHANGE_AMOUNT) 
                    VALUES ('$user_id', '$total', '$payment_method', '$amount_paid', '$change')";
        $result_order = sqlsrv_query($conn, $sql_order);
        
        if($result_order){
            $sql_get_order = "SELECT MAX(ORDER_ID) AS ORDER_ID FROM ORDERS";
            $result_get = sqlsrv_query($conn, $sql_get_order);
            $row_order = sqlsrv_fetch_array($result_get);
            $order_id = $row_order['ORDER_ID'];
            
            foreach($cart as $item){
                $item_id = $item['id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;
                
                $sql_item = "INSERT INTO ORDER_ITEMS (ORDER_ID, ITEM_ID, QUANTITY, ITEM_PRICE, SUBTOTAL) 
                            VALUES ('$order_id', '$item_id', '$quantity', '$price', '$subtotal')";
                sqlsrv_query($conn, $sql_item);
            }
            
            $order_complete = true;
        } else {
            $error = "Error processing order: " . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/more-sugar" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/checkout_styles.css" rel="stylesheet">
</head>
<body>
    <div class="checkout-container">
        <?php if(isset($order_complete) && $order_complete): ?>
            <div class="success-badge">
                ✅ Order Completed Successfully!
            </div>
            
            <div class="receipt-container">
                <div class="receipt-header">
                    <div class="receipt-logo">🦦</div>
                    <div class="receipt-title">Otterly Brewed</div>
                    <div class="receipt-subtitle">Our Coffee's are Otterly Delicious!</div>
                </div>
                
                <div class="order-info">
                    <strong>Order #<?php echo $order_id; ?></strong><br>
                    Date: <?php echo date('F d, Y h:i A'); ?><br>
                    Cashier: <?php echo $fullname; ?>
                </div>
                
                <div style="margin: 20px 0;">
                    <strong style="color: #6B4423; font-size: 18px;">Order Items:</strong>
                    <?php foreach($cart as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                    <div class="order-item">
                        <div>
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-details">
                                ₱<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                            </div>
                        </div>
                        <div style="font-weight: bold; color: #6B4423;">
                            ₱<?php echo number_format($subtotal, 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="total-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Payment Method:</span>
                        <span><?php echo $payment_method; ?></span>
                    </div>
                    <div class="total-row">
                        <span>Amount Paid:</span>
                        <span>₱<?php echo number_format($amount_paid, 2); ?></span>
                    </div>
                    <div class="total-row grand">
                        <span>Change:</span>
                        <span>₱<?php echo number_format($change, 2); ?></span>
                    </div>
                </div>
                
                <div class="receipt-footer">
                    Thank you for your order!<br>
                    Have an otterly wonderful day! ☕
                </div>
            </div>
            
            <button class="btn-new-order" onclick="window.location.href='order.php'">
                New Order
            </button>
            
            <?php if($role == 'Admin'): ?>
            <button class="btn-back" onclick="window.location.href='dashboard.php'">
                Back to Dashboard
            </button>
            <?php endif; ?>
            
        <?php else: ?>
            <h2 style="color: #6B4423; text-align: center; margin-bottom: 30px;">
                💳 Checkout
            </h2>
            
            <div class="receipt-container">
                <h4 style="color: #6B4423; margin-bottom: 20px;">Order Summary:</h4>
                <?php foreach($cart as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                <div class="order-item">
                    <div>
                        <div class="item-name"><?php echo $item['name']; ?></div>
                        <div class="item-details">
                            ₱<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    <div style="font-weight: bold; color: #6B4423;">
                        ₱<?php echo number_format($subtotal, 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div style="background-color: #6B4423; color: white; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: center; font-size: 24px; font-weight: bold;">
                    Total: ₱<?php echo number_format($total, 2); ?>
                </div>
            </div>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="cart_data" value='<?php echo $cart_json; ?>'>
                
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" name="payment_method" required>
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="GCash">GCash</option>
                        <option value="PayMaya">PayMaya</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Amount Paid (₱)</label>
                    <input type="number" class="form-control" name="amount_paid" step="0.01" min="<?php echo $total; ?>" value="<?php echo $total; ?>" required>
                </div>
                
                <button type="submit" class="btn-submit">Complete Payment</button>
                <button type="button" class="btn-back" onclick="window.location.href='order.php'">
                    Back to Order
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>