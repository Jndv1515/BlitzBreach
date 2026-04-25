<?php
include 'db.php';

$user = mysqli_real_escape_string($conn, $_POST['user']);
$pass = $_POST['pass'];
$type = $_POST['type'];

if ($type == "reg") {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$user', '$hash')");
    header("Location: index.php?msg=Registered!");
} else {
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    $row = mysqli_fetch_assoc($res);
    if ($row && password_verify($pass, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: dashboard.php");
    } else {
        echo "Access Denied.";
    }
}
?>