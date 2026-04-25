<?php 
include 'db.php'; 

$error_msg = "";

// Tactical Authentication Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = $_POST['pass'];
    $type = $_POST['type'];

    if ($type == 'login') {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            // Verify password (assuming password_hash was used during registration)
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error_msg = "AUTHENTICATION_FAILED: INVALID_PASSCODE";
            }
        } else {
            // Your specific alert for unregistered accounts
            $error_msg = "UNAUTHORIZED PERSON ALERT";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Terminal Entry</title>
    <style>
        :root { 
            --cyan: #00ffcc; 
            --dark-cyan: #004433;
            --bg-black: #050505; 
            --alert-red: #ff3333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', monospace; }

        body { 
            background-color: var(--bg-black);
            background-image: 
                linear-gradient(rgba(0, 255, 204, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 204, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center;
            color: var(--cyan);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        body::before {
            content: " "; display: block; position: fixed; top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.2) 50%);
            z-index: 9999; background-size: 100% 3px; pointer-events: none; opacity: 0.3;
        }

        .login-container {
            width: 420px;
            border: 1px solid var(--cyan);
            background: rgba(0, 15, 15, 0.95);
            padding: 50px 40px;
            position: relative;
            box-shadow: 0 0 40px rgba(0, 255, 204, 0.1);
        }

        .login-container::before {
            content: 'SYS_v2.6_NULL';
            position: absolute;
            top: -10px;
            left: 15px;
            background: var(--bg-black);
            padding: 0 10px;
            font-size: 0.65rem;
            letter-spacing: 2px;
            color: var(--cyan);
        }

        h1 {
            text-align: center;
            font-size: 2rem;
            letter-spacing: 6px;
            margin-bottom: 5px;
            text-shadow: 0 0 15px var(--cyan);
        }

        .subtitle {
            text-align: center;
            font-size: 0.6rem;
            letter-spacing: 2px;
            margin-bottom: 40px;
            opacity: 0.7;
            text-transform: uppercase;
        }

        /* Tactical Alert Box */
        .msg {
            text-align: center;
            font-size: 0.75rem;
            margin-bottom: 25px;
            letter-spacing: 1px;
            padding: 10px;
            border: 1px solid var(--alert-red);
            background: rgba(255, 51, 51, 0.1);
            color: var(--alert-red);
            text-transform: uppercase;
            font-weight: bold;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0% { opacity: 1; border-color: var(--alert-red); }
            50% { opacity: 0.6; border-color: transparent; }
            100% { opacity: 1; border-color: var(--alert-red); }
        }

        input {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            background: #000;
            border: 1px solid var(--dark-cyan);
            color: var(--cyan);
            outline: none;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-align: center;
        }

        input:focus {
            border-color: var(--cyan);
            box-shadow: inset 0 0 10px rgba(0, 255, 204, 0.1);
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        button {
            width: 100%;
            padding: 15px;
            font-weight: bold;
            cursor: pointer;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: 0.3s;
            font-family: 'Courier New', monospace;
        }

        .login-btn {
            background: var(--cyan);
            color: #000;
            border: none;
        }

        .login-btn:hover {
            background: #fff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
        }

        .reg-btn {
            background: transparent;
            color: var(--cyan);
            border: 1px solid var(--cyan);
        }

        .reg-btn:hover {
            background: rgba(0, 255, 204, 0.1);
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
        }

    </style>
</head>
<body>

    <div class="login-container">
        <h1>BLITZBREACH</h1>
        <p class="subtitle">Where your weapon is Critical Thinking.</p>

        <?php if($error_msg): ?>
            <div class="msg">>> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="user" placeholder="AGENT_NAME" required autocomplete="off">
            <input type="password" name="pass" placeholder="PASSCODE" required>
            
            <div class="btn-group">
                <button type="submit" name="type" value="login" class="login-btn">INITIALIZE LOGIN</button>
                <button type="submit" name="type" value="reg" class="reg-btn">REGISTER AGENT</button>
            </div>
        </form>
    </div>

</body>
</html>