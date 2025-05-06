<?php
session_start();
require_once 'Database_Connection.php';

// Admin Authentication Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Grafitoon_login.php");
    exit();
}

$name = $email = $password = $role = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and Validate Inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    if (empty($name)) { $errors[] = "Name is required."; }
    if (empty($email)) { $errors[] = "Email is required."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    if (empty($password)) { $errors[] = "Password is required."; }
    // Basic password length check (add more complex rules if needed)
    elseif (strlen($password) < 6) { $errors[] = "Password must be at least 6 characters long."; }
    if (empty($role)) { $errors[] = "Role is required."; }
    elseif (!in_array($role, ['customer', 'admin'])) { $errors[] = "Invalid role selected."; }

    // Check if email already exists
    if (empty($errors) && $conn) {
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt_check->close();
    }

    // If no errors, proceed with insertion
    if (empty($errors) && $conn) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt_insert->execute()) {
            $_SESSION['admin_message'] = "User created successfully!";
            $_SESSION['admin_message_type'] = 'success';
            header("Location: Grafitoon_admin.php");
            exit();
        } else {
            $errors[] = "Database error creating user: " . htmlspecialchars($conn->error);
        }
        $stmt_insert->close();
        $conn->close();
    } elseif(!$conn) {
         $errors[] = "Database connection failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New User - Grafitoon Admin</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Reuse admin styles, adjust form container */
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
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: white;
            box-sizing: border-box; /* Include padding in width */
        }
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
    <h2>Create New User</h2>

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

    <form method="POST" action="admin_create_user.php">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <small style="color: #aaa;">Min 6 characters.</small>
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="" disabled <?= empty($role) ? 'selected' : '' ?>>-- Select Role --</option>
                <option value="customer" <?= ($role === 'customer') ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-buttons">
            <a href="Grafitoon_admin.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Create User</button>
        </div>
    </form>
</div>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>
</body>
</html>