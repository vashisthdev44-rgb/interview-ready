<?php
include 'db.php';

$error_msg   = "";
$success_msg = "";

if(isset($_POST['register']))
{
    $name     = $_POST['user_name'];
    $email    = $_POST['email_id'];
    $password = $_POST['password'];

    // Check user already exists
    $check = "SELECT * FROM users WHERE email_id='$email'";
    $run   = mysqli_query($conn, $check);

    if(mysqli_num_rows($run) > 0)
    {
        $error_msg = "User already exists with this email!";
    }
    else
    {
        $query = "INSERT INTO users(user_name, email_id, password)
                  VALUES('$name', '$email', '$password')";

        if(mysqli_query($conn, $query))
        {
            header("Location: candidate_login.php");
            exit();
        }
        else
        {
            $error_msg = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register – Interview Ready</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">

    <h2>Candidate Registration</h2>

    <?php if($error_msg): ?>
        <div style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.5);color:#fca5a5;padding:12px;border-radius:10px;margin-bottom:15px;text-align:center;">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Enter Your Name</label>
        <input type="text" name="user_name" placeholder="Full Name" required>

        <label>Enter Your Email</label>
        <input type="email" name="email_id" placeholder="Email Address" required>

        <label>Create Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <input type="submit" name="register" value="Register Now">

    </form>

    <p style="text-align:center; margin-top:20px; color:#94a3b8;">
        Already have an account? <a href="candidate_login.php">Login here</a>
    </p>

</div>

<?php include 'footer.php'; ?>

</body>
</html>