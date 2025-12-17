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

$sql = "SELECT * FROM MENU_ITEMS WHERE ITEM_ID = '$item_id'";
$result = sqlsrv_query($conn, $sql);
$item = sqlsrv_fetch_array($result);

if(!$item){
    header('Location: manage_menu.php');
    exit();
}

$sql_categories = "SELECT * FROM CATEGORIES ORDER BY CATEGORY_NAME";
$result_categories = sqlsrv_query($conn, $sql_categories);

if(isset($_POST['update'])){
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $is_available = $_POST['is_available'];

    if($_FILES['item_image']['size'] > 0){
        $destination = "../uploads/";
        $filename = basename($_FILES['item_image']['name']);
        $finalfilepath = $destination . $filename;
        
        $allowtypes = array('jpg', 'jpeg', 'png');
        $filetype = pathinfo($finalfilepath, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($filetype), $allowtypes)){
            move_uploaded_file($_FILES['item_image']['tmp_name'], $finalfilepath);
            $image_path = "uploads/" . $filename;
            
            $sql_update = "UPDATE MENU_ITEMS 
                        SET ITEM_NAME = '$item_name', 
                            CATEGORY_ID = '$category_id', 
                            PRICE = '$price', 
                            IMAGE_PATH = '$image_path',
                            IS_AVAILABLE = '$is_available'
                        WHERE ITEM_ID = '$item_id'";
        }
    } else {
        $sql_update = "UPDATE MENU_ITEMS 
                    SET ITEM_NAME = '$item_name', 
                        CATEGORY_ID = '$category_id', 
                        PRICE = '$price',
                        IS_AVAILABLE = '$is_available'
                    WHERE ITEM_ID = '$item_id'";
    }
    
    $result_update = sqlsrv_query($conn, $sql_update);
    
    if($result_update){
        header('Location: manage_menu.php');
        exit();
    } else {
        $error = "Error updating item: " . print_r(sqlsrv_errors(), true);
    }
}

$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/more-sugar" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/manage_menu_styles.css" rel="stylesheet">
</head>
<body class="edit-body">
    <div class="edit-container">
        <h1 class="page-title">✏️ Edit Menu Item</h1>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <img src="../<?php echo $item['IMAGE_PATH']; ?>" class="current-image" alt="Current Image" onerror="this.src='https://via.placeholder.com/200x150/8B6F47/FFFFFF?text=No+Image'">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" class="form-control" name="item_name" value="<?php echo $item['ITEM_NAME']; ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price (₱)</label>
                    <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $item['PRICE']; ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" required>
                        <?php
                        while($cat = sqlsrv_fetch_array($result_categories)){
                            $selected = ($cat['CATEGORY_ID'] == $item['CATEGORY_ID']) ? 'selected' : '';
                            echo '<option value="'.$cat['CATEGORY_ID'].'" '.$selected.'>'.$cat['CATEGORY_NAME'].'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select class="form-select" name="is_available" required>
                    <option value="1" <?php echo ($item['IS_AVAILABLE'] == 1) ? 'selected' : ''; ?>>Available</option>
                    <option value="0" <?php echo ($item['IS_AVAILABLE'] == 0) ? 'selected' : ''; ?>>Unavailable</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Change Image (Optional)</label>
                <input type="file" class="form-control" name="item_image" accept=".jpg, .jpeg, .png">
                <small style="color: #8B6F47;">Leave empty to keep current image</small>
            </div>
            
            <button type="submit" name="update" class="btn-submit">Update Item</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='manage_menu.php'">Cancel</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>