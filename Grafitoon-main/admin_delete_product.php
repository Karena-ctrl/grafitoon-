<?php
session_start();
require_once 'Database_Connection.php';

// Admin Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['admin_message'] = "Unauthorized access.";
    $_SESSION['admin_message_type'] = 'error';
    header("Location: Grafitoon_login.php");
    exit();
}

$product_id_to_delete = null;
$image_path_to_delete = null;

if (isset($_GET['id'])) {
    $product_id_to_delete = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($product_id_to_delete && $conn) {
        // **WARNING**: Deleting products can affect historical orders if they reference this product_id.
        // Consider soft delete (setting 'is_available' = 0) instead for e-commerce.
        // Proceeding with hard delete as requested.

        // Step 1: Get the image path BEFORE deleting the DB record
        $sql_fetch = "SELECT image_path FROM products WHERE product_id = ?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param("i", $product_id_to_delete);
        if ($stmt_fetch->execute()) {
             $result_fetch = $stmt_fetch->get_result();
             if ($result_fetch->num_rows === 1) {
                 $product_data = $result_fetch->fetch_assoc();
                 $image_path_to_delete = $product_data['image_path'];
             }
        } else {
             // Log error or notify, but proceed with deletion attempt anyway
             error_log("Admin Delete Product: Failed to fetch image path for product ID $product_id_to_delete - " . $conn->error);
        }
        $stmt_fetch->close();


        // Step 2: Delete the product record from the database
        $sql_delete = "DELETE FROM products WHERE product_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $product_id_to_delete);

        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['admin_message'] = "Product (ID: " . $product_id_to_delete . ") deleted successfully.";
                $_SESSION['admin_message_type'] = 'success';

                // Step 3: Attempt to delete the image file if path was found
                if (!empty($image_path_to_delete)) {
                    if (file_exists($image_path_to_delete)) {
                        if (!unlink($image_path_to_delete)) {
                            // Image deletion failed - add note to success message
                            $_SESSION['admin_message'] .= " (Note: Could not delete image file: " . htmlspecialchars($image_path_to_delete) . ". Check permissions.)";
                        }
                    } else {
                         // Image path existed in DB but file didn't - add note
                         $_SESSION['admin_message'] .= " (Note: Image file not found at " . htmlspecialchars($image_path_to_delete) . ".)";
                    }
                }

            } else {
                 $_SESSION['admin_message'] = "Product (ID: " . $product_id_to_delete . ") not found or already deleted.";
                 $_SESSION['admin_message_type'] = 'error';
            }
        } else {
            $_SESSION['admin_message'] = "Database error deleting product: " . htmlspecialchars($conn->error);
            $_SESSION['admin_message_type'] = 'error';
        }
        $stmt_delete->close();
        $conn->close();

    } elseif (!$conn) {
        $_SESSION['admin_message'] = "Database connection failed.";
        $_SESSION['admin_message_type'] = 'error';
    } else {
         $_SESSION['admin_message'] = "Invalid Product ID provided for deletion.";
         $_SESSION['admin_message_type'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "No Product ID provided for deletion.";
    $_SESSION['admin_message_type'] = 'error';
}

// Redirect back to the admin page
header("Location: Grafitoon_admin.php");
exit();
?>