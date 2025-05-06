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
    $conn->select_db($db_name);
    $sql_get = "SELECT * FROM users";
    $results = $conn->query($sql_get);
    if($results->num_rows > 0)
    {
        while($row = result-> fetch_assoc()){
 echo "ID". $row["id"]. "First name: ".$row["FirstName"]."Last Name:".$row["LastName"]. "Email:".$row["Email"]."<br>";

        }
    }
    else{
        echo "0 results";
    }
    ?>
</body>
</html>
