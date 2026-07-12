<!DOCTYPE html>
<html lang="id">

<?php
/**
 * @var array $campuses
 * @var array $results
 * @var int $selectedCampusId
 * @var object|null $currentCampus
 * @var string $selectedLifestyle
 */
$campuses = $campuses ?? []; // Ensure $campuses is always an array to prevent "Undefined variable" errors
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Pencarian Kost Palangka Raya</title>

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

        /* Reset pembungkus icon kustom Leaflet agar menyatu dengan peta */
        .custom-campus-marker,
        .custom-kost-marker {
            background: none;
            border: none;
        }
    </style>
</head>

<body class="bg-[#0f172a] text-slate-200 min-h-screen dynamic-ui antialiased">

    <div class="container mx-auto px-4 py-10 max-w-5xl">

        <header class="mb-10 text-center border-b border-slate-800 pb-8">
            <h1 class="text-4xl font-extrabold text-white tracking-tight sm:text-5xl">
                Sistem Pendukung Keputusan <span class="text-teal-400">Rekomendasi Kost</span>
            </h1>
            <!-- <p class="text-slate-400 mt-3 text-base max-w-2xl mx-auto font-light">
                Komputasi matriks keputusan keputusan objektif terintegrasi GIS dengan visualisasi fasilitas fisik waktu-nyata.
            </p> -->
        </header>

        <div class="bg-[#1e293b] p-6 rounded-2xl shadow-xl border border-slate-700/60 mb-8">
            <form method="GET" action="<?= base_url('/') ?>" class="grid grid-cols-1 md:grid-cols-3 items-end gap-5">

                <div>
                    <label for="campus_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pusat Kampus Patokan</label>
                    <select name="campus_id" id="campus_id" class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm font-medium">
                        <?php foreach ($campuses as $campus): ?>
                            <option value="<?= $campus['id'] ?>" <?= $campus['id'] == $selectedCampusId ? 'selected' : '' ?>>
                                <?= esc($campus['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="lifestyle" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Preset</label>
                    <select name="lifestyle" id="lifestyle" class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm font-medium">
                        <option value="default" <?= $selectedLifestyle === 'default' ? 'selected' : '' ?>>Standar Berimbang Sistem</option>
                        <option value="mendang_mending" <?= $selectedLifestyle === 'mendang_mending' ? 'selected' : '' ?>>Kaum Mendang-Mending (Ekonomis & Dekat)</option>
                        <option value="anak_sultan" <?= $selectedLifestyle === 'anak_sultan' ? 'selected' : '' ?>>Anak Sultan (Fokus Aman & Mewah)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-bold px-6 py-3 rounded-xl shadow-lg shadow-teal-500/20 transition-all duration-200 uppercase text-xs tracking-wider cursor-pointer">
                    Mulai Pencarian
                </button>
            </form>
        </div>

        <div class="bg-[#1e293b] border border-slate-700/60 p-4 rounded-2xl shadow-xl mb-8">
            <h2 class="text-sm font-bold uppercase tracking-widest text-slate-300 mb-4 flex items-center gap-2">
                pemetaan
            </h2>
            <div id="map" class="h-96 rounded-xl border border-slate-900 shadow-inner z-10"></div>
        </div>

        <div class="bg-[#1e293b] rounded-2xl shadow-xl border border-slate-700/60 overflow-hidden">
            <div class="px-6 py-5 bg-[#172554]/20 border-b border-slate-700/60 flex justify-between items-center">
                <h2 class="text-base font-bold text-white tracking-tight">Peringkat Kelayakan Rekomendasi Kost</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[768px]">
                    <thead>
                        <tr class="bg-[#0f172a]/60 text-slate-400 uppercase text-[11px] tracking-widest font-bold border-b border-slate-700/60">
                            <th class="px-6 py-4 text-center w-24 whitespace-nowrap">Rank</th>
                            <th class="px-6 py-4">Informasi Kost & Fasilitas Terbaca</th>
                            <th class="px-6 py-4 text-right whitespace-nowrap">Harga Sewa</th>
                            <th class="px-6 py-4 text-center whitespace-nowrap">Jarak Ukur</th>
                            <th class="px-6 py-4 text-center bg-[#0f172a]/80 text-teal-400 font-bold whitespace-nowrap">Skor Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700 text-sm">
                        <?php if (!empty($results)): ?>
                            <?php $rank = 1;
                            foreach ($results as $row): ?>
                                <tr class="hover:bg-slate-800/60 transition duration-150 group">

                                    <td class="px-6 py-5 text-center font-bold whitespace-nowrap">
                                        <?php if ($rank === 1): ?>
                                            <span class="bg-gradient-to-r from-teal-400 to-emerald-400 text-slate-950 px-3 py-1 rounded-full text-[10px] font-extrabold shadow-sm uppercase tracking-wider">Top Match</span>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-mono text-base">#<?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-white text-base group-hover:text-teal-400 transition"><?= esc($row['name']) ?></div>

                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <?php if (!empty($row['features'])): ?>
                                                <?php foreach ($row['features'] as $feature): ?>
                                                    <span class="bg-[#0f172a] text-slate-300 border border-slate-700 text-[10px] px-2 py-0.5 rounded-md font-medium whitespace-nowrap">
                                                        ✓ <?= esc($feature) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-slate-500 text-[11px] italic font-light">Tanpa fasilitas penunjang tercatat</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 text-right font-semibold text-emerald-400 font-mono text-sm whitespace-nowrap">
                                        Rp <?= number_format($row['price'], 0, ',', '.') ?><span class="text-slate-400 font-sans text-xs font-normal">/bln</span>
                                    </td>

                                    <td class="px-6 py-5 text-center font-medium text-amber-400 font-mono text-sm whitespace-nowrap">
                                        <?= $row['distance'] ?> <span class="text-xs text-slate-400 font-sans font-normal">Km</span>
                                    </td>

                                    <td class="px-6 py-5 text-center font-bold bg-[#0f172a]/40 text-teal-400 font-mono text-base border-l border-slate-700 whitespace-nowrap">
                                        <?= $row['final_score'] ?>
                                    </td>
                                </tr>
                            <?php $rank++;
                            endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic font-light">Matriks data kosong. Koordinat gagal di-inject.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const campusInfo = <?= json_encode($currentCampus) ?>;
        const kostLocations = <?= json_encode($results) ?>;

        if (campusInfo) {
            const map = L.map('map').setView([campusInfo.latitude, campusInfo.longitude], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // 1. Ikon Kustom Kampus Induk (Gedung Merah - 40px)
            const campusCustomIcon = L.divIcon({
                html: `
                    <div class="flex items-center justify-center w-10 h-10 bg-rose-600 rounded-full shadow-lg border-2 border-white text-white transform -translate-x-1/2 -translate-y-1/2 hover:scale-110 transition duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                `,
                className: 'custom-campus-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                popupAnchor: [0, -20]
            });

            const campusMarker = L.marker([campusInfo.latitude, campusInfo.longitude], {
                icon: campusCustomIcon
            }).addTo(map);
            campusMarker.bindPopup(`<div class="text-slate-900 font-sans p-1"><b class="text-sm block uppercase text-rose-600">${campusInfo.name}</b><span class="text-slate-500 text-xs">Titik Pusat Ukur Radius</span></div>`).openPopup();

            // Pemetaan Seluruh Titik Lokasi Alternatif Kost (Menggunakan Ikon Rumah Kustom)
            if (kostLocations.length > 0) {
                kostLocations.forEach(function(kost, index) {
                    if (kost.latitude && kost.longitude) {

                        // 2. Ikon Kustom Khusus Kost (Lingkaran Sian/Teal dengan Simbol Rumah - 32px)
                        const kostCustomIcon = L.divIcon({
                            html: `
                                <div class="flex items-center justify-center w-8 h-8 bg-teal-600 rounded-full shadow-md border-2 border-white text-white transform -translate-x-1/2 -translate-y-1/2 hover:scale-115 hover:bg-teal-500 transition duration-150 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                            `,
                            className: 'custom-kost-marker',
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                            popupAnchor: [0, -16]
                        });

                        const marker = L.marker([kost.latitude, kost.longitude], {
                            icon: kostCustomIcon
                        }).addTo(map);

                        let facilityList = '';
                        if (kost.features.length > 0) {
                            kost.features.forEach(f => {
                                facilityList += `<span style="background:#f1f5f9; color:#475569; padding:2px 6px; border-radius:4px; font-size:10px; margin-right:4px; display:inline-block; margin-top:4px;">✓ ${f}</span>`;
                            });
                        } else {
                            facilityList = '<i style="color:#94a3b8; font-size:11px;">Tanpa fasilitas</i>';
                        }

                        const popupContent = `
                            <div class="text-slate-900 font-sans text-xs" style="min-width:180px;">
                                <b class="text-sm block text-teal-600 border-b pb-1 mb-1" style="font-size:13px;">${kost.name}</b>
                                <div style="margin-bottom:6px;"><b>Peringkat Ke-</b>: <span style="background:#ccfbf1; color:#0d9488; font-weight:bold; padding:1px 6px; border-radius:10px;">#${index + 1}</span></div>
                                <b>Jarak Radius</b>: <span class="text-amber-600 font-mono font-bold">${kost.distance} Km</span><br>
                                <b>Skor SPK Akhir</b>: <span class="text-teal-600 font-mono font-bold">${kost.final_score}</span>
                                <div style="margin-top:6px; border-top:1px solid #e2e8f0; padding-top:4px;">
                                    <b>Fasilitas Terdata:</b><br>${facilityList}
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupContent);
                    }
                });
            }
        }
    </script>
</body>

</html>