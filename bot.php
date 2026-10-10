<?php
/**
 * bot.php - Bot Server "Aluh" (webhook Telegram)
 * Alur: Telegram Client -> Telegram Server -> bot.php -> MySQL -> Telegram Server -> Telegram Client
 *
 * Pasang webhook (HTTPS wajib):
 *   https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://domain-anda.com/si-aluh/bot.php
 */
require __DIR__ . '/koneksi.php';

function kirim($chat_id, $teks) {
    $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => ['chat_id' => $chat_id, 'text' => $teks],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$update = json_decode(file_get_contents('php://input'), true);
if (!isset($update['message']['text'])) exit;

$chat_id = $update['message']['chat']['id'];
$teks    = trim($update['message']['text']);
$nama    = trim(($update['message']['from']['first_name'] ?? '') . ' ' . ($update['message']['from']['last_name'] ?? ''));

if (!$db) { kirim($chat_id, 'Maaf, database sedang tidak dapat diakses.'); exit; }

if (strpos($teks, '/start') === 0 || strpos($teks, '/bantuan') === 0) {
    kirim($chat_id,
        "Halo $nama, saya Aluh, asisten administrasi PDE.\n\n" .
        "Perintah:\n" .
        "/lapor Jenis | Isi laporan\n   (Jenis: Harian, Mingguan, atau Insidentil)\n" .
        "/daftar - 5 laporan terakhir Anda\n" .
        "/pending - laporan Anda yang belum selesai\n" .
        "/selesai ID - tandai laporan selesai");

} elseif (strpos($teks, '/lapor') === 0) {
    $isi_cmd = trim(substr($teks, 6));
    $bagian  = array_map('trim', explode('|', $isi_cmd, 2));
    $jenis   = ucfirst(strtolower($bagian[0] ?? ''));
    $isi     = $bagian[1] ?? '';
    if (!in_array($jenis, ['Harian', 'Mingguan', 'Insidentil'], true) || $isi === '') {
        kirim($chat_id, "Format: /lapor Harian | Rekap data server pagi");
    } else {
        $tgl = date('Y-m-d');
        $st = $db->prepare('INSERT INTO laporan_pde (nama_karyawan, tanggal, jenis_laporan, isi_laporan) VALUES (?,?,?,?)');
        $st->bind_param('ssss', $nama, $tgl, $jenis, $isi);
        $st->execute();
        kirim($chat_id, "Laporan $jenis tercatat dengan nomor #" . $db->insert_id . " (status Pending).");
    }

} elseif (strpos($teks, '/daftar') === 0 || strpos($teks, '/pending') === 0) {
    $hanya_pending = strpos($teks, '/pending') === 0;
    $sql = 'SELECT id, tanggal, jenis_laporan, status FROM laporan_pde WHERE nama_karyawan=?'
         . ($hanya_pending ? " AND status='Pending'" : '') . ' ORDER BY id DESC LIMIT 5';
    $st = $db->prepare($sql);
    $st->bind_param('s', $nama);
    $st->execute();
    $hasil = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    if (!$hasil) { kirim($chat_id, 'Tidak ada laporan.'); exit; }
    $out = '';
    foreach ($hasil as $r) $out .= "#{$r['id']} | {$r['tanggal']} | {$r['jenis_laporan']} | {$r['status']}\n";
    kirim($chat_id, $out);

} elseif (strpos($teks, '/selesai') === 0) {
    $id = (int)trim(substr($teks, 8));
    $st = $db->prepare("UPDATE laporan_pde SET status='Selesai' WHERE id=? AND nama_karyawan=?");
    $st->bind_param('is', $id, $nama);
    $st->execute();
    kirim($chat_id, $st->affected_rows ? "Laporan #$id ditandai selesai." : "Laporan #$id tidak ditemukan atau bukan milik Anda.");

} else {
    kirim($chat_id, 'Perintah tidak dikenali. Ketik /bantuan untuk melihat daftar perintah.');
}
