<?php
/**
 * koneksi.php - Koneksi database SI ALUH
 * Sesuaikan kredensial dengan server Anda.
 * Error koneksi tidak menghentikan halaman; pesannya disimpan di $db_error.
 */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'si_aluh_db';

// Token bot Telegram (dipakai bot.php & pengingat.php)
define('BOT_TOKEN', 'ISI_TOKEN_DARI_BOTFATHER');

$db = null;
$db_error = null;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $db = null;
    $db_error = 'Tidak dapat terhubung ke database. Periksa pengaturan di koneksi.php dan pastikan MySQL berjalan.';
    error_log('SI ALUH DB: ' . $e->getMessage());
}
