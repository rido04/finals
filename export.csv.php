<?php
require 'functions.php';

// Konfigurasi database
$host = 'localhost';
$user = 'root';
$password = '12345';
$dbname = 'cs';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Konfigurasi header untuk file CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=rekap_data.csv');

// Membuka output untuk file CSV
$output = fopen('php://output', 'w');

// Menulis BOM UTF-8 agar kompatibel dengan Excel
fwrite($output, "\xEF\xBB\xBF");

// Menulis header CSV
fputcsv($output, ['Admin', 'Pilihan', 'Jenis Pelayanan', 'Tanggal', 'Jam', 'Total'], ';');

// Struktur data
$admins = ['admin1', 'admin2', 'admin3', 'admin4'];
$choices = ['call_center', 'whatsapp', 'customer_service', 'home_apps'];

// Loop melalui setiap admin dan pilihan
foreach ($admins as $admin) {
    $subtotal = 0; // Variabel untuk menghitung total layanan per admin

    foreach ($choices as $choice) {
        $tableName = "{$choice}_{$admin}";

        // Validasi nama tabel
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            continue;
        }

        // Ambil data dari tabel
        $query = "SELECT jenis_pelayanan, tanggal, jam FROM `$tableName`";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Menulis data ke file CSV
                fputcsv($output, [
                    $admin,
                    ucfirst(str_replace('_', ' ', $choice)),
                    $row['jenis_pelayanan'],
                    $row['tanggal'],
                    $row['jam'],
                    1 // Anggap setiap baris mewakili satu layanan
                ], ';');
                $subtotal++;
            }
        }
    }

    // Tulis subtotal untuk admin saat ini
    fputcsv($output, [
        'Subtotal',
        '',
        '',
        '',
        '',
        $subtotal
    ], ';');
}

// Menutup koneksi dan output CSV
fclose($output);
$conn->close();
exit;
?>
