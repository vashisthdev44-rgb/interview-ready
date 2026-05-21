<?php

session_start();

if(!isset($_SESSION['id']))
{
    header("Location: candidate_login.php");
    exit();
}

$mock_score = isset($_SESSION['score']) ? $_SESSION['score'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Voice Interview – Interview Ready</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .interview-wrapper {
            max-width: 750px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .score-badge {
            display: inline-block;
            background: linear-gradient(to right, #3b82f6, #06b6d4);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        #chatbox {
            width: 100%;
            margin: 0 auto 25px;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 25px;
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
            color: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .msg-ai {
            background: rgba(59,130,246,0.15);
            border-left: 3px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            animation: fadeIn 0.4s ease;
        }

        .msg-user {
            background: rgba(6,182,212,0.12);
            border-left: 3px solid #06b6d4;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .status-bar {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 20px;
            min-height: 24px;
        }

        .status-bar.listening {
            color: #34d399;
            font-weight: bold;
        }

        .progress-bar-wrap {
            background: rgba(255,255,255,0.08);
            border-radius: 10px;
            height: 8px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(to right, #3b82f6, #06b6d4);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .btn-start {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(to right, #3b82f6, #06b6d4);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-start:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(59,130,246,0.5);
        }

        .btn-start:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        h2 { text-align: center; color: white; }
        .center { text-align: center; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="interview-wrapper">

    <h2>🎙️ AI Voice Interview</h2>

    <div class="center">
        <span class="score-badge">📝 Mock Test Score: <?php echo $mock_score; ?>/3</span>
    </div>

    <div class="progress-bar-wrap">
        <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
    </div>

    <div id="chatbox"></div>

    <div class="status-bar" id="statusBar">Click "Start Interview" to begin</div>

    <button class="btn-start" id="startBtn" onclick="startInterview()">
        🎙️ Start Interview
    </button>

</div>

<?php include 'footer.php'; ?>

<script>

const questions = [
    "Tell me about yourself.",
    "What are your key strengths?",
    "Why should we hire you?",
    "What is PHP and how have you used it?"
];

const answers = [];
let index = 0;
const totalQ = questions.length;

function speak(text, callback)
{
    window.speechSynthesis.cancel();
    let utter = new SpeechSynthesisUtterance(text);
    utter.lang = "en-US";
    utter.rate = 0.95;
    if(callback) utter.onend = callback;
    speechSynthesis.speak(utter);
}

function addMessage(type, text)
{
    let box = document.getElementById('chatbox');
    let div = document.createElement('div');
    div.className = type === 'ai' ? 'msg-ai' : 'msg-user';
    div.innerHTML = (type === 'ai' ? '<b>🤖 AI:</b> ' : '<b>🧑 You:</b> ') + text;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

function updateProgress()
{
    let pct = (index / totalQ) * 100;
    document.getElementById('progressBar').style.width = pct + '%';
}

function setStatus(msg, listening = false)
{
    let el = document.getElementById('statusBar');
    el.textContent = msg;
    el.className = 'status-bar' + (listening ? ' listening' : '');
}

function startInterview()
{
    document.getElementById('startBtn').disabled = true;
    document.getElementById('startBtn').textContent = '⏳ Interview in Progress...';
    askQuestion();
}

function askQuestion()
{
    if(index < totalQ)
    {
        updateProgress();
        let q = questions[index];
        addMessage('ai', q);
        setStatus('AI is speaking...');

        speak(q, function() {
            setStatus('🎙️ Listening... Speak your answer', true);
            startRecognition();
        });
    }
    else
    {
        updateProgress();
        finishInterview();
    }
}

function startRecognition()
{
    let SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if(!SpeechRecognition)
    {
        alert("Your browser does not support Speech Recognition. Please use Chrome.");
        return;
    }

    let recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.start();

    recognition.onresult = function(event)
    {
        let userAnswer = event.results[0][0].transcript;
        addMessage('user', userAnswer);
        answers.push({ question: questions[index], answer: userAnswer });
        setStatus('Answer recorded ✓');
        index++;

        setTimeout(() => { askQuestion(); }, 1500);
    };

    recognition.onerror = function(event)
    {
        setStatus('Could not hear you. Skipping to next question.');
        answers.push({ question: questions[index], answer: "(No answer given)" });
        index++;
        setTimeout(() => { askQuestion(); }, 1500);
    };
}

function finishInterview()
{
    addMessage('Interview completed! Generating your evaluation report...');
    setStatus('✅ All questions done! Redirecting...');
    speak('Interview completed. Generating your evaluation report.');

    
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'result.php';

    let answersInput = document.createElement('input');
    answersInput.type = 'hidden';
    answersInput.name = 'interview_answers';
    answersInput.value = JSON.stringify(answers);
    form.appendChild(answersInput);

    document.body.appendChild(form);

    setTimeout(() => { form.submit(); }, 2500);
}

</script>

</body>
</html>