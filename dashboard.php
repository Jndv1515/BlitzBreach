<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];

// 1. RANKINGS QUERY
$leaderboard_res = mysqli_query($conn, "
    SELECT u.username, MIN(l.time_taken) as best_time 
    FROM leaderboard l
    JOIN users u ON l.user_id = u.id 
    GROUP BY u.id 
    ORDER BY best_time ASC 
    LIMIT 5
");

// 2. COMPLETED LEVELS CHECK
$unlocked_res = mysqli_query($conn, "SELECT DISTINCT level_id FROM leaderboard WHERE user_id = '$uid'");
$completed_levels = [];
while ($row = mysqli_fetch_assoc($unlocked_res)) {
    $completed_levels[] = (int)$row['level_id'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Terminal</title>
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
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* 📺 Retro Scanline Effect */
        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.2) 50%);
            z-index: 9999;
            background-size: 100% 3px;
            pointer-events: none;
            opacity: 0.3;
        }

        .terminal-frame {
            width: 90vw;
            height: 85vh;
            border: 2px solid var(--cyan);
            background: var(--panel-bg);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .ui-header {
            background: var(--cyan);
            color: #000;
            padding: 5px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .ui-body {
            flex-grow: 1;
            padding: 20px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Wide Branding Header */
        .branding {
            text-align: center;
            margin-bottom: 30px;
        }

        .branding h1 {
            font-size: 3.5rem;
            letter-spacing: 25px;
            text-transform: uppercase;
            text-shadow: 0 0 20px var(--cyan);
        }

        /* Mission Selection Grid */
        .mission-grid {
            display: flex;
            gap: 30px;
            justify-content: center;
            width: 100%;
            margin-bottom: 30px;
        }

        .mission-card {
            width: 280px;
            border: 1px solid var(--cyan);
            padding: 20px;
            background: rgba(0, 255, 204, 0.02);
            text-align: center;
            text-decoration: none;
            color: var(--cyan);
            transition: 0.2s;
        }

        .mission-card:hover:not(.locked) {
            background: rgba(0, 255, 204, 0.1);
            box-shadow: inset 0 0 15px var(--cyan);
        }

        .mission-icon-box {
            width: 100px;
            height: 100px;
            border: 1px solid var(--dark-cyan);
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 255, 204, 0.05);
        }

        .mission-icon {
            width: 65%;
            height: auto;
            filter: drop-shadow(0 0 8px var(--cyan));
        }

        .mission-title {
            font-weight: bold;
            font-size: 0.9rem;
            letter-spacing: 2px;
            border-bottom: 1px solid var(--dark-cyan);
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .mission-status {
            font-size: 0.7rem;
            letter-spacing: 2px;
            opacity: 0.8;
        }

        .locked {
            opacity: 0.3;
            filter: grayscale(1);
            cursor: not-allowed;
            border-style: dashed;
        }

        /* Rankings HUD */
        .rankings-container {
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--dark-cyan);
            padding: 20px;
            background: rgba(0, 0, 0, 0.4);
        }

        .rankings-title {
            font-size: 0.8rem;
            letter-spacing: 4px;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--dark-cyan);
            padding-bottom: 5px;
        }

        .rank-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }

        /* Footer HUD */
        .ui-footer {
            border-top: 1px solid var(--cyan);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            align-items: center;
        }

        .footer-left,
        .footer-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .footer-center {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-footer {
            cursor: pointer;
            border: 1px solid var(--cyan);
            padding: 3px 12px;
            color: var(--cyan);
            text-transform: uppercase;
            background: transparent;
            font-size: 0.65rem;
            transition: 0.2s;
        }

        .btn-footer:hover {
            background: var(--cyan);
            color: #000;
        }

        .volume-slider {
            -webkit-appearance: none;
            width: 70px;
            height: 2px;
            background: var(--dark-cyan);
            outline: none;
        }

        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 10px;
            height: 10px;
            background: var(--cyan);
            cursor: pointer;
        }

        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .processing-box {
            border: 1px solid var(--cyan);
            background: #000;
            padding: 20px;
            width: 400px;
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            border: 1px solid var(--cyan);
            margin-top: 15px;
        }

        .progress-fill {
            height: 100%;
            background: var(--cyan);
            width: 0%;
            transition: width 0.1s;
        }
    </style>
</head>

<body>
    <audio id="bg-music" loop>
        <source src="bgm/adrenaline.mp3" type="audio/mpeg">
    </audio>

    <div id="loading-overlay">
        <div class="processing-box">
            <div style="font-size: 0.8rem; margin-bottom: 5px; border-bottom: 1px solid var(--cyan);">NODE_INITIALIZATION</div>
            <div class="progress-bar">
                <div class="progress-fill" id="fill"></div>
            </div>
        </div>
    </div>

    <div class="terminal-frame">
        <div class="ui-header">
            <div>BLITZBREACH_TERMINAL_V1.0</div>
            <div onclick="location.href='logout.php'" style="cursor:pointer">LOGOUT [X]</div>
        </div>

        <div class="ui-body">
            <div class="branding">
                <h1>BLITZBREACH</h1>
            </div>

            <div class="mission-grid">
                <a href="javascript:void(0)" class="mission-card" onclick="initNode('level1.php?user=look@endofcode.com')">
                    <div class="mission-icon-box"><img src="icons/eye.png" class="mission-icon"></div>
                    <div class="mission-title">RECON_INIT</div>
                    <div class="mission-status"><?php echo in_array(1, $completed_levels) ? '[ COMPLETED ]' : '[ AVAILABLE ]'; ?></div>
                </a>

                <?php $l2_u = in_array(1, $completed_levels); ?>
                <a href="javascript:void(0)" class="mission-card <?php echo !$l2_u ? 'locked' : ''; ?>" onclick="<?php echo $l2_u ? "initNode('level2.php')" : ""; ?>">
                    <div class="mission-icon-box"><img src="icons/currency.png" class="mission-icon"></div>
                    <div class="mission-title">PRICE_MOD</div>
                    <div class="mission-status"><?php echo in_array(2, $completed_levels) ? '[ COMPLETED ]' : ($l2_u ? '[ UNLOCKED ]' : '[ ENCRYPTED ]'); ?></div>
                </a>

                <?php $l3_u = in_array(2, $completed_levels); ?>
                <a href="javascript:void(0)" class="mission-card <?php echo !$l3_u ? 'locked' : ''; ?>" onclick="<?php echo $l3_u ? "initNode('level3.php')" : ""; ?>">
                    <div class="mission-icon-box"><img src="icons/card.png" class="mission-icon"></div>
                    <div class="mission-title">CARD_RIGGING</div>
                    <div class="mission-status"><?php echo in_array(3, $completed_levels) ? '[ COMPLETED ]' : ($l3_u ? '[ UNLOCKED ]' : '[ ENCRYPTED ]'); ?></div>
                </a>
            </div>

            <div class="rankings-container">
                <div class="rankings-title">TOP_OPERATIVES_RANKING</div>
                <?php if ($leaderboard_res && mysqli_num_rows($leaderboard_res) > 0): ?>
                    <?php $rank = 1;
                    while ($row = mysqli_fetch_assoc($leaderboard_res)): ?>
                        <div class="rank-row">
                            <span><?php echo $rank . ". " . htmlspecialchars($row['username']); ?></span>
                            <span><?php echo number_format($row['best_time'], 2); ?>s</span>
                        </div>
                    <?php $rank++;
                    endwhile; ?>
                <?php else: ?>
                    <div style="font-size: 0.7rem; opacity: 0.5;">NO_RANKING_DATA_FOUND</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="ui-footer">
            <div class="footer-left">
                <div>OPERATOR: <?php echo strtoupper($_SESSION['username']); ?></div>
            </div>

            <div class="footer-center">
                <span style="font-size: 0.6rem;">VOL:</span>
                <input type="range" class="volume-slider" id="vol-slider" min="0" max="1" step="0.1" value="0.5">
                <button id="audio-ctrl" class="btn-footer" onclick="toggleAudio()">AUDIO: OFF</button>
            </div>

            <div class="footer-right">
                <div>SYS_NODES: 3/3 | LEVEL_04: [PURGED]</div>
            </div>
        </div>
    </div>

    <script>
        const music = document.getElementById('bg-music');
        const audioBtn = document.getElementById('audio-ctrl');
        const volSlider = document.getElementById('vol-slider');

        music.volume = 0.5;

        // Auto-unlock audio on first click
        const startMusic = () => {
            music.play().then(() => {
                audioBtn.innerText = "AUDIO: ON";
                document.removeEventListener('click', startMusic);
            }).catch(e => console.log("Waiting for user handshake..."));
        };
        document.addEventListener('click', startMusic);

        function toggleAudio() {
            if (music.paused) {
                music.play();
                audioBtn.innerText = "AUDIO: ON";
            } else {
                music.pause();
                audioBtn.innerText = "AUDIO: OFF";
            }
        }

        volSlider.addEventListener('input', (e) => {
            music.volume = e.target.value;
        });

        function initNode(url) {
            const overlay = document.getElementById('loading-overlay');
            const fill = document.getElementById('fill');
            overlay.style.display = 'flex';
            let width = 0;
            let interval = setInterval(() => {
                if (width >= 100) {
                    clearInterval(interval);
                    window.location.href = url;
                } else {
                    width += Math.random() * 20;
                    if (width > 100) width = 100;
                    fill.style.width = width + '%';
                }
            }, 100);
        }
    </script>
</body>

</html>