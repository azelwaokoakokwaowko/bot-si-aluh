<?php
$host     = "sql103.infinityfree.com"; // Hostname MySQL InfinityFree
$user     = "if0_43135972";            // Username Database
$pass     = "PASSWORD_DB_ANDA";        // Password Database InfinityFree Anda
$dbname   = "if0_43135972_si_aluh_db";  // Nama Database

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>
