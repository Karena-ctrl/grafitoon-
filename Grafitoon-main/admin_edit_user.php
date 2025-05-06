<?php
session_start();
require_once 'Database_Connection.php';

// Admin Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Grafitoon_login.php");
    exit();
}

$user_id_to_edit = null;
$user_data = null;
$name = $email = $role = '';
$errors = [];

// --- Fetch User Data ---
if (isset($_GET['id'])) {
    $user_id_to_edit = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($user_id_to_edit && $conn) {
        $sql_fetch = "SELECT name, email, role FROM users WHERE user_id = ?";
        $stmt_fetch = $conn->prepare($sql_fetch);
        $stmt_fetch->bind_param("i", $user_id_to_edit);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            $name = $user_data['name'];
            $email = $user_data['email'];
            $role = $user_data['role'];
        } else {
            $_SESSION['admin_message'] = "User not found.";
            $_SESSION['admin_message_type'] = 'error';
            header("Location: Grafitoon_admin.php");
            exit();
        }
        $stmt_fetch->close();
    } elseif (!$conn) {
         $errors[] = "Database connection failed.";
    } else {
         $_SESSION['admin_message'] = "Invalid User ID provided.";
         $_SESSION['admin_message_type'] = 'error';
         header("Location: Grafitoon_admin.php");
         exit();
    }
} else {
    // Redirect if no ID is provided
    header("Location: Grafitoon_admin.php");
    exit();
}


// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && $_POST['user_id'] == $user_id_to_edit) {
    // Sanitize and Validate Inputs
    $name = trim($_POST['name'] ?? '');
    $new_email = trim($_POST['email'] ?? ''); // Use new_email to check against others
    $role = trim($_POST['role'] ?? '');
    $new_password = trim($_POST['new_password'] ?? ''); // Optional new password

    if (empty($name)) { $errors[] = "Name is required."; }
    if (empty($new_email)) { $errors[] = "Email is required."; }
    elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    if (empty($role)) { $errors[] = "Role is required."; }
    elseif (!in_array($role, ['customer', 'admin'])) { $errors[] = "Invalid role selected."; }

    // Optional Password Validation (only if provided)
     if (!empty($new_password) && strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }

    // Check if email already exists (for a DIFFERENT user)
    if (empty($errors) && $conn && $new_email !== $user_data['email']) { // Only check if email changed
        $sql_check = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $new_email, $user_id_to_edit);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $errors[] = "This email address is already used by another account.";
        }
        $stmt_check->close();
    }

    // If no errors, proceed with update
    if (empty($errors) && $conn) {
         // Prevent admin from demoting themselves if they are the only admin (optional but recommended)
         if ($user_id_to_edit == $_SESSION['user_id'] && $role == 'customer') {
            // Check if there are other admins
            $sql_admin_count = "SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND user_id != ?";
            $stmt_admin_count = $conn->prepare($sql_admin_count);
            $stmt_admin_count->bind_param("i", $user_id_to_edit);
            $stmt_admin_count->execute();
            $result_admin_count = $stmt_admin_count->get_result()->fetch_assoc();
            $stmt_admin_count->close();
            if ($result_admin_count['admin_count'] == 0) {
                 $errors[] = "Cannot remove admin role. You are the only administrator.";
            }
         }

         // Proceed if still no errors
         if(empty($errors)) {
            // Prepare update query
            $update_fields = ["name = ?", "email = ?", "role = ?"];
            $params = [$name, $new_email, $role];
            $types = "sss";

            // Add password update if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_fields[] = "password = ?";
                $params[] = $hashed_password;
                $types .= "s";
            }

            $params[] = $user_id_to_edit; // Add user_id for WHERE clause
            $types .= "i";

            $sql_update = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param($types, ...$params); // Use argument unpacking

            if ($stmt_update->execute()) {
                $_SESSION['admin_message'] = "User details updated successfully!";
                $_SESSION['admin_message_type'] = 'success';
                header("Location: Grafitoon_admin.php");
                exit();
            } else {
                $errors[] = "Database error updating user: " . htmlspecialchars($conn->error);
            }
            $stmt_update->close();
         }

    } elseif(!$conn) {
        $errors[] = "Database connection failed during update.";
    }

     // If errors occurred during POST, repopulate variables for the form
     $email = $new_email; // Show the attempted email

     if($conn) $conn->close(); // Close connection if open
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Grafitoon Admin</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <style>
        body { margin: 0; font-family: Arial, sans-serif; background: url('images/background.GIF') no-repeat center center fixed; background-size: cover; color: white; display: flex; flex-direction: column; min-height: 100vh; }
        header { background-color: #111; color: #fff; padding: 20px; text-align: center; }
        .logo { font-size: 36px; font-weight: bold; } .grafi { color: white; } .toon { color: orange; }
        nav { display: flex; justify-content: center; gap: 25px; background-color: #1a1a1a; padding: 15px 0; position: sticky; top: 0; z-index: 999; }
        nav a { color: white; text-decoration: none; font-weight: bold; padding: 8px 14px; transition: color 0.3s ease; }
        nav a:hover { color: orange; }
        .form-container { max-width: 600px; margin: 40px auto; background-color: rgba(0, 0, 0, 0.85); padding: 30px; border-radius: 15px; flex-grow: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.4); }
        .form-container h2 { text-align: center; margin-bottom: 25px; color: orange; font-size: 1.8em; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #ccc; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select { width: 100%; padding: 10px 12px; border-radius: 5px; border: 1px solid #555; background-color: #333; color: white; box-sizing: border-box; }
        .form-buttons { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn-submit, .btn-cancel { padding: 10px 20px; border: none; border-radius: 5px; color: white; cursor: pointer; text-decoration: none; font-size: 1em; transition: background-color 0.3s ease; }
        .btn-submit { background-color: #ff6600; } .btn-submit:hover { background-color: #cc5200; }
        .btn-cancel { background-color: #555; text-align: center; } .btn-cancel:hover { background-color: #777; }
        .error-messages { margin-bottom: 20px; padding: 10px; background-color: rgba(255, 0, 0, 0.2); border: 1px solid red; border-radius: 5px; color: #ffcccc; }
        .error-messages ul { margin: 0; padding-left: 20px; }
        footer { background-color: #111; color: #888; text-align: center; padding: 20px; margin-top: auto; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>
<header>
    <div class="logo"><span class="grafi">Grafi</span><span class="toon">toon</span></div>
</header>
<nav>
    <a href="Grafitoon_admin.php"><i class="fas fa-arrow-left"></i> Back to Admin</a>
</nav>

<div class="form-container">
    <h2>Edit User (ID: <?= htmlspecialchars($user_id_to_edit) ?>)</h2>

     <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <strong>Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

     <?php if ($user_data): // Only show form if user data was fetched ?>
    <form method="POST" action="admin_edit_user.php?id=<?= htmlspecialchars($user_id_to_edit) ?>">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id_to_edit) ?>">

        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="customer" <?= ($role === 'customer') ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
             <?php if ($user_id_to_edit == $_SESSION['user_id']): ?>
                <small style="color: #aaa;">Be careful changing your own role.</small>
            <?php endif; ?>
        </div>
         <div class="form-group">
            <label for="new_password">New Password (Optional):</label>
            <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
             <small style="color: #aaa;">Min 6 characters if changing.</small>
        </div>
        <div class="form-buttons">
            <a href="Grafitoon_admin.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Update User</button>
        </div>
    </form>
     <?php endif; ?>
</div>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>
</body>
</html>