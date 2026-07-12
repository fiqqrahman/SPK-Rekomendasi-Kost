<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Owner - Portal Mitra Kost</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-[#0f172a] text-slate-200 min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="w-full max-w-md bg-[#1e293b] p-8 rounded-2xl shadow-2xl border border-slate-700/60">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Portal <span class="text-teal-400">Pemilik Kost</span></h2>
            <p class="text-slate-400 text-xs mt-1 font-light">Masukkan kredensial terdaftar untuk mengakses control panel properti.</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-3.5 rounded-xl mb-5 font-medium flex items-center gap-2">
                <span>⚠️</span> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('/auth/attempt') ?>" class="space-y-5">

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Alamat Email Mitra</label>
                <input type="email" id="email" name="email" required placeholder="owner@gmail.com"
                    class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-700 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm font-mono">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi Akun</label>
                <input type="password" id="password" name="password" required placeholder="••••••••"
                    class="w-full bg-[#0f172a] border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-700 focus:outline-none focus:border-teal-400 focus:ring-1 focus:ring-teal-400 transition text-sm">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-slate-950 font-extrabold px-6 py-3.5 rounded-xl shadow-lg shadow-teal-500/10 transition duration-200 uppercase text-xs tracking-wider cursor-pointer font-sans pt-3">
                Otorisasi Masuk Ke Sistem
            </button>
        </form>
    </div>

</body>

</html>