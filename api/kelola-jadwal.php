<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

$user = checkAuth();

// Cek Admin
if ($user['role'] !== 'admin') {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Logika Tambah & Update — gunakan prepared statement
if (isset($_POST['simpan'])) {
    $nama      = trim($_POST['nama'] ?? '');
    $spesialis = trim($_POST['spesialis'] ?? '');
    $status    = trim($_POST['status'] ?? 'Tersedia');

    // Hari: bisa multi-hari (checkbox), gabung dengan koma
    $hari_arr  = isset($_POST['hari']) ? (array)$_POST['hari'] : [];
    $hari      = implode(', ', array_map('trim', $hari_arr));

    // Jam: dari input jam_mulai & jam_selesai
    $jam_mulai   = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '');
    $jam         = $jam_mulai . ' - ' . $jam_selesai;

    if ($_POST['id_dokter'] == '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO dokter (nama_dokter, spesialis, hari, jam_praktik, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $nama, $spesialis, $hari, $jam, $status);
    } else {
        $id = (int)$_POST['id_dokter'];
        $stmt = mysqli_prepare($conn,
            "UPDATE dokter SET nama_dokter=?, spesialis=?, hari=?, jam_praktik=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $spesialis, $hari, $jam, $status, $id);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: /api/kelola-jadwal.php");
    exit();
}

// Logika Hapus
if (isset($_GET['hapus'])) {
    $id   = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM dokter WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: /api/kelola-jadwal.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM dokter ORDER BY nama_dokter ASC");

$HARI_LIST = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hari-btn input:checked + span {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- Layout -->
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-slate-900 text-white p-6 flex flex-col">
            <h2 class="text-xl font-bold mb-8 text-blue-400">Admin Klinik</h2>
            <nav class="flex flex-col gap-2 flex-1">
                <a href="/api/dashboard-admin.php" class="block py-2.5 px-4 hover:bg-slate-700 rounded transition text-slate-300 font-semibold">Data Pasien</a>
                <a href="/api/kelola-jadwal.php"   class="block py-2.5 px-4 bg-blue-700 rounded text-white font-semibold">Kelola Jadwal Dokter</a>
            </nav>
            <a href="/api/logout.php" class="block py-2.5 px-4 text-red-400 hover:bg-red-900/20 rounded transition text-sm">Logout</a>
        </aside>

        <!-- Main -->
        <main class="flex-1 p-8 overflow-auto">
            <div class="max-w-5xl mx-auto">

                <!-- Page Title -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800">Kelola Jadwal Dokter</h1>
                        <p class="text-slate-500 text-sm mt-1">Tambah, ubah, atau hapus jadwal praktik dokter</p>
                    </div>
                    <a href="/api/dashboard-admin.php"
                       class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 transition">
                        ← Kembali ke Dashboard
                    </a>
                </div>

                <!-- ===== FORM ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
                    <h2 class="text-lg font-bold text-slate-700 mb-5" id="form-title">➕ Tambah Jadwal Dokter</h2>

                    <form method="POST" id="form-jadwal" class="space-y-5">
                        <input type="hidden" name="id_dokter" id="id_dokter">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama Dokter -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Dokter</label>
                                <input type="text" name="nama" id="nama" placeholder="dr. Ahmad Wijaya, Sp.PD"
                                       required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-400 outline-none transition text-sm">
                            </div>

                            <!-- Spesialis -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Spesialis</label>
                                <input type="text" name="spesialis" id="spesialis" placeholder="Penyakit Dalam"
                                       required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-400 outline-none transition text-sm">
                            </div>
                        </div>

                        <!-- Hari Praktik (Checkbox visual) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Hari Praktik</label>
                            <div class="flex flex-wrap gap-2" id="hari-group">
                                <?php foreach ($HARI_LIST as $h): ?>
                                <label class="hari-btn cursor-pointer">
                                    <input type="checkbox" name="hari[]" value="<?php echo $h; ?>" class="sr-only">
                                    <span class="block border-2 border-slate-200 rounded-xl px-3 py-1.5 text-sm font-semibold text-slate-600 transition select-none">
                                        <?php echo $h; ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Pilih satu atau beberapa hari sekaligus</p>
                        </div>

                        <!-- Jam Praktik -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Jam Praktik</label>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="text-xs text-slate-400 mb-1 block">Mulai</label>
                                    <input type="time" name="jam_mulai" id="jam_mulai" value="08:00"
                                           required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-400 outline-none transition text-sm">
                                </div>
                                <div class="pt-5 text-slate-400 font-bold">—</div>
                                <div class="flex-1">
                                    <label class="text-xs text-slate-400 mb-1 block">Selesai</label>
                                    <input type="time" name="jam_selesai" id="jam_selesai" value="12:00"
                                           required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-400 outline-none transition text-sm">
                                </div>
                                <div class="flex-1 pt-5">
                                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2.5 text-indigo-700 text-sm font-bold text-center" id="preview-jam">
                                        08:00 - 12:00
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                            <div class="flex gap-3">
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="status" value="Tersedia" id="status_tersedia" class="peer sr-only" checked>
                                    <div class="border-2 border-slate-200 peer-checked:border-green-500 peer-checked:bg-green-50 rounded-xl p-3 text-center transition">
                                        <span class="block text-sm font-bold text-slate-600 peer-checked:text-green-700">✅ Tersedia</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer flex-1">
                                    <input type="radio" name="status" value="Penuh" id="status_penuh" class="peer sr-only">
                                    <div class="border-2 border-slate-200 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-xl p-3 text-center transition">
                                        <span class="block text-sm font-bold text-slate-600 peer-checked:text-red-700">❌ Penuh</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-2">
                            <button type="button" id="btn-batal"
                                    onclick="resetForm()"
                                    class="hidden px-6 py-2.5 border-2 border-slate-200 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition text-sm">
                                Batal Edit
                            </button>
                            <button type="submit" name="simpan" id="btn-simpan"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-bold shadow-sm transition text-sm">
                                Simpan Jadwal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ===== TABEL DOKTER ===== -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-bold text-slate-700">Daftar Jadwal Dokter</h2>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Dokter</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Hari</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Jam Praktik</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">Belum ada data dokter.</td>
                            </tr>
                            <?php else: ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-slate-50 transition" data-id="<?php echo $row['id']; ?>">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($row['nama_dokter']); ?></p>
                                    <p class="text-xs text-blue-600"><?php echo htmlspecialchars($row['spesialis']); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach (explode(',', $row['hari']) as $h): ?>
                                        <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-2 py-0.5 rounded-full"><?php echo trim(htmlspecialchars($h)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    🕐 <?php echo htmlspecialchars($row['jam_praktik']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full <?php echo ($row['status'] === 'Tersedia') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick='editDokter(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES); ?>)'
                                            class="text-indigo-600 hover:text-indigo-800 font-bold text-sm mr-3">Edit</button>
                                    <a href="?hapus=<?php echo $row['id']; ?>"
                                       onclick="return confirm('Yakin hapus jadwal <?php echo htmlspecialchars(addslashes($row['nama_dokter'])); ?>?')"
                                       class="text-red-500 hover:text-red-700 font-bold text-sm">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script>
    // Preview jam realtime
    function updatePreviewJam() {
        const m = document.getElementById('jam_mulai').value;
        const s = document.getElementById('jam_selesai').value;
        if (m && s) {
            document.getElementById('preview-jam').textContent = m + ' - ' + s;
        }
    }
    document.getElementById('jam_mulai').addEventListener('change', updatePreviewJam);
    document.getElementById('jam_selesai').addEventListener('change', updatePreviewJam);

    function editDokter(data) {
        // Set form title
        document.getElementById('form-title').textContent = '✏️ Edit Jadwal: ' + data.nama_dokter;
        document.getElementById('btn-batal').classList.remove('hidden');
        document.getElementById('btn-simpan').textContent = 'Update Jadwal';
        document.getElementById('btn-simpan').classList.remove('bg-indigo-600','hover:bg-indigo-700');
        document.getElementById('btn-simpan').classList.add('bg-orange-500','hover:bg-orange-600');

        // Isi field
        document.getElementById('id_dokter').value = data.id;
        document.getElementById('nama').value = data.nama_dokter;
        document.getElementById('spesialis').value = data.spesialis;

        // Hari: centang checkbox yang sesuai
        const hariDokter = (data.hari || '').split(',').map(h => h.trim());
        document.querySelectorAll('input[name="hari[]"]').forEach(cb => {
            cb.checked = hariDokter.includes(cb.value);
        });

        // Jam: parse "HH:MM - HH:MM"
        const jamParts = (data.jam_praktik || '').split(' - ');
        if (jamParts.length === 2) {
            // Normalisasi ke HH:MM (hilangkan detik jika ada)
            document.getElementById('jam_mulai').value   = jamParts[0].trim().substring(0, 5);
            document.getElementById('jam_selesai').value = jamParts[1].trim().substring(0, 5);
        }
        updatePreviewJam();

        // Status
        document.getElementById('status_tersedia').checked = data.status === 'Tersedia';
        document.getElementById('status_penuh').checked    = data.status === 'Penuh';

        // Scroll ke form
        document.getElementById('form-jadwal').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetForm() {
        document.getElementById('form-jadwal').reset();
        document.getElementById('id_dokter').value = '';
        document.getElementById('form-title').textContent = '➕ Tambah Jadwal Dokter';
        document.getElementById('btn-batal').classList.add('hidden');
        document.getElementById('btn-simpan').textContent = 'Simpan Jadwal';
        document.getElementById('btn-simpan').classList.add('bg-indigo-600','hover:bg-indigo-700');
        document.getElementById('btn-simpan').classList.remove('bg-orange-500','hover:bg-orange-600');
        document.querySelectorAll('input[name="hari[]"]').forEach(cb => cb.checked = false);
        document.getElementById('jam_mulai').value = '08:00';
        document.getElementById('jam_selesai').value = '12:00';
        updatePreviewJam();
    }
    </script>
</body>
</html>