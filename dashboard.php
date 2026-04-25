<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['user_id'];

// 1. Level Completion Check
$unlocked_res = mysqli_query($conn, "SELECT DISTINCT level_id FROM leaderboard WHERE user_id = '$uid'");
$completed_levels = [];
while ($row = mysqli_fetch_assoc($unlocked_res)) {
    $completed_levels[] = (int)$row['level_id'];
}

// 2. Ranking Fetcher Function
function fetchTopFive($conn, $lvl)
{
    return mysqli_query($conn, "
        SELECT u.username, l.time_taken 
        FROM leaderboard l
        JOIN users u ON l.user_id = u.id 
        WHERE l.level_id = $lvl
        ORDER BY l.time_taken ASC 
        LIMIT 5
    ");
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

        /* Background Effects */
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

        .branding h1 {
            font-size: 3.5rem;
            letter-spacing: 25px;
            text-transform: uppercase;
            text-shadow: 0 0 20px var(--cyan);
            margin-bottom: 40px;
        }

        /* Missions */
        .mission-grid {
            display: flex;
            gap: 30px;
            justify-content: center;
            width: 100%;
            margin-bottom: 50px;
        }

        .mission-card {
            width: 280px;
            border: 1px solid var(--cyan);
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--cyan);
            transition: 0.2s;
            background: rgba(0, 255, 204, 0.02);
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
        }

        .mission-icon {
            width: 65%;
            filter: drop-shadow(0 0 8px var(--cyan));
        }

        .locked {
            opacity: 0.3;
            filter: grayscale(1);
            cursor: not-allowed;
            border-style: dashed;
        }

        /* --- RANKING OVERLAY STYLE --- */
        #rank-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 5000;
            justify-content: center;
            align-items: center;
        }

        .rank-window {
            width: 700px;
            border: 1px solid var(--cyan);
            background: #000;
            padding: 30px;
        }

        .rank-category-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .rank-column {
            flex: 1;
            border: 1px solid var(--dark-cyan);
            padding: 15px;
            background: rgba(0, 255, 204, 0.02);
        }

        .column-title {
            font-size: 0.7rem;
            border-bottom: 1px solid var(--dark-cyan);
            margin-bottom: 10px;
            padding-bottom: 5px;
            color: var(--cyan);
            letter-spacing: 2px;
            text-align: center;
        }

        .rank-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            margin-bottom: 4px;
            opacity: 0.8;
        }

        /* Footer */
        .ui-footer {
            border-top: 1px solid var(--cyan);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            align-items: center;
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
    </style>
</head>

<body>
    <audio id="bg-music" loop>
        <source src="bgm/adrenaline.mp3" type="audio/mpeg">
    </audio>

    <div id="rank-overlay">
        <div class="rank-window">
            <h2 style="text-align:center; letter-spacing: 10px;">GLOBAL_RANKINGS</h2>

            <div class="rank-category-container">
                <?php
                $levels = ["RECON_INIT", "PRICE_MOD", "CARD_RIGGING"];
                for ($i = 1; $i <= 3; $i++):
                    $data = fetchTopFive($conn, $i);
                ?>
                    <div class="rank-column">
                        <div class="column-title">LEVEL_0<?php echo $i; ?><br><?php echo $levels[$i - 1]; ?></div>
                        <?php if (mysqli_num_rows($data) > 0): $r = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($data)): ?>
                                <div class="rank-row">
                                    <span><?php echo $r; ?>. <?php echo htmlspecialchars($row['username']); ?></span>
                                    <span><?php echo number_format($row['time_taken'], 2); ?>s</span>
                                </div>
                            <?php $r++;
                            endwhile; ?>
                        <?php else: ?>
                            <div style="font-size:0.6rem; opacity:0.4; text-align:center;">NO_DATA</div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <button class="btn-footer" style="width:100%; margin-top:30px;" onclick="toggleRankings()">CLOSE_RANKINGS</button>
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
                <a href="javascript:void(0)" class="mission-card <?php echo !in_array(1, $completed_levels) ? 'locked' : ''; ?>" onclick="<?php echo in_array(1, $completed_levels) ? "initNode('level2.php')" : ""; ?>">
                    <div class="mission-icon-box"><img src="icons/currency.png" class="mission-icon"></div>
                    <div class="mission-title">PRICE_MOD</div>
                    <div class="mission-status"><?php echo in_array(2, $completed_levels) ? '[ COMPLETED ]' : (in_array(1, $completed_levels) ? '[ UNLOCKED ]' : '[ ENCRYPTED ]'); ?></div>
                </a>
                <a href="javascript:void(0)" class="mission-card <?php echo !in_array(2, $completed_levels) ? 'locked' : ''; ?>" onclick="<?php echo in_array(2, $completed_levels) ? "initNode('level3.php')" : ""; ?>">
                    <div class="mission-icon-box"><img src="icons/card.png" class="mission-icon"></div>
                    <div class="mission-title">CARD_RIGGING</div>
                    <div class="mission-status"><?php echo in_array(3, $completed_levels) ? '[ COMPLETED ]' : (in_array(2, $completed_levels) ? '[ UNLOCKED ]' : '[ ENCRYPTED ]'); ?></div>
                </a>
            </div>

            <button class="btn-footer" style="padding: 15px 40px; font-size: 0.9rem;" onclick="toggleRankings()">ACCESS_GLOBAL_RANKINGS</button>
        </div>

        <div class="ui-footer">
            <div>OPERATOR: <?php echo strtoupper($_SESSION['username']); ?></div>
            <div class="footer-center">
                <span style="font-size: 0.6rem;">VOL:</span>
                <input type="range" class="volume-slider" id="vol-slider" min="0" max="1" step="0.1" value="0.5">
                <button id="audio-ctrl" class="btn-footer" onclick="toggleAudio()">AUDIO: OFF</button>
            </div>
            <div>SYS_NODES: 3/3 | LEVEL_04: [PURGED]</div>
        </div>
    </div>

    <script>
        // Audio Logic
        const music = document.getElementById('bg-music');
        const audioBtn = document.getElementById('audio-ctrl');
        const volSlider = document.getElementById('vol-slider');
        music.volume = 0.5;

        document.addEventListener('click', () => {
            if (music.paused) {
                music.play();
                audioBtn.innerText = "AUDIO: ON";
            }
        }, {
            once: true
        });

        function toggleAudio() {
            if (music.paused) {
                music.play();
                audioBtn.innerText = "AUDIO: ON";
            } else {
                music.pause();
                audioBtn.innerText = "AUDIO: OFF";
            }
        }
        volSlider.oninput = (e) => {
            music.volume = e.target.value;
        };

        // Rankings Toggle
        function toggleRankings() {
            const overlay = document.getElementById('rank-overlay');
            overlay.style.display = (overlay.style.display === 'flex') ? 'none' : 'flex';
        }

        function initNode(url) {
            /* Your loading sequence logic here */
            window.location.href = url;
        }
    </script>
</body>

</html>