<?php
error_reporting(0);
include 'koneksi.php';

// Token Bot Telegram Baru Anda
$bot_token = "8671141642:AAHFjjknh8oJfz1bLbE7zujq4PYun-V6Q1c";
$website   = "https://api.telegram.org/bot" . $bot_token;

// Menerima Webhook dari Telegram
$content = file_get_contents("php://input");
$update  = json_decode($content, TRUE);

if (isset($update["message"])) {
    $chat_id    = $update["message"]["chat"]["id"];
    $text       = trim($update["message"]["text"]);
    $first_name = isset($update["message"]["from"]["first_name"]) ? $update["message"]["from"]["first_name"] : "User";

    if ($text == "/start") {
        $reply = "Halo " . $first_name . "! Selamat datang di Bot SI ALUH (Sistem Administrasi Laporan PDE). Bot berhasil aktif di Render!";
    } else {
        $reply = "Pesan Anda sudah kami terima: " . $text;
    }

    // Mengirim Balasan ke Telegram via cURL
    $send_url = $website . "/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $send_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id' => $chat_id,
        'text'    => $reply
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
}
?>
