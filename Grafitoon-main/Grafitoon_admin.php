<?php
session_start();
require_once 'Database_Connection.php'; // Ensure $conn is correctly established here

// Admin Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Grafitoon_login.php");
    exit();
}

// --- Initialize Variables ---
$user_search_term = '';
$product_search_term = '';
$users = [];
$products = [];
$user_error = $product_error = $connection_error = null;

// --- Database Operations ---
if ($conn) { // Check if connection is valid before proceeding

    // --- User Search & Fetch Logic ---
    $user_search_term = trim($_GET['search_user'] ?? ''); // Use specific param name
    if (!empty($user_search_term)) {
        $sql_users = "SELECT user_id, name, email, role, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
        $stmt_users = $conn->prepare($sql_users);
        $like_term_user = "%" . $user_search_term . "%";
        $stmt_users->bind_param("ss", $like_term_user, $like_term_user);
    } else {
        $sql_users = "SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt_users = $conn->prepare($sql_users);
    }

    if ($stmt_users) {
        if($stmt_users->execute()){ // Execute the prepared statement
             $result_users = $stmt_users->get_result();
             if ($result_users) {
                 while ($row = $result_users->fetch_assoc()) {
                     $users[] = $row;
                 }
             } else {
                 $user_error = "Error getting user results: " . htmlspecialchars($conn->error);
             }
        } else {
             $user_error = "Error executing user query: " . htmlspecialchars($stmt_users->error);
        }
        $stmt_users->close();
    } else {
         $user_error = "Error preparing user query: " . htmlspecialchars($conn->error);
    }


    // --- Product Search & Fetch Logic ---
    $product_search_term = trim($_GET['search_product'] ?? ''); // Use specific param name
    if(!empty($product_search_term)) {
        $sql_products = "SELECT * FROM products WHERE NAME LIKE ? OR category LIKE ? ORDER BY created_at DESC";
        $stmt_products = $conn->prepare($sql_products);
        $like_term_product = "%" . $product_search_term . "%";
        $stmt_products->bind_param("ss", $like_term_product, $like_term_product);
    } else {
         $sql_products = "SELECT * FROM products ORDER BY created_at DESC";
         $stmt_products = $conn->prepare($sql_products); // Prepare even if no search term
    }

     if ($stmt_products) {
        if($stmt_products->execute()){ // Execute the prepared statement
             $result_products = $stmt_products->get_result();
             if ($result_products) {
                  while ($row = $result_products->fetch_assoc()) {
                      $products[] = $row;
                  }
             } else {
                  $product_error = "Error getting product results: " . htmlspecialchars($conn->error);
             }
        } else {
            $product_error = "Error executing product query: " . htmlspecialchars($stmt_products->error);
        }
         $stmt_products->close();
    } else {
        $product_error = "Error preparing product query: " . htmlspecialchars($conn->error);
    }


     // Close connection if it was opened successfully initially
     $conn->close();

} else {
    $connection_error = "Database connection error. Check Database_Connection.php";
    // Set errors for both sections if connection fails
    $user_error = "Database connection failed.";
    $product_error = "Database connection failed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grafitoon - Admin Panel</title>
    <link rel="stylesheet" href="grafitoon_css.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Base Styles */
        body { margin: 0; font-family: Arial, sans-serif; background: url('images/background.GIF') no-repeat center center fixed; background-size: cover; color: white; display: flex; flex-direction: column; min-height: 100vh; }
        header { background-color: #111; color: #fff; padding: 20px; text-align: center; }
        .logo { font-size: 36px; font-weight: bold; } .grafi { color: white; } .toon { color: orange; }
        nav { display: flex; justify-content: center; gap: 25px; background-color: #1a1a1a; padding: 15px 0; position: sticky; top: 0; z-index: 999; }
        nav a { color: white; text-decoration: none; font-weight: bold; padding: 8px 14px; transition: color 0.3s ease; }
        nav a:hover { color: orange; }
        .profile-dropdown { position: relative; display: inline-block; margin-left: 15px; }
        .profile-avatar { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; cursor: pointer; vertical-align: middle; border: 2px solid #444; }
        .profile-dropdown-content { display: none; position: absolute; right: 0; background-color: #222; min-width: 180px; box-shadow: 0 0 10px rgba(0,0,0,0.5); z-index: 1000; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .profile-dropdown-content a { color: white; padding: 12px 16px; text-decoration: none; display: block; transition: background 0.3s ease; font-size: 0.95em; white-space: nowrap; }
        .profile-dropdown-content a i { margin-right: 8px; width: 16px; text-align: center; }
        .profile-dropdown-content a:hover { background-color: #333; }
        .profile-dropdown:hover .profile-dropdown-content { display: block; }

        /* Admin Container & Sections */
        .admin-container { max-width: 1200px; margin: 40px auto; background-color: rgba(0, 0, 0, 0.85); padding: 30px; border-radius: 15px; flex-grow: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
        .admin-section { margin-bottom: 40px; }
        .admin-section h2 { text-align: center; margin-bottom: 20px; color: orange; font-size: 1.8em; border-bottom: 2px solid orange; padding-bottom: 10px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; background-color: #2e2e2e; color: #f0f0f0; border-radius: 8px; overflow: hidden; margin-top: 20px; }
        table th, table td { border: 1px solid #444; padding: 12px 15px; text-align: left; vertical-align: middle; }
        table th { background-color: #ff6600; color: white; font-weight: bold; }
        table tr:nth-child(even) { background-color: rgba(255, 255, 255, 0.05); }
        table tr:hover { background-color: rgba(255, 165, 0, 0.1); }
        .admin-actions a { margin-right: 12px; color: #ffab73; text-decoration: none; transition: color 0.3s ease; white-space: nowrap; }
        .admin-actions a:last-child { margin-right: 0; }
        .admin-actions a:hover { color: #ff6600; text-decoration: underline; }
        .admin-actions i { margin-right: 5px; }
        td img.product-thumb { max-width: 50px; max-height: 50px; border-radius: 4px; vertical-align: middle; margin-right: 10px;} /* Style for product image thumbnail */

        /* Search and Add Forms/Buttons */
        .admin-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .search-form { display: flex; gap: 10px; }
        .search-form input[type="text"] { padding: 8px 12px; border-radius: 5px; border: 1px solid #555; background-color: #333; color: white; min-width: 200px; } /* Ensure input is wide enough */
        .search-form button, .btn-add-new { padding: 8px 15px; border: none; border-radius: 5px; background-color: #ff6600; color: white; cursor: pointer; text-decoration: none; font-size: 0.95em; transition: background-color 0.3s ease; }
        .search-form button:hover, .btn-add-new:hover { background-color: #cc5200; }
        .btn-add-new i, .search-form button i { margin-right: 5px; }
         .clear-search-link { margin-left: 10px; color: #ccc; font-size: 0.9em; text-decoration: none; }
         .clear-search-link:hover { color: white; }

        /* Footer */
        footer { background-color: #111; color: #888; text-align: center; padding: 20px; margin-top: auto; width: 100%; box-sizing: border-box; }
         /* Alert Message Styling */
         .alert { padding: 10px 15px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }
         .alert-success { background-color: #4CAF50; color: white; }
         .alert-danger { background-color: #f44336; color: white; }
    </style>
</head>
<body>

<header>
    <div class="logo"><span class="grafi">Grafi</span><span class="toon">toon</span></div>
</header>

<nav>
    <a href="Grafitoon_index.php">Home</a>
    <a href="about_us.php">About</a>
    <a href="Grafitoon_shoppingsection.php">Shop</a>
    <a href="Grafitoon_contactus.php">Contact</a>
    <a href="Grafitoon_shoppingcart.php"><i class="fas fa-shopping-cart"></i></a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="profile-dropdown">
            <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'images/placeholders/default_profile.png') ?>" alt="Profile" class="profile-avatar">
            <div class="profile-dropdown-content">
                <a href="Grafitoon_profile.php"><i class="fas fa-user fa-fw"></i> My Profile</a>
                <a href="grafitoon_checkout.php"><i class="fas fa-credit-card fa-fw"></i> Checkout</a>
                <a href="Grafitoon_ordershistory.php"><i class="fas fa-history fa-fw"></i> Order History</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="Grafitoon_admin.php"><i class="fas fa-tools fa-fw"></i> Admin Dashboard</a>
                <?php endif; ?>
                <a href="#" onclick="confirmLogout()"><i class="fas fa-sign-out-alt fa-fw"></i> Sign Out</a>
            </div>
        </div>
    <?php else: ?>
        <a href="Grafitoon_login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
</nav>

<script>
function confirmLogout() { if (confirm("Are you sure you want to sign out?")) { window.location.href = "logout.php"; } }
function confirmUserDelete(userId) { if (confirm("Delete this user?\nThis action cannot be undone.")) { window.location.href = 'admin_delete_user.php?id=' + userId; } return false; }
function confirmProductDelete(productId) { if (confirm("Delete this product?\nThis action cannot be undone.")) { window.location.href = 'admin_delete_product.php?id=' + productId; } return false; }
</script>

<div class="admin-container">

    <?php if (isset($connection_error)): ?>
        <div class="alert alert-danger"><?= $connection_error ?></div>
    <?php endif; ?>

     <?php if(isset($_SESSION['admin_message'])): ?>
        <div class="alert <?= (isset($_SESSION['admin_message_type']) && $_SESSION['admin_message_type'] == 'error') ? 'alert-danger' : 'alert-success' ?>">
            <?= htmlspecialchars($_SESSION['admin_message']) ?>
        </div>
        <?php unset($_SESSION['admin_message'], $_SESSION['admin_message_type']); // Clear message ?>
    <?php endif; ?>


    <section class="admin-section">
        <h2>User Management</h2>
        <div class="admin-controls">
            <form method="GET" action="Grafitoon_admin.php" class="search-form">
                <input type="text" name="search_user" placeholder="Search Name or Email..." value="<?= htmlspecialchars($user_search_term) ?>">
                <button type="submit"><i class="fas fa-search"></i> Search Users</button>
                 <?php if (!empty($user_search_term)): ?>
                    <a href="Grafitoon_admin.php" class="clear-search-link">Clear</a>
                <?php endif; ?>
            </form>
            <a href="admin_create_user.php" class="btn-add-new"><i class="fas fa-user-plus"></i> Create New User</a>
        </div>

        <?php if (isset($user_error)): ?> <p class='alert alert-danger'><?= $user_error ?></p>
        <?php elseif (empty($users) && !empty($user_search_term)): ?> <p style='text-align: center;'>No users found matching '<?= htmlspecialchars($user_search_term) ?>'.</p>
        <?php elseif (empty($users)): ?> <p style='text-align: center;'>No users found.</p>
        <?php else: ?>
            <table>
                <thead></thead>
                <tbody>
                    <?php foreach ($users as $user): $current_user_id = $_SESSION['user_id']; ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($user['role'])) ?></td>
                            <td><?= date("Y-m-d H:i", strtotime($user['created_at'])) ?></td>
                            <td class="admin-actions">
                                <a href="admin_edit_user.php?id=<?= $user['user_id'] ?>" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                <?php if ($user['user_id'] != $current_user_id): ?>
                                    <a href="#" onclick="return confirmUserDelete(<?= $user['user_id'] ?>);" title="Delete"><i class="fas fa-trash-alt"></i> Delete</a>
                                <?php else: ?>
                                     <span title="Cannot delete self" style="color:#666;cursor:not-allowed;"><i class="fas fa-trash-alt"></i> Delete</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section> <section class="admin-section">
        <h2>Product Management</h2>
        <div class="admin-controls">
             <form method="GET" action="Grafitoon_admin.php" class="search-form">
                 <input type="text" name="search_product" placeholder="Search Product Name or Category..." value="<?= htmlspecialchars($product_search_term) ?>">
                 <button type="submit"><i class="fas fa-search"></i> Search Products</button>
                 <?php if (!empty($product_search_term)): ?>
                     <a href="Grafitoon_admin.php" class="clear-search-link">Clear</a>
                 <?php endif; ?>
             </form>
             <a href="admin_create_product.php" class="btn-add-new"><i class="fas fa-plus"></i> Add New Product</a>
         </div>

        <?php if (isset($product_error)): ?> <p class='alert alert-danger'><?= $product_error ?></p>
        <?php elseif (empty($products) && !empty($product_search_term)): ?> <p style='text-align: center;'>No products found matching '<?= htmlspecialchars($product_search_term) ?>'.</p>
        <?php elseif (empty($products)): ?> <p style='text-align: center;'>No products found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                             <td><?= $product['product_id'] ?></td>
                             <td>
                                 <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                                     <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['NAME']) ?>" class="product-thumb">
                                 <?php else: ?>
                                     <i class="fas fa-image fa-lg" title="No image" style="color:#666;"></i>
                                 <?php endif; ?>
                             </td>
                             <td><?= htmlspecialchars($product['NAME']) ?></td>
                             <td><?= htmlspecialchars($product['category']) ?></td>
                             <td>$<?= number_format(htmlspecialchars($product['price']), 2) ?></td>
                             <td><?= $product['stock_quantity'] ?></td>
                             <td class="admin-actions">
                                 <a href="admin_edit_product.php?id=<?= $product['product_id'] ?>" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                 <a href="#" onclick="return confirmProductDelete(<?= $product['product_id'] ?>);" title="Delete"><i class="fas fa-trash-alt"></i> Delete</a>
                             </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section> </div> <footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>
</body>
</html>
