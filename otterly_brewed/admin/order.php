<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

$sql_categories = "SELECT * FROM CATEGORIES ORDER BY CATEGORY_NAME";
$result_categories = sqlsrv_query($conn, $sql_categories);

$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

if($filter_category == 'all'){
    $sql_items = "SELECT M.*, C.CATEGORY_NAME 
                FROM MENU_ITEMS M 
                INNER JOIN CATEGORIES C ON M.CATEGORY_ID = C.CATEGORY_ID 
                WHERE M.IS_AVAILABLE = 1
                ORDER BY C.CATEGORY_NAME, M.ITEM_NAME";
} else {
    $sql_items = "SELECT M.*, C.CATEGORY_NAME 
                FROM MENU_ITEMS M 
                INNER JOIN CATEGORIES C ON M.CATEGORY_ID = C.CATEGORY_ID 
                WHERE M.IS_AVAILABLE = 1 AND M.CATEGORY_ID = '$filter_category'
                ORDER BY M.ITEM_NAME";
}
$result_items = sqlsrv_query($conn, $sql_items);

$role = $_SESSION['role'];
$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Otterly Brewed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/more-sugar" rel="stylesheet">
    <link href="../assests/css/main_styles.css" rel="stylesheet">
    <link href="../assests/css/order_styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo ($role == 'Admin') ? 'dashboard.php' : 'order.php'; ?>">
                <img src="../assests/images/otter-logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40/6B4423/FFFFFF?text=🦦'">
                Otterly Brewed
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo ($role == 'Admin') ? 'order.php' : 'order.php'; ?>">🏠 Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            📋 Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="order.php?category=all">All Items</a></li>
                            <?php
                            $result_categories2 = sqlsrv_query($conn, $sql_categories);
                            while($cat = sqlsrv_fetch_array($result_categories2)){
                                echo '<li><a class="dropdown-item" href="order.php?category='.$cat['CATEGORY_ID'].'">'.$cat['CATEGORY_NAME'].'</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                </ul>
                <span class="navbar-text text-white me-3">
                    <?php echo $fullname; ?> (<?php echo $role; ?>)
                </span>
                <button class="btn btn-logout" onclick="window.location.href='../logout.php'">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="menu-panel">
                    <h2 class="section-title">☕ Menu Items</h2>
                    <div class="row">
                        <?php
                        while($item = sqlsrv_fetch_array($result_items)){
                            $item_id = $item['ITEM_ID'];
                            $item_name = $item['ITEM_NAME'];
                            $price = number_format($item['PRICE'], 2);
                            $image = $item['IMAGE_PATH'] ? '../'.$item['IMAGE_PATH'] : 'https://via.placeholder.com/300x180/8B6F47/FFFFFF?text='.$item_name;
                            $category = $item['CATEGORY_NAME'];
                            
                            echo '
                            <div class="col-md-6 col-lg-4">
                                <div class="menu-item-card">
                                    <img src="'.$image.'" class="item-image" alt="'.$item_name.'" onerror="this.src=\'https://via.placeholder.com/300x180/8B6F47/FFFFFF?text='.urlencode($item_name).'\'">
                                    <div class="item-name">'.$item_name.'</div>
                                    <div style="color: #8B6F47; font-size: 14px; margin-bottom: 8px;">'.$category.'</div>
                                    <div class="item-price">₱'.$price.'</div>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="decreaseQty('.$item_id.')">-</button>
                                        <span class="quantity-display" id="qty-'.$item_id.'">1</span>
                                        <button class="quantity-btn" onclick="increaseQty('.$item_id.')">+</button>
                                    </div>
                                    <button class="btn-add-cart" onclick="addToCart('.$item_id.', \''.$item_name.'\', '.$item['PRICE'].')">
                                        Add to Cart 🛒
                                    </button>
                                </div>
                            </div>
                            ';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="cart-panel">
                    <h3 class="cart-title">🛒 Your Cart</h3>
                    <div id="cart-items">
                        <div class="cart-empty">
                            Your cart is empty<br>
                            Start adding items! 🦦☕
                        </div>
                    </div>
                    <div id="cart-total-section" style="display:none;">
                        <div class="cart-total">
                            <div class="cart-total-label">Total Amount:</div>
                            <div class="cart-total-amount">₱<span id="total-amount">0.00</span></div>
                        </div>
                        <button class="btn-checkout" onclick="checkout()">Proceed to Checkout</button>
                        <button class="btn-clear-cart" onclick="clearCart()">Clear Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];

        function increaseQty(itemId) {
            let qtyElement = document.getElementById('qty-' + itemId);
            let currentQty = parseInt(qtyElement.textContent);
            qtyElement.textContent = currentQty + 1;
        }

        function decreaseQty(itemId) {
            let qtyElement = document.getElementById('qty-' + itemId);
            let currentQty = parseInt(qtyElement.textContent);
            if (currentQty > 1) {
                qtyElement.textContent = currentQty - 1;
            }
        }

        function addToCart(itemId, itemName, itemPrice) {
            let qty = parseInt(document.getElementById('qty-' + itemId).textContent);
            
            let existingItem = cart.find(item => item.id === itemId);
            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                cart.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: qty
                });
            }
            
            document.getElementById('qty-' + itemId).textContent = '1';
            
            updateCart();
        }

        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCart();
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear the cart?')) {
                cart = [];
                updateCart();
            }
        }

        function updateCart() {
            let cartItemsDiv = document.getElementById('cart-items');
            let totalSection = document.getElementById('cart-total-section');
            
            if (cart.length === 0) {
                cartItemsDiv.innerHTML = '<div class="cart-empty">Your cart is empty<br>Start adding items! 🦦☕</div>';
                totalSection.style.display = 'none';
                return;
            }
            
            let html = '';
            let total = 0;
            
            cart.forEach(item => {
                let subtotal = item.price * item.quantity;
                total += subtotal;
                
                html += `
                    <div class="cart-item">
                        <button class="btn-remove-item" onclick="removeFromCart(${item.id})">✕</button>
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-details">
                            ₱${item.price.toFixed(2)} × ${item.quantity} = ₱${subtotal.toFixed(2)}
                        </div>
                    </div>
                `;
            });
            
            cartItemsDiv.innerHTML = html;
            document.getElementById('total-amount').textContent = total.toFixed(2);
            totalSection.style.display = 'block';
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';
            
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cart_data';
            input.value = JSON.stringify(cart);
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>