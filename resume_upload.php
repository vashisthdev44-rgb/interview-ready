<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
    exit();
}

include 'db.php';

if(isset($_POST['upload']))
{
    $file = $_FILES['resume'];

    $name       = $_FILES['resume']['name'];
    $temp_name  = $_FILES['resume']['tmp_name'];
    $size       = $_FILES['resume']['size'];
    $error      = $_FILES['resume']['error'];
    $type       = $_FILES['resume']['type'];

    $unique_name = time().'_'.$name;
    $folder      = "files/".$unique_name;

    $allowed_types = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ];

    if($error == 0)
    {
        if($size <= 2 * 1024 * 1024)
        {
            if(in_array($type, $allowed_types))
            {
                if(move_uploaded_file($temp_name, $folder))
                {
                    // Save resume path in DB
                    $user_id = $_SESSION['id'];
                    $save = "UPDATE users SET resume_path='$folder' WHERE id='$user_id'";
                    mysqli_query($conn, $save);

                    $_SESSION['resume_path'] = $folder;

                    header("Location: mock_test.php");
                    exit();
                }
                else
                {
                    $error_msg = "File upload failed. Make sure 'files/' folder exists.";
                }
            }
            else
            {
                $error_msg = "Only PDF, DOC, DOCX files allowed.";
            }
        }
        else
        {
            $error_msg = "File size too large (MAX 2MB).";
        }
    }
    else
    {
        $error_msg = "Error in file upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Resume – Interview Ready</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .upload-box {
            text-align: center;
        }
        .upload-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .error-msg {
            background: rgba(239,68,68,0.2);
            border: 1px solid rgba(239,68,68,0.5);
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .file-info {
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
            margin-top: -10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container upload-box">

    <div class="upload-icon">📄</div>
    <h2>Upload Your Resume</h2>
    <p style="color:#94a3b8; margin-bottom:25px;">PDF, DOC or DOCX — Max 2MB</p>

    <?php if(isset($error_msg)) { ?>
        <div class="error-msg"><?php echo $error_msg; ?></div>
    <?php } ?>

    <form action="" method="POST" enctype="multipart/form-data">

        <label>Select Resume File</label>
        <input type="file" name="resume" accept=".pdf,.doc,.docx" required><br>
        <p class="file-info">Allowed: PDF, DOC, DOCX &nbsp;|&nbsp; Max size: 2MB</p>

        <input type="submit" name="upload" value="Upload & Continue →">

    </form>

</div>

<?php include 'footer.php'; ?>

</body>
</html>