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

$sql_categories = "SELECT * FROM CATEGORIES ORDER BY CATEGORY_NAME";
$result_categories = sqlsrv_query($conn, $sql_categories);

$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

if($filter_category == 'all'){
    $sql_items = "SELECT M.*, C.CATEGORY_NAME 
                FROM MENU_ITEMS M 
                INNER JOIN CATEGORIES C ON M.CATEGORY_ID = C.CATEGORY_ID 
                ORDER BY C.CATEGORY_NAME, M.ITEM_NAME";
} else {
    $sql_items = "SELECT M.*, C.CATEGORY_NAME 
                FROM MENU_ITEMS M 
                INNER JOIN CATEGORIES C ON M.CATEGORY_ID = C.CATEGORY_ID 
                WHERE M.CATEGORY_ID = '$filter_category'
                ORDER BY M.ITEM_NAME";
}
$result_items = sqlsrv_query($conn, $sql_items);

$sql_count = "SELECT C.CATEGORY_ID, C.CATEGORY_NAME, COUNT(M.ITEM_ID) AS ITEM_COUNT
            FROM CATEGORIES C
            LEFT JOIN MENU_ITEMS M ON C.CATEGORY_ID = M.CATEGORY_ID
            GROUP BY C.CATEGORY_ID, C.CATEGORY_NAME
            ORDER BY C.CATEGORY_NAME";
$result_count = sqlsrv_query($conn, $sql_count);

$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/manage_menu_styles.css" rel="stylesheet">
    <style>
        .category-filter {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .category-btn {
            padding: 10px 20px;
            margin: 5px;
            border-radius: 10px;
            border: 2px solid #D4A574;
            background-color: white;
            color: #6B4423;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .category-btn:hover {
            background-color: #FFF8E7;
            transform: scale(1.05);
        }
        .category-btn.active {
            background-color: #6B4423;
            color: white;
            border-color: #6B4423;
        }
        .category-badge {
            background-color: #8B6F47;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 8px;
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
                        <a class="nav-link active" href="manage_menu.php">📋 Manage Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_reports.php">📊 Reports</a>
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
            <h1 class="page-title">📋 Manage Menu Items</h1>
            <p style="color: #8B6F47;">Add, edit, or remove items from your menu</p>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <?php if($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ Item deleted successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="category-filter">
            <h4 style="color: #6B4423; margin-bottom: 15px; font-weight: bold;">🗂️ Filter by Category</h4>
            <div class="d-flex flex-wrap">
                <button class="category-btn <?php echo ($filter_category == 'all') ? 'active' : ''; ?>" 
                        onclick="window.location.href='manage_menu.php?category=all'">
                    All Items
                    <?php
                    $sql_total = "SELECT COUNT(*) AS TOTAL FROM MENU_ITEMS";
                    $result_total = sqlsrv_query($conn, $sql_total);
                    $row_total = sqlsrv_fetch_array($result_total);
                    echo '<span class="category-badge">'.$row_total['TOTAL'].'</span>';
                    ?>
                </button>
                <?php
                while($cat = sqlsrv_fetch_array($result_count)){
                    $active_class = ($filter_category == $cat['CATEGORY_ID']) ? 'active' : '';
                    echo '<button class="category-btn '.$active_class.'" onclick="window.location.href=\'manage_menu.php?category='.$cat['CATEGORY_ID'].'\'">
                            '.$cat['CATEGORY_NAME'].'
                            <span class="category-badge">'.$cat['ITEM_COUNT'].'</span>
                        </button>';
                }
                ?>
            </div>
        </div>

        <div class="add-item-section">
            <h3 class="section-title">➕ Add New Menu Item</h3>
            <form action="add_item.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="item_name" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $result_cat2 = sqlsrv_query($conn, $sql_categories);
                            while($cat = sqlsrv_fetch_array($result_cat2)){
                                echo '<option value="'.$cat['CATEGORY_ID'].'">'.$cat['CATEGORY_NAME'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Upload Item Image</label>
                        <input type="file" class="form-control" name="item_image" accept=".jpg, .jpeg, .png" required>
                        <small style="color: #8B6F47;">Accepted formats: JPG, JPEG, PNG</small>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn-submit">Add Item</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="menu-items-list">
            <h3 class="section-title">
                📝 <?php echo ($filter_category == 'all') ? 'All Menu Items' : 'Filtered Items'; ?>
            </h3>
            <div class="row">
                <?php
                $item_count = 0;
                while($item = sqlsrv_fetch_array($result_items)){
                    $item_count++;
                    $item_id = $item['ITEM_ID'];
                    $item_name = $item['ITEM_NAME'];
                    $price = number_format($item['PRICE'], 2);
                    $category = $item['CATEGORY_NAME'];
                    $image = $item['IMAGE_PATH'] ? '../'.$item['IMAGE_PATH'] : 'https://via.placeholder.com/300x150/8B6F47/FFFFFF?text='.$item_name;
                    
                    echo '
                    <div class="col-md-4 col-lg-3">
                        <div class="menu-item-card">
                            <img src="'.$image.'" class="item-image" alt="'.$item_name.'" onerror="this.src=\'https://via.placeholder.com/300x150/8B6F47/FFFFFF?text='.urlencode($item_name).'\'">
                            <div class="item-name">'.$item_name.'</div>
                            <div style="color: #8B6F47; font-size: 14px; margin-bottom: 8px;">'.$category.'</div>
                            <div class="item-price">₱'.$price.'</div>
                            <button class="btn-edit" onclick="editItem('.$item_id.')">Edit</button>
                            <button class="btn-delete" onclick="deleteItem('.$item_id.', \''.$item_name.'\')">Delete</button>
                        </div>
                    </div>
                    ';
                }
                
                if($item_count == 0){
                    echo '<div class="col-12 text-center" style="padding: 50px; color: #8B6F47;">
                            <h4>No items found in this category</h4>
                            <p>Add some items to get started!</p>
                        </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editItem(id) {
            window.location.href = 'edit_item.php?id=' + id;
        }

        function deleteItem(id, name) {
            if(confirm('Are you sure you want to DELETE "' + name + '"?\n\nThis will permanently remove it from the database and order history!')) {
                window.location.href = 'delete_item.php?id=' + id;
            }
        }
    </script>
</body>
</html>