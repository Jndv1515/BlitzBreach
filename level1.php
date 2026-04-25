<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$_SESSION['start_time'] = microtime(true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Node 01</title>
    <style>
        :root {
            --cyan: #00ffcc;
            --dark-cyan: #004433;
            --panel-bg: rgba(0, 20, 20, 0.95);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
        }

        body {
            background-color: #050505;
            color: var(--cyan);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .hud-navbar {
            padding: 10px 40px;
            background: var(--cyan);
            color: #000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .nav-item {
            cursor: pointer;
            text-decoration: none;
            color: #000;
            text-transform: uppercase;
        }

        .terminal-frame {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            width: 380px;
            border: 1px solid var(--cyan);
            padding: 40px;
            background: rgba(0, 255, 204, 0.02);
            position: relative;
        }

        .login-box::before {
            content: 'TRACE_ANALYSIS_v1';
            position: absolute;
            top: -10px;
            left: 20px;
            background: #050505;
            padding: 0 10px;
            font-size: 0.7rem;
            letter-spacing: 2px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background: #000;
            border: 1px solid var(--dark-cyan);
            color: var(--cyan);
            outline: none;
            text-align: center;
            font-size: 0.9rem;
        }

        input:focus {
            border-color: var(--cyan);
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: transparent;
            color: var(--cyan);
            border: 1px solid var(--cyan);
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-submit:hover {
            background: var(--cyan);
            color: #000;
        }

        .full-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 5000;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>
    <nav class="hud-navbar">
        <a href="dashboard.php" class="nav-item">EXIT_NODE</a>
        <div id="timer-hud">TIME: 0.00</div>
        <div class="nav-item" onclick="togglePause()">PAUSE</div>
    </nav>

    <div class="terminal-frame">
        <div class="login-box">
            <form id="breach-form">
                <input type="hidden" name="level_id" value="1">
                <div style="font-size: 0.6rem; color: var(--dark-cyan); margin-bottom: 5px;">DETECTOR_ID:</div>
                <input type="text" name="submitted_email" placeholder="[INPUT_REQUIRED]" required autocomplete="off">
                <div style="font-size: 0.6rem; color: var(--dark-cyan); margin-bottom: 5px;">DECRYPT_PASS:</div>
                <input type="password" name="submitted_password" placeholder="********" required autocomplete="off">
                <button type="submit" class="btn-submit">CONFIRM_IDENTITY</button>
            </form>
        </div>
    </div>

    <div id="pause-overlay" class="full-overlay">
        <h2 style="color: var(--cyan); letter-spacing: 5px;">ANALYSIS_HALTED</h2>
        <button class="btn-submit" onclick="togglePause()" style="width: 150px; margin-top:20px;">RESUME</button>
    </div>

    <div id="win-overlay" class="full-overlay">
        <div style="border: 2px solid var(--cyan); padding: 50px; text-align: center; background: #000;">
            <h1 style="letter-spacing: 12px; color: var(--cyan);">NODE_SOLVED</h1>
            <button class="btn-submit" onclick="location.href='level2.php'" style="margin-top:20px;">NEXT_NODE</button>
        </div>
    </div>

    <script>
        let startTime = Date.now(),
            elapsedTime = 0,
            isPaused = false,
            isFinished = false;

        function togglePause() {
            if (isFinished) return;
            isPaused = !isPaused;
            document.getElementById('pause-overlay').style.display = isPaused ? 'flex' : 'none';
            if (isPaused) {
                elapsedTime += (Date.now() - startTime);
            } else {
                startTime = Date.now();
            }
        }
        setInterval(() => {
            if (!isFinished && !isPaused) {
                let total = (elapsedTime + (Date.now() - startTime)) / 1000;
                document.getElementById('timer-hud').innerText = "TIME: " + total.toFixed(2);
            }
        }, 50);

        document.getElementById('breach-form').onsubmit = async (e) => {
            e.preventDefault();
            const urlParams = new URLSearchParams(window.location.search);
            const urlUser = urlParams.get('user');

            if (urlUser !== 'look@endofcode.com') {
                alert("CRITICAL ERROR: URL_IDENTITY_MISMATCH. Adjust the URL parameter to proceed.");
                return;
            }

            const res = await fetch('verify.php', {
                method: 'POST',
                body: new FormData(e.target)
            });
            const data = await res.json();
            if (data.status === 'success') {
                isFinished = true;
                document.getElementById('win-overlay').style.display = 'flex';
            } else {
                alert("ACCESS_DENIED: IDENTITY_NOT_VERIFIED");
            }
        };
        //______________________PASSWORD IS BREACH_______________________//
    </script>
</body>

</html>