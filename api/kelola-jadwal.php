<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

$user = checkAuth();

// Cek Admin
if ($user['role'] !== 'admin') {
    header("Location: /api/dashboard-user.php");
    exit();
}

// Logika Tambah & Update
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];
    $status = $_POST['status'];

    if ($_POST['id_dokter'] == "") {
        mysqli_query($conn, "INSERT INTO dokter (nama_dokter, spesialis, hari, jam_praktik, status) VALUES ('$nama', '$spesialis', '$hari', '$jam', '$status')");
    } else {
        $id = $_POST['id_dokter'];
        mysqli_query($conn, "UPDATE dokter SET nama_dokter='$nama', spesialis='$spesialis', hari='$hari', jam_praktik='$jam', status='$status' WHERE id=$id");
    }
    header("Location: /api/kelola-jadwal.php");
    exit();
}

// Logika Hapus
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM dokter WHERE id=$id");
    header("Location: /api/kelola-jadwal.php");
}

$result = mysqli_query($conn, "SELECT * FROM dokter");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Pengaturan Jadwal Dokter</h1>
            <a href="/api/dashboard-admin.php" class="block py-2.5 px-4 hover:bg-slate-700 rounded transition text-blue-300 font-semibold">← Kembali</a>
        </div>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 bg-blue-50 p-4 rounded-lg">
            <input type="hidden" name="id_dokter" id="id_dokter">
            <input type="text" name="nama" id="nama" placeholder="Nama Dokter" class="p-2 border rounded" required>
            <input type="text" name="spesialis" id="spesialis" placeholder="Spesialis" class="p-2 border rounded" required>
            <input type="text" name="hari" id="hari" placeholder="Hari (Senin-Jumat)" class="p-2 border rounded" required>
            <input type="text" name="jam" id="jam" placeholder="Jam (08:00-12:00)" class="p-2 border rounded" required>
            <select name="status" id="status" class="p-2 border rounded">
                <option value="Tersedia">Tersedia</option>
                <option value="Penuh">Penuh</option>
            </select>
            <button type="submit" name="simpan" class="bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Simpan Jadwal</button>
        </form>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-3">Nama Dokter</th>
                    <th class="p-3">Spesialis</th>
                    <th class="p-3">Jadwal</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="border-b">
                    <td class="p-3"><?php echo $row['nama_dokter']; ?></td>
                    <td class="p-3"><?php echo $row['spesialis']; ?></td>
                    <td class="p-3"><?php echo $row['hari']; ?> | <?php echo $row['jam_praktik']; ?></td>
                    <td class="p-3 font-bold <?php echo ($row['status']=='Tersedia') ? 'text-green-600' : 'text-red-600'; ?>"><?php echo $row['status']; ?></td>
                    <td class="p-3">
                        <button onclick="editDokter(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-blue-500 mr-2">Edit</button>
                        <a href="?hapus=<?php echo $row['id']; ?>" class="text-red-500" onclick="return confirm('Hapus jadwal?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function editDokter(data) {
        document.getElementById('id_dokter').value = data.id;
        document.getElementById('nama').value = data.nama_dokter;
        document.getElementById('spesialis').value = data.spesialis;
        document.getElementById('hari').value = data.hari;
        document.getElementById('jam').value = data.jam_praktik;
        document.getElementById('status').value = data.status;

        // Ganti teks tombol supaya user tahu sedang mode edit
        var btn = document.querySelector('button[name="simpan"]');
        btn.textContent = 'Update Jadwal';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-orange-500', 'hover:bg-orange-600');

        // Scroll ke form agar user sadar form sudah terisi
        document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>
