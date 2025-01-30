<?php  
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

require 'functions.php';

// Pastikan nama tabel sudah tersedia di session
if (!isset($_SESSION['table_name'])) {
    echo "<script>
    alert('Tabel tidak ditemukan. Silakan login ulang.');
    document.location.href = 'logout.php';
    </script>";
    exit;
}

$table_name = $_SESSION['table_name'];

// Ambil data di URL
$id = $_GET["id"];

// Query data berdasarkan ID dari tabel yang sesuai
$pelayanan = query("SELECT * FROM $table_name WHERE id = $id")[0];

// Cek apakah tombol submit ditekan
if (isset($_POST["submit"])) {
    // Tambahkan nama tabel ke data POST untuk diproses di function `ubah`
    $_POST['table_name'] = $table_name;

    // Validasi data apakah sudah diubah
    if (ubah($_POST) > 0) {
        echo "
        <script>
        alert('Data berhasil diubah');
        document.location.href = 'index.php';
        </script>";
    } else {
        echo "
        <script>
        alert('Data gagal diubah');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <div class="container">
    <h1>Ubah Data Pelayanan</h1>

    <form action="" method="post">
        <!-- Input ID (hidden) -->
        <input type="hidden" name="id" id="id" value="<?= $pelayanan['id']; ?>">

        <ul>
            <!-- Jenis Pelayanan -->
            <li>
                <label for="pelayanan">Jenis Pelayanan</label>
                <input type="text" name="pelayanan" id="pelayanan" required placeholder="Masukkan Data Baru" autofocus autocomplete="off"
                       value="<?= $pelayanan['jenis_pelayanan']; ?>">
            </li>
            
            <!-- Tanggal -->
            <li>
                <label for="tanggal">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" required 
                       value="<?= $pelayanan['tanggal']; ?>">
            </li>
            
            <!-- Jam -->
            <li>
                <label for="jam">Jam</label>
                <input type="time" name="jam" id="jam" required 
                       value="<?= $pelayanan['jam']; ?>">
            </li>
            <br>
            <!-- Tombol Submit -->
            <li>
                <button class="" type="submit" name="submit">Ubah Data</button>
                <button class="back"><a href="cari.php">Kembali</a></button>
            </li>
        </ul>
    </form>
    </div>
</body>
</html>
