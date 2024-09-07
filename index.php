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
        <title>Manajemen Tagihan IR NET</title>
        <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        <link rel='stylesheet' href='styles.css'>
        <script>
            function confirmChange(name, id) {
                if (confirm('Apakah Anda yakin ingin mengubah pelanggan \"' + name + '\" menjadi \"lunas\"?')) {
                    document.getElementById('secret_id').value = id;
                    document.getElementById('change_form').submit();
                }
            }
            function hideNotification() {
                const notification = document.getElementById('confirmation_message');
                if (notification) {
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 5000);
                }
            }
        </script>
    </head>
    <body onload='hideNotification()'>
        <div class='container mt-4'>
            <h1 class='text-center mb-4'>Manajemen Tagihan IR NET</h1>
            <a href='logout.php' class='btn btn-danger mb-3'>Logout</a>
            <nav class='mb-4'>
                <a href='index.php' class='btn btn-primary'>Manajemen Tagihan</a>
                <a href='tambah_secret.php' class='btn btn-success'>Tambah PPP Secret</a>
            </nav>";

    $secrets = $API->comm('/ppp/secret/print');

    $belumLunas = [];
    $totalWithComment = [];

    foreach ($secrets as $secret) {
        if (isset($secret['comment']) && !empty($secret['comment'])) {
            $comment = $secret['comment'];
            if (strpos($comment, 'belum') !== false) {
                $belumLunas[] = $secret;
            }
            $totalWithComment[] = $secret;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secret_id'])) {
        $secretId = $_POST['secret_id'];
        foreach ($totalWithComment as $secret) {
            if ($secret['.id'] === $secretId) {
                $currentComment = $secret['comment'];
                $newComment = 'lunas' . substr($currentComment, 5);
                $API->comm('/ppp/secret/set', [
                    ".id" => $secret['.id'],
                    "comment" => $newComment,
                ]);
                echo "<div class='alert alert-success' id='confirmation_message'>Komentar untuk PPP secret {$secret['name']} telah diubah menjadi 'lunas'.</div>";
                header("Location: manajemen_tagihan.php");
                exit();
            }
        }
    }

    echo "<div class='card mb-4'>
            <div class='card-body'>
                <h5 class='card-title'>Ringkasan</h5>
                <p>Jumlah Semua Pelanggan: " . count($totalWithComment) . "</p>
                <p>Jumlah Pelanggan Yang Belum Bayar: " . count($belumLunas) . "</p>
            </div>
        </div>";

    echo "<div>
            <h3>Pilih Pelanggan Yang Sudah Bayar:</h3>
            <form id='change_form' method='POST'>
                <input type='hidden' name='secret_id' id='secret_id' />
                <div class='list-group'>";

    foreach ($totalWithComment as $secret) {
        echo "<div class='list-group-item'>
                <div class='d-flex flex-column'>
                    <div class='mb-2'>
                        <button type='button' class='btn btn-primary' onclick=\"confirmChange('{$secret['name']}', '{$secret['.id']}')\">Ubah menjadi Lunas</button>
                    </div>
                    <div>
                        <strong>{$secret['name']}</strong>
                        <p>{$secret['comment']}</p>
                    </div>
                </div>
            </div>";
    }

    echo "  </div>
            </form>
        </div>
    </body>
    </html>";

    $API->disconnect();
} else {
    echo "<p>Gagal terhubung ke MikroTik. Periksa koneksi Anda.</p>";
}
?>
