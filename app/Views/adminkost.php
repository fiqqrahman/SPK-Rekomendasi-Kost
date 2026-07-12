<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemilik - Manajemen & Input Kost</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .custom-campus-marker,
        .custom-kost-marker {
            background: none;
            border: none;
        }
    </style>
</head>

<body class="bg-[#0f172a] text-slate-200 min-h-screen dynamic-ui antialiased">

    <div class="container mx-auto px-4 py-10 max-w-5xl">

        <header class="mb-10 border-b border-slate-800 pb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-teal-500/10 text-teal-400 px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase mb-2 border border-teal-500/20">
                    Property Owner Center
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    Control Panel <span class="text-teal-400">Pemilik Kost</span>
                </h1>
                <p class="text-slate-400 mt-1 text-sm font-light">
                    Registrasikan properti baru dan kelola ketersediaan status hunian kamar antum dalam satu layar terintegrasi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 md:self-end">

                <div class="flex items-center gap-2.5 bg-[#1e293b] border border-slate-700/60 px-3.5 py-2 rounded-xl text-xs shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400/50"></span>
                    <div class="text-left">
                        <span class="text-slate-400 block text-[9px] uppercase font-bold tracking-wider mb-0.5">Sesi Aktif</span>
                        <span class="text-white font-semibold font-mono">
                            <?= esc(session()->get('username')) ?>
                            <span class="text-slate-500 font-sans font-normal">(<?= esc(session()->get('email')) ?>)</span>
                        </span>
                    </div>
                </div>

                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 bg-[#1e293b] hover:bg-slate-700 border border-slate-700 text-slate-300 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                    ← Monitor SPK
                </a>

                <a href="<?= base_url('/logout') ?>" class="inline-flex items-center gap-2 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm shadow-rose-950/20">
                    🚪 Keluar
                </a>
            </div>
        </header>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6 font-medium">
                ✨ <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm p-4 rounded-xl mb-6 font-medium">
                ⚠️ <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mb-10">

            <div class="lg:col-span-5 bg-[#1e293b] p-6 rounded-2xl shadow-xl border border-slate-700/60">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-5 border-b border-slate-700 pb-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                    Form Tambah Properti Baru
                </h2>

                <form method="POST" action="<?= base_url('/owner/save') ?>" class="space-y-5">

                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Properti Kost</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Kost Kahayan Permai"
                            class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm">
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Harga Sewa Bulanan (Rp)</label>
                        <input type="number" id="price" name="price" required placeholder="Contoh: 850000" min="0"
                            class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm font-mono">
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Inventaris Fasilitas Riil</label>

                        <div class="space-y-3 bg-[#0f172a]/50 p-4 rounded-xl border border-slate-700/50">
                            <div class="text-[10px] font-bold uppercase tracking-widest text-teal-400 mb-1">Fasilitas Parkir (C3)</div>
                            <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-300 hover:text-white transition">
                                <input type="checkbox" name="features[]" value="1" class="w-4 h-4 rounded text-teal-500 bg-[#0f172a] border-slate-600 focus:ring-teal-400 focus:ring-offset-0 accent-teal-400">
                                <span>Parkir Motor Berkanopi</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-300 hover:text-white transition pb-2 border-b border-slate-800">
                                <input type="checkbox" name="features[]" value="2" class="w-4 h-4 rounded text-teal-500 bg-[#0f172a] border-slate-600 focus:ring-teal-400 focus:ring-offset-0 accent-teal-400">
                                <span>Lahan Parkir Mobil Luas</span>
                            </label>

                            <div class="text-[10px] font-bold uppercase tracking-widest text-rose-400 mt-2 mb-1">Proteksi Keamanan (C4)</div>
                            <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-300 hover:text-white transition">
                                <input type="checkbox" name="features[]" value="3" class="w-4 h-4 rounded text-teal-500 bg-[#0f172a] border-slate-600 focus:ring-teal-400 focus:ring-offset-0 accent-teal-400">
                                <span>Pagar Pengaman Kokoh</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-300 hover:text-white transition">
                                <input type="checkbox" name="features[]" value="4" class="w-4 h-4 rounded text-teal-500 bg-[#0f172a] border-slate-600 focus:ring-teal-400 focus:ring-offset-0 accent-teal-400">
                                <span>Kamera CCTV Aktif 24 Jam</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-300 hover:text-white transition">
                                <input type="checkbox" name="features[]" value="5" class="w-4 h-4 rounded text-teal-500 bg-[#0f172a] border-slate-600 focus:ring-teal-400 focus:ring-offset-0 accent-teal-400">
                                <span>Satpam / Penjaga Kost</span>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" id="latitude" name="latitude" required>
                    <input type="hidden" id="longitude" name="longitude" required>

                    <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-extrabold px-6 py-3.5 rounded-xl shadow-lg shadow-teal-500/10 transition duration-200 uppercase text-xs tracking-wider cursor-pointer">
                        Simpan & Daftarkan Kost
                    </button>
                </form>
            </div>

            <div class="lg:col-span-7 bg-[#1e293b] p-4 rounded-2xl shadow-xl border border-slate-700/60">
                <div class="px-2 pb-3">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-slate-300 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        Penentu Koordinat Geospasial
                    </h2>
                    <p class="text-xs text-slate-400 mt-1 font-light">
                        Cari lokasi properti antum di peta bawah ini, lalu **Klik Kiri** tepat di atas atap bangunannya untuk mengunci posisi koordinat.
                    </p>
                </div>

                <div id="map-picker" class="h-[310px] rounded-xl border border-slate-900 shadow-inner z-10"></div>

                <div class="grid grid-cols-2 gap-4 mt-4 bg-[#0f172a]/40 p-3 rounded-xl border border-slate-700/40 text-xs font-mono">
                    <div class="bg-[#0f172a] p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-sans font-bold tracking-wider mb-0.5">Latitude Terkunci</span>
                        <span id="display-lat" class="text-rose-400 font-bold">Belum Dipilih</span>
                    </div>
                    <div class="bg-[#0f172a] p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase font-sans font-bold tracking-wider mb-0.5">Longitude Terkunci</span>
                        <span id="display-lng" class="text-amber-400 font-bold">Belum Dipilih</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#1e293b] rounded-2xl shadow-xl border border-slate-700/60 overflow-hidden">
            <div class="px-6 py-5 bg-[#172554]/20 border-b border-slate-700/60 flex justify-between items-center">
                <h2 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                    Daftar Inventaris Properti Kost Anda
                </h2>
                <span class="bg-teal-500/10 text-teal-400 text-xs px-2.5 py-1 rounded-md font-mono border border-teal-500/20">Live Controller Active</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[768px]">
                    <thead>
                        <tr class="bg-[#0f172a]/60 text-slate-400 uppercase text-[11px] tracking-widest font-bold border-b border-slate-700/60">
                            <th class="px-6 py-4">Nama Properti Kost</th>
                            <th class="px-6 py-4 text-right w-48">Harga Sewa / Bulan</th>
                            <th class="px-6 py-4 text-center w-40">Status Hunian</th>
                            <th class="px-6 py-4 text-center w-48">Aksi Saklar Kontrol</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700 text-sm">
                        <?php if (!empty($myKosts)): ?>
                            <?php foreach ($myKosts as $kost): ?>
                                <tr class="hover:bg-slate-800/40 transition duration-150">
                                    <td class="px-6 py-5 font-semibold text-white text-base"><?= esc($kost['name']) ?></td>
                                    <td class="px-6 py-5 text-right font-semibold text-emerald-400 font-mono text-sm">
                                        Rp <?= number_format($kost['price'], 0, ',', '.') ?>
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <?php if (isset($kost['is_full']) && $kost['is_full'] == 1): ?>
                                            <span class="bg-rose-500/10 text-rose-400 border border-rose-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider">🚨 Sudah Penuh</span>
                                        <?php else: ?>
                                            <span class="bg-teal-500/10 text-teal-400 border border-teal-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider">✓ Tersedia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <a href="<?= base_url('/owner/toggle-status/' . $kost['id']) ?>"
                                            class="inline-block text-xs font-bold px-4 py-2 rounded-xl transition cursor-pointer font-sans shadow-md text-center w-36 uppercase tracking-wider text-[11px] <?= (isset($kost['is_full']) && $kost['is_full'] == 1) ? 'bg-gradient-to-r from-teal-500 to-emerald-500 text-slate-950 hover:from-teal-600 hover:to-emerald-600 shadow-teal-500/5' : 'bg-gradient-to-r from-rose-500 to-red-600 text-white hover:from-rose-600 hover:to-red-700 shadow-rose-500/5' ?>">
                                            <?= (isset($kost['is_full']) && $kost['is_full'] == 1) ? 'Buka Kamar' : 'Set Jadi Penuh' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic font-light">
                                    Belum ada data properti yang didaftarkan. Silakan isi form di atas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        const initialLat = -2.22623400;
        const initialLng = 113.92423100;

        const map = L.map('map-picker').setView([initialLat, initialLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let currentMarker = null;

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            if (currentMarker) {
                currentMarker.setLatLng(e.latlng);
            } else {
                currentMarker = L.marker(e.latlng).addTo(map);
                currentMarker.bindPopup('<b class="text-slate-900 text-xs">Lokasi Target Kost</b>').openPopup();
            }

            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);

            document.getElementById('display-lat').innerText = lat.toFixed(8);
            document.getElementById('display-lng').innerText = lng.toFixed(8);

            document.getElementById('display-lat').className = "text-teal-400 font-bold font-mono text-sm";
            document.getElementById('display-lng').className = "text-teal-400 font-bold font-mono text-sm";
        });
    </script>
</body>

</html>