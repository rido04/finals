<?php 
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

require 'functions.php';

// Pastikan nama tabel tersedia di session
if (!isset($_SESSION['table_name'])) {
    echo "<script>
    alert('Tabel tidak ditemukan. Silakan login ulang.');
    document.location.href = 'logout.php';
    </script>";
    exit;
}

$table_name = $_SESSION['table_name']; // Ambil nama tabel sesuai admin yang login
$id = $_GET["id"]; // Ambil ID dari URL

// Panggil fungsi hapus dengan parameter ID dan tabel
if (hapus($id) > 0) {
    echo "
        <script>
        alert('Data berhasil dihapus');
        document.location.href = 'index.php';
        </script>";
    exit;
} else {
    echo "
        <script>
        alert('Data gagal dihapus');
        document.location.href = 'index.php';
        </script>";
    exit;      
}
?>
