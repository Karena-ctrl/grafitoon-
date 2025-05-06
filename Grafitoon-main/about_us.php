<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Grafitoon</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            background: url('images/background.gif') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); /* Dark overlay for readability */
            z-index: -1;
        }


        header {
            padding: 20px;
            background-color: #000;
            text-align: center;
        }

        .logo {
            font-size: 36px;
            font-weight: bold;
        }

        .grafi {
            color: white;
        }

        .toon {
            color: orange;
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 25px;
            background-color: #1a1a1a;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            position: relative;
            padding: 8px 14px;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: orange;
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            vertical-align: middle;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #222;
            min-width: 180px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            z-index: 1000;
            border-radius: 10px;
            overflow: hidden;
        }

        .profile-dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.3s ease;
        }

        .profile-dropdown-content a:hover {
            background-color: #333;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .hero {
            text-align: center;
            padding: 60px 20px 30px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 1.2rem;
            color: #ccc;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            padding: 40px 20px;
        }

        .feature-card {
            background-color: #1e1e1e;
            padding: 25px;
            border-radius: 20px;
            max-width: 380px;
            box-shadow: 0 0 12px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card h2 {
            color: orange;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #ccc;
            line-height: 1.5;
        }

        .team-card img {
            width: 100%;
            max-width: 320px;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        footer {
            background-color: #000;
            color: #888;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

        #welcome-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: orange;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: bold;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            z-index: 9999;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    <div id="welcome-popup">
        Signed in successfully, welcome back <?= htmlspecialchars($_SESSION['username']) ?>!
    </div>
    <script>
        setTimeout(() => {
            const popup = document.getElementById('welcome-popup');
            if (popup) popup.style.display = 'none';
        }, 4000);
    </script>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<header>
    <div class="logo"><span class="grafi">Grafi</span><span class="toon">toon</span></div>
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
    <h1>About Grafitoon</h1>
    <p>Streetwear meets Saturday morning vibes — bold, animated, unforgettable.</p>
</section>

<section class="features">
    <div class="feature-card">
        <h2>Our Mission</h2>
        <p>Grafitoon is a celebration of self-expression. We fuse graffiti art with cartoon aesthetics to create vibrant and rebellious clothing that lets you stand out, not blend in.</p>
    </div>
    <div class="feature-card">
        <h2>Why Grafitoon?</h2>
        <p>We believe your outfit should speak louder than your words. Our designs are bold, quirky, and full of flavor — perfect for artists, rebels, and streetwear lovers.</p>
    </div>
    <div class="feature-card">
        <h2>Behind the Brand</h2>
        <p>Built by college creatives for fashion-forward thinkers. Grafitoon is a class project turned real-world brand built from the ground up.</p>
    </div>
</section>

<section class="features">
    <div class="feature-card team-card">
        <img src="images/team1.jpg" alt="Juliann Wilson">
        <h2>Juliann Wilson</h2>
        <p>Design lead with a passion for animation and color. Juliann infuses playful vibes and bold contrast into every stitch of Grafitoon gear.</p>
    </div>
    <div class="feature-card team-card">
        <img src="images/team2.jpg" alt="Karena Galloway">
        <h2>Karena Galloway</h2>
        <p>Founder and visionary. Karena blends art, business, and tech to guide Grafitoon’s direction and voice with a unique, stylish edge.</p>
    </div>
    <div class="feature-card team-card">
        <img src="images/team3.jpg" alt="Ira Thompson">
        <h2>Ira Thompson</h2>
        <p>The engine room of creativity. From graphics to backend, Ira brings everything together — a true multi-talented force behind Grafitoon.</p>
    </div>
</section>

<footer>
    &copy; <?= date('Y') ?> Grafitoon. All rights reserved.
</footer>

</body>
</html>
