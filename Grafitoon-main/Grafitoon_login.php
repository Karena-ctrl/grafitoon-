<?php
session_start();
include('Database_Connection.php'); // Ensure connection is working

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $name, $hashed_password, $role);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $name;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;

                    header("Location: grafitoon_index.php?login=success");
                    exit();
                } else {
                    $error = "The email or password entered is incorrect.";
                }
            } else {
                $error = "The email or password entered is incorrect.";
            }
            $stmt->close();
        } else {
            $error = "Something went wrong. Please try again.";
        }
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
    <title>Login - Grafitoon</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <style>
        .popup {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #00000080;
            z-index: 9999;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>

<?php if (isset($_GET['login']) && $_GET['login'] === 'success' && isset($_SESSION['username'])): ?>
    <div class="popup" id="loginPopup">
        Signed in Successfully, welcome back <?= htmlspecialchars($_SESSION['username']) ?>!
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const popup = document.getElementById("loginPopup");
            if (popup) {
                popup.style.display = "block";
                setTimeout(() => popup.style.display = "none", 5000);
            }
        });
    </script>
<?php endif; ?>

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
    <h1>Welcome Back</h1>
    <p>Login to continue your Grafitoon journey.</p>
</section>

<section class="login-section">
    <div class="login-card">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</section>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>

</body>
</html>
