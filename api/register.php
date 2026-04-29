<?php
include __DIR__ . '/config.php';

// PERBAIKAN: Deteksi POST dengan REQUEST_METHOD, bukan isset($_POST['register'])
// karena form tidak punya field name="register"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama         = trim($_POST['nama'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $nik          = trim($_POST['nik'] ?? '');
    $password     = $_POST['password'] ?? '';
    $provinsi     = trim($_POST['provinsi'] ?? '');
    $kabupaten    = trim($_POST['kabupaten'] ?? '');
    $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
    $tgl_lahir    = trim($_POST['tgl_lahir'] ?? '');
    $role         = 'user';

    // Validasi field wajib
    if (empty($nama) || empty($email) || empty($nik) || empty($password)) {
        echo "<script>alert('Semua field wajib diisi!'); window.history.back();</script>";
        exit();
    }

    $pass_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Cek NIK duplikat (prepared statement)
    $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE nik = ?");
    mysqli_stmt_bind_param($cek, 's', $nik);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        echo "<script>alert('NIK sudah terdaftar! Gunakan NIK lain.'); window.history.back();</script>";
        exit();
    }
    mysqli_stmt_close($cek);

    // Cek email duplikat
    $cek_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($cek_email, 's', $email);
    mysqli_stmt_execute($cek_email);
    mysqli_stmt_store_result($cek_email);

    if (mysqli_stmt_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.history.back();</script>";
        exit();
    }
    mysqli_stmt_close($cek_email);

    // INSERT — PERBAIKAN: ikutkan semua kolom dari form
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (nama, email, nik, password, provinsi, kabupaten, tempat_lahir, tgl_lahir, role)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'sssssssss',
        $nama, $email, $nik, $pass_hashed,
        $provinsi, $kabupaten, $tempat_lahir, $tgl_lahir, $role
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Registrasi Berhasil! Silakan login.'); window.location.href='/api/login.php';</script>";
    } else {
        $err = mysqli_stmt_error($stmt);
        echo "<script>alert('Gagal mendaftar: " . addslashes($err) . "'); window.history.back();</script>";
    }
    mysqli_stmt_close($stmt);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 flex items-center justify-center py-10">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Pasien Baru</h2>

        <form action="" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                    <input type="text" name="nama" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">NIK</label>
                    <input type="text" name="nik" maxlength="16" pattern="\d{16}" title="NIK harus 16 digit angka" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Provinsi Domisili</label>
                    <select id="provinsi" name="provinsi" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Memuat...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Kabupaten/Kota</label>
                    <select id="kabupaten" name="kabupaten" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Provinsi dulu</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Password</label>
                <input type="password" name="password" minlength="8" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
                Daftar Sekarang
            </button>
            <p class="text-center text-sm mt-2">Sudah Punya Akun? <b><a href="/api/login.php" class="text-blue-600 hover:underline">Masuk</a></b></p>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectProv = document.getElementById('provinsi');
            const selectKab  = document.getElementById('kabupaten');

            fetch('/api/get-provinsi.php')
                .then(r => r.json())
                .then(data => {
                    selectProv.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                    data.data[1].forEach(item => {
                        let opt = document.createElement('option');
                        opt.value = item.domain_id;
                        opt.text  = item.domain_name;
                        selectProv.appendChild(opt);
                    });
                })
                .catch(() => {
                    selectProv.innerHTML = '<option value="">Gagal memuat provinsi</option>';
                });

            selectProv.addEventListener('change', function () {
                const idProv = this.value;
                if (!idProv) return;

                selectKab.innerHTML = '<option value="">Memuat Kabupaten...</option>';
                const urlKab = `https://webapi.bps.go.id/v1/api/domain/type/kabbyprov/prov/${idProv}/key/5fac61ddd743d80db3867d22d4baf88c/`;

                fetch(urlKab)
                    .then(r => r.json())
                    .then(resData => {
                        selectKab.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                        resData.data[1].forEach(kab => {
                            let opt = document.createElement('option');
                            opt.value = kab.domain_name;
                            opt.text  = kab.domain_name;
                            selectKab.appendChild(opt);
                        });
                    })
                    .catch(() => {
                        selectKab.innerHTML = '<option value="">Gagal memuat kabupaten</option>';
                    });
            });
        });
    </script>
</body>
</html>