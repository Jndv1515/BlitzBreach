<?php
ini_set('display_errors', 0); 
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized session']);
        exit();
    }

    $uid = $_SESSION['user_id'];
    $level_id = $_POST['level_id'] ?? null;
    $success = false;

    // --- LEVEL 1: RECON_INIT (Credentials) ---
    if ($level_id == "1") {
        $email = $_POST['submitted_email'] ?? '';
        $pass = $_POST['submitted_password'] ?? '';
        if ($email === "look@endofcode.com" && $pass === "breach") {
            $success = true;
        }
    }

    // --- LEVEL 2: PRICE_MANIPULATION (Data Rigging) ---
    elseif ($level_id == "2") {
        if (($_POST['item_price'] ?? '') === "0") {
            $success = true;
        }
    }

    // --- LEVEL 3: CARD_RIGGING (Logic/Timing Attack) ---
    elseif ($level_id == "3") {
        if (($_POST['card_value'] ?? '') == "9") {
            $success = true;
        }
    }

    // --- LEVEL 4: BOUNDARY_BYPASS (Maze Hack) ---
    elseif ($level_id == "4") {
        // Validation for the maze breach
        $success = true; 
    }

    // --- DATABASE PROCESSING ---
    if ($success) {
        // Calculate final time based on when the level loaded
        $time_taken = round(microtime(true) - $_SESSION['start_time'], 2);
        
        // Insert into leaderboard
        $stmt = $conn->prepare("INSERT INTO leaderboard (user_id, level_id, time_taken) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $uid, $level_id, $time_taken);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'time' => $time_taken]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database failure']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'Verification failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>