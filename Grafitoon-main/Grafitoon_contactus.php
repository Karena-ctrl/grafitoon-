<?php
session_start();
$page_title = "Contact Us - Grafitoon";
$company_name = "Grafitoon";
$address = "123 Cartoon Lane, ToonTown, TX 75001";
$email = "support@grafitoon.com";
$phone = "(123) 456-7890";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $page_title; ?></title>
  <link rel="stylesheet" href="grafitoon_css.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    html, body {
      margin: 0;
      padding: 0;
      min-height: 100%;
      font-family: Arial, sans-serif;
      position: relative;
      color: black;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      position: relative;
      color: black;
    }

    .background-gif {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: url('images/background.GIF') no-repeat center center / cover;
      z-index: -1;
      opacity: 0.2;
    }

    main {
      flex: 1;
      padding-top: 80px;
      padding-bottom: 60px;
    }

    .contact-container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.25);
    }

    .contact-container h1 {
      font-size: 2em;
      margin-bottom: 20px;
      color: black;
    }

    .contact-container p {
      font-size: 1.1em;
      margin-bottom: 10px;
      color: black;
    }

    .contact-container a {
      color: #ff6600;
      text-decoration: none;
    }

    .profile-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
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

    footer {
      background-color: #333;
      color: white;
      padding: 10px;
      text-align: center;
      width: 100%;
    }
  </style>
</head>
<body>

<div class="background-gif"></div>

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

<main>
  <section class="contact-container">
    <h1>Contact <?php echo $company_name; ?></h1>
    <p><strong>Address:</strong> <?php echo $address; ?></p>
    <p><strong>Email:</strong> <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></p>
    <p><strong>Phone:</strong> <?php echo $phone; ?></p>
  </section>
</main>

<footer>
  &copy; 2025 Grafitoon. All rights reserved.
</footer>

</body>
</html>
