<?php 
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require 'functions.php';

// Ambil nama tabel yang dipilih dari sesi
$tabelDipilih = $_SESSION['table_name'] ?? '';

if (!$tabelDipilih) {
    echo "Tabel tidak ditemukan! Silakan login kembali.";
    exit;
}

// Aktifkan laporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Terjemahkan nama tabel ke nama pilihan
$pilihanAdmin = [
    'call_center_admin1' => 'Call Center',
    'call_center_admin2' => 'Call Center',
    'call_center_admin3' => 'Call Center',
    'call_center_admin4' => 'Call Center',
    'whatsapp_admin1' => 'Whatsapp',
    'whatsapp_admin2' => 'Whatsapp',
    'whatsapp_admin3' => 'Whatsapp',
    'whatsapp_admin4' => 'Whatsapp',
    'customer_service_admin1' => 'Customer Service',
    'customer_service_admin2' => 'Customer Service',
    'customer_service_admin3' => 'Customer Service',
    'customer_service_admin4' => 'Customer Service',
    'home_apps_admin1' => 'Home Apps',
    'home_apps_admin2' => 'Home Apps',
    'home_apps_admin3' => 'Home Apps',
    'home_apps_admin4' => 'Home Apps'
];

// Array nama tabel berdasarkan pilihan
$pilihanTabel = [
    'call_center' => 'call_center_admin1',
    'whatsapp' => 'whatsapp_admin1',
    'customer_service' => 'customer_service_admin1',
    'home_apps' => 'home_apps_admin1'
];

// Tangani pilihan pengguna
if (isset($_GET['pilihan']) && array_key_exists($_GET['pilihan'], $pilihanTabel)) {
    $_SESSION['table_name'] = $pilihanTabel[$_GET['pilihan']];
    header("Location: index.php");
}

// // Tangani perubahan pilihan
// if (isset($_POST['ubah_pilihan'])) {
//     if (!isset($_POST['pilihan_baru'])) {
//         echo "Pilihan baru tidak ditemukan!";
//         exit;
//     }

//     $pilihanBaru = $_POST['pilihan_baru'];
//     $username = isset($_SESSION['username']) ? trim($_SESSION['username']) :''; // Pastikan username tanpa spasi

//     if (!$username) {
//         echo "Username tidak ditemukan di sesi!";
//         exit;
//     }

//     // Format nama tabel berdasarkan pilihan baru dan username
// $tabelDipilihBaru = strtolower(str_replace(' ', '_', trim($pilihanBaru))) . '_' . $username;


//     // Cek apakah tabel tersebut valid
//     if (array_key_exists($tabelDipilihBaru, $pilihanAdmin)) {
//         $_SESSION['table_name'] = $tabelDipilihBaru; // Update sesi
//         header("Location: index.php"); // Refresh halaman
//         exit;
//     } else {
//         echo "Pilihan tidak valid: " . htmlspecialchars($tabelDipilihBaru);
//         exit;
//     }
// }


// Konfigurasi pagination
$jumlahDataPerHalaman = 10;

// Tangkap data pencarian dari form atau sesi
if (isset($_POST["cari"])) {
    $_SESSION['pencarian'] = [
        'jenis' => $_POST['jenis'] ?? '',
        'tanggal_mulai' => $_POST['tanggal_mulai'] ?? '',
        'tanggal_selesai' => $_POST['tanggal_selesai'] ?? '',
        'jam_mulai' => $_POST['jam_mulai'] ?? '',
        'jam_selesai' => $_POST['jam_selesai'] ?? ''
    ];
}

// Ambil parameter pencarian
$pencarian = $_SESSION['pencarian'] ?? [
    'jenis' => '',
    'tanggal_mulai' => '',
    'tanggal_selesai' => '',
    'jam_mulai' => '',
    'jam_selesai' => ''
];

// // Pastikan username tersedia di sesi
// if (!isset($_SESSION['username'])) {
//     echo "Session tidak valid. Silakan login kembali.";
//     exit;
// }

// // Ambil username dari sesi dan hapus spasi
// $username = strtolower(str_replace(' ', '', trim($_SESSION['username']))); // Format: admin1

// // Tentukan halaman landing
// $landingPage = $username . "_landing.php"; // Format: admin1_landing.php

// // Cek apakah file landing page ada
// if (!file_exists($landingPage)) {
//     echo "Halaman landing tidak ditemukan: " . htmlspecialchars($landingPage);
//     exit;
// }

// // Tampilkan tombol kembali
// echo '<button onclick="window.location.href=\'' . $landingPage . '\'">Kembali ke Landing</button>';

$pilihanSekarang = $pilihanAdmin[$tabelDipilih] ?? 'Pilihan Tidak Dikenal';

// Ambil data berdasarkan pencarian
$dataHasilPencarian = cari($pencarian, $tabelDipilih);
$jumlahData = count($dataHasilPencarian);
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
$halamanAktif = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;

// Ambil data untuk halaman yang sekarang
$pelayanan = array_slice($dataHasilPencarian, $awalData, $jumlahDataPerHalaman);

// Ngitung total jenis pelayanan
$totalPelayanan = [];
foreach ($dataHasilPencarian as $row) {
    $jenis = $row['jenis_pelayanan'];
    $totalPelayanan[$jenis] = ($totalPelayanan[$jenis] ?? 0) + 1;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Data</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>

<body>
<!-- Form pencarian -->
 <h1>Cari Data <?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($pilihanSekarang); ?>)</h1>
 <br>
 <br>
 <form action="" method="post">
        <h4>(Data tiap admin terhubung ke CS masing masing)</h4>
        <br>
        <label for="jenis">Jenis Pelayanan:</label>
        <input type="text" name="jenis" id="jenis" value="<?= htmlspecialchars($pencarian['jenis']); ?>" placeholder="Masukkan jenis pelayanan...">
        
        <label for="tanggal_mulai">Dari Tanggal: (Opsional)</label>
        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?= htmlspecialchars($pencarian['tanggal_mulai']); ?>">
        <label for="tanggal_selesai">Sampai Tanggal: (Opsional)</label>
        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?= htmlspecialchars($pencarian['tanggal_selesai']); ?>">
        
        <label for="jam_mulai">Dari Jam: (Opsional)</label>
        <input type="time" name="jam_mulai" id="jam_mulai" value="<?= htmlspecialchars($pencarian['jam_mulai']); ?>">
        <label for="jam_selesai">Sampai Jam: (Opsional)</label>
        <input type="time" name="jam_selesai" id="jam_selesai" value="<?= htmlspecialchars($pencarian['jam_selesai']); ?>">
        <button id="" type="submit" name="cari">Cari</button>
        <button class="oren"><a href="index.php">Kembali</a></button>
    </form>
    <br>
    <br>
    <br><br>
    <div class="container">
    <h1>Total Pelayanan CS dan <?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($pilihanSekarang); ?>)</h1>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Jenis Pelayanan</th>
        <th>Total</th>
    </tr>
    <?php 
    $grandTotal = 0; // Variabel untuk menghitung total keseluruhan
    foreach ($totalPelayanan as $jenis => $total): 
        $grandTotal += $total; // Tambahkan setiap total ke grand total
    ?>
    <tr>
        <td><?= htmlspecialchars($jenis); ?></td>
        <td><?= $total; ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td><strong>Jumlah Total</strong></td>
        <td><strong><?= $grandTotal; ?></strong></td>
    </tr>
</table>

    <br>
    <h2>Daftar Pelayanan <?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($pilihanSekarang); ?>)</h2>
    <!-- Tabel data pelayanan -->
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>No</th>
            <th>Aksi</th>
            <th>Jenis Pelayanan</th>
            <th>Tanggal</th>
            <th>Jam</th>
        </tr>
        <?php $i = $awalData + 1; ?>
        <?php foreach ($pelayanan as $row): ?>
        <tr>
            <td><?= $i++; ?></td>
            <td>
            <a class="aksi" href="ubah.php?id=<?= $row["id"] ?>&tabel=<?= urlencode($tabelDipilih); ?>"><button>Ubah</button></a>
            <a class="aksi" href="hapus.php?id=<?= $row["id"] ?>&tabel=<?= urlencode($tabelDipilih); ?>" onclick="return confirm('Yakin ingin menghapus                     data ini?');"><button class="logout" >Hapus</button></a>
            </td>
            <td><?= htmlspecialchars($row["jenis_pelayanan"]); ?></td>
            <td><?= htmlspecialchars($row["tanggal"]); ?></td>
            <td><?= htmlspecialchars($row["jam"]); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <!-- Pagination -->
    <div class="pagination">
        <?php if ($halamanAktif > 1): ?>
            <a href="?halaman=<?= $halamanAktif - 1; ?>">&laquo;</a>
        <?php endif; ?>

        <?php for ($j = 1; $j <= $jumlahHalaman; $j++): ?>
            <a href="?halaman=<?= $j; ?>" <?= $j == $halamanAktif ? 'style="font-weight: bold;"' : ''; ?>><?= $j; ?></a>
        <?php endfor; ?>

        <?php if ($halamanAktif < $jumlahHalaman): ?>
            <a href="?halaman=<?= $halamanAktif + 1; ?>">&raquo;</a>
        <?php endif; ?>
    </div>
    </div>
    </body>
</html>