<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $success = true;
    $_SESSION['cart'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Grafitoon</title>
    <link rel="stylesheet" href="grafitoon_css.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            color: #222;
        }
        header {
            background-color: #000;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 30px;
            gap: 30px;
        }
        .form-section, .summary-section {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 300px;
            max-width: 550px;
        }
        h2 {
            color: #111;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .input-group-row {
            display: flex;
            gap: 15px;
        }
        .input-group-row input {
            flex: 1;
        }
        .checkbox-group {
            margin-top: 15px;
        }
        .order-items {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .order-summary-values p {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #ff6600;
            color: white;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #cc5200;
        }
        .payment-icons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .payment-icons span {
            background: #eee;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }
        footer {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<header>
    <h1>Checkout - Grafitoon</h1>
</header>

<?php if (isset($success)): ?>
    <div style="padding: 20px; background: #d4edda; color: #155724; text-align: center;">
        <strong>Success!</strong> Your order was placed.
    </div>
<?php endif; ?>

<div class="checkout-container">
    <form class="form-section" method="POST">
        <h2>Contact Information</h2>
        <label>Email</label>
        <input type="email" name="email" required>

        <div class="checkbox-group">
            <label><input type="checkbox" name="newsletter"> Email me with news and offers</label>
        </div>

        <h2>Billing Address</h2>
        <label>Country</label>
        <select name="country" required>
            <option value="Jamaica" selected>Jamaica</option>
            <option value="USA">USA</option>
            <option value="Canada">Canada</option>
        </select>

        <label>First Name</label>
        <input type="text" name="fname" required>

        <label>Last Name</label>
        <input type="text" name="lname" required>

        <label>Address</label>
        <input type="text" name="address" required>

        <label>Apartment, suite, etc. (optional)</label>
        <input type="text" name="apt">

        <label>City</label>
        <input type="text" name="city" required>

        <label>Phone (optional)</label>
        <input type="tel" name="phone">

        <div class="checkbox-group">
            <label><input type="checkbox" name="save_info"> Save this info for next time</label>
        </div>

        <h2>Payment</h2>
        <p>All transactions are secure and encrypted.</p>

        <label>Credit Card/ Debit Card</label>
       

        <label>Card Number</label>
        <input type="text" name="card_number" placeholder="1234 5678 9012 3456" required>

        <label>Name on Card</label>
        <input type="text" name="card_name" required>

        <div class="input-group-row">
            <div>
                <label>Expiry Date</label>
                <input type="text" name="card_expiry" placeholder="MM/YY" required>
            </div>
            <div>
                <label>CVV</label>
                <input type="text" name="card_cvv" placeholder="123" required>
            </div>
        </div>

        <div class="checkbox-group">
            <label><input type="checkbox" name="billing_same"> Use shipping address as billing address</label>
        </div>

        <label><input type="radio" name="payment_method" value="paypal"> PayPal</label>
        <label><input type="radio" name="payment_method" value="afterpay"> Afterpay</label>
        <label><input type="radio" name="payment_method" value="klarna"> Klarna - Flexible payments</label>

        <button type="submit" name="submit_order" class="btn">Submit Order</button>
    </form>

    <div class="summary-section">
        <h2>Order Summary</h2>
        <ul class="order-items">
            <?php
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                echo "<li class='order-item'><span>{$item['name']}</span><span>\${$item['price']}</span></li>";
                $subtotal += $item['price'];
            }
            $shipping = 10;
            $total = $subtotal + $shipping;
            ?>
        </ul>
        <div class="order-summary-values">
            <p><strong>Subtotal:</strong> <span>$<?= number_format($subtotal, 2) ?></span></p>
            <p><strong>Shipping:</strong> <span>$<?= number_format($shipping, 2) ?></span></p>
            <p><strong>Total:</strong> <span>$<?= number_format($total, 2) ?></span></p>
        </div>
    </div>
</div>

<footer>
    &copy; <?= date("Y") ?> Grafitoon. All Rights Reserved.
</footer>
</body>
</html>
