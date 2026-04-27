<?php
include 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $nik = $_POST['nik'];
    $email = $_POST['email'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $riwayat = $_POST['riwayat_penyakit'];
    $provinsi = $_POST['provinsi'];
    $kabupaten = $_POST['kabupaten']; // Ambil data kabupaten
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'user'; 

    $sql = "INSERT INTO users (nama, nik, email, password, tempat_lahir, tgl_lahir, riwayat_penyakit, provinsi, kabupaten, role) 
            VALUES ('$nama', '$nik', '$email', '$password', '$tempat_lahir', '$tgl_lahir', '$riwayat', '$provinsi', '$kabupaten', '$role')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pendaftaran Berhasil!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
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
                    <input type="text" name="nik" maxlength="16" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
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
                <input type="password" name="password" required class="w-full mt-1 px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">Daftar Sekarang</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectProv = document.getElementById('provinsi');
            const selectKab = document.getElementById('kabupaten');

            // 1. Ambil Data Provinsi dari API BPS (via proxy file kamu)
            fetch('get-provinsi.php')
                .then(response => response.json())
                .then(data => {
                    selectProv.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                    const listProv = data.data[1];
                    listProv.forEach(item => {
                        let opt = document.createElement('option');
                        opt.value = item.domain_id; // Simpan ID untuk filter kabupaten
                        opt.text = item.domain_name;
                        selectProv.appendChild(opt);
                    });
                });

            // 2. Logika saat Provinsi dipilih
            selectProv.addEventListener('change', function() {
                const idProv = this.value;
                const namaProv = this.options[this.selectedIndex].text;
                
                if (idProv) {
                    selectKab.innerHTML = '<option value="">Memuat Kabupaten...</option>';
                    
                    const urlKab = `https://webapi.bps.go.id/v1/api/domain/type/kabbyprov/prov/${idProv}/key/5fac61ddd743d80db3867d22d4baf88c/`;
                    
                    fetch(urlKab)
                        .then(res => res.json())
                        .then(resData => {
                            selectKab.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                            const listKab = resData.data[1];
                            listKab.forEach(kab => {
                                let opt = document.createElement('option');
                                opt.value = kab.domain_name;
                                opt.text = kab.domain_name;
                                selectKab.appendChild(opt);
                            });
                            
                           
                        });
                }
            });
        });
    </script>
</body>
</html>