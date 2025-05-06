<?php
session_start();
include('Database_Connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: Grafitoon_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $stmt->bind_param("si", $name, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['username'] = $name;
            $success = "Profile updated successfully.";
        } else {
            $error = "Name cannot be empty.";
        }
    }

    if (isset($_POST['update_password'])) {
        $old_password = trim($_POST['old_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (!empty($old_password) && !empty($new_password) && $new_password === $confirm_password) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($old_password, $hashed_password)) {
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_hashed, $user_id);
                $stmt->execute();
                $stmt->close();
                $success = "Password updated successfully.";
            } else {
                $error = "Old password is incorrect.";
            }
        } else {
            $error = "Passwords must match and cannot be empty.";
        }
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $target_dir = "uploads/profile_pictures/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid("profile_", true) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['profile_picture'] = $target_file;
            $success = "Profile picture updated.";
        } else {
            $error = "Failed to upload image.";
        }
    }
}

$stmt = $conn->prepare("SELECT name, email, role, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $role, $profile_picture);
$stmt->fetch();
$stmt->close();

$role_display = ($role === 'admin') ? 'Administrator' : 'Customer';
$profile_picture_url = !empty($profile_picture) ? $profile_picture : 'images/default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Grafitoon</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .profile-card {
            max-width: 600px;
            margin: 80px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.25);
        }
        .profile-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff6600;
        }
        .profile-card h2 {
            margin-top: 10px;
            color: #333;
        }
        .profile-card form {
            margin-top: 20px;
            text-align: left;
        }
        .profile-card input[type="text"],
        .profile-card input[type="email"],
        .profile-card input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .profile-card input[type="file"] {
            margin: 10px 0;
        }
        .profile-card button {
            background-color: #ff6600;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .profile-card button:hover {
            background-color: #e65c00;
        }
        .status {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        .profile-dropdown-content a {
            color: black;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
        }
        .profile-dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }
    </style>
</head>
<body>

<div class="background-gif"></div>

<header>
    <div class="logo">
        <span class="grafi">Grafi</span><span class="toon">toon</span>
    </div>
</header>

<nav>
    <a href="grafitoon_index.php">Home</a>
    <a href="about_us.php">About</a>
    <a href="Grafitoon_shoppingsection.php">Shop</a>
    <a href="Grafitoon_contactus.php">Contact</a>
    <a href="Grafitoon_shoppingcart.php"><i class="fas fa-shopping-cart"></i></a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="profile-dropdown">
            <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? 'images/placeholders/default_profile.png') ?>" alt="Profile" class="profile-avatar">
            <div class="profile-dropdown-content">
                <a href="Grafitoon_profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="grafitoon_checkout.php"><i class="fas fa-credit-card"></i> Checkout</a>
                <a href="Grafitoon_ordershistory.php"><i class="fas fa-history"></i> Order History</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="Grafitoon_admin.php"><i class="fas fa-tools fa-fw"></i> Admin Dashboard</a>
                <?php endif; ?>
                <a href="#" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            </div>
        </div>
    <?php else: ?>
        <a href="Grafitoon_login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
</nav>

<script>
function confirmLogout() {
    if (confirm("Are you sure you want to sign out?")) {
        window.location.href = "logout.php";
    }
}
</script>

<div class="profile-card">
    <img src="<?= htmlspecialchars($profile_picture_url) ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($name) ?></h2>
    <p style="color: black;"><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p style="color: black;"><strong>Account Type:</strong> <?= htmlspecialchars($role_display) ?></p>

    <?php if ($success): ?><p class="status"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="profile_picture" style="color: black;">Change Profile Picture:</label><br>
        <input style="color: black;" type="file" name="profile_picture" accept="image/*">
        <button type="submit">Upload</button>
    </form>

    <form method="post">
        <h3 style="color: black;">Update Name</h3>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        <button type="submit" name="update_profile">Save</button>
    </form>

    <form method="post">
        <h3 style="color: black;">Change Password</h3>
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit" name="update_password">Update Password</button>
    </form>
</div>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All rights reserved.
</footer>

</body>
</html>
