<?php
require 'db_connect.php';

//Get data from AJAX request
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $name =$_POST['name'];
    $email =$_POST['email'];

    //Validate inputs
    if(empty($name) || empty($email)) {
        echo "Please fill in all fields";
    }else{
        //Insert into database
        $stmt =$conn->prepare("INSERT INTO users(name,email) VALUES (?,?)");
        $stmt->bind_param("ss",$name,$email);

        if($stmt->excute()){
            echo "Data successfully inserted";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>
