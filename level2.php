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
    <title>BlitzBreach | Node 02</title>
    <style>
        :root {
            --cyan: #00ffcc;
            --dark-cyan: #004433;
            --panel-bg: rgba(0, 20, 20, 0.95);
            --error-red: #ff3333;
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

        .exploit-card {
            width: 450px;
            border: 1px solid var(--cyan);
            padding: 40px;
            background: rgba(0, 255, 204, 0.02);
            text-align: center;
            position: relative;
        }

        .exploit-card::before {
            content: 'NODE_02: SECURE_MARKET';
            position: absolute;
            top: -10px;
            left: 20px;
            background: #050505;
            padding: 0 10px;
            font-size: 0.7rem;
            letter-spacing: 2px;
        }

        .balance-info {
            font-size: 0.7rem;
            margin-bottom: 30px;
            border: 1px dashed var(--dark-cyan);
            padding: 10px;
        }

        .balance-amount {
            color: var(--error-red);
            font-weight: bold;
        }

        .price-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .currency-icon {
            width: 40px;
            height: 40px;
            filter: drop-shadow(0 0 5px var(--error-red));
        }

        .amount {
            font-size: 2.5rem;
            color: var(--error-red);
            font-weight: bold;
            letter-spacing: 2px;
        }

        .btn-buy {
            width: 100%;
            padding: 15px;
            background: rgba(255, 51, 51, 0.1);
            color: #555;
            border: 1px solid #333;
            font-weight: bold;
            cursor: not-allowed;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.3s;
        }

        /* This class will be applied by JS if the player manipulates the price correctly */
        .btn-enabled {
            background: transparent !important;
            color: var(--cyan) !important;
            border-color: var(--cyan) !important;
            cursor: pointer !important;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
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
        <div class="exploit-card">
            <div class="balance-info">
                CURRENT_OPERATIVE_BALANCE: <span class="balance-amount">0.00</span>
            </div>

            <div style="font-size: 0.65rem; color: var(--dark-cyan);">TARGET_ASSET: ADMIN_ENCRYPTION_KEY</div>

            <div class="price-display">
                <img src="icons/currency.png" class="currency-icon" alt="Currency">
                <span class="amount" id="visual-price">1,000,000</span>
            </div>

            <form id="breach-form">
                <input type="hidden" name="level_id" value="2">
                <input type="hidden" id="hidden-price" name="item_price" value="1000000">

                <button type="submit" id="buy-button" class="btn-buy" disabled>EXECUTE_PURCHASE</button>
            </form>

            <div style="margin-top: 20px; font-size: 0.6rem; color: #444;">
                ERROR: INSUFFICIENT_FUNDS. TRANSACTION_BLOCKED.
            </div>
        </div>
    </div>

    <div id="pause-overlay" class="full-overlay">
        <h2 style="color: var(--cyan); letter-spacing: 5px;">MARKET_HALTED</h2>
        <button class="btn-buy btn-enabled" onclick="togglePause()" style="width: 150px; margin-top:20px;">RESUME</button>
    </div>

    <div id="win-overlay" class="full-overlay">
        <div style="border: 2px solid var(--cyan); padding: 50px; text-align: center; background: #000;">
            <h1 style="letter-spacing: 12px; color: var(--cyan);">TRANSACTION_SUCCESS</h1>
            <p style="margin-top: 10px; font-size: 0.8rem;">ASSET_ACQUIRED: NODE_03_ACCESS_CODES</p>
            <button class="btn-buy btn-enabled" onclick="location.href='level3.php'" style="margin-top:20px;">PROCEED_TO_NODE_03</button>
        </div>
    </div>

    <script>
        let startTime = Date.now(),
            elapsedTime = 0,
            isPaused = false,
            isFinished = false;

        // Monitoring the hidden input for changes
        const hiddenPrice = document.getElementById('hidden-price');
        const buyButton = document.getElementById('buy-button');
        const visualPrice = document.getElementById('visual-price');

        function checkPrice() {
            if (parseFloat(hiddenPrice.value) <= 0) {
                buyButton.disabled = false;
                buyButton.classList.add('btn-enabled');
                buyButton.innerText = "CONFIRM_FREE_PURCHASE";
                visualPrice.innerText = hiddenPrice.value;
                visualPrice.style.color = "var(--cyan)";
            } else {
                buyButton.disabled = true;
                buyButton.classList.remove('btn-enabled');
            }
        }

        // Run check every 500ms to see if user manipulated the DOM
        setInterval(checkPrice, 500);

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
            const res = await fetch('verify.php', {
                method: 'POST',
                body: new FormData(e.target)
            });
            const data = await res.json();
            if (data.status === 'success') {
                isFinished = true;
                document.getElementById('win-overlay').style.display = 'flex';
            }
        };
    </script>
</body>

</html>