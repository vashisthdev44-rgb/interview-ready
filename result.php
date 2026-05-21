<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
    exit();
}

include 'db.php';

// =============================================
// APNI API KEY YAHAN PASTE KARO
$api_key = "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";
// =============================================

// Get user data
$user_id = $_SESSION['id'];
$query   = "SELECT * FROM users WHERE id='$user_id'";
$run     = mysqli_query($conn, $query);
$user    = mysqli_fetch_assoc($run);

$mock_score     = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
$resume_path    = isset($user['resume_path']) ? $user['resume_path'] : null;
$interview_data = isset($_POST['interview_answers']) ? $_POST['interview_answers'] : '[]';
$answers_array  = json_decode($interview_data, true);

// Read resume text if PDF
$resume_text = "";
if($resume_path && file_exists($resume_path))
{
    $ext = strtolower(pathinfo($resume_path, PATHINFO_EXTENSION));
    if($ext === 'pdf')
    {
        $escaped     = escapeshellarg($resume_path);
        $resume_text = shell_exec("pdftotext $escaped -");
        if(!$resume_text) {
            $resume_text = "Resume uploaded (PDF - text extraction not available on this server).";
        }
    }
    else
    {
        $resume_text = "Resume uploaded as DOC/DOCX file.";
    }
}
else
{
    $resume_text = "No resume uploaded.";
}

// Build interview Q&A string
$interview_text = "";
if(!empty($answers_array))
{
    foreach($answers_array as $i => $qa)
    {
        $interview_text .= "Q" . ($i+1) . ": " . $qa['question'] . "\n";
        $interview_text .= "A" . ($i+1) . ": " . $qa['answer'] . "\n\n";
    }
}
else
{
    $interview_text = "No interview answers recorded.";
}

$total_mock   = 3;
$mock_percent = round(($mock_score / $total_mock) * 100);

// Build prompt
$prompt = "You are an expert HR interviewer and talent evaluator.
Evaluate the following candidate for a Software Developer / Web Developer role.

Candidate Name: " . $user['user_name'] . "

--- RESUME CONTENT ---
" . $resume_text . "

--- MOCK TEST RESULT ---
Score: " . $mock_score . " out of " . $total_mock . " (" . $mock_percent . "%)

--- VOICE INTERVIEW ANSWERS ---
" . $interview_text . "

---

Please provide a detailed evaluation report with the following sections:

1. **Overall Summary** (2-3 lines about the candidate)
2. **Resume Analysis** (skills, experience, education highlights)
3. **Mock Test Performance** (based on " . $mock_percent . "% score - what it indicates)
4. **Interview Performance** (evaluate each answer for clarity, confidence, and relevance)
5. **Key Strengths** (3-4 bullet points)
6. **Areas of Improvement** (3-4 bullet points)
7. **Final Verdict** (one of: Highly Recommended / Recommended / Needs Improvement / Not Recommended)
8. **Overall Score** (give a score out of 100 based on all three factors)

Be honest, constructive, and professional.";

// Call Claude API using PHP cURL
$ai_result = "";
$ai_error  = "";

$payload = json_encode([
    "model"      => "claude-sonnet-4-20250514",
    "max_tokens" => 1500,
    "messages"   => [
        ["role" => "user", "content" => $prompt]
    ]
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.anthropic.com/v1/messages");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-api-key: " . $api_key,
    "anthropic-version: 2023-06-01"
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response  = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if($curl_error)
{
    $ai_error = "Connection error: " . $curl_error;
}
else
{
    $data = json_decode($response, true);
    if(isset($data['content'][0]['text']))
    {
        $ai_result = $data['content'][0]['text'];
        // Format bold markdown to HTML
        $ai_result = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $ai_result);
        $ai_result = nl2br($ai_result);
    }
    else if(isset($data['error']))
    {
        $ai_error = "API Error: " . $data['error']['message'];
    }
    else
    {
        $ai_error = "Could not generate evaluation. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Evaluation Report – Interview Ready</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .report-wrapper {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }

        .report-card {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 35px;
            margin-bottom: 25px;
            color: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        .report-card h3 {
            color: #38bdf8;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
            text-align: left;
        }

        .score-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .score-box {
            flex: 1;
            min-width: 150px;
            background: rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.08);
        }

        .score-box .num {
            font-size: 38px;
            font-weight: bold;
            background: linear-gradient(to right, #3b82f6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .score-box .label {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .ai-result {
            line-height: 1.8;
            font-size: 15px;
            color: #e2e8f0;
        }

        .error-box {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.4);
            color: #fca5a5;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .btn-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 14px;
            border-radius: 12px;
            background: linear-gradient(to right, #3b82f6, #06b6d4);
            color: white;
            font-weight: bold;
            font-size: 15px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-home:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(59,130,246,0.5);
        }

        h2 { text-align: center; color: white; margin-bottom: 30px; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="report-wrapper">

    <h2>📊 Your AI Evaluation Report</h2>

    <!-- Score Summary -->
    <div class="score-row">
        <div class="score-box">
            <div class="num"><?php echo $mock_score; ?>/<?php echo $total_mock; ?></div>
            <div class="label">Mock Test Score</div>
        </div>
        <div class="score-box">
            <div class="num"><?php echo $mock_percent; ?>%</div>
            <div class="label">Mock Test %</div>
        </div>
        <div class="score-box">
            <div class="num"><?php echo count($answers_array); ?></div>
            <div class="label">Questions Answered</div>
        </div>
    </div>

    <!-- AI Report -->
    <div class="report-card">
        <h3>🤖 AI Evaluation by Claude</h3>

        <?php if($ai_error): ?>
            <div class="error-box">
                ❌ <?php echo $ai_error; ?>
            </div>
        <?php else: ?>
            <div class="ai-result">
                <?php echo $ai_result; ?>
            </div>
        <?php endif; ?>

    </div>

    <a href="index.php" class="btn-home">🏠 Go to Home</a>

</div>

<?php include 'footer.php'; ?>

</body>
</html>