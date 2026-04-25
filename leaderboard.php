<?php 
include 'db.php'; 
if(!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// Default to Level 1 if no level is selected
$level_to_show = isset($_GET['lvl']) ? (int)$_GET['lvl'] : 1;

// Query to get top 10 fastest times for the selected level
$query = "SELECT u.username, l.time_taken, l.created_at 
          FROM leaderboard l 
          JOIN users u ON l.user_id = u.id 
          WHERE l.level_id = $level_to_show 
          ORDER BY l.time_taken ASC 
          LIMIT 10";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BlitzBreach | Leaderboard</title>
    <link rel="stylesheet" href="style_dash.css">
    <style>
        .lb-container { width: 80%; max-width: 800px; margin: 50px auto; background: rgba(0, 212, 255, 0.02); border: 1px solid #333; padding: 30px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; justify-content: center; }
        .tab-btn { padding: 10px 20px; border: 1px solid var(--neon-blue); background: transparent; color: var(--neon-blue); cursor: pointer; text-decoration: none; font-size: 0.8rem; }
        .tab-btn.active { background: var(--neon-blue); color: #000; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; border-bottom: 2px solid #333; padding: 10px; color: var(--neon-blue); letter-spacing: 2px; }
        td { padding: 15px 10px; border-bottom: 1px solid #222; font-size: 0.9rem; }
        .rank { color: #555; width: 50px; }
        .time { color: #00ff88; font-weight: bold; text-align: right; }
        tr:hover { background: rgba(0, 212, 255, 0.05); }
    </style>
</head>
<body>

    <header>
        <div class="logo">⚡ BLITZBREACH_RANKINGS</div>
        <div class="nav"><a href="dashboard.php">BACK TO TERMINAL</a></div>
    </header>

    <div class="lb-container">
        <div class="tabs">
            <a href="?lvl=1" class="tab-btn <?php echo $level_to_show == 1 ? 'active' : ''; ?>">LVL 01</a>
            <a href="?lvl=2" class="tab-btn <?php echo $level_to_show == 2 ? 'active' : ''; ?>">LVL 02</a>
            <a href="?lvl=3" class="tab-btn <?php echo $level_to_show == 3 ? 'active' : ''; ?>">LVL 03</a>
        </div>

        <h2 style="text-align: center; color: #fff; margin-bottom: 20px;">
            TOP OPERATIVES: MISSION 0<?php echo $level_to_show; ?>
        </h2>

        <table>
            <thead>
                <tr>
                    <th class="rank">#</th>
                    <th>OPERATIVE</th>
                    <th style="text-align: right;">BREACH_TIME</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                if(mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="rank"><?php echo str_pad($rank++, 2, "0", STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="time"><?php echo number_format($row['time_taken'], 2); ?>s</td>
                        </tr>
                    <?php endwhile; 
                else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #444; padding: 40px;">NO DATA RECORDED FOR THIS MISSION</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>