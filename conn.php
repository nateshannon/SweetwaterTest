<?php

$conn = new mysqli($dbServerName, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


$showMenu = FALSE;
$databaseTablesSweetwaterTest = FALSE;
$databaseTablesCandy = FALSE;
$databaseTablesCandyAssoc = FALSE;
$databaseTablesPeople = FALSE;
$databaseTablesPeopleAssoc = FALSE;

if ($result = mysqli_query($conn, "SHOW TABLES LIKE 'sweetwater_test'")) {
    if($result->num_rows == 1) {
        $databaseTablesSweetwaterTest = TRUE;
    }
}
if ($result = mysqli_query($conn, "SHOW TABLES LIKE 'candy'")) {
    if($result->num_rows == 1) {
        $databaseTablesCandy = TRUE;
    }
}
if ($result = mysqli_query($conn, "SHOW TABLES LIKE 'candy_assoc'")) {
    if($result->num_rows == 1) {
        $databaseTablesCandyAssoc = TRUE;
    }
}
if ($result = mysqli_query($conn, "SHOW TABLES LIKE 'people'")) {
    if($result->num_rows == 1) {
        $databaseTablesPeople = TRUE;
    }
}
if ($result = mysqli_query($conn, "SHOW TABLES LIKE 'people_assoc'")) {
    if($result->num_rows == 1) {
        $databaseTablesPeopleAssoc = TRUE;
    }
}

if ($databaseTablesSweetwaterTest && 
    $databaseTablesCandy &&
    $databaseTablesCandyAssoc &&
    $databaseTablesPeople &&
    $databaseTablesPeopleAssoc) {
    $showMenu = TRUE;
} else {
    $currentPage = basename($_SERVER["REQUEST_URI"], ".php");
    if ($currentPage != "index") {
        header('Location: index.php');
        exit;
    }
}

?>