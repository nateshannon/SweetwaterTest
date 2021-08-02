<?php

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conn.php');

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        
        $result = mysqli_query($conn, "SELECT * FROM sweetwater_test WHERE orderid=25006706 LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        echo $row['comments'];
        
        ?>
        
    </body>
</html>
