<?php
require_once 'config/database.php';

$kategori_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

// Filter
$search = isset($_GET['search']) ? escape($conn, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? escape($conn, $_GET['kategori']) : '';

// Query buku
$query = "SELECT b.*, k.nama_kategori 
          FROM buku b 
          LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
          WHERE 1=1";

if($search) {
    $query .= " AND (b.judul LIKE '%$search%' OR b.pengarang LIKE '%$search%' OR b.penerbit LIKE '%$search%')";
}

if($kategori_filter) {
    $query .= " AND b.id_kategori = '$kategori_filter'";
}

$query .= " ORDER BY b.judul ASC";
$buku_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Koleksi - Perpinfo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .book-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .book-card:hover { transform: translateY(-10px); }
        .img-zoom { transition: transform 0.6s shadow 0.6s; }
        .book-card:hover .img-zoom { transform: scale(1.1); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-blue-600/95 backdrop-blur-md text-white shadow-lg sticky top-0 z-50 transition-all">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="text-xl font-bold tracking-tight">Perpinfo</span>
            </div>
            
            <div class="hidden md:flex gap-8 items-center font-semibold text-sm">
                <a href="index" class="hover:text-blue-200 transition">Beranda</a>
                <a href="katalog" class="text-blue-100 border-b-2 border-blue-100 pb-1">Katalog</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $_SESSION['user_type']; ?>/index" class="bg-white/10 px-4 py-2 rounded-xl hover:bg-white/20 transition">Dashboard</a>
                <?php else: ?>
                    <a href="login" class="bg-white text-blue-600 px-6 py-2.5 rounded-xl hover:bg-slate-100 shadow-xl transition">Masuk</a>
                <?php endif; ?>
            </div>
            <button id="mobile-btn" class="md:hidden p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16m-7 6h7" stroke-width="2" stroke-linecap="round"/></svg></button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-700 px-4 py-6 space-y-4">
            <a href="index" class="block">Beranda</a>
            <a href="katalog" class="block font-bold">Katalog</a>
            <a href="login" class="block">Masuk</a>
        </div>
    </nav>

    <header class="bg-blue-600 text-white pt-12 pb-24">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-black mb-2 tracking-tight">Katalog Pustaka</h1>
            <p class="text-blue-100/80 max-w-xl">Temukan referensi terbaik dari ribuan koleksi buku fisik dan digital kami.</p>
        </div>
    </header>

    <main class="container mx-auto px-4 -mt-12">
        <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-slate-100 mb-12">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5 relative">
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari judul atau penulis..." class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition">
                    <svg class="w-5 h-5 absolute left-4 top-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="md:col-span-4">
                    <select name="kategori" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition cursor-pointer">
                        <option value="">Semua Kategori</option>
                        <?php mysqli_data_seek($kategori_query, 0); while($kat = mysqli_fetch_assoc($kategori_query)): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>" <?php echo $kategori_filter == $kat['id_kategori'] ? 'selected' : ''; ?>>
                                <?php echo $kat['nama_kategori']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-2xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform active:scale-95">
                        Tampilkan Hasil
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-20">
            <?php if(mysqli_num_rows($buku_result) > 0): ?>
                <?php while($buku = mysqli_fetch_assoc($buku_result)): ?>
                    <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm book-card flex flex-col group">
                        <div class="h-72 overflow-hidden bg-slate-100 relative">
                            <?php if($buku['foto']): ?>
                                <img src="uploads/buku/<?php echo $buku['foto']; ?>" class="w-full h-full object-cover img-zoom">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400">
                                    <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2"/></svg>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-white/90 backdrop-blur text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-lg">
                                    <?php echo htmlspecialchars($buku['nama_kategori']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col text-center">
                            <h3 class="font-bold text-slate-800 text-lg mb-1 leading-tight line-clamp-2"><?php echo htmlspecialchars($buku['judul']); ?></h3>
                            <p class="text-slate-400 text-xs mb-4 font-medium"><?php echo htmlspecialchars($buku['pengarang']); ?></p>
                            
                            <div class="flex items-center justify-center gap-4 mb-6 text-[10px] font-bold text-slate-500 bg-slate-50 py-2 rounded-xl">
                                <div class="flex items-center gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full <?php echo $buku['jumlah_tersedia'] > 0 ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                                    <?php echo $buku['jumlah_tersedia'] > 0 ? $buku['jumlah_tersedia'].' Tersedia' : 'Kosong'; ?>
                                </div>
                                <div class="w-[1px] h-3 bg-slate-200"></div>
                                <div>Rak: <?php echo htmlspecialchars($buku['lokasi_rak']); ?></div>
                            </div>

                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'member'): ?>
                                <?php if($buku['jumlah_tersedia'] > 0): ?>
                                    <a href="member/index?pinjam=<?php echo $buku['id_buku']; ?>" class="mt-auto block w-full py-3 bg-blue-600 text-white rounded-2xl font-bold text-xs hover:bg-blue-700 transition shadow-lg shadow-blue-100 uppercase tracking-widest">Pinjam Buku</a>
                                <?php else: ?>
                                    <button disabled class="mt-auto block w-full py-3 bg-slate-100 text-slate-400 rounded-2xl font-bold text-xs cursor-not-allowed uppercase tracking-widest">Tidak Tersedia</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login" class="mt-auto block w-full py-3 bg-slate-900 text-white rounded-2xl font-bold text-xs hover:bg-slate-800 transition uppercase tracking-widest">Login Member</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center">
                    <h3 class="text-2xl font-bold text-slate-800 mb-2">Pencarian tidak ditemukan</h3>
                    <p class="text-slate-500 italic">Silakan gunakan kata kunci atau kategori lain.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-slate-900 text-white py-12 text-center">
        <p class="text-slate-500 text-xs uppercase tracking-widest italic">&copy; 2026 Perpinfo Digital Platform</p>
    </nav>

    <script>
        const btn = document.getElementById('mobile-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    </script>
</body>
</html>