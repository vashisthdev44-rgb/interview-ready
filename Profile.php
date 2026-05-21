<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
}
else
{
    include 'db.php';

    $id = $_SESSION['id'];

    // Correct query
    $query = "SELECT * FROM users WHERE ID='$id'";

    $run = mysqli_query($conn, $query);

    $row = mysqli_fetch_assoc($run);

    echo "<pre>";
    print_r($row);
    echo "</pre>";

    echo '<a href="logout.php">Logout</a>';
}

?>