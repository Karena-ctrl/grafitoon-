<?php
session_start(); // Add this to manage sessions

// Include configuration file for database connection
require_once 'configuration.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user'] = $email; // Store user email in session
                $_SESSION['user_id'] = $user_id;
                header("Location: Grafitoon_index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "No account found with this email.";
        }
        $stmt->close();
    } else {
        $error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Grafitoon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #4a4a4a;
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #131313;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .signin-container {
            margin: 50px auto;
            padding: 20px;
            background: #2b2b2b;
            width: 300px;
            border-radius: 10px;
        }
        .signin-container input, .signin-container button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
        }
        .signin-container input {
            background: #f9f9f9;
            color: #000;
        }
        .signin-container button {
            background-color: #ff6600;
            color: white;
            cursor: pointer;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        a {
            color: #ff6600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>Grafitoon - Sign In</header>
    <div class="signin-container">
        <h2>Sign In</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a href="Grafitoon_signup.php">Sign Up</a></p>
    </div>
</body>
</html>
