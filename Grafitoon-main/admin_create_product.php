<?php
session_start();
require_once 'Database_Connection.php';

// Admin Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Grafitoon_login.php");
    exit();
}

// Define allowed product categories
$allowed_categories = ['T-Shirts', 'Hoodies', 'Pants', 'Accessories'];

$name = $description = $price = $size_options = $category = $stock_quantity = '';
$errors = [];
$upload_dir = 'images/products/';
$upload_dir_ok = false; // Flag for directory status

// Check upload directory
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0775, true)) {
        $errors[] = "Error: Upload directory ('{$upload_dir}') setup failed.";
    } else {
        $upload_dir_ok = true;
    }
} elseif (!is_writable($upload_dir)) {
    $errors[] = "Error: Upload directory ('{$upload_dir}') is not writable.";
} else {
    $upload_dir_ok = true;
}

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and Validate Inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var(trim($_POST['price'] ?? '0'), FILTER_VALIDATE_FLOAT);
    $size_options = trim($_POST['size_options'] ?? '');
    $category = trim($_POST['category'] ?? ''); // Get selected category
    $stock_quantity = filter_var(trim($_POST['stock_quantity'] ?? '0'), FILTER_VALIDATE_INT);

    // --- Input Validations ---
    if (empty($name)) { $errors[] = "Product Name is required."; }
    if (empty($description)) { $errors[] = "Description is required."; }
    if ($price === false || $price <= 0) { $errors[] = "Invalid Price. Must be a positive number."; }
    if (empty($category)) { $errors[] = "Category is required."; }
    elseif (!in_array($category, $allowed_categories)) { $errors[] = "Invalid category selected."; } // Check against allowed list
    if ($stock_quantity === false || $stock_quantity < 0) { $errors[] = "Invalid Stock Quantity."; }

    $new_image_path = null; // Reset image path

    // --- Handle Image Upload (Only if initial validation passes and directory is okay) ---
    if (empty($errors) && $upload_dir_ok) {
         if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];

            // Validate file type and size
             if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                 $errors[] = "Invalid file type.";
             } elseif ($file['size'] > 5 * 1024 * 1024) { // Max 5MB
                 $errors[] = "File size exceeds 5MB limit.";
             } else { // Validation passed
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safe_filename_base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                if (empty($safe_filename_base)) $safe_filename_base = 'product_image';
                $unique_filename = $safe_filename_base . "_" . uniqid() . "." . $file_extension;
                $target_path = $upload_dir . $unique_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $new_image_path = $target_path; // Assign path ONLY on success
                } else {
                    $errors[] = "Failed to move uploaded file.";
                }
            }
        } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Product image is required."; // Still required for create
        } elseif (isset($_FILES['product_image'])) { // Check if file was uploaded but had other errors
            $errors[] = "Error uploading image. Code: " . $_FILES['product_image']['error'];
        } else {
             $errors[] = "Product image is required (no file data received)."; // Should not happen with 'required' form attribute
        }
    } elseif (!$upload_dir_ok && empty($errors)) {
        // If directory wasn't okay initially, add error now if no other validation errors exist yet
        $errors[] = "Cannot process image due to upload directory issue.";
    }

    // --- Database Insertion (Only if NO errors and image path is set) ---
    if (empty($errors) && $conn && $new_image_path !== null) {

        $sql_insert = "INSERT INTO products (NAME, description, price, size_options, category, stock_quantity, image_path, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);

        if ($stmt_insert) { // Check if prepare() succeeded
             // Types: s s d s s i s
             $stmt_insert->bind_param("ssdsiss", $name, $description, $price, $size_options, $category, $stock_quantity, $new_image_path);

             if ($stmt_insert->execute()) {
                 $_SESSION['admin_message'] = "Product created successfully!";
                 $_SESSION['admin_message_type'] = 'success';
                 header("Location: Grafitoon_admin.php");
                 exit();
             } else {
                 $errors[] = "Database error creating product: " . htmlspecialchars($stmt_insert->error); // Use $stmt_insert->error
                 if ($new_image_path && file_exists($new_image_path)) { @unlink($new_image_path); }
             }
             $stmt_insert->close();
        } else {
            // Error preparing the statement
            $errors[] = "Database error preparing statement: " . htmlspecialchars($conn->error);
        }

    } elseif(!$conn && empty($errors)) { // Add error only if no previous errors
        $errors[] = "Database connection failed during insert.";
    }

    // Close connection if open
    if($conn) $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Product - Grafitoon Admin</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <style>
        body { margin: 0; font-family: Arial, sans-serif; background: url('images/background.GIF') no-repeat center center fixed; background-size: cover; color: white; display: flex; flex-direction: column; min-height: 100vh; }
        header { background-color: #111; color: #fff; padding: 20px; text-align: center; }
        .logo { font-size: 36px; font-weight: bold; } .grafi { color: white; } .toon { color: orange; }
        nav { display: flex; justify-content: center; gap: 25px; background-color: #1a1a1a; padding: 15px 0; position: sticky; top: 0; z-index: 999; }
        nav a { color: white; text-decoration: none; font-weight: bold; padding: 8px 14px; transition: color 0.3s ease; }
        nav a:hover { color: orange; }
        .form-container { max-width: 700px; margin: 40px auto; background-color: rgba(0, 0, 0, 0.85); padding: 30px; border-radius: 15px; flex-grow: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
        .form-container h2 { text-align: center; margin-bottom: 25px; color: orange; font-size: 1.8em; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #ccc; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select,
        .form-group input[type="file"] { width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid #555; background-color: #333; color: white; box-sizing: border-box; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group input[type="file"] { background-color: transparent; border: none; padding-left: 0; color:#ccc; }
        .form-buttons { display: flex; justify-content: space-between; margin-top: 25px; }
        .btn-submit, .btn-cancel { padding: 10px 20px; border: none; border-radius: 5px; color: white; cursor: pointer; text-decoration: none; font-size: 1em; transition: background-color 0.3s ease; }
        .btn-submit { background-color: #ff6600; } .btn-submit:hover { background-color: #cc5200; }
        .btn-cancel { background-color: #555; text-align: center; } .btn-cancel:hover { background-color: #777; }
        .error-messages { margin-bottom: 20px; padding: 10px; background-color: rgba(255, 0, 0, 0.2); border: 1px solid red; border-radius: 5px; color: #ffcccc; }
        .error-messages ul { margin: 0; padding-left: 20px; }
        footer { background-color: #111; color: #888; text-align: center; padding: 20px; margin-top: auto; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
<header> <div class="logo"><span class="grafi">Grafi</span><span class="toon">toon</span></div> </header>
<nav> <a href="Grafitoon_admin.php"><i class="fas fa-arrow-left"></i> Back to Admin</a> </nav>

<div class="form-container">
    <h2>Create New Product</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <strong>Please fix the following errors:</strong>
            <ul> <?php foreach ($errors as $error): ?> <li><?= htmlspecialchars($error) ?></li> <?php endforeach; ?> </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_create_product.php" enctype="multipart/form-data">
        <div class="form-group"> <label for="name">Product Name:</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required> </div>
        <div class="form-group"> <label for="description">Description:</label> <textarea id="description" name="description" required><?= htmlspecialchars($description) ?></textarea> </div>
        <div class="form-group"> <label for="price">Price ($):</label> <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" min="0.01" step="0.01" required> </div>

        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="" disabled <?= empty($category) ? 'selected' : '' ?>>-- Select Category --</option>
                <?php foreach ($allowed_categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

         <div class="form-group"> <label for="size_options">Size Options (comma-separated):</label> <input type="text" id="size_options" name="size_options" value="<?= htmlspecialchars($size_options) ?>" placeholder="e.g., S,M,L,XL or One Size"> </div>
        <div class="form-group"> <label for="stock_quantity">Stock Quantity:</label> <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($stock_quantity) ?>" min="0" step="1" required> </div>
        <div class="form-group"> <label for="product_image">Product Image:</label> <input type="file" id="product_image" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp" required> <small style="color: #aaa;">Required. Max 5MB.</small> </div>
        <div class="form-buttons"> <a href="Grafitoon_admin.php" class="btn-cancel">Cancel</a> <button type="submit" class="btn-submit">Create Product</button> </div>
    </form>
</div>

<footer> <p>&copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.</p> </footer>
</body>
</html>
