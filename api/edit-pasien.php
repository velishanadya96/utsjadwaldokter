<?php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';

$user = checkAuth();

// Proteksi: Hanya admin yang boleh edit
if ($user['role'] !== 'admin') {
    header("Location: /api/login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
$stmt_get = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, 'i', $id);
mysqli_stmt_execute($stmt_get);
$result_get = mysqli_stmt_get_result($stmt_get);
$data = mysqli_fetch_assoc($result_get);

if (!$data) {
    header("Location: /api/dashboard-admin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama    = trim($_POST['nama'] ?? '');
    $nik     = trim($_POST['nik'] ?? '');
    $riwayat = trim($_POST['riwayat_penyakit'] ?? '');
    $provinsi = trim($_POST['provinsi'] ?? '');

    // Update pakai prepared statement agar aman
    $stmt_upd = mysqli_prepare($conn, "UPDATE users SET nama=?, nik=?, riwayat_penyakit=?, provinsi=? WHERE id=?");
    mysqli_stmt_bind_param($stmt_upd, 'ssssi', $nama, $nik, $riwayat, $provinsi, $id);

    if (mysqli_stmt_execute($stmt_upd)) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='/api/dashboard-admin.php';</script>";
    } else {
        echo "<script>alert('Error: " . addslashes(mysqli_stmt_error($stmt_upd)) . "');</script>";
    }
    mysqli_stmt_close($stmt_upd);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Pasien</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Data Pasien</h2>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo $data['nama']; ?>" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700">NIK</label>
                <input type="text" name="nik" value="<?php echo $data['nik']; ?>" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Domisili (Provinsi)</label>
                <select id="provinsi" name="provinsi" required class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="<?php echo $data['provinsi']; ?>"><?php echo !empty($data['provinsi']) ? $data['provinsi'] : '-- Pilih Provinsi --'; ?></option>
                    <option value="">Memuat data provinsi...</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Riwayat Penyakit</label>
                <textarea name="riwayat_penyakit" rows="3" class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"><?php echo $data['riwayat_penyakit']; ?></textarea>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-md">Simpan Perubahan</button>
                <a href="/api/dashboard-admin.php" class="flex-1 bg-gray-200 text-center text-gray-700 py-2 rounded-lg font-bold hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>

    <script>
        // Ambil data dari API BPS lewat file proxy kamu
        fetch('get-provinsi.php')
            .then(response => response.json())
            .then(data => {
                const selectProvinsi = document.getElementById('provinsi');
                const listProvinsi = data.data[1]; 
                
                listProvinsi.forEach(item => {
                    let option = document.createElement('option');
                    option.value = item.domain_name;
                    option.text = item.domain_name;
                    // Jika provinsi sama dengan data di DB, kita tidak perlu duplikat tapi tetap munculkan semua opsi
                    selectProvinsi.appendChild(option);
                });
            })
            .catch(err => console.error('Gagal ambil data:', err));
    </script>
</body>
</html>