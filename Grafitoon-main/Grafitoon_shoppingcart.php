<?php
session_start();
// No need to include Database_Connection.php unless needed for other operations on this page

// Initialize cart total
$total = 0;

// Ensure cart exists in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Grafitoon - Shopping Cart</title>
  <link rel="stylesheet" href="grafitoon_css.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* Styles remain the same as provided */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      /* Using fixed background from your CSS */
       background: url('images/background.GIF') no-repeat center center fixed;
       background-size: cover;
      display: flex;
      flex-direction: column;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #f0f0f0; /* Default text color for dark theme */
    }

    header {
      text-align: center;
      padding: 20px 0;
      background-color: rgba(0, 0, 0, 0.85); /* Match nav background */
    }

    .logo {
      font-size: 40px;
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
      background-color: rgba(0, 0, 0, 0.85);
      padding: 12px 0;
      flex-wrap: wrap;
       position: sticky; /* Make nav sticky */
       top: 0;
       z-index: 1000; /* Ensure nav stays on top */
    }

    nav a {
      color: white;
      text-decoration: none;
      margin: 0 15px;
      padding: 10px 15px;
      border-radius: 8px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    nav a:hover {
      background-color: orange;
      color: black;
    }

    .profile-dropdown {
      position: relative;
      display: inline-block;
       margin-left: 15px; /* Add some space */
    }

    .profile-dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 180px; /* Slightly wider */
        box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
        z-index: 1;
        right: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .profile-dropdown-content a {
      color: black; /* Text color inside dropdown */
      padding: 12px 16px;
      text-decoration: none;
      display: block;
       white-space: nowrap; /* Prevent wrapping */
    }

    .profile-dropdown-content a:hover {
      background-color: #f1f1f1;
      color: #333; /* Darken text on hover */
    }
     /* Keep icon color consistent */
    .profile-dropdown-content a i {
       margin-right: 8px;
       color: #555; /* Icon color */
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
       cursor: pointer;
       vertical-align: middle; /* Align with text links */
    }

    .cart-container {
      max-width: 1000px;
      margin: 40px auto;
      background: rgba(30, 30, 30, 0.9); /* Darker semi-transparent background */
      color: #f0f0f0; /* Light text color */
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 0 20px rgba(0,0,0,0.6); /* Stronger shadow */
      flex-grow: 1; /* Allows container to fill space */
    }

    .cart-container h2 {
      text-align: center;
      margin-bottom: 25px;
       color: orange; /* Heading color */
    }

    table {
      width: 100%;
      border-collapse: collapse;
       margin-bottom: 20px; /* Space below table */
    }

    table thead {
      background-color: #ff6600;
      color: white;
    }

    th, td {
      padding: 12px 15px; /* Adjust padding */
      text-align: center;
      border-bottom: 1px solid #444; /* Darker border */
    }
     /* Zebra striping for rows */
    tbody tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .cart-actions {
      margin-top: 30px;
      text-align: right;
       border-top: 1px solid #444; /* Separator line */
       padding-top: 20px;
    }

     .cart-actions strong {
       font-size: 1.2em;
       margin-right: 20px;
       color: white; /* Ensure total is visible */
    }

    .cart-actions button, .cart-actions a.btn { /* Style links as buttons */
      padding: 10px 20px;
      background-color: #ff6600;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 10px;
       text-decoration: none; /* Remove underline from links */
       font-size: 1em;
       transition: background-color 0.3s ease;
    }
     /* Specific style for clear cart button */
     .cart-actions button.clear-cart,
     .cart-actions a.clear-cart {
         background-color: #555; /* Grey */
     }
     .cart-actions button.clear-cart:hover,
     .cart-actions a.clear-cart:hover {
         background-color: #777;
     }

    .cart-actions button:hover, .cart-actions a.btn:hover {
      background-color: #cc5200;
    }

     /* Message for empty cart */
     .empty-cart-message {
         text-align: center;
         font-size: 1.1em;
         padding: 40px 0;
         color: #ccc; /* Lighter grey color */
     }
      /* Style remove links/buttons */
      .remove-item-btn {
          color: #ff4d4d; /* Red color for removal */
          text-decoration: none;
          font-weight: bold;
          transition: color 0.3s ease;
      }
      .remove-item-btn:hover {
          color: #ff1a1a; /* Brighter red on hover */
      }


    footer {
      text-align: center;
      padding: 20px;
      background-color: rgba(0, 0, 0, 0.85); /* Match nav background */
      color: white;
      width: 100%;
      margin-top: auto; /* Pushes footer to bottom */
       box-sizing: border-box; /* Include padding in width */
    }
  </style>
</head>
<body>

<header>
  <div class="logo">
    <img src="images/grafitoon_logo.png" alt="GrafitoonLogo" width="160">
  </div>
</header>

<nav>
  <a href="Grafitoon_index.php">Home</a>
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

// Confirm before clearing cart
function confirmClearCart() {
  if (confirm("Are you sure you want to clear your entire cart?")) {
    window.location.href = "clear_cart.php";
  }
}
</script>

<div class="cart-container">
  <h2>Your Shopping Cart</h2>

  <?php if (empty($_SESSION['cart'])): ?>
      <p class="empty-cart-message">Your cart is currently empty. <a href="Grafitoon_shoppingsection.php" style="color: orange;">Go Shopping!</a></p>
  <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Size</th> <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="cart-items">
          <?php
          // **MODIFIED**: Loop through session cart
          foreach ($_SESSION['cart'] as $id => $item):
              $item_name = htmlspecialchars($item['name']);
              $item_price = floatval($item['price']);
              $item_quantity = intval($item['quantity']);
              $subtotal = $item_price * $item_quantity;
              $total += $subtotal; // Add to grand total
          ?>
          <tr>
            <td><?= $item_name ?></td>
            <td>N/A</td> <td>$<?= number_format($item_price, 2) ?></td>
            <td><?= $item_quantity ?></td>
            <td>$<?= number_format($subtotal, 2) ?></td>
            <td>
              <a href="remove_from_cart.php?id=<?= $id ?>" class="remove-item-btn" title="Remove item">
                 <i class="fas fa-trash-alt"></i> </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="cart-actions">
        <strong>Total: $<?= number_format($total, 2) ?></strong>
        <a href="grafitoon_checkout.php" class="btn">Checkout</a>
        <button onclick="confirmClearCart()" class="clear-cart">Clear Cart</button>
      </div>

  <?php endif; ?>
</div>

<footer>
  &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>

</body>
</html>
