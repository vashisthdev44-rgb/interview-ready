<?php

include 'db.php';

$id = $_GET['id'];

$q = "SELECT * FROM register WHERE id='$id'";

$run = mysqli_query($conn, $q);

$row = mysqli_fetch_array($run);

?>