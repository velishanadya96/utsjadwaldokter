<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

$user = checkAuth();

// Cek Proteksi Admin
if ($user['role'] !== 'admin') {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Logika Tambah & Update menggunakan Prepared Statement (Aman)
if (isset($_POST['simpan'])) {
    $nama = trim($_POST['nama']);
    $spesialis = trim($_POST['spesialis']);
    $hari = trim($_POST['hari']);
    $jam = trim($_POST['jam']);
    $status = trim($_POST['status']);
    $id_dokter = $_POST['id_dokter'];

    if ($id_dokter == "") {
        // INSERT Baru
        $stmt = mysqli_prepare($conn, "INSERT INTO dokter (nama_dokter, spesialis, hari, jam_praktik, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $nama, $spesialis, $hari, $jam, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // UPDATE Data Lama
        $id = (int)$id_dokter;
        $stmt = mysqli_prepare($conn, "UPDATE dokter SET nama_dokter=?, spesialis=?, hari=?, jam_praktik=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $spesialis, $hari, $jam, $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: /api/kelola-jadwal.php");
    exit();
}

// Logika Hapus memakai Prepared Statement
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM dokter WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: /api/kelola-jadwal.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM dokter ORDER BY nama_dokter ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow-md border border-slate-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Pengaturan Jadwal Dokter</h1>
            <a href="/api/dashboard-admin.php" class="py-2 px-4 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition font-semibold text-sm">&larr; Kembali ke Pasien</a>
        </div>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10 bg-indigo-50/50 p-5 rounded-xl border border-indigo-100">
            <input type="hidden" name="id_dokter" id="id_dokter">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Dokter</label>
                <input type="text" name="nama" id="nama" placeholder="Contoh: Dr. Budi Utomo" class="w-full p-2.5 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 outline-none text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Spesialis</label>
                <input type="text" name="spesialis" id="spesialis" placeholder="Contoh: Spesialis Anak" class="w-full p-2.5 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 outline-none text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Hari Kerja (Pisahkan dengan koma jika multi-hari)</label>
                <input type="text" name="hari" id="hari" placeholder="Contoh: Senin, Rabu, Jumat" class="w-full p-2.5 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 outline-none text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Jam Praktik</label>
                <input type="text" name="jam" id="jam" placeholder="Contoh: 08:00 – 12:00 atau 14:00 – 17:00" class="w-full p-2.5 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 outline-none text-sm" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status Ketersediaan Antrean</label>
                <select name="status" id="status" class="w-full p-2.5 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 outline-none text-sm">
                    <option value="Tersedia">Tersedia</option>
                    <option value="Penuh">Penuh</option>
                </select>
            </div>

            <div class="md:col-span-2 flex justify-end mt-2">
                <button type="submit" name="simpan" id="btn-submit" class="w-full md:w-auto bg-indigo-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition shadow-md text-sm">
                    Simpan Jadwal
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border border-slate-100 rounded-xl overflow-hidden">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="p-3 text-sm font-bold text-slate-600">Nama Dokter</th>
                        <th class="p-3 text-sm font-bold text-slate-600">Spesialis</th>
                        <th class="p-3 text-sm font-bold text-slate-600">Hari Kerja</th>
                        <th class="p-3 text-sm font-bold text-slate-600">Jam Kerja</th>
                        <th class="p-3 text-sm font-bold text-slate-600">Status</th>
                        <th class="p-3 text-sm font-bold text-slate-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($row['nama_dokter']); ?></td>
                        <td class="p-3 text-sm text-blue-600 font-medium"><?php echo htmlspecialchars($row['spesialis']); ?></td>
                        <td class="p-3 text-sm text-slate-700 font-medium"><?php echo htmlspecialchars($row['hari']); ?></td>
                        <td class="p-3 text-sm text-slate-500"><?php echo htmlspecialchars($row['jam_praktik']); ?></td>
                        <td class="p-3 text-sm">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo ($row['status']=='Tersedia') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td class="p-3 text-sm text-center font-bold">
                            <button onclick="editDokter(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <a href="?hapus=<?php echo $row['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal dokter ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function editDokter(data) {
        document.getElementById('id_dokter').value = data.id;
        document.getElementById('nama').value = data.nama_dokter;
        document.getElementById('spesialis').value = data.spesialis;
        document.getElementById('hari').value = data.hari;
        document.getElementById('jam').value = data.jam_praktik;
        document.getElementById('status').value = data.status;

        // Ubah styling tombol form menjadi mode edit info
        var btn = document.getElementById('btn-submit');
        btn.textContent = 'Update Perubahan Jadwal';
        btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        btn.classList.add('bg-orange-500', 'hover:bg-orange-600');

        // Scroll halus ke arah form masukan
        document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>