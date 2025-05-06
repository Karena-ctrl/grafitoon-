<?php include 'Database_Connection.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    $con->select_db($db_name);
    //Insert Data in table
    name = "";
    $email = "";
    $sql = "INSERT INTO users (FirstName, LastName,Email) VALUES ('fname','lname','email')";
    if ($con ->query ($sql) ===true)
    {
        echo "Record created Successfully";
    }
    else{
        echo "<br>Record not added $con -> error";

    }
   ?>
</body>
</html>
