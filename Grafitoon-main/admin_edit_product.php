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

$product_id_to_edit = null;
$product_data = null;
$name = $description = $price = $size_options = $category = $stock_quantity = '';
$image_path = ''; // Initialize image path
$errors = [];
$upload_dir = 'images/products/';
$upload_dir_ok = false;

// Check upload directory status
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0775, true)) { $errors[] = "Error: Upload directory ('{$upload_dir}') setup failed."; } else { $upload_dir_ok = true; }
} elseif (!is_writable($upload_dir)) { $errors[] = "Error: Upload directory ('{$upload_dir}') is not writable."; }
else { $upload_dir_ok = true; }


// --- Fetch Product Data ---
if (isset($_GET['id'])) {
    $product_id_to_edit = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($product_id_to_edit && $conn) {
        $sql_fetch = "SELECT * FROM products WHERE product_id = ?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        if($stmt_fetch) { // Check if prepare succeeded
            $stmt_fetch->bind_param("i", $product_id_to_edit);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            if ($result->num_rows === 1) {
                $product_data = $result->fetch_assoc();
                $image_path = $product_data['image_path']; // *** Store original image path reliably ***

                // Populate form fields only on initial load (GET request)
                 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    $name = $product_data['NAME'];
                    $description = $product_data['description'];
                    $price = $product_data['price'];
                    $size_options = $product_data['size_options'];
                    $category = $product_data['category'];
                    $stock_quantity = $product_data['stock_quantity'];
                 }
            } else { /* Product not found redirect... */ }
            $stmt_fetch->close();
        } else {
            $errors[] = "Database error preparing fetch statement: " . htmlspecialchars($conn->error);
        }
    } elseif (!$conn && empty($errors)) { $errors[] = "Database connection failed."; }
    // (Simplified redirect logic)
    if (empty($product_data) && empty($errors)) {
         $_SESSION['admin_message'] = "Product not found or invalid ID.";
         $_SESSION['admin_message_type'] = 'error';
         header("Location: Grafitoon_admin.php"); exit();
    }
} else { /* No ID redirect... */ header("Location: Grafitoon_admin.php"); exit(); }


// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && $_POST['product_id'] == $product_id_to_edit) {
    // Get submitted values (use values from POST to repopulate form on error)
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var(trim($_POST['price'] ?? '0'), FILTER_VALIDATE_FLOAT);
    $size_options = trim($_POST['size_options'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $stock_quantity = filter_var(trim($_POST['stock_quantity'] ?? '0'), FILTER_VALIDATE_INT);

    // --- Input Validations ---
    if (empty($name)) { $errors[] = "Product Name is required."; }
    if (empty($description)) { $errors[] = "Description is required."; }
    if ($price === false || $price < 0) { $errors[] = "Invalid Price."; } // Allow 0 price? Change if needed
    if (empty($category)) { $errors[] = "Category is required."; }
    elseif (!in_array($category, $allowed_categories)) { $errors[] = "Invalid category selected."; }
    if ($stock_quantity === false || $stock_quantity < 0) { $errors[] = "Invalid Stock Quantity."; }

    // --- Image Handling Logic ---
    $path_for_db_update = $image_path; // Initialize with the path fetched earlier
    $upload_attempted = isset($_FILES['new_image']) && $_FILES['new_image']['error'] !== UPLOAD_ERR_NO_FILE;
    $new_upload_succeeded = false;

    if ($upload_attempted) {
        if (!$upload_dir_ok) {
            $errors[] = "Cannot upload image: Upload directory issue.";
        } elseif ($_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['new_image'];
            // ... (file type, size validation) ...
            if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) { $errors[] = "Invalid file type."; }
            elseif ($file['size'] > 5 * 1024 * 1024) { $errors[] = "File size exceeds 5MB limit."; }
            else { // Validation passed, attempt move
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safe_filename_base = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                if(empty($safe_filename_base)) $safe_filename_base = 'product_image';
                $unique_filename = $safe_filename_base . "_" . $product_id_to_edit . "_" . uniqid() . "." . $file_extension;
                $target_path = $upload_dir . $unique_filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $path_for_db_update = $target_path; // *** Update path ONLY on successful move ***
                    $new_upload_succeeded = true;
                } else { $errors[] = "Failed to move uploaded file."; }
            }
        } else { // Other upload error (e.g., partial upload)
             $errors[] = "Error uploading image. Code: " . $_FILES['new_image']['error'];
        }
    }
    // At this point:
    // If no file was uploaded, $path_for_db_update still holds original $image_path.
    // If upload was attempted and succeeded, $path_for_db_update holds the new path.
    // If upload was attempted but failed validation/move, $errors is populated, and $path_for_db_update holds the original $image_path.

    // --- Database Update ---
    if (empty($errors) && $conn) {
        // *** DEBUGGING: Check path before binding ***
        // var_dump($path_for_db_update); exit(); // Uncomment to check

        $sql_update = "UPDATE products SET NAME = ?, description = ?, price = ?, size_options = ?, category = ?, stock_quantity = ?, image_path = ? WHERE product_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if($stmt_update) { // Check prepare success
            $stmt_update->bind_param("ssdsisis", $name, $description, $price, $size_options, $category, $stock_quantity, $path_for_db_update, $product_id_to_edit);

            if ($stmt_update->execute()) {
                 // If update succeeded AND a new image was successfully uploaded, delete old image
                 if ($new_upload_succeeded && !empty($image_path) && $image_path !== $path_for_db_update && file_exists($image_path)) {
                     @unlink($image_path);
                 }
                 $_SESSION['admin_message'] = "Product details updated successfully!";
                 $_SESSION['admin_message_type'] = 'success';
                 header("Location: Grafitoon_admin.php");
                 exit();
            } else { $errors[] = "Database error updating product: " . htmlspecialchars($stmt_update->error); }
            $stmt_update->close();
        } else { $errors[] = "Database error preparing update statement: " . htmlspecialchars($conn->error); }

    } elseif(!$conn && empty($errors)) { $errors[] = "Database connection failed."; }

    // Close DB connection
     if($conn) $conn->close();
} // End POST handler

// If we reach here, it's either a GET request or a POST with errors,
// display the form with current values (either fetched or from POST).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Grafitoon Admin</title>
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
        .form-group select, /* Added select */
        .form-group input[type="file"] { width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid #555; background-color: #333; color: white; box-sizing: border-box; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group input[type="file"] { background-color: transparent; border: none; padding-left: 0; color:#ccc; }
        .form-buttons { display: flex; justify-content: space-between; margin-top: 25px; }
        .btn-submit, .btn-cancel { padding: 10px 20px; border: none; border-radius: 5px; color: white; cursor: pointer; text-decoration: none; font-size: 1em; transition: background-color 0.3s ease; }
        .btn-submit { background-color: #ff6600; } .btn-submit:hover { background-color: #cc5200; }
        .btn-cancel { background-color: #555; text-align: center; } .btn-cancel:hover { background-color: #777; }
        .error-messages { margin-bottom: 20px; padding: 10px; background-color: rgba(255, 0, 0, 0.2); border: 1px solid red; border-radius: 5px; color: #ffcccc; }
        .error-messages ul { margin: 0; padding-left: 20px; }
        .current-image { margin-top: 10px; margin-bottom: 15px; }
        .current-image img { max-width: 150px; max-height: 150px; border-radius: 5px; border: 1px solid #555; display: block; margin-bottom: 5px;}
        .current-image p { font-size: 0.9em; color: #aaa; margin-top: 0; }
        footer { background-color: #111; color: #888; text-align: center; padding: 20px; margin-top: auto; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
<header> <div class="logo"><span class="grafi">Grafi</span><span class="toon">toon</span></div> </header>
<nav> <a href="Grafitoon_admin.php"><i class="fas fa-arrow-left"></i> Back to Admin</a> </nav>

<div class="form-container">
    <h2>Edit Product (ID: <?= htmlspecialchars($product_id_to_edit) ?>)</h2>

     <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <strong>Please fix the following errors:</strong>
            <ul> <?php foreach ($errors as $error): ?> <li><?= htmlspecialchars($error) ?></li> <?php endforeach; ?> </ul>
        </div>
    <?php endif; ?>

     <?php // Check if product_data exists; errors might occur before fetch completes ?>
     <?php if ($product_data || $_SERVER['REQUEST_METHOD'] === 'POST'): // Show form if data exists or if it's a POST request (to show errors with submitted values) ?>
    <form method="POST" action="admin_edit_product.php?id=<?= htmlspecialchars($product_id_to_edit) ?>" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id_to_edit) ?>">

        <div class="form-group"> <label for="name">Product Name:</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required> </div>
        <div class="form-group"> <label for="description">Description:</label> <textarea id="description" name="description" required><?= htmlspecialchars($description) ?></textarea> </div>
        <div class="form-group"> <label for="price">Price ($):</label> <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" min="0" step="0.01" required> </div>

        <div class="form-group">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="" disabled>-- Select Category --</option>
                <?php foreach ($allowed_categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

         <div class="form-group"> <label for="size_options">Size Options (comma-separated):</label> <input type="text" id="size_options" name="size_options" value="<?= htmlspecialchars($size_options) ?>" placeholder="e.g., S,M,L,XL"> </div>
        <div class="form-group"> <label for="stock_quantity">Stock Quantity:</label> <input type="number" id="stock_quantity" name="stock_quantity" value="<?= htmlspecialchars($stock_quantity) ?>" min="0" step="1" required> </div>

        <div class="form-group">
             <label>Current Image:</label>
             <div class="current-image">
                 <?php if (!empty($image_path) && file_exists($image_path)): ?>
                     <img src="<?= htmlspecialchars($image_path) ?>?t=<?= time() // Cache buster ?>" alt="Current">
                     <p><?= htmlspecialchars(basename($image_path)) ?></p>
                 <?php else: ?>
                      <p>No current image.</p>
                      <?php if (!empty($image_path)) echo "<small style='color:red'>(File missing)</small>"; ?>
                 <?php endif; ?>
             </div>
        </div>

        <div class="form-group">
            <label for="new_image">Upload New Image (Optional):</label>
            <input type="file" id="new_image" name="new_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <small style="color: #aaa;">Max 5MB. Replaces current image.</small>
        </div>

        <div class="form-buttons"> <a href="Grafitoon_admin.php" class="btn-cancel">Cancel</a> <button type="submit" class="btn-submit">Update Product</button> </div>
    </form>
     <?php elseif (empty($errors)): // Only show if no errors AND product_data was null (e.g., initial load failed) ?>
         <p class="error-messages">Could not load product data.</p>
     <?php endif; ?>
</div>

<footer> <p>&copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.</p> </footer>
</body>
</html>