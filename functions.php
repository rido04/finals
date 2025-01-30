<?php
// Koneksi ke database
function getConnection() {
    $conn = mysqli_connect("localhost", "root", "12345", "cs");
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    return $conn;
}

$conn = getConnection();

// Fungsi query umum
function query($query) {
    global $conn;

    if (!isset($_SESSION['table_name']) || empty($_SESSION['table_name'])) {
        die("Tabel tidak ditemukan dalam sesi. Pastikan pengguna sudah login.");
    }

    $table_name = mysqli_real_escape_string($conn, $_SESSION['table_name']);
    $query = str_replace("pelayanan", $table_name, $query);

    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Error pada query: " . mysqli_error($conn));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}



// Fungsi tambah data ke tabel yang sesuai
// functions.php
function tambah($data, $tabelDipilih) {
    global $conn;

    // Data yang diterima adalah array checkbox
    $jenisPelayanan = $data["jenis_pelayanan"] ?? [];
    // $tanggal = htmlspecialchars($data["tanggal"]);
    // $jam = htmlspecialchars($data["jam"]);

    if (empty($jenisPelayanan)) {
        return "Pilih minimal satu jenis pelayanan!";
    }

    // Loop dan masukkan data ke tabel
    foreach ($jenisPelayanan as $jenis) {
        $jenis = htmlspecialchars($jenis);
        $query = "INSERT INTO $tabelDipilih (jenis_pelayanan, tanggal, jam) VALUES ('$jenis', CURRENt_DATE, CURRENT_TIME)";

        if (!mysqli_query($conn, $query)) {
            return "Gagal menambahkan data: " . mysqli_error($conn);
        }
    }

    return true;
}

// Fungsi hapus data
function hapus($id) {
    global $conn;

    if (!isset($_SESSION['table_name'])) {
        echo "Session tabel tidak ditemukan.";
        return false;
    }

    $table_name = $_SESSION['table_name']; // Tabel spesifik untuk admin
    $query = "DELETE FROM $table_name WHERE id=$id";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi ubah data
function ubah($data) {
    global $conn;

    if (!isset($_SESSION['table_name'])) {
        echo "Session tabel tidak ditemukan.";
        return false;
    }

    $table_name = $_SESSION['table_name']; // Tabel spesifik untuk admin
    $id = htmlspecialchars($data["id"]);
    $pelayanan = htmlspecialchars($data["pelayanan"]);
    $tanggal = htmlspecialchars($data["tanggal"]);
    $jam = htmlspecialchars($data["jam"]);

    $query = "UPDATE $table_name SET jenis_pelayanan = '$pelayanan', tanggal = '$tanggal', jam = '$jam' WHERE id = $id";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi pencarian data
function cari($pencarian, $tabelDipilih) {
    global $conn;

    $query = "SELECT * FROM $tabelDipilih WHERE 1";

    // Filter tanggal
    if (!empty($pencarian['tanggal_mulai']) && !empty($pencarian['tanggal_selesai'])) {
        $query .= " AND tanggal BETWEEN '" . mysqli_real_escape_string($conn, $pencarian['tanggal_mulai']) . "' AND '" . mysqli_real_escape_string($conn, $pencarian['tanggal_selesai']) . "'";
    }

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}






// Fungsi registrasi user
function registrasi($data) {
    global $conn;

    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);

    $result = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script> alert('Username Sudah Terdaftar') </script>";
        return false;
    }

    if ($password !== $password2) {
        echo "<script> alert('Password Tidak Sesuai') </script>";
        return false;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}

// Fungsi login user
function login($data) {
    global $conn;

    $username = htmlspecialchars($data['username']);
    $password = htmlspecialchars($data['password']);

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['table_name'] = null; // Reset session tabel untuk landing page

        return true;
    } else {
        echo "<script>alert('Username atau Password salah!')</script>";
        return false;
    }
}



// Pastikan koneksi ke database sudah ada
function getRekapDataExcel($tables, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $jenisPelayanan) {
    global $conn;
    $rekapData = [];
    
    foreach ($tables as $table) {
        // Mulai membangun query
        $query = "SELECT *, '$table' AS table_name FROM $table WHERE 1=1";
        
        // Menambahkan kondisi untuk jenis pelayanan jika ada
        if ($jenisPelayanan) {
            $query .= " AND jenis_pelayanan LIKE '%" . mysqli_real_escape_string($conn, $jenisPelayanan) . "%'";
        }

        // Menambahkan kondisi untuk tanggal jika ada
        if ($tanggalMulai && $tanggalSelesai) {
            $query .= " AND tanggal BETWEEN '$tanggalMulai' AND '$tanggalSelesai'";
        }

        // Menambahkan kondisi untuk jam jika ada
        if ($jamMulai && $jamSelesai) {
            $query .= " AND jam BETWEEN '$jamMulai' AND '$jamSelesai'";
        }
        

        // Eksekusi query
        $result = mysqli_query($conn, $query);
        
        // Cek jika query berhasil
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rekapData[] = $row;
            }
        } else {
            // Menangani kesalahan jika query gagal
            die("Query failed: " . mysqli_error($conn));
        }
    }

    return $rekapData;
}



function getLandingPageData($username, $category) {
    $conn = getConnection();

    // Format username menjadi format tabel (tanpa spasi, huruf kecil)
    $username_clean = str_replace(' ', '_', strtolower($username));
    $table_name = "{$category}_{$username_clean}";

    // Periksa keberadaan tabel di database
    $query_check_table = "SHOW TABLES LIKE '$table_name'";
    $result = mysqli_query($conn, $query_check_table);

    if (mysqli_num_rows($result) === 0)
     {
        return [
            'error' => true,
            'message' => "Tabel '$table_name' tidak ditemukan di database."
        ];
    }

    // Query untuk mendapatkan data dari tabel
    $query = "SELECT * FROM $table_name";
    $result_data = mysqli_query($conn, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result_data)) {
        $data[] = $row;
    }

    return [
        'error' => false,
        'data' => $data,
        'table_name' => $table_name
    ];
}
