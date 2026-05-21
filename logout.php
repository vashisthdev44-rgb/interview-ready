<?php
session_start();

session_destroy();

header("Location: candidate_login.php");

exit();

?>