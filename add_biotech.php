<?php

include "db_connect.php";

$sql = "INSERT INTO department (dept_name) VALUES ('BIOTECH')";

if (mysqli_query($conn, $sql)) {
    echo "BIOTECH department added successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}

?>