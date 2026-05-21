<?php
include 'db.php';
?>

<!DOCTYPE html>

<html>

<head>

<title>Candidate List</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<h2>Welcome to Register</h2>

<a href="register.php">Register</a>

<br><br>

<table border="2">

<tr>
    <th>S.NO</th>
    <th>Name</th>
    <th>Email ID</th>
    <th>Reg Date</th>
    <th>Action</th>
</tr>

<?php

$query = "SELECT * FROM users";

$run = mysqli_query($conn, $query);

while($row = mysqli_fetch_array($run))
{
    echo "<tr>";

    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['user_name']."</td>";
    echo "<td>".$row['email_id']."</td>";
    echo "<td>".$row['reg_date']."</td>";

    echo "<td>
            <a href='edit.php?id=".$row['id']."'>
            Edit
            </a>
          </td>";

    echo "</tr>";
}

?>

</table>

</body>

</html>