<?php 
session_start();

// Pastikan pengguna login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Array nama tabel berdasarkan pilihan
$pilihanTabel = [
    'call_center' => 'call_center_admin3',
    'whatsapp' => 'whatsapp_admin3',
    'customer_service' => 'customer_service_admin3',
    'home_apps' => 'home_apps_admin3'
];

// Tangani pilihan pengguna
if (isset($_GET['pilihan']) && array_key_exists($_GET['pilihan'], $pilihanTabel)) {
    $_SESSION['table_name'] = $pilihanTabel[$_GET['pilihan']];
    header("Location: tambah.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page <?= htmlspecialchars($_SESSION['username']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
<h1>Halo!, <?= htmlspecialchars($_SESSION['username']); ?></h1>
<br>
    <form action="">
    <h3>Masuk Ke Pelayanan :</h3>
    <br>
   <ul>
    <li><a href="?pilihan=call_center" class="button">Call Center</a></li>
    <br>
    <li><a href="?pilihan=whatsapp" class="button">Whatsapp</a></li>
    <br>
    <li><a href="?pilihan=customer_service" class="button">Customer Service</a></li>
    <br>
    <li><a href="?pilihan=home_apps" class="button">Home Apps</a></li>
</ul>
    <br>
    </form>
    <div class="container">
    <a href="logout.php"><button class="logoutLanding">Logout</button></a>
    </div>
</body>
</html>
