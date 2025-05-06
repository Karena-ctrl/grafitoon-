<?php
session_start();
require 'Database_Connection.php'; // Make sure this file connects using $conn
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafitoon - Home</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            padding: 40px 20px;
            background-color: rgba(255, 255, 255, 0.45);
            border-radius: 10px;
            margin: 30px;
            backdrop-filter: blur(3px);
        }
        .feature-card {
            background: white;
            padding: 15px;
            border-radius: 12px;
            width: 250px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        .feature-card img {
            width: 100%;
            border-radius: 10px;
            object-fit: cover;
        }
        .feature-card h3 {
            margin-top: 15px;
            color: #222;
        }
        .feature-card p {
            color: #444;
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
        .background-gif {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: url('images/background.GIF') no-repeat center center fixed;
            background-size: cover;
            z-index: -1;
            opacity: 0.3;
        }
        footer {
            text-align: center;
            padding: 15px;
            background-color: #111;
            color: #fff;
            position: relative;
            bottom: 0;
            width: 100%;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    <div id="welcome-popup" style="
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #ff6600;
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        font-weight: bold;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        z-index: 1000;
    ">
        Signed in successfully, welcome back <?= htmlspecialchars($_SESSION['username']) ?>!
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('welcome-popup').style.display = 'none';
        }, 5000);
    </script>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<div class="background-gif"></div>

<header>
  <div class="logo">
    <img src="images/grafitoon_logo.png" alt="GrafitoonLogo" width="160">
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

<section class="hero">
    <h1>Welcome to Grafitoon</h1>
    <p>Where Urban Streetwear Meets Cartoon Vibes!</p>
</section>

<section class="features">
<?php
$sql = "SELECT product_id, NAME, description, image_path FROM products LIMIT 3";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    while($row = $result->fetch_assoc()):
?>
    <div class="feature-card">
        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['NAME']) ?>">
        <h3><?= htmlspecialchars($row['NAME']) ?></h3>
        <p><?= htmlspecialchars($row['description']) ?></p>
    </div>
<?php
    endwhile;
else:
    echo "<p>No products available at the moment. Please check back later!</p>";
endif;
?>
</section>

<footer>
    &copy; 2025 Grafitoon. All rights reserved.
</footer>

</body>
</html>
