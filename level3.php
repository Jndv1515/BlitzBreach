<?php 
include 'db.php'; 
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
$_SESSION['start_time'] = microtime(true); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Node 03</title>
    <style>
        :root { --cyan: #00ffcc; --dark-cyan: #004433; --panel-bg: rgba(0, 20, 20, 0.95); --error-red: #ff3333; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', monospace; }
        body { background-color: #050505; color: var(--cyan); height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        .hud-navbar { 
            padding: 10px 40px; background: var(--cyan); color: #000; 
            display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 0.8rem;
        }

        .terminal-frame { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding-bottom: 100px; }
        .game-container { display: flex; gap: 40px; align-items: center; }
        
        .card-slot { 
            width: 90px; height: 130px; border: 2px solid var(--cyan); 
            display: flex; justify-content: center; align-items: center; 
            font-size: 4.5rem; background: rgba(0, 255, 204, 0.05); 
            text-shadow: 0 0 10px var(--cyan);
        }
        
        .deck-button { background: rgba(0, 255, 204, 0.1); border: 1px solid var(--cyan); padding: 10px; cursor: pointer; }
        .deck-img { width: 80px; height: auto; filter: drop-shadow(0 0 5px var(--cyan)) brightness(1.5); }

        /* --- TACTICAL LOWER MESSAGE HUD --- */
        #status-overlay {
            display: none; 
            position: fixed; 
            bottom: 0; left: 0; width: 100%; height: 180px;
            background: rgba(0, 0, 0, 0.9); 
            border-top: 2px solid var(--cyan);
            z-index: 6000;
            justify-content: center; align-items: center;
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }

        .status-window {
            width: 80%; display: flex; justify-content: space-between; align-items: center;
        }
        
        #status-box.error { color: var(--error-red); }
        
        .status-header { font-size: 1.1rem; font-weight: bold; letter-spacing: 3px; margin-bottom: 5px; }
        .status-text { font-size: 0.8rem; color: #888; }
        
        .btn-action { padding: 12px 25px; background: transparent; color: var(--cyan); border: 1px solid var(--cyan); cursor: pointer; font-weight: bold; letter-spacing: 2px; }
        .btn-action:hover { background: var(--cyan); color: #000; }
        
        .error .btn-action { color: var(--error-red); border-color: var(--error-red); }
        .error .btn-action:hover { background: var(--error-red); color: #000; }

        .full-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 5000; flex-direction: column; justify-content: center; align-items: center; }
    </style>
</head>
<body>

    <div id="status-overlay">
        <div class="status-window" id="status-box">
            <div>
                <div class="status-header" id="status-title">STATUS_MSG</div>
                <div class="status-text" id="status-desc">System report content...</div>
            </div>
            <button class="btn-action" onclick="closeStatus()">RETRY_SEQUENCE</button>
        </div>
    </div>

    <nav class="hud-navbar">
        <a href="dashboard.php" style="text-decoration:none; color:#000;">EXIT_NODE</a>
        <div id="timer-hud">TIME: 0.00</div>
        <div onclick="togglePause()" style="cursor:pointer;">PAUSE</div>
    </nav>

    <div class="terminal-frame">
        <h2 style="letter-spacing: 8px; margin-bottom: 50px;">BACCARAT_MANIPULATION</h2>

        <div class="game-container">
            <div>
                <div style="font-size:0.6rem; text-align:center; margin-bottom:10px;">BANKER_HAND [8]</div>
                <div style="display: flex; gap: 10px;">
                    <div class="card-slot">🂦</div>
                    <div class="card-slot">🂢</div>
                </div>
            </div>
            <div style="font-size: 1.5rem; opacity: 0.4;">VS</div>
            <div>
                <div style="font-size:0.6rem; text-align:center; margin-bottom:10px;">PLAYER_HAND [<span id="player-total">10</span>]</div>
                <div style="display: flex; gap: 10px;">
                    <div class="card-slot">🂪</div>
                    <div id="drawn-card-slot" class="card-slot">?</div>
                </div>
            </div>
            <div style="margin-left: 50px;">
                <div style="font-size:0.6rem; text-align:center; margin-bottom:10px;">DRAW_NODE</div>
                <button class="deck-button" onclick="drawCard()">
                    <img src="icons/deck.png" class="deck-img" alt="DECK">
                </button>
            </div>
        </div>
    </div>

    <div id="win-overlay" class="full-overlay">
        <div style="border: 2px solid var(--cyan); padding: 60px; background: #000; text-align: center;">
            <h1 style="letter-spacing: 10px;">NATURAL_9_CONFIRMED</h1>
            <button class="btn-action" onclick="location.href='dashboard.php'" style="margin-top:20px;">FINALIZE_EXIT</button>
        </div>
    </div>

    <script>
        let startTime = Date.now(), elapsedTime = 0, isPaused = false, isFinished = false;
        var deckIndex = 0;
        var protocolDeck = [{ symbol: '🂡', value: 1 }, { symbol: '🂩', value: 9 }];

        function showStatus(title, message) {
            document.getElementById('status-title').innerText = title;
            document.getElementById('status-desc').innerText = message;
            document.getElementById('status-box').classList.add('error');
            document.getElementById('status-overlay').style.display = 'flex';
        }

        function closeStatus() {
            document.getElementById('status-overlay').style.display = 'none';
            location.reload();
        }

        function drawCard() {
            if (isFinished || isPaused) return;
            let card = protocolDeck[deckIndex];
            let slot = document.getElementById('drawn-card-slot');
            slot.innerText = card.symbol;
            
            let finalTotal = (10 + card.value) % 10;
            document.getElementById('player-total').innerText = finalTotal;

            if (finalTotal > 8) {
                setTimeout(submitWin, 1000);
            } else {
                setTimeout(() => {
                    showStatus("PROTOCOL_FAILURE", "Banker hand [8] exceeds player hand ["+finalTotal+"]. Authorization denied.");
                }, 500);
            }
        }

        async function submitWin() {
            isFinished = true;
            await fetch('verify.php', { method: 'POST', body: `level_id=3&hack_confirmed=true`, headers: {'Content-Type': 'application/x-www-form-urlencoded'} });
            document.getElementById('win-overlay').style.display = 'flex';
        }

        function togglePause() {
            if (isFinished) return;
            isPaused = !isPaused;
            if (isPaused) { elapsedTime += (Date.now() - startTime); } else { startTime = Date.now(); }
        }

        setInterval(() => {
            if(!isFinished && !isPaused) {
                let total = (elapsedTime + (Date.now() - startTime)) / 1000;
                document.getElementById('timer-hud').innerText = "TIME: " + total.toFixed(2);
            }
        }, 50);
        //___________change the index of deck to 1__________//
    </script>
</body>
</html>