<?php
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require 'functions.php';

function rekapData($tables, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $jenisPelayanan, $awalData, $jumlahDataPerHalaman) {
    global $conn; // Pastikan $conn didefinisikan
    $rekapData = [];

    foreach ($tables as $table) {
        $query = "SELECT *, '$table' AS table_name FROM $table WHERE 1=1";

        if ($jenisPelayanan) {
            $query .= " AND jenis_pelayanan LIKE '%" . mysqli_real_escape_string($conn, $jenisPelayanan) . "%'";
        }

        if ($tanggalMulai && $tanggalSelesai) {
            $query .= " AND tanggal BETWEEN '$tanggalMulai' AND '$tanggalSelesai'";
        }

        if ($jamMulai && $jamSelesai) {
            $query .= " AND jam BETWEEN '$jamMulai' AND '$jamSelesai'";
        }

        $query .= " LIMIT $awalData, $jumlahDataPerHalaman";

        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $rekapData[] = $row;
        }
    }

    return $rekapData;
}

// Ekspor ke CSV
if (isset($_POST["export_csv"])) {
    $adminDipilih = $_POST["admin"] ?? null;
    $tanggalMulai = $_POST["tanggal_mulai"] ?? null;
    $tanggalSelesai = $_POST["tanggal_selesai"] ?? null;
    $jamMulai = $_POST["jam_mulai"] ?? null;
    $jamSelesai = $_POST["jam_selesai"] ?? null;
    $jenisPelayanan = $_POST["jenis_pelayanan"] ?? null;

    if ($adminDipilih) {
        $tables = [
            "call_center_$adminDipilih",
            "whatsapp_$adminDipilih",
            "customer_service_$adminDipilih",
            "home_apps_$adminDipilih"
        ];

        $data = rekapData($tables, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $jenisPelayanan, 0, PHP_INT_MAX);

        // Header file CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="rekap_data_' . date('Y-m-d_H-i-s') . '.csv"');

        // Output CSV
        $output = fopen('php://output', 'w');

        // Header kolom
        fputcsv($output, ['No', 'Tabel', 'Jenis Pelayanan', 'Tanggal', 'Jam']);

        // Isi data
        $no = 1;
        foreach ($data as $record) {
            fputcsv($output, [
                $no++,
                $record['table_name'],
                $record['jenis_pelayanan'],
                $record['tanggal'],
                $record['jam'],
            ]);
        }

        fclose($output);
        exit;
    } else {
        $error = "Pilih admin yang datanya ingin direkap.";
    }
}

// Konfigurasi pagination
$jumlahDataPerHalaman = 30;
$halamanAktif = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
$awalData = ($halamanAktif - 1) * $jumlahDataPerHalaman;

// Inisialisasi variabel
$rekapData = [];
$totalData = 0;
$tanggalMulai = $tanggalSelesai = $jamMulai = $jamSelesai = $jenisPelayanan = null;
$adminDipilih = null;

if (isset($_POST["rekap"])) {
    $adminDipilih = $_POST["admins"] ?? [];
    $pilihanDipilih = $_POST["pilihan"] ?? [];
    $tanggalMulai = $_POST["tanggal_mulai"] ?? null;
    $tanggalSelesai = $_POST["tanggal_selesai"] ?? null;
    $jamMulai = $_POST["jam_mulai"] ?? null;
    $jamSelesai = $_POST["jam_selesai"] ?? null;
    $jenisPelayanan = $_POST["jenis_pelayanan"] ?? null;

    if (!empty($adminDipilih) && !empty($pilihanDipilih)) {
        $tables = [];
        foreach ($adminDipilih as $admin) {
            foreach ($pilihanDipilih as $pilihan) {
                $tables[] = "{$pilihan}_{$admin}";
            }
        }

        foreach ($tables as $table) {
            $queryTotal = "SELECT COUNT(*) AS total FROM $table WHERE 1=1";

            if ($jenisPelayanan) {
                $queryTotal .= " AND jenis_pelayanan LIKE '%" . mysqli_real_escape_string($conn, $jenisPelayanan) . "%'";
            }

            if ($tanggalMulai && $tanggalSelesai) {
                $queryTotal .= " AND tanggal BETWEEN '$tanggalMulai' AND '$tanggalSelesai'";
            }

            if ($jamMulai && $jamSelesai) {
                $queryTotal .= " AND jam BETWEEN '$jamMulai' AND '$jamSelesai'";
            }

            $resultTotal = mysqli_query($conn, $queryTotal);
            $dataTotal = mysqli_fetch_assoc($resultTotal);
            $totalData += $dataTotal['total'];
        }

        $rekapData = rekapData($tables, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $jenisPelayanan, $awalData, $jumlahDataPerHalaman);
    } else {
        $error = "Pilih minimal satu admin dan satu jenis data yang ingin direkap!.";
    }
}


$jumlahHalaman = ceil($totalData / $jumlahDataPerHalaman);

?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Data</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <br>
 <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>
    <div class="container">
        <h1>Rekap Data</h1>
        <br>
        <form action="" method="post">
        <h3>Pilih Admin :</h3>
        <label>
            <input type="checkbox" name="admins[]" value="admin1" <?= isset($adminDipilih) && in_array("admin1", $adminDipilih) ? "checked" : ""; ?>>
            Admin 1
        </label>
        <br>
        <label>
            <input type="checkbox" name="admins[]" value="admin2" <?= isset($adminDipilih) && in_array("admin2", $adminDipilih) ? "checked" : ""; ?>>
            Admin 2
        </label>
        <br>
        <label>
            <input type="checkbox" name="admins[]" value="admin3" <?= isset($adminDipilih) && in_array("admin3", $adminDipilih) ? "checked" : ""; ?>>
            Admin 3
        </label>
        <br>
        <label>
            <input type="checkbox" name="admins[]" value="admin4" <?= isset($adminDipilih) && in_array("admin4", $adminDipilih) ? "checked" : ""; ?>>
            Admin 4
        </label>
        <br><br>

        <h3>Data yang ingin di Rekap :</h3>
        <label>
            <input type="checkbox" name="pilihan[]" value="call_center" <?= isset($pilihanDipilih) && in_array("call_center", $pilihanDipilih) ? "checked" : ""; ?>>
            Call Center
        </label>
        <br>
        <label>
            <input type="checkbox" name="pilihan[]" value="whatsapp" <?= isset($pilihanDipilih) && in_array("whatsapp", $pilihanDipilih) ? "checked" : ""; ?>>
            WhatsApp
        </label>
        <br>
        <label>
            <input type="checkbox" name="pilihan[]" value="customer_service" <?= isset($pilihanDipilih) && in_array("customer_service", $pilihanDipilih) ? "checked" : ""; ?>>
            Customer Service
        </label>
        <br>
        <label>
            <input type="checkbox" name="pilihan[]" value="home_apps" <?= isset($pilihanDipilih) && in_array("home_apps", $pilihanDipilih) ? "checked" : ""; ?>>
            Home Apps
        </label>
            <br><br>
            <hr>
            <label for="jenis_pelayanan">Jenis Pelayanan: (Opsional)</label>
            <input type="text" name="jenis_pelayanan" id="jenis_pelayanan" placeholder="Masukkan Jenis Pelayanan yang ingin direkap" autocomplete="off">
            <br><br>
            <label for="tanggal_mulai">Dari Tanggal: (Opsional)</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?= $tanggalMulai; ?>">
            <label for="tanggal_selesai">Sampai Tanggal (Opsional)</label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?= $tanggalSelesai; ?>">
            <br><br>
            <label for="jam_mulai">Dari Jam: (Opsional)</label>
            <input type="time" name="jam_mulai" id="jam_mulai" value="<?= $jamMulai; ?>">
            <label for="jam_selesai">Sampai Jam (Opsional)</label>
            <input type="time" name="jam_selesai" id="jam_selesai" value="<?= $jamSelesai; ?>">
            <br><br>
            <button type="submit" name="rekap">Rekap</button>
            <button class="oren"><a href="index.php">Kembali</a></button>
        </form>
        <br>
       
        </form>
        <?php if ($totalData > 0): ?>
        <h2>Total Data Rekap</h2>
        <table border="1" cellpadding="10" cellspacing="1">
        <tr>
            <th>List Admin</th>
            <th>Total Data</th>
        </tr>
        <?php 
        $totalKeseluruhan = 0; // Variabel untuk total keseluruhan
        foreach ($tables as $table): ?>
            <?php
            $queryTotalPerTable = "SELECT COUNT(*) AS total FROM $table WHERE 1=1";
            if ($jenisPelayanan) {
                $queryTotalPerTable .= " AND jenis_pelayanan LIKE '%" . mysqli_real_escape_string($conn, $jenisPelayanan) . "%'";
            }
            if ($tanggalMulai && $tanggalSelesai) {
                $queryTotalPerTable .= " AND tanggal BETWEEN '$tanggalMulai' AND '$tanggalSelesai'";
            }
            if ($jamMulai && $jamSelesai) {
                $queryTotalPerTable .= " AND jam BETWEEN '$jamMulai' AND '$jamSelesai'";
            }
            $resultTotalPerTable = mysqli_query($conn, $queryTotalPerTable);
            $dataTotalPerTable = mysqli_fetch_assoc($resultTotalPerTable);
            $totalKeseluruhan += $dataTotalPerTable['total']; // Tambahkan total tabel ke keseluruhan
            ?>
            <tr>
                <td><?= htmlspecialchars($table); ?></td>
                <td><strong><?= $dataTotalPerTable['total']; ?></strong></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Total Keseluruhan</strong></td>
            <td><strong><?= $totalKeseluruhan; ?></strong></td>
        </tr>
        <form class="ekspor" action="export.csv.php" method="post">
        <input type="hidden" name="admin" value="<?= $adminDipilih; ?>">
        <input type="hidden" name="tanggal_mulai" value="<?= $tanggalMulai; ?>">
        <input type="hidden" name="tanggal_selesai" value="<?= $tanggalSelesai; ?>">
        <input type="hidden" name="jam_mulai" value="<?= $jamMulai; ?>">
        <input type="hidden" name="jam_selesai" value="<?= $jamSelesai; ?>">
        <input type="hidden" name="jenis_pelayanan" value="<?= $jenisPelayanan; ?>">
        <button type="submit" name="export_csv">Ekspor Ke Excel >>></button>
    </form>
        </table>
        
        <?php endif; ?>
        
    <!-- Hasil Rekap Data -->
<?php if (!empty($rekapData)): ?>
    <h2>Hasil Rekap Data</h2>
    <div style="overflow-x: auto; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
        <table border="1" cellpadding="10" cellspacing="1" style="min-width: 1500px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>List Admin</th>
                    <th>Komplain</th>
                    <th>Informasi</th>
                    <th>Perijinan</th>
                    <th>Renovasi & Canopy</th>
                    <th>Insentive Huni & Voucher</th>
                    <th>Member Club</th>
                    <th>Access Card</th>
                    <th>SSC</th>
                    <th>Kwitansi IPL</th>
                    <th>Kwitansi PAM</th>
                    <th>PBB</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $processedTables = [];
                $i = 1;

                // Total keseluruhan
                $grandTotal = [
                    "Komplain" => 0,
                    "Informasi" => 0,
                    "Perijinan" => 0,
                    "Renovasi & Canopy" => 0,
                    "Insentive Huni & Voucher" => 0,
                    "Member Club" => 0,
                    "Access Card" => 0,
                    "SSC" => 0,
                    "Kwitansi IPL" => 0,
                    "Kwitansi PAM" => 0,
                    "PBB" => 0,
                ];

                foreach ($rekapData as $data): 
                    if (in_array($data["table_name"], $processedTables)) {
                        continue;
                    }
                    $processedTables[] = $data["table_name"];

                    // Hitung total untuk tabel ini
                    $totalKomplain = $totalInformasi = $totalPerijinan = $totalRenovasi = 0;
                    $totalInsentive = $totalMemberClub = $totalAccessCard = 0;
                    $totalSSC = $totalKwitansiIPL = $totalKwitansiPAM = $totalPBB = 0;

                    foreach ($rekapData as $item) {
                        if ($item["table_name"] == $data["table_name"]) {
                            switch ($item["jenis_pelayanan"]) {
                                case "Komplain": $totalKomplain++; break;
                                case "Informasi": $totalInformasi++; break;
                                case "Perijinan": $totalPerijinan++; break;
                                case "Renovasi & Canopy": $totalRenovasi++; break;
                                case "Insentive Huni & Voucher": $totalInsentive++; break;
                                case "Member Club": $totalMemberClub++; break;
                                case "Access Card": $totalAccessCard++; break;
                                case "SSC": $totalSSC++; break;
                                case "Kwitansi IPL": $totalKwitansiIPL++; break;
                                case "Kwitansi PAM": $totalKwitansiPAM++; break;
                                case "PBB": $totalPBB++; break;
                            }
                        }
                    }

                    // Tambahkan ke total keseluruhan
                    $grandTotal["Komplain"] += $totalKomplain;
                    $grandTotal["Informasi"] += $totalInformasi;
                    $grandTotal["Perijinan"] += $totalPerijinan;
                    $grandTotal["Renovasi & Canopy"] += $totalRenovasi;
                    $grandTotal["Insentive Huni & Voucher"] += $totalInsentive;
                    $grandTotal["Member Club"] += $totalMemberClub;
                    $grandTotal["Access Card"] += $totalAccessCard;
                    $grandTotal["SSC"] += $totalSSC;
                    $grandTotal["Kwitansi IPL"] += $totalKwitansiIPL;
                    $grandTotal["Kwitansi PAM"] += $totalKwitansiPAM;
                    $grandTotal["PBB"] += $totalPBB;
                ?>
                    <tr>
                        <td><?= $i; ?></td>
                        <td><?= htmlspecialchars($data["table_name"]); ?></td>
                        <td><?= $totalKomplain; ?></td>
                        <td><?= $totalInformasi; ?></td>
                        <td><?= $totalPerijinan; ?></td>
                        <td><?= $totalRenovasi; ?></td>
                        <td><?= $totalInsentive; ?></td>
                        <td><?= $totalMemberClub; ?></td>
                        <td><?= $totalAccessCard; ?></td>
                        <td><?= $totalSSC; ?></td>
                        <td><?= $totalKwitansiIPL; ?></td>
                        <td><?= $totalKwitansiPAM; ?></td>
                        <td><?= $totalPBB; ?></td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td colspan="2" style="text-align: center;">Jumlah Total</td>
                    <td><?= $grandTotal["Komplain"]; ?></td>
                    <td><?= $grandTotal["Informasi"]; ?></td>
                    <td><?= $grandTotal["Perijinan"]; ?></td>
                    <td><?= $grandTotal["Renovasi & Canopy"]; ?></td>
                    <td><?= $grandTotal["Insentive Huni & Voucher"]; ?></td>
                    <td><?= $grandTotal["Member Club"]; ?></td>
                    <td><?= $grandTotal["Access Card"]; ?></td>
                    <td><?= $grandTotal["SSC"]; ?></td>
                    <td><?= $grandTotal["Kwitansi IPL"]; ?></td>
                    <td><?= $grandTotal["Kwitansi PAM"]; ?></td>
                    <td><?= $grandTotal["PBB"]; ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>

    </div>
</body>
</html>
