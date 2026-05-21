<?php

session_start();

if(isset($_POST['login']))
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    include "db.php";

    $query = "SELECT * FROM users WHERE email_id='$email' AND password='$password'";

    $run = mysqli_query($conn, $query);

    $row = mysqli_fetch_array($run);

    if($row)
    {
        $_SESSION['id'] = $row['id'];

        header("Location: resume_upload.php");
        exit();
    }
    else
    {
        echo "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Candidate Login</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

<h2>Candidate Login</h2>

<form action="" method="POST">

<label>Enter your Email_id</label>
<input type="email" name="email" required><br><br>

<label>Enter your Password</label>
<input type="password" name="password" required><br><br>

<input type="submit" name="login" value="Login">

</form>

</div>

</body>
</html>