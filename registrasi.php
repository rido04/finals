<?php 
require 'functions.php';
// $conn = getConnection();
if (isset($_POST["register"])){

    if (registrasi($_POST)>0)
    echo "<script>
        alert ('User Baru Berhasil ditambahkan')
        </script>";
} else {
    echo mysqli_error($conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        label{
            display: block;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Registrasi</h1>

    <form action="" method="post">
        <ul>
            <li>
                <label for="username">username</label>
                <input type="text" name="username" id="username" autofocus autocomplete="off"
                 placeholder="Msukkan username">
            </li>
            <li>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autocomplete="off" placeholder="Masukkan Password">
            </li>
            <li>
                <label for="password2">Konfirmasi Password</label>
                <input type="password" name="password2" id="password2" autocomplete="off" placeholder="Konfirmasi Password">
            </li>
            <li>
                <button type="submit" name="register">Register</button>
            </li>
        </ul>
    </form>
    </div>
</body>
</html>