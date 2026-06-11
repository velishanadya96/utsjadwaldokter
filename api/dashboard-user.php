<?php
// dashboard-user.php
include __DIR__ . '/config.php';
include __DIR__ . '/auth_check.php';
$user = checkAuth();

// Ambil hari ini dalam bahasa Indonesia untuk filter jadwal
$hari_map = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu',
];
$hari_ini_en = date('l');
$hari_ini_id = $hari_map[$hari_ini_en] ?? $hari_ini_en;

// Ambil tanggal yang dipilih user (default: hari ini)
$selected_date = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');
$selected_ts   = strtotime($selected_date);
$selected_day_en = date('l', $selected_ts);
$selected_day_id = $hari_map[$selected_day_en] ?? $selected_day_en;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien - Klinik Sehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Scrollbar tipis untuk date strip */
        .date-strip::-webkit-scrollbar { height: 4px; }
        .date-strip::-webkit-scrollbar-track { background: #e0e7ff; border-radius: 99px; }
        .date-strip::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 99px; }

        /* Smooth scroll pada strip */
        .date-strip { scroll-behavior: smooth; }

        /* Badge pulse untuk status Tersedia */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }
        .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-50 flex min-h-screen">

    <!-- ===== SIDEBAR ===== -->
    <aside class="w-64 min-h-screen bg-gradient-to-b from-blue-700 to-blue-900 text-white p-6 flex flex-col shadow-xl">
        <div class="mb-10">
            <h2 class="text-2xl font-black tracking-tight">Klinik<span class="text-blue-300">Sehat</span></h2>
            <p class="text-blue-300 text-xs mt-1">Sistem Jadwal Dokter</p>
        </div>
        <nav class="space-y-2 flex-1">
            <a href="/api/dashboard-user.php"
               class="flex items-center gap-3 py-2.5 px-4 bg-white/15 rounded-xl font-semibold text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cek Jadwal
            </a>
            <a href="/api/riwayat.php"
               class="flex items-center gap-3 py-2.5 px-4 hover:bg-white/10 rounded-xl transition text-blue-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Riwayat Antrean
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-blue-600">
            <p class="text-xs text-blue-300 mb-1">Login sebagai</p>
            <p class="font-bold text-sm truncate"><?php echo htmlspecialchars($user['nama']); ?></p>
            <a href="/api/logout.php" class="mt-3 flex items-center gap-2 text-red-300 text-sm hover:text-red-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="flex-1 p-8 overflow-auto">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-black text-slate-800">Selamat datang, <?php echo htmlspecialchars(explode(' ', $user['nama'])[0]); ?>! 👋</h1>
            <p class="text-slate-500 mt-1">Cek jadwal dokter dan ambil antrean Anda</p>
        </div>

        <!-- ===== SECTION 1: JADWAL HARI INI ===== -->
        <section class="mb-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                <h2 class="text-lg font-bold text-slate-700">Jadwal Dokter Hari Ini
                    <span class="text-sm font-normal text-slate-400 ml-2"><?php echo $hari_ini_id . ', ' . date('d F Y'); ?></span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php
                $q_hari_ini = mysqli_prepare($conn, "SELECT * FROM dokter WHERE hari LIKE ? ORDER BY nama_dokter ASC");
                $like_hari  = '%' . $hari_ini_id . '%';
                mysqli_stmt_bind_param($q_hari_ini, 's', $like_hari);
                mysqli_stmt_execute($q_hari_ini);
                $res_hari_ini = mysqli_stmt_get_result($q_hari_ini);

                if ($res_hari_ini && mysqli_num_rows($res_hari_ini) > 0):
                    while ($d = mysqli_fetch_assoc($res_hari_ini)):
                        $tersedia = ($d['status'] === 'Tersedia');
                ?>
                <div class="bg-white rounded-2xl border <?php echo $tersedia ? 'border-green-100' : 'border-red-100'; ?> p-5 shadow-sm flex gap-4 items-start">
                    <!-- Avatar inisial -->
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-white text-lg flex-shrink-0
                        <?php echo $tersedia ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-slate-400 to-slate-500'; ?>">
                        <?php echo mb_strtoupper(mb_substr($d['nama_dokter'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($d['nama_dokter']); ?></p>
                        <p class="text-xs text-blue-600 font-medium mb-2"><?php echo htmlspecialchars($d['spesialis']); ?></p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">
                                🕐 <?php echo htmlspecialchars($d['jam_praktik']); ?>
                            </span>
                            <span class="flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full
                                <?php echo $tersedia ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php if ($tersedia): ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 pulse-dot"></span> Tersedia
                                <?php else: ?>
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Penuh
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                <div class="col-span-full bg-amber-50 border border-amber-100 text-amber-700 rounded-2xl p-5 text-sm">
                    Tidak ada dokter yang berpraktik hari <?php echo $hari_ini_id; ?>.
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== SECTION 2: PILIH TANGGAL & CARI DOKTER ===== -->
        <section>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                <h2 class="text-lg font-bold text-slate-700">Ambil Antrean</h2>
            </div>

            <!-- Date Strip ala KAI -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-slate-600">Pilih Tanggal Kunjungan</p>
                    <div class="flex gap-2">
                        <button id="prev-week" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition text-slate-600 text-xs font-bold">‹ Sebelumnya</button>
                        <button id="next-week" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 transition text-slate-600 text-xs font-bold">Selanjutnya ›</button>
                    </div>
                </div>

                <!-- Strip tanggal -->
                <div id="date-strip" class="date-strip flex gap-2 overflow-x-auto pb-2">
                    <!-- Diisi JS -->
                </div>
            </div>

            <!-- Label tanggal terpilih -->
            <div class="flex items-center gap-2 mb-4">
                <span class="text-sm text-slate-500">Menampilkan jadwal untuk:</span>
                <span id="label-terpilih" class="text-sm font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full"></span>
            </div>

            <!-- Tabel Dokter -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left" id="tabel-dokter">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Dokter</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Jadwal Rutin</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-dokter" class="divide-y divide-slate-50">
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                <div class="inline-block w-6 h-6 border-2 border-blue-400 border-t-transparent rounded-full animate-spin mb-2"></div>
                                <p class="text-sm">Memuat jadwal...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
    (function () {
        // ============================================================
        //  DATA DOKTER (di-embed dari PHP agar tidak perlu AJAX tambahan)
        // ============================================================
        const semuaDokter = <?php
            $q_all = mysqli_query($conn, "SELECT * FROM dokter ORDER BY nama_dokter ASC");
            $arr = [];
            while ($d = mysqli_fetch_assoc($q_all)) { $arr[] = $d; }
            echo json_encode($arr);
        ?>;

        // ============================================================
        //  KONFIGURASI TANGGAL
        // ============================================================
        const HARI_ID = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const BULAN_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        let currentWeekStart = getMonday(new Date()); // selalu mulai Senin
        let selectedDate = new Date(); // default hari ini

        function getMonday(d) {
            const dt = new Date(d);
            const day = dt.getDay();
            const diff = dt.getDate() - day + (day === 0 ? -6 : 1);
            return new Date(dt.setDate(diff));
        }

        function toYMD(d) {
            return d.getFullYear() + '-'
                + String(d.getMonth() + 1).padStart(2, '0') + '-'
                + String(d.getDate()).padStart(2, '0');
        }

        // ============================================================
        //  RENDER STRIP
        // ============================================================
        function renderStrip() {
            const strip = document.getElementById('date-strip');
            strip.innerHTML = '';
            const todayStr = toYMD(new Date());

            for (let i = 0; i < 14; i++) { // 2 minggu
                const d = new Date(currentWeekStart);
                d.setDate(d.getDate() + i);
                const dStr = toYMD(d);
                const isSelected = dStr === toYMD(selectedDate);
                const isToday    = dStr === todayStr;
                const isPast     = d < new Date(new Date().setHours(0,0,0,0));

                const btn = document.createElement('button');
                btn.dataset.date = dStr;
                btn.disabled = isPast && !isToday;

                btn.className = [
                    'flex flex-col items-center flex-shrink-0 w-14 py-2.5 rounded-xl border-2 transition font-medium text-center',
                    isSelected
                        ? 'border-indigo-500 bg-indigo-500 text-white shadow-md shadow-indigo-200'
                        : isPast && !isToday
                            ? 'border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed'
                            : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300 hover:bg-indigo-50'
                ].join(' ');

                btn.innerHTML = `
                    <span class="text-[10px] font-bold uppercase tracking-wide ${isSelected ? 'text-indigo-200' : 'text-slate-400'}">${HARI_ID[d.getDay()].slice(0,3)}</span>
                    <span class="text-xl font-black leading-tight">${d.getDate()}</span>
                    <span class="text-[10px] ${isSelected ? 'text-indigo-200' : 'text-slate-400'}">${BULAN_ID[d.getMonth()]}</span>
                    ${isToday ? `<span class="mt-1 w-1.5 h-1.5 rounded-full ${isSelected ? 'bg-white' : 'bg-indigo-500'}"></span>` : ''}
                `;

                btn.addEventListener('click', () => {
                    selectedDate = d;
                    renderStrip();
                    renderTable();
                });

                strip.appendChild(btn);
            }

            // Scroll agar tanggal terpilih terlihat
            const selBtn = strip.querySelector('[data-date="' + toYMD(selectedDate) + '"]');
            if (selBtn) selBtn.scrollIntoView({ inline: 'center', behavior: 'smooth' });
        }

        // ============================================================
        //  RENDER TABEL DOKTER
        // ============================================================
        function renderTable() {
            const tbody = document.getElementById('tbody-dokter');
            const labelTerpilih = document.getElementById('label-terpilih');
            const d = selectedDate;
            const hariId = HARI_ID[d.getDay()];
            const tglFormatted = d.getDate() + ' ' + ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][d.getMonth()] + ' ' + d.getFullYear();

            labelTerpilih.textContent = hariId + ', ' + tglFormatted;

            // Filter dokter yang jadwalnya mencakup hari ini
            const filtered = semuaDokter.filter(dok => dok.hari.includes(hariId));

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="text-4xl mb-3">🏥</div>
                            <p class="font-semibold text-slate-500">Tidak ada dokter yang berpraktik</p>
                            <p class="text-sm text-slate-400 mt-1">pada hari ${hariId}</p>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(dok => {
                const tersedia = dok.status === 'Tersedia';
                const inisial  = dok.nama_dokter.charAt(0).toUpperCase();
                return `
                <tr class="hover:bg-blue-50/40 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white text-base flex-shrink-0
                                ${tersedia ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-slate-400 to-slate-500'}">
                                ${inisial}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">${escHtml(dok.nama_dokter)}</p>
                                <p class="text-xs text-blue-600">${escHtml(dok.spesialis)}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="block font-medium text-slate-700 text-sm">${escHtml(dok.hari)}</span>
                        <span class="text-xs text-slate-400">🕐 ${escHtml(dok.jam_praktik)}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="flex items-center gap-1.5 w-fit text-xs font-bold px-3 py-1 rounded-full
                            ${tersedia ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                            <span class="w-1.5 h-1.5 rounded-full ${tersedia ? 'bg-green-500' : 'bg-red-500'}"></span>
                            ${escHtml(dok.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${tersedia
                            ? `<a href="/api/daftar-antrean.php?id=${dok.id}&tgl=${toYMD(selectedDate)}"
                                  class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm shadow-indigo-200 transition active:scale-95">
                                   Ambil Antrean
                               </a>`
                            : `<span class="inline-block bg-slate-100 text-slate-400 px-4 py-2 rounded-xl text-sm font-bold cursor-not-allowed">Penuh</span>`
                        }
                    </td>
                </tr>`;
            }).join('');
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str || ''));
            return div.innerHTML;
        }

        // Navigasi minggu
        document.getElementById('prev-week').addEventListener('click', () => {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            renderStrip();
        });
        document.getElementById('next-week').addEventListener('click', () => {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            renderStrip();
        });

        // Init
        renderStrip();
        renderTable();
    })();
    </script>
</body>
</html>