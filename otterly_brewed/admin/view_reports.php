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

$period = isset($_GET['period']) ? $_GET['period'] : 'all';

$where_clause = "";
switch($period){
    case 'today':
        $where_clause = "WHERE CONVERT(DATE, O.ORDER_DATE) = CONVERT(DATE, GETDATE())";
        break;
    case 'monthly':
        $where_clause = "WHERE MONTH(O.ORDER_DATE) = MONTH(GETDATE()) AND YEAR(O.ORDER_DATE) = YEAR(GETDATE())";
        break;
    case 'yearly':
        $where_clause = "WHERE YEAR(O.ORDER_DATE) = YEAR(GETDATE())";
        break;
    default:
        $where_clause = "";
}

$sql_orders = "SELECT O.*, U.FULL_NAME 
            FROM ORDERS O
            INNER JOIN USERS U ON O.USER_ID = U.USER_ID
            $where_clause
            ORDER BY O.ORDER_DATE DESC";
$result_orders = sqlsrv_query($conn, $sql_orders);

$sql_total = "SELECT SUM(TOTAL_AMOUNT) AS TOTAL_SALES FROM ORDERS O $where_clause";
$result_total = sqlsrv_query($conn, $sql_total);
$row_total = sqlsrv_fetch_array($result_total);
$total_sales = $row_total['TOTAL_SALES'] ? $row_total['TOTAL_SALES'] : 0;

$sql_count = "SELECT COUNT(ORDER_ID) AS ORDER_COUNT FROM ORDERS O $where_clause";
$result_count = sqlsrv_query($conn, $sql_count);
$row_count = sqlsrv_fetch_array($result_count);
$order_count = $row_count['ORDER_COUNT'];

$period_label = "";
switch($period){
    case 'today':
        $period_label = "Today's";
        break;
    case 'monthly':
        $period_label = "This Month's";
        break;
    case 'yearly':
        $period_label = "This Year's";
        break;
    default:
        $period_label = "All Time";
}

$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/reports_styles.css" rel="stylesheet">
    <style>
        .period-filter {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .period-btn {
            padding: 12px 25px;
            margin: 5px;
            border-radius: 10px;
            border: 2px solid #D4A574;
            background-color: white;
            color: #6B4423;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }
        .period-btn:hover {
            background-color: #FFF8E7;
            transform: scale(1.05);
        }
        .period-btn.active {
            background-color: #6B4423;
            color: white;
            border-color: #6B4423;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../assests/images/otter-logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40/6B4423/FFFFFF?text=🦦'">
                Otterly Brewed
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">🏠 Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order.php">🛒 Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_menu.php">📋 Manage Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view_reports.php">📊 Reports</a>
                    </li>
                </ul>
                <span class="navbar-text text-white me-3">
                    <?php echo $fullname; ?>
                </span>
                <button class="btn btn-logout" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container-custom">
        <div class="page-header">
            <h1 class="page-title">📊 Sales Reports</h1>
            <p style="color: #8B6F47;">View all orders and sales statistics</p>
        </div>

        <div class="period-filter">
            <h4 style="color: #6B4423; margin-bottom: 15px; font-weight: bold;">📅 Select Time Period</h4>
            <div class="d-flex flex-wrap justify-content-center">
                <button class="period-btn <?php echo ($period == 'all') ? 'active' : ''; ?>" 
                        onclick="window.location.href='view_reports.php?period=all'">
                    📊 All Time
                </button>
                <button class="period-btn <?php echo ($period == 'today') ? 'active' : ''; ?>" 
                        onclick="window.location.href='view_reports.php?period=today'">
                    📆 Today
                </button>
                <button class="period-btn <?php echo ($period == 'monthly') ? 'active' : ''; ?>" 
                        onclick="window.location.href='view_reports.php?period=monthly'">
                    📅 This Month
                </button>
                <button class="period-btn <?php echo ($period == 'yearly') ? 'active' : ''; ?>" 
                        onclick="window.location.href='view_reports.php?period=yearly'">
                    📈 This Year
                </button>
            </div>
        </div>

        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-label"><?php echo $period_label; ?> Total Sales</div>
                <div class="stat-value">₱<?php echo number_format($total_sales, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><?php echo $period_label; ?> Total Orders</div>
                <div class="stat-value"><?php echo $order_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><?php echo $period_label; ?> Average Order</div>
                <div class="stat-value">₱<?php echo $order_count > 0 ? number_format($total_sales / $order_count, 2) : '0.00'; ?></div>
            </div>
        </div>

        <div class="reports-table">
            <h3 class="section-title">📝 <?php echo $period_label; ?> Order History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(sqlsrv_has_rows($result_orders)){
                        while($order = sqlsrv_fetch_array($result_orders)){
                            $order_id = $order['ORDER_ID'];
                            $date = $order['ORDER_DATE']->format('M d, Y h:i A');
                            $cashier = $order['FULL_NAME'];
                            $total = number_format($order['TOTAL_AMOUNT'], 2);
                            $payment = $order['PAYMENT_METHOD'];
                            
                            echo '
                            <tr>
                                <td><strong>#'.$order_id.'</strong></td>
                                <td>'.$date.'</td>
                                <td>'.$cashier.'</td>
                                <td><strong>₱'.$total.'</strong></td>
                                <td>'.$payment.'</td>
                                <td>
                                    <button class="btn-view" onclick="viewOrder('.$order_id.')">View Details</button>
                                </td>
                            </tr>
                            ';
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 30px;">No orders found for this period</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrder(orderId) {
            window.location.href = 'view_order_details.php?id=' + orderId;
        }
    </script>
</body>
</html>