<?php
session_start();
// Pastikan hanya pasien yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Simulasi data dokter yang dipilih (biasanya dikirim via URL: ?id=1)
$nama_dokter = isset($_GET['dokter']) ? $_GET['dokter'] : "dr. Andi Wijaya";
$spesialis = isset($_GET['spesialis']) ? $_GET['spesialis'] : "Spesialis Anak";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Antrean - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-600 p-6 text-white">
            <h1 class="text-2xl font-bold">Form Pendaftaran Antrean</h1>
            <p class="text-blue-100">Silakan lengkapi data untuk kunjungan Anda</p>
        </div>

        <form action="proses-daftar.php" method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-blue-50 p-4 rounded-lg border border-blue-100">
                <div>
                    <label class="text-xs font-bold text-blue-600 uppercase">Dokter Tujuan</label>
                    <p class="text-lg font-semibold text-gray-800"><?php echo $nama_dokter; ?></p>
                </div>
                <div>
                    <label class="text-xs font-bold text-blue-600 uppercase">Spesialisasi</label>
                    <p class="text-lg font-semibold text-gray-800"><?php echo $spesialis; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Kunjungan</label>
                    <input type="date" name="tgl_kunjungan" required 
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimasi Jam Datang</label>
                    <select name="jam_kunjungan" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option>Sesi Pagi (08:00 - 12:00)</option>
                        <option>Sesi Sore (14:00 - 17:00)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Keluhan Singkat</label>
                <textarea name="keluhan" rows="3" placeholder="Contoh: Demam sudah 2 hari..." 
                          class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4">
                <a href="dashboard-user.php" class="text-gray-500 hover:text-gray-700 font-medium">Batal</a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition transform active:scale-95">
                    Ambil Nomor Antrean
                </button>
            </div>
        </form>
    </div>

</body>
</html>