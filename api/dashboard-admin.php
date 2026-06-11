<?php
include __DIR__ . '/auth_check.php';
$user = checkAuth();

if ($user['role'] !== 'admin') {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Handle hapus pasien
if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $hapus_id AND role = 'user'");
    header("Location: /api/dashboard-admin.php");
    exit();
}

// Ambil data pasien
$result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Antrean hari ini
$q_antrean = mysqli_query($conn,
    "SELECT antrean.*, dokter.nama_dokter, dokter.spesialis FROM antrean
     JOIN dokter ON antrean.id_dokter = dokter.id
     WHERE DATE(antrean.tanggal_daftar) = CURDATE()
     ORDER BY antrean.no_antrean ASC"
);

// Statistik
$total_pasien   = mysqli_num_rows($result);
$total_antrean  = $q_antrean ? mysqli_num_rows($q_antrean) : 0;
$q_dok          = mysqli_query($conn, "SELECT COUNT(*) as c FROM dokter WHERE status='Tersedia'");
$total_tersedia = mysqli_fetch_assoc($q_dok)['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-slate-900 text-white p-6 flex flex-col shadow-xl">
        <div class="mb-10">
            <h2 class="text-xl font-bold text-blue-400">Admin Klinik</h2>
            <p class="text-slate-500 text-xs mt-1">Panel Administrasi</p>
        </div>
        <nav class="flex flex-col gap-2 flex-1">
            <a href="/api/dashboard-admin.php" class="flex items-center gap-2 py-2.5 px-4 bg-blue-700 rounded-xl text-white font-semibold text-sm">
                🧑‍⚕️ Data Pasien
            </a>
            <a href="/api/kelola-jadwal.php" class="flex items-center gap-2 py-2.5 px-4 hover:bg-slate-700 rounded-xl transition text-slate-300 font-semibold text-sm">
                📅 Kelola Jadwal Dokter
            </a>
        </nav>
        <div class="border-t border-slate-700 pt-4">
            <p class="text-xs text-slate-500 mb-1">Login sebagai</p>
            <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($user['nama']); ?></p>
            <a href="/api/logout.php" class="mt-3 flex items-center gap-1 text-red-400 text-xs hover:text-red-300 transition">
                ↩ Logout
            </a>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 p-8 overflow-auto">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-black text-slate-800">Dashboard Admin</h1>
            <p class="text-slate-500 text-sm mt-1">Kelola pasien dan pantau antrean klinik</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 text-2xl flex items-center justify-center flex-shrink-0">🧑‍⚕️</div>
                <div>
                    <p class="text-3xl font-black text-slate-800"><?php echo $total_pasien; ?></p>
                    <p class="text-slate-500 text-sm">Total Pasien</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 text-2xl flex items-center justify-center flex-shrink-0">📋</div>
                <div>
                    <p class="text-3xl font-black text-slate-800"><?php echo $total_antrean; ?></p>
                    <p class="text-slate-500 text-sm">Antrean Hari Ini</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 text-2xl flex items-center justify-center flex-shrink-0">✅</div>
                <div>
                    <p class="text-3xl font-black text-slate-800"><?php echo $total_tersedia; ?></p>
                    <p class="text-slate-500 text-sm">Dokter Tersedia</p>
                </div>
            </div>
        </div>

        <!-- ===== TABEL PASIEN ===== -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black text-slate-800">Daftar Pasien Terdaftar</h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200 mb-10">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama & NIK</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">TTL</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Domisili</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Riwayat</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada data pasien.</td>
                    </tr>
                    <?php else: ?>
                    <?php mysqli_data_seek($result, 0); while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama']); ?></p>
                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($row['nik'] ?? '-'); ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <?php echo htmlspecialchars(($row['tempat_lahir'] ?? '') . ', ' . ($row['tgl_lahir'] ?? '')); ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-blue-600">
                            <?php echo !empty($row['provinsi']) ? htmlspecialchars($row['provinsi']) : '-'; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">
                                <?php echo !empty($row['riwayat_penyakit']) ? htmlspecialchars($row['riwayat_penyakit']) : '-'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="/api/edit-pasien.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-800 font-bold mr-3">Edit</a>
                            <a href="/api/dashboard-admin.php?hapus=<?php echo $row['id']; ?>"
                               onclick="return confirm('Yakin hapus data <?php echo htmlspecialchars(addslashes($row['nama'])); ?>?')"
                               class="text-red-500 hover:text-red-700 font-bold">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== ANTREAN HARI INI ===== -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black text-slate-800">Antrean Hari Ini
                <span class="text-sm font-normal text-slate-400 ml-2"><?php echo date('d F Y'); ?></span>
            </h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">No.</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Pasien</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Dokter Tujuan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Spesialis</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Jam Kunjungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!$q_antrean || mysqli_num_rows($q_antrean) === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada antrean hari ini.</td>
                    </tr>
                    <?php else: ?>
                    <?php while($a = mysqli_fetch_assoc($q_antrean)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-black text-blue-600 text-2xl">#<?php echo $a['no_antrean']; ?></td>
                        <td class="px-6 py-4 font-semibold text-slate-800"><?php echo htmlspecialchars($a['nama_pasien']); ?></td>
                        <td class="px-6 py-4 font-medium text-slate-700"><?php echo htmlspecialchars($a['nama_dokter']); ?></td>
                        <td class="px-6 py-4 text-xs text-blue-600 font-semibold"><?php echo htmlspecialchars($a['spesialis']); ?></td>
                        <td class="px-6 py-4 text-sm text-slate-500"><?php echo date('H:i', strtotime($a['tanggal_daftar'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>