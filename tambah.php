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

// Daftar layanan berdasarkan tabel
$daftarPelayanan = [];
if (strpos($tabelDipilih, 'customer_service') !== false) {
    $daftarPelayanan = [
        "Komplain", "Informasi", "Perijinan", "Renovasi dan Canopy", "Insentive Huni/Voucher",
        "Member Club", "Acces Card", "SSC", "Kwitansi IPL", "Kwitansi PAM", "PBB"
    ];
} else {
    $daftarPelayanan = ["Komplain", "Informasi", "Perijinan", "PAM"];
}

// Proses data yang dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = tambah($_POST, $tabelDipilih);
    if ($result === true) {
        echo "<script>alert('Data berhasil disimpan!'); window.location.href='tambah.php';</script>";
    } else {
        echo "<script>alert('$result');</script>";
    }
}

// Ambil data untuk tabel bawah (hanya data hari ini)
$tanggalHariIni = date("Y-m-d"); // Tanggal hari ini
$dataHasilPencarian = cari([
    'tanggal_mulai' => $tanggalHariIni,
    'tanggal_selesai' => $tanggalHariIni
], $tabelDipilih);

$totalPelayanan = [];
foreach ($dataHasilPencarian as $row) {
    $jenis = $row['jenis_pelayanan'];
    $totalPelayanan[$jenis] = ($totalPelayanan[$jenis] ?? 0) + 1;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Pelayanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($tabelDipilih); ?>)</h1>
        <h4>(Data CS otomatis terhubung dengan Admin)</h4>

        <!-- Form Input -->
        <form action="" method="post">
            <h3>Pelayanan :</h3>
            <?php foreach ($daftarPelayanan as $jenis): ?>
                <input type="radio" name="jenis_pelayanan[]" value="<?= htmlspecialchars($jenis); ?>" id="<?= htmlspecialchars($jenis); ?>">
                <label for="<?= htmlspecialchars($jenis); ?>"><?= htmlspecialchars($jenis); ?></label><br>
            <?php endforeach; ?>
            <br>
            <button type="submit" name="input">Simpan</button>
        </form>

        <!-- Tabel Data -->
        <h1>Data Hari Ini (<?= date("d-m-Y"); ?>)</h1>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Jenis Pelayanan</th>
                <th>Total</th>
            </tr>
            <?php 
            $grandTotal = 0;
            foreach ($totalPelayanan as $jenis => $total): 
                $grandTotal += $total;
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
    </div>
</body>
</html>
