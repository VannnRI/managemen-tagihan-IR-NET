<?php
session_start();
require 'routeros_api.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $API = new RouterosAPI();

    if ($API->connect($host, $username, $password)) {
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['host'] = $host;
        $_SESSION['loggedin'] = true;

        header("Location: index.php");
        exit();
    } else {
        $error_message = "Login gagal. Periksa host, username, atau password Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login MikroTik</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Login MikroTik</h1>
        <form method="POST" action="login.php">
            <div>
                <label for="host">Host:</label>
                <input type="text" id="host" name="host" required>
            </div>
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    </div>
</body>
</html>
