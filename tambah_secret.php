<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

require 'routeros_api.class.php';

$API = new RouterosAPI();
$host = $_SESSION['host'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];

if ($API->connect($host, $username, $password)) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Tambah PPP Secret</title>
        <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='container mt-4'>
            <h1 class='text-center mb-4'>Tambah PPP Secret Baru</h1>
            <a href='index.php' class='btn btn-secondary mb-3'>Kembali ke Manajemen Tagihan</a>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_secret'])) {
        $name = $_POST['name'];
        $password = $_POST['password'];
        $profile = $_POST['profile'];
        $local_address = $_POST['local_address'];
        $remote_address = $_POST['remote_address'];
        $comment = $_POST['comment'];

        $API->comm('/ppp/secret/add', [
            'name' => $name,
            'password' => $password,
            'profile' => $profile,
            'local-address' => $local_address,
            'remote-address' => $remote_address,
            'comment' => $comment,
        ]);

        echo "<div class='alert alert-success'>PPP Secret baru telah ditambahkan.</div>";
    }

    $profiles = $API->comm('/ppp/profile/print');

    echo "
    <form method='POST'>
        <div class='form-group'>
            <label for='name'>Nama:</label>
            <input type='text' name='name' class='form-control' required>
        </div>
        <div class='form-group'>
            <label for='password'>Password:</label>
            <input type='text' name='password' class='form-control' required>
        </div>
        <div class='form-group'>
            <label for='profile'>Profile:</label>
            <select name='profile' class='form-control' required>";
    foreach ($profiles as $profile) {
        echo "<option value='{$profile['name']}'>{$profile['name']}</option>";
    }
    echo "</select>
        </div>
        <div class='form-group'>
            <label for='local_address'>Local Address:</label>
            <input type='text' name='local_address' class='form-control' required>
        </div>
        <div class='form-group'>
            <label for='remote_address'>Remote Address:</label>
            <input type='text' name='remote_address' class='form-control' required>
        </div>
        <div class='form-group'>
            <label for='comment'>Komentar:</label>
            <input type='text' name='comment' class='form-control' required>
        </div>
        <button type='submit' name='add_secret' class='btn btn-primary'>Tambah Secret</button>
    </form>
    </div>
    </body>
    </html>";

    $API->disconnect();
} else {
    echo "<p>Gagal terhubung ke MikroTik. Periksa koneksi Anda.</p>";
}
?>
