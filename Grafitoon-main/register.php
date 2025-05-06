<?php
session_start();
include('Database_Connection.php'); // Ensure this file sets up $conn properly

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Account created successfully! You can now <a href='Grafitoon_login.php'>log in</a>.";
            } else {
                $error = "Error registering user. Please try again.";
            }

            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Grafitoon</title>
    <link rel="stylesheet" href="grafitoon_css.css">
</head>
<body>

<div class="background-gif"></div>

<header>
    <div class="grafitoon-logo">
        <span class="grafi">Grafi</span><span class="toon">toon</span>
    </div>
</header>

<nav>
    <a href="grafitoon_index.php">Home</a>
    <a href="about_us.php">About</a>
    <a href="products.php">Products</a>
    <a href="Grafitoon_contactus.php">Contact</a>
    <a href="Grafitoon_login.php">Login</a>
</nav>

<section class="hero">
    <h1>Join the Grafitoon Community</h1>
    <p>Register now and start exploring the drip!</p>
</section>

<section class="login-section">
    <div class="login-card">
        <h2>Create Account</h2>

        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($success)): ?>
            <p style="color: green; font-weight: bold;"><?= $success ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Create Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Register</button>
        </form>

        <p>Already have an account? <a href="Grafitoon_login.php">Login here</a></p>
    </div>
</section>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>

</body>
</html>
