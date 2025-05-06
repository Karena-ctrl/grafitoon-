<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = "grafitoon_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    
    // Check if database already exists
    $db_check_query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'";
    $result = $conn->query($db_check_query);

    if ($result->num_rows > 0) {
        
    } else {
        // Create the database
        $sql = "CREATE DATABASE $db_name";
        if ($conn->query($sql) === TRUE) {
            echo "Database $db_name created<br>";
        } else {
            echo "Error creating database: " . $conn->error;
            exit();
        }
    }

    // Select the database
    $conn->select_db($db_name);

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        FirstName VARCHAR(30),
        LastName VARCHAR(50),
        Email VARCHAR(40),
        Password VARCHAR(255),
        Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        
    } else {
        echo "Error creating table: " . $conn->error;
    }
}
?>
