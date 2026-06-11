<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

$user = checkAuth();

if ($user['role'] !== 'user') {
    header("Location: /api/dashboard-admin.php");
    exit();
}

// Ambil data dokter dari URL id
$id_dokter = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil tanggal kunjungan yang dipilih dari strip (dikirim via GET)
$tgl_kunjungan = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');
// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_kunjungan)) {
    $tgl_kunjungan = date('Y-m-d');
}
// Jangan boleh tanggal lampau
if ($tgl_kunjungan < date('Y-m-d')) {
    $tgl_kunjungan = date('Y-m-d');
}

$BULAN_ID = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$HARI_ID  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

$ts_kunjungan  = strtotime($tgl_kunjungan);
$hari_kunjungan = $HARI_ID[(int)date('w', $ts_kunjungan)];
$tgl_tampil    = $hari_kunjungan . ', ' . (int)date('j', $ts_kunjungan) . ' ' . $BULAN_ID[(int)date('n', $ts_kunjungan)] . ' ' . date('Y', $ts_kunjungan);

if ($id_dokter) {
    $q = mysqli_prepare($conn, "SELECT * FROM dokter WHERE id = ?");
    mysqli_stmt_bind_param($q, 'i', $id_dokter);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $dok = mysqli_fetch_assoc($res);
    $nama_dokter = $dok ? $dok['nama_dokter'] : '-';
    $spesialis   = $dok ? $dok['spesialis']   : '-';
    $jam_praktik = $dok ? $dok['jam_praktik'] : '-';
    $hari_praktik = $dok ? $dok['hari']       : '-';
} else {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Hitung nomor antrean yang sudah terisi untuk hari ini
$q_cnt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM antrean WHERE id_dokter = ? AND DATE(tanggal_daftar) = ?");
mysqli_stmt_bind_param($q_cnt, 'is', $id_dokter, $tgl_kunjungan);
mysqli_stmt_execute($q_cnt);
$cnt_res  = mysqli_stmt_get_result($q_cnt);
$cnt_row  = mysqli_fetch_assoc($cnt_res);
$no_antrean_berikutnya = ($cnt_row['total'] ?? 0) + 1;

// Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $nama    = mysqli_real_escape_string($conn, $user['nama']);
    $nik     = mysqli_real_escape_string($conn, $user['nik'] ?? 'N/A');
    $tgl_db  = mysqli_real_escape_string($conn, $_POST['tgl_kunjungan'] ?? $tgl_kunjungan);
    $jam_db  = mysqli_real_escape_string($conn, $_POST['jam_kunjungan'] ?? '');
    $keluhan = mysqli_real_escape_string($conn, $_POST['keluhan'] ?? '');

    // Hitung nomor antrean terbaru (re-query saat submit agar akurat)
    $cek = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM antrean WHERE id_dokter = ? AND DATE(tanggal_daftar) = ?");
    mysqli_stmt_bind_param($cek, 'is', $id_dokter, $tgl_db);
    mysqli_stmt_execute($cek);
    $cek_res = mysqli_stmt_get_result($cek);
    $cek_row = mysqli_fetch_assoc($cek_res);
    $nomor_baru = ($cek_row['total'] ?? 0) + 1;

    $simpan = mysqli_query($conn,
        "INSERT INTO antrean (id_dokter, nama_pasien, nik_pasien, no_antrean, tanggal_daftar)
         VALUES ('$id_dokter', '$nama', '$nik', '$nomor_baru', '$tgl_db $jam_db')"
    );

    if ($simpan) {
        echo "<script>alert('✅ Berhasil! Nomor Antrean Anda: #$nomor_baru\\nTanggal: $tgl_db'); window.location='/api/dashboard-user.php';</script>";
        exit();
    } else {
        $err_msg = "Gagal mengambil antrean.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Antrean - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-start justify-center py-10 px-4">

    <div class="w-full max-w-2xl space-y-4">

        <!-- Back -->
        <a href="/api/dashboard-user.php?tgl=<?php echo $tgl_kunjungan; ?>"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-700 text-sm font-medium transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Jadwal
        </a>

        <!-- Card Utama -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

            <!-- Header Card -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">
                <p class="text-indigo-200 text-sm font-medium uppercase tracking-wider mb-1">Form Antrean</p>
                <h1 class="text-2xl font-black">Konfirmasi Jadwal Kunjungan</h1>
            </div>

            <!-- Info Dokter + Tanggal -->
            <div class="p-6 border-b border-slate-100">
                <div class="flex gap-4 items-start">
                    <!-- Avatar -->
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-black text-2xl flex-shrink-0">
                        <?php echo mb_strtoupper(mb_substr($nama_dokter, 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-black text-slate-800 text-lg"><?php echo htmlspecialchars($nama_dokter); ?></p>
                        <p class="text-blue-600 font-semibold text-sm"><?php echo htmlspecialchars($spesialis); ?></p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full">
                                📅 <?php echo htmlspecialchars($hari_praktik); ?>
                            </span>
                            <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full">
                                🕐 <?php echo htmlspecialchars($jam_praktik); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanggal Kunjungan Terpilih -->
            <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Tanggal Kunjungan</p>
                        <p class="text-lg font-black text-indigo-800 mt-0.5"><?php echo $tgl_tampil; ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estimasi No. Antrean</p>
                        <p class="text-3xl font-black text-indigo-600">#<?php echo $no_antrean_berikutnya; ?></p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" class="p-6 space-y-5">
                <?php if (!empty($err_msg)): ?>
                <div class="bg-red-50 text-red-600 text-sm font-medium px-4 py-3 rounded-xl border border-red-100">
                    ⚠️ <?php echo htmlspecialchars($err_msg); ?>
                </div>
                <?php endif; ?>

                <input type="hidden" name="tgl_kunjungan" value="<?php echo htmlspecialchars($tgl_kunjungan); ?>">

                <!-- Info pasien (read-only) -->
                <div class="bg-slate-50 rounded-xl p-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Data Pasien</p>
                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($user['nama']); ?></p>
                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <!-- Sesi -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estimasi Jam Datang</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="jam_kunjungan" value="08:00:00" class="peer sr-only" checked>
                            <div class="border-2 border-slate-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 rounded-xl p-3.5 text-center transition">
                                <p class="font-bold text-slate-700 peer-checked:text-indigo-700 text-sm">☀️ Sesi Pagi</p>
                                <p class="text-xs text-slate-400 mt-0.5">08:00 – 12:00</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="jam_kunjungan" value="14:00:00" class="peer sr-only">
                            <div class="border-2 border-slate-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 rounded-xl p-3.5 text-center transition">
                                <p class="font-bold text-slate-700 peer-checked:text-indigo-700 text-sm">🌤 Sesi Sore</p>
                                <p class="text-xs text-slate-400 mt-0.5">14:00 – 17:00</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Keluhan -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keluhan Singkat <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <textarea name="keluhan" rows="3"
                              placeholder="Contoh: Demam sejak 2 hari, sakit kepala..."
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none transition text-sm resize-none"></textarea>
                </div>

                <!-- Tombol -->
                <div class="flex gap-3 pt-2">
                    <a href="/api/dashboard-user.php"
                       class="flex-1 text-center bg-slate-100 text-slate-600 py-3.5 rounded-xl font-bold hover:bg-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit" name="konfirmasi"
                            class="flex-2 flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-black shadow-lg shadow-indigo-200 transition active:scale-95">
                        ✅ Ambil Nomor Antrean
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Radio card styling helper — supaya label di dalam radio tetap update
        document.querySelectorAll('input[name="jam_kunjungan"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('input[name="jam_kunjungan"]').forEach(r => {
                    const div = r.closest('label').querySelector('div');
                    if (r.checked) {
                        div.classList.add('border-indigo-500', 'bg-indigo-50');
                        div.classList.remove('border-slate-200');
                        div.querySelector('p').classList.add('text-indigo-700');
                        div.querySelector('p').classList.remove('text-slate-700');
                    } else {
                        div.classList.remove('border-indigo-500', 'bg-indigo-50');
                        div.classList.add('border-slate-200');
                        div.querySelector('p').classList.remove('text-indigo-700');
                        div.querySelector('p').classList.add('text-slate-700');
                    }
                });
            });
        });
    </script>
</body>
</html>