<?php
    session_start();
    require_once 'Database_Connection.php'; // Ensure $conn is established here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafitoon - Shop</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Styles remain the same as provided */
        .container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: auto;
            text-align: center;
        }

        .filter-buttons {
            margin-bottom: 30px;
        }

        .filter-buttons button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filter-buttons button:hover,
        .filter-buttons button.active {
            background-color: #cc5200;
        }

        .products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .product {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .product img {
            width: 100%;
            height: auto; /* Maintain aspect ratio */
            max-height: 200px; /* Limit image height */
            object-fit: cover; /* Cover the area nicely */
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .product h3 { /* Use h3 for name for better structure */
            color: black;
            font-size: 1.1em;
            margin: 10px 0;
            min-height: 40px; /* Ensure consistent height for names */
        }

        .product p {
            color: black;
            font-weight: bold;
            margin: 5px 0; /* Adjust margin */
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: auto; /* Pushes button to bottom */
            cursor: pointer;
            border: none; /* Ensure buttons look like buttons */
        }

        .stock-indicator {
            font-size: 0.9em;
            color: #cc0000;
            margin-bottom: 5px;
        }

        .background-gif {
            /* Using fixed background from your CSS */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/background.GIF') no-repeat center center fixed;
            background-size: cover;
            z-index: -1;
            opacity: 0.3; /* Adjusted opacity from other pages */
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
        /* Added notification style */
        #cart-notification {
            position: fixed;
            top: 80px; /* Below nav */
            right: 20px;
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 1001;
            display: none; /* Hidden by default */
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
<div class="background-gif"></div>

<div id="cart-notification">Item added to cart!</div>

<header>
    <div class="logo">
        <span class="grafi">Grafi</span><span class="toon">toon</span>
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
</script>

<section class="container">
    <h2>Shop Our Cartoon Collection</h2>

    <div class="filter-buttons">
        <button class="active" onclick="filterProducts('all', event)">All</button>
        <button onclick="filterProducts('t-shirts', event)">T-Shirts</button>
        <button onclick="filterProducts('hoodies', event)">Hoodies</button>
        <button onclick="filterProducts('pants', event)">Pants</button>
        <button onclick="filterProducts('accessories', event)">Accessories</button>
    </div>

    <div class="products" id="productList">
        <?php
        // Ensure $conn is available from Database_Connection.php
        if ($conn) {
            $query = "SELECT * FROM products";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    $id = htmlspecialchars($product['product_id']);
                    $img = htmlspecialchars($product['image_path']);
                    $name = htmlspecialchars($product['NAME']);
                    $price = htmlspecialchars($product['price']);
                    $stock = (int)$product['stock_quantity'];
                    $category = strtolower(htmlspecialchars($product['category'])); // Sanitize category

                    $stock_msg = $stock <= 5 ? "<div class='stock-indicator'>Only $stock left!</div>" : "";

                    echo "<div class='product' data-type='{$category}'>";
                    echo "<img src='{$img}' alt='{$name}'>";
                    echo "<h3>{$name}</h3>"; // Use h3 for the name
                    echo "<p>\$" . number_format($price, 2) . "</p>"; // Format price
                    echo $stock_msg;
                    // **MODIFIED**: Added data-id attribute
                    echo "<button class='btn add-to-cart' data-id='{$id}' data-name='{$name}' data-price='{$price}'>Add to Cart</button>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color: white;'>No products found.</p>"; // Ensure message is visible
            }
             $conn->close(); // Close connection when done
        } else {
             echo "<p style='color: red;'>Database connection error.</p>"; // Error message
        }
        ?>
    </div>
</section>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>

<script>
function filterProducts(category, event) {
    const products = document.querySelectorAll('.product');
    document.querySelectorAll('.filter-buttons button').forEach(btn => btn.classList.remove('active'));
    if (event && event.target) { // Check if event and target exist
         event.target.classList.add('active');
    } else {
        // If called without event (e.g. on page load), activate 'All'
        document.querySelector('.filter-buttons button').classList.add('active');
    }


    products.forEach(product => {
        const type = product.getAttribute('data-type');
        // Make comparison case-insensitive just in case
        product.style.display = (category.toLowerCase() === 'all' || type.toLowerCase() === category.toLowerCase()) ? 'flex' : 'none';
    });
}

// Call filterProducts on page load to ensure correct initial display
document.addEventListener('DOMContentLoaded', () => {
    filterProducts('all'); // Initialize with 'all' filter

    // AJAX Cart Add
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            // **MODIFIED**: Get product_id
            const productId = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const price = btn.getAttribute('data-price');

            // **MODIFIED**: Include product_id in fetch body
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `product_id=${encodeURIComponent(productId)}&name=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}`
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Log server response for debugging
                if (data.trim() === "success") {
                     // Show notification
                    const notification = document.getElementById('cart-notification');
                    notification.textContent = `"${name}" added to cart!`;
                    notification.style.display = 'block';
                    // Hide after 3 seconds
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Error adding item to cart.'); // Provide feedback on error
                }
            })
            .catch(err => {
                console.error('Fetch Error:', err);
                alert('Could not connect to add item.'); // Network error feedback
            });
        });
    });
});
</script>
</body>
</html>
