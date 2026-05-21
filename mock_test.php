<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
    exit();
}

include 'db.php';

$query = "SELECT * FROM mock_questions";

$run = mysqli_query($conn, $query);

?>

<!DOCTYPE html>

<html>

<head>

<title>Mock Test</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

<h2>Mock Test</h2>

<form action="submit_test.php" method="POST">

<?php

while($row = mysqli_fetch_array($run))
{
?>

<h3>
<?php echo $row['question']; ?>
</h3>

<input type="radio" 
name="question<?php echo $row['id']; ?>" 
value="<?php echo $row['option1']; ?>">

<?php echo $row['option1']; ?>

<br><br>

<input type="radio" 
name="question<?php echo $row['id']; ?>" 
value="<?php echo $row['option2']; ?>">

<?php echo $row['option2']; ?>

<br><br>

<input type="radio" 
name="question<?php echo $row['id']; ?>" 
value="<?php echo $row['option3']; ?>">

<?php echo $row['option3']; ?>

<br><br>

<input type="radio" 
name="question<?php echo $row['id']; ?>" 
value="<?php echo $row['option4']; ?>">

<?php echo $row['option4']; ?>

<br><br>

<?php
}
?>

<input type="submit" name="submit_test" value="Submit Test">

</form>

</div>

</body>

</html>