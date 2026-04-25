<?php 
include 'db.php'; 
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$_SESSION['start_time'] = microtime(true); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Level 04</title>
    <style>
        :root { --neon-blue: #00d4ff; --bg-black: #000; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', monospace; }
        
        body { 
            background: #000; color: #fff; height: 100vh; display: flex; flex-direction: column; overflow: hidden; 
            background-image: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), 
                              linear-gradient(90deg, rgba(0, 212, 255, 0.02), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.02));
            background-size: 100% 2px, 3px 100%;
        }

        /* --- Master Stealth Navbar --- */
        .hud-navbar { 
            padding: 15px 40px; background: rgba(0,212,255,0.05); border-bottom: 1px solid var(--neon-blue); 
            display: flex; justify-content: space-between; align-items: center; z-index: 1000;
        }
        .nav-group { display: flex; align-items: center; gap: 15px; }
        .nav-item { color: var(--neon-blue); text-decoration: none; padding: 5px 12px; cursor: pointer; font-size: 0.8rem; }

        .vault-wrapper { 
            flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; 
            background: radial-gradient(circle, #0a1416 0%, #000 100%);
        }

        .vault-door {
            width: 350px; padding: 40px; border: 4px solid #222; background: #111;
            box-shadow: inset 0 0 50px #000, 0 0 20px rgba(0,0,0,0.5);
            text-align: center; position: relative;
        }

        .keypad-display {
            background: #050505; color: var(--neon-blue); padding: 15px;
            font-size: 1.5rem; letter-spacing: 10px; margin-bottom: 25px;
            border: 1px solid #333; height: 60px; display: flex; align-items: center; justify-content: center;
        }

        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .key { 
            padding: 15px; background: #1a1a1a; border: 1px solid #333; 
            color: #888; cursor: pointer; transition: 0.2s; font-weight: bold;
        }
        .key:hover { background: #222; color: var(--neon-blue); border-color: var(--neon-blue); }
        .key:active { transform: scale(0.95); }

        .status-light { width: 10px; height: 10px; border-radius: 50%; background: #300; margin: 0 auto 10px; }
        .status-light.active { background: #f00; box-shadow: 0 0 10px #f00; }

        .full-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0, 0, 0, 0.95); z-index: 5000; flex-direction: column; 
            justify-content: center; align-items: center; text-align: center; 
        }
    </style>
</head>
<body>

    <nav class="hud-navbar">
        <div class="nav-group">
            <a href="dashboard.php" class="nav-item">🏠 HOME</a>
            <div class="nav-item" id="timer-hud">TIME: 0.00s</div>
        </div>
        <div style="color: var(--neon-blue); font-weight: bold; letter-spacing: 2px;">VAULT_OVERRIDE_L04</div>
        <div class="nav-item" onclick="togglePause()">⏸ PAUSE</div>
    </nav>

    <div class="vault-wrapper">
        <div class="vault-door">
            <div id="light" class="status-light"></div>
            <div class="keypad-display" id="display">----</div>
            <div class="grid">
                <?php for($i=1; $i<=9; $i++) echo "<div class='key' onclick='press($i)'>$i</div>"; ?>
                <div></div><div class='key' onclick='press(0)'>0</div>
                <div class='key' onclick='resetKeypad()' style="font-size:0.6rem; color:#f44;">CLR</div>
            </div>
        </div>
        <p style="margin-top: 30px; color: #333; font-size: 0.7rem; letter-spacing: 1px;">[SYSTEM] MONITORING EXTERNAL PACKET DATA...</p>
    </div>

    <div id="pause-overlay" class="full-overlay">
        <h2 style="color: var(--neon-blue); letter-spacing: 5px;">SYSTEM_SUSPENDED</h2>
        <button onclick="togglePause()" style="padding: 10px 20px; margin-top: 20px; cursor: pointer;">RESUME</button>
    </div>

    <div id="win-overlay" class="full-overlay">
        <h1 style="color: var(--neon-blue); font-size: 3rem; letter-spacing: 10px;">VAULT COMPROMISED</h1>
        <p id="final-time" style="color: #fff; font-size: 1.5rem; margin: 20px 0;"></p>
        <button onclick="location.href='dashboard.php'" style="padding: 12px 30px; cursor: pointer; background: #fff; font-weight: bold;">COMPLETE MISSION</button>
    </div>

    <script>
        let code = "";
        let isPaused = false, isFinished = false;
        let startTime = Date.now(), elapsedTime = 0;

        // The Secret PIN
        const secret = "<?php echo rand(1000, 9999); ?>";

        function press(num) {
            if (isPaused || isFinished || code.length >= 4) return;
            code += num;
            document.getElementById('display').innerText = code.padEnd(4, '-');
            if (code.length === 4) checkCode();
        }

        function resetKeypad() {
            code = "";
            document.getElementById('display').innerText = "----";
        }

        async function checkCode() {
            if (code === secret) {
                isFinished = true;
                const res = await fetch('verify.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'level_id=4' });
                const data = await res.json();
                document.getElementById('final-time').innerText = "BREACH TIME: " + data.time + "s";
                document.getElementById('win-overlay').style.display = 'flex';
            } else {
                document.getElementById('light').classList.add('active');
                setTimeout(() => document.getElementById('light').classList.remove('active'), 500);
                resetKeypad();
            }
        }

        function togglePause() {
            if (isFinished) return;
            isPaused = !isPaused;
            document.getElementById('pause-overlay').style.display = isPaused ? 'flex' : 'none';
            if (isPaused) { elapsedTime += (Date.now() - startTime); } else { startTime = Date.now(); }
        }

        // --- THE "HACK" ---
        // Every 5 seconds, we fetch a "metadata" file. 
        // The player just needs to look at the Network Tab (F12 -> Network)
        // and check the response of 'leak_detector.php'.
        setInterval(() => {
            if(!isFinished && !isPaused) {
                // We fetch a fake URL that contains the PIN in the query string or response
                fetch(`leak_detector.php?session_sync_id=${secret}&status=active`);
            }
        }, 5000);

        setInterval(() => {
            if(!isFinished && !isPaused) {
                let total = (elapsedTime + (Date.now() - startTime)) / 1000;
                document.getElementById('timer-hud').innerText = "TIME: " + total.toFixed(2) + "s";
            }
        }, 50);
    </script>
</body>
</html>