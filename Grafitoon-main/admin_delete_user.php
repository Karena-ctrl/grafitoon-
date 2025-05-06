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

$user_id_to_delete = null;

if (isset($_GET['id'])) {
    $user_id_to_delete = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // **CRITICAL**: Prevent admin from deleting their own account
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $_SESSION['admin_message'] = "Error: You cannot delete your own account.";
        $_SESSION['admin_message_type'] = 'error';
        header("Location: Grafitoon_admin.php");
        exit();
    }

    if ($user_id_to_delete && $conn) {
        // **WARNING**: Deleting users can orphan related data (orders, logs).
        // Consider implementing soft delete (setting an 'is_active' flag) instead,
        // or setting up database constraints (ON DELETE SET NULL / ON DELETE CASCADE) carefully.
        // For this example, we proceed with direct deletion as requested.

        $sql_delete = "DELETE FROM users WHERE user_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id_to_delete);

        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $_SESSION['admin_message'] = "User (ID: " . $user_id_to_delete . ") deleted successfully.";
                $_SESSION['admin_message_type'] = 'success';
            } else {
                 $_SESSION['admin_message'] = "User (ID: " . $user_id_to_delete . ") not found or already deleted.";
                 $_SESSION['admin_message_type'] = 'error';
            }
        } else {
            $_SESSION['admin_message'] = "Database error deleting user: " . htmlspecialchars($conn->error);
            $_SESSION['admin_message_type'] = 'error';
        }
        $stmt_delete->close();
        $conn->close();

    } elseif (!$conn) {
        $_SESSION['admin_message'] = "Database connection failed.";
        $_SESSION['admin_message_type'] = 'error';
    } else {
         $_SESSION['admin_message'] = "Invalid User ID provided for deletion.";
         $_SESSION['admin_message_type'] = 'error';
    }
} else {
    $_SESSION['admin_message'] = "No User ID provided for deletion.";
    $_SESSION['admin_message_type'] = 'error';
}

// Redirect back to the admin page regardless of outcome
header("Location: Grafitoon_admin.php");
exit();
?>