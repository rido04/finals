<?php
session_start();
require 'functions.php';

// Cek apakah form login telah disubmit
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk memeriksa username di tabel users
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    // Cek apakah username ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $username;

            // Redirect ke landing page sesuai username
            if ($username === 'admin 1') {
                header("Location: admin1_landing.php");
            } elseif ($username === 'admin 2') {
                header("Location: admin2_landing.php");
            } elseif ($username === 'admin 3') {
                header("Location: admin3_landing.php");
            } elseif ($username === 'admin 4') {
                header("Location: admin4_landing.php");
            }elseif ($username === 'cs 1') {
                header("Location: cs1_landing.php");
            }elseif ($username === 'cs 2') {
                header("Location: cs2_landing.php");
            }elseif ($username === 'cs 3') {
                header("Location: cs4_landing.php");
            }elseif ($username === 'cs 4') {
                header("Location: cs4_landing.php");
            } elseif ($username === 'operator') {
                header("Location: admin1_landing.php");
            } else {
                $error = "Akses tidak valid!";
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak terdaftar!";
    }
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
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
    <form id="login" action="" method="post">
    <h1>Login</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error; ?></p>
    <?php endif; ?>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autocomplete="off" autofocus>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <br>
        <button id="btnlgn" type="submit" name="login">Login</button>
        <p>Summarecon Serpong</p>
        <p>All Right Reserved</p>
        <br>
        <p>Created By: Ridho Febrian</p>
         <p>Contact Creator :  <a id="normal" href="https://api.whatsapp.com/send/?phone=6289643119513&text&type=phone_number&app_absent=0" target="_blank" > 089643119513(WhatsApp)</a></p>
    </form>
</body>
</html>
