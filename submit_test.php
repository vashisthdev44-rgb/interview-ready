<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
    exit();
}

include 'db.php';

$score = 0;

$query = "SELECT * FROM mock_questions";

$run = mysqli_query($conn, $query);

while($row = mysqli_fetch_array($run))
{
    $question_id = $row['id'];

    $correct_answer = $row['correct_answer'];

    if(isset($_POST["question$question_id"]))
    {
        $user_answer = $_POST["question$question_id"];

        if($user_answer == $correct_answer)
        {
            $score++;
        }
    }
}

$_SESSION['score'] = $score;

header("Location: voice_ai_interview.php");

exit();

?>