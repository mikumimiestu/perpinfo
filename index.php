<?php
session_start();
require_once 'config/database.php';

$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota WHERE status='aktif'"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpinfo - Literasi Tanpa Batas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .hero-mesh {
            background-color: #2563eb;
            background-image: 
                radial-gradient(at 0% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225, 39%, 23%, 1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(225, 39%, 33%, 1) 0, transparent 50%);
        }
        .stat-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .stat-card:hover { transform: translateY(-10px) scale(1.02); }
    </style>
</head>
<body class="bg-slate-50">

    <nav class="bg-blue-600/95 backdrop-blur-md text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="text-2xl font-extrabold tracking-tight">Perpinfo</span>
            </div>
            
            <div class="hidden md:flex gap-8 items-center font-semibold text-sm">
                <a href="index" class="text-blue-100 border-b-2 border-blue-100 pb-1">Beranda</a>
                <a href="katalog" class="hover:text-blue-200 transition">Katalog</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $_SESSION['user_type']; ?>/index" class="bg-white/10 px-4 py-2 rounded-xl hover:bg-white/20 transition">Dashboard</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-5 py-2 rounded-xl shadow-lg transition">Keluar</a>
                <?php else: ?>
                    <a href="login" class="hover:text-blue-200 transition">Masuk</a>
                    <a href="register" class="bg-white text-blue-600 px-6 py-2.5 rounded-xl hover:bg-slate-100 shadow-xl transition">Gabung Sekarang</a>
                <?php endif; ?>
            </div>

            <button id="mobile-btn" class="md:hidden p-2"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16m-7 6h7" stroke-width="2" stroke-linecap="round"/></svg></button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-700 px-4 py-6 space-y-4 shadow-inner">
            <a href="index" class="block font-bold">Beranda</a>
            <a href="katalog" class="block">Katalog</a>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="login" class="block">Masuk</a>
                <a href="register" class="block bg-white text-blue-600 p-3 rounded-xl text-center font-bold">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-mesh text-white py-20 lg:py-32 relative overflow-hidden">
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <span class="inline-block px-4 py-1.5 bg-white/10 backdrop-blur-md rounded-full text-xs font-bold tracking-widest uppercase mb-6 border border-white/20">Akses Literasi Masa Kini</span>
                <h1 class="text-5xl md:text-7xl font-extrabold mb-8 leading-tight tracking-tighter">Buka Jendela Dunia Dari Genggaman Anda.</h1>
                <p class="text-lg md:text-xl text-blue-100/80 mb-12 leading-relaxed">Platform perpustakaan digital terpadu yang menyediakan akses ribuan judul buku, referensi ilmiah, dan jurnal eksklusif secara instan.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="katalog" class="px-10 py-4 bg-white text-blue-700 rounded-2xl font-bold text-lg hover:shadow-2xl hover:shadow-white/20 transition transform hover:-translate-y-1">Mulai Menjelajah</a>
                    <a href="register" class="px-10 py-4 bg-blue-500/30 backdrop-blur-md border border-white/30 rounded-2xl font-bold text-lg hover:bg-white/10 transition">Jadi Anggota</a>
                </div>
            </div>
        </div>
    </section>

    <section class="container mx-auto px-4 -mt-16 relative z-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-10 rounded-3xl shadow-xl stat-card border-b-8 border-blue-500 flex flex-col items-center">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mb-4">Koleksi Digital</p>
                <h2 class="text-6xl font-black text-slate-800 tracking-tighter mb-2"><?php echo number_format($total_buku); ?></h2>
                <p class="text-slate-500 font-medium italic text-sm">Judul Buku Tersedia</p>
            </div>
            <div class="bg-white p-10 rounded-3xl shadow-xl stat-card border-b-8 border-green-500 flex flex-col items-center">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mb-4">Komunitas</p>
                <h2 class="text-6xl font-black text-slate-800 tracking-tighter mb-2"><?php echo number_format($total_anggota); ?></h2>
                <p class="text-slate-500 font-medium italic text-sm">Pembaca Terdaftar</p>
            </div>
            <div class="bg-white p-10 rounded-3xl shadow-xl stat-card border-b-8 border-orange-500 flex flex-col items-center text-center">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] mb-4">Sirkulasi</p>
                <h2 class="text-6xl font-black text-slate-800 tracking-tighter mb-2"><?php echo number_format($total_dipinjam); ?></h2>
                <p class="text-slate-500 font-medium italic text-sm">Buku Sedang Dibaca</p>
            </div>
        </div>
    </section>

    <section class="py-32 bg-slate-50 overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-black text-slate-800 mb-4">Mengapa Memilih Kami?</h2>
                <p class="text-slate-500 max-w-xl mx-auto">Sistem manajemen perpustakaan yang dirancang untuk kecepatan dan kenyamanan Anda.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-3">Pencarian Kilat</h4>
                    <p class="text-slate-500 text-sm leading-relaxed">Temukan buku berdasarkan kategori, pengarang, atau ISBN dalam hitungan detik.</p>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-16 h-16 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-3">Reservasi 24/7</h4>
                    <p class="text-slate-500 text-sm leading-relaxed">Pinjam dan perpanjang masa peminjaman kapan saja melalui portal anggota.</p>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-3">Riwayat Digital</h4>
                    <p class="text-slate-500 text-sm leading-relaxed">Pantau daftar buku yang pernah Anda baca dan kelola daftar keinginan Anda.</p>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-16 h-16 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-orange-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-slate-800 mb-3">Notifikasi Pintar</h4>
                    <p class="text-slate-500 text-sm leading-relaxed">Dapatkan pengingat otomatis sebelum batas waktu peminjaman berakhir.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 py-20 border-t border-slate-800">
        <div class="container mx-auto px-4 text-center">
            <div class="flex flex-col items-center mb-10">
                <div class="bg-blue-600 p-3 rounded-2xl text-white mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Perpinfo</h3>
                <p class="text-slate-500 text-sm max-w-sm">Memberdayakan masyarakat melalui akses informasi digital yang terbuka dan modern.</p>
            </div>
            
            <div class="flex justify-center gap-8 mb-12 text-sm font-medium text-slate-400">
                <a href="katalog" class="hover:text-white transition">Koleksi</a>
                <a href="login" class="hover:text-white transition">Akses Akun</a>
                <a href="#" class="hover:text-white transition">Bantuan</a>
            </div>
            
            <p class="text-slate-600 text-[10px] uppercase tracking-[0.3em]">&copy; 2026 Perpinfo Management System. Padang, West Sumatra.</p>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('mobile-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    </script>
</body>
</html>