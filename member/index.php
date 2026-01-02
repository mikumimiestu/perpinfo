<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'member') {
    header('Location: ../login');
    exit();
}

$id_anggota = $_SESSION['user_id'];

// Logika Pinjam Buku
if(isset($_GET['pinjam'])) {
    $id_buku = escape($conn, $_GET['pinjam']);
    $buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id_buku'"));
    
    if($buku['jumlah_tersedia'] > 0) {
        $cek = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_anggota='$id_anggota' AND id_buku='$id_buku' AND status='dipinjam'");
        
        if(mysqli_num_rows($cek) == 0) {
            $tanggal_pinjam = date('Y-m-d');
            $tanggal_kembali = date('Y-m-d', strtotime('+7 days'));
            mysqli_query($conn, "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali) VALUES ('$id_anggota', '$id_buku', '$tanggal_pinjam', '$tanggal_kembali')");
            mysqli_query($conn, "UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id_buku='$id_buku'");
            header('Location: index.php?success=pinjam');
            exit();
        } else {
            header('Location: index.php?error=sudah_pinjam');
            exit();
        }
    } else {
        header('Location: index.php?error=tidak_tersedia');
        exit();
    }
}

// Logika Perpanjang
if(isset($_GET['perpanjang'])) {
    $id_peminjaman = escape($conn, $_GET['perpanjang']);
    mysqli_query($conn, "UPDATE peminjaman SET tanggal_kembali = DATE_ADD(tanggal_kembali, INTERVAL 7 DAY) WHERE id_peminjaman='$id_peminjaman' AND id_anggota='$id_anggota'");
    header('Location: index.php?success=perpanjang');
    exit();
}

// Query Data
$peminjaman_aktif = mysqli_query($conn, "
    SELECT p.*, b.judul, b.pengarang 
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.id_anggota = '$id_anggota' AND p.status = 'dipinjam'
    ORDER BY p.tanggal_pinjam DESC
");

$riwayat = mysqli_query($conn, "
    SELECT p.*, b.judul, b.pengarang 
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.id_anggota = '$id_anggota' AND p.status = 'dikembalikan'
    ORDER BY p.tanggal_dikembalikan DESC
    LIMIT 5
");

$total_pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_anggota='$id_anggota' AND status='dipinjam'"))['total'];
$total_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE id_anggota='$id_anggota' AND status='dikembalikan'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Perpinfo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-nav { background: rgba(37, 99, 235, 0.95); backdrop-filter: blur(8px); }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-anim { animation: slideDown 0.4s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="glass-nav text-white shadow-lg sticky top-0 z-50 transition-all">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-none tracking-tight">Perpinfo</h1>
                        <p class="text-[10px] text-blue-100 uppercase tracking-widest mt-1">Dashboard Anggota</p>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center gap-6 text-sm font-semibold">
                    <a href="../index" class="hover:text-blue-200 transition">Beranda</a>
                    <a href="../katalog" class="hover:text-blue-200 transition">Katalog Buku</a>
                    <div class="h-6 w-[1px] bg-blue-400"></div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-normal text-blue-100 italic"><?php echo explode(' ', $_SESSION['nama_lengkap'])[0]; ?></span>
                        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-xl transition shadow-lg shadow-red-900/20">Keluar</a>
                    </div>
                </div>

                <button id="menu-btn" class="md:hidden p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg></button>
            </div>
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-3 animate-slideDown">
                <a href="../index" class="block px-4 py-2 hover:bg-white/10 rounded-lg">Beranda</a>
                <a href="../katalog" class="block px-4 py-2 hover:bg-white/10 rounded-lg">Katalog Buku</a>
                <a href="../logout.php" class="block px-4 py-2 bg-red-500 rounded-lg text-center">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php if(isset($_GET['success']) || isset($_GET['error'])): ?>
            <div class="alert-anim mb-6 flex items-center gap-3 p-4 rounded-2xl shadow-sm <?php echo isset($_GET['success']) ? 'bg-green-100 border border-green-200 text-green-800' : 'bg-red-100 border border-red-200 text-red-800'; ?>">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <span class="text-sm font-medium">
                    <?php 
                    if(@$_GET['success'] == 'pinjam') echo 'Buku berhasil masuk ke daftar pinjam Anda.';
                    if(@$_GET['success'] == 'perpanjang') echo 'Batas pengembalian berhasil diperbarui.';
                    if(@$_GET['error'] == 'sudah_pinjam') echo 'Anda masih meminjam buku ini.';
                    if(@$_GET['error'] == 'tidak_tersedia') echo 'Maaf, stok buku ini sedang kosong.';
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-24 h-24 bg-orange-50 rounded-full transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Peminjaman Aktif</p>
                    <h3 class="text-4xl font-extrabold text-slate-800"><?php echo $total_pinjam; ?> <span class="text-lg font-medium text-slate-400">Buku</span></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 translate-x-4 -translate-y-4 w-24 h-24 bg-green-50 rounded-full transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Selesai</p>
                    <h3 class="text-4xl font-extrabold text-slate-800"><?php echo $total_selesai; ?> <span class="text-lg font-medium text-slate-400">Buku</span></h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-800">Sedang Dipinjam</h2>
                    <a href="../katalog" class="text-sm font-bold text-blue-600 hover:underline">Cari Buku Lagi →</a>
                </div>

                <?php if(mysqli_num_rows($peminjaman_aktif) > 0): ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php while($p = mysqli_fetch_assoc($peminjaman_aktif)): 
                            $terlambat = strtotime($p['tanggal_kembali']) < strtotime(date('Y-m-d'));
                            $hari_tersisa = ceil((strtotime($p['tanggal_kembali']) - strtotime(date('Y-m-d'))) / 86400);
                        ?>
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="bg-slate-100 w-12 h-16 rounded-lg flex-shrink-0 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 leading-tight mb-1"><?php echo htmlspecialchars($p['judul']); ?></h4>
                                        <p class="text-xs text-slate-500 mb-2"><?php echo htmlspecialchars($p['pengarang']); ?></p>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="text-[10px] px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md font-bold italic">Pinjam: <?php echo date('d M', strtotime($p['tanggal_pinjam'])); ?></span>
                                            <span class="text-[10px] px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md font-bold">Kembali: <?php echo date('d M', strtotime($p['tanggal_kembali'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-4 md:pt-0">
                                    <?php if($terlambat): ?>
                                        <span class="text-[10px] font-bold text-red-600 bg-red-50 px-3 py-1 rounded-full uppercase">Terlambat!</span>
                                    <?php elseif($hari_tersisa <= 2): ?>
                                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-full uppercase">Tersisa <?php echo $hari_tersisa; ?> Hari</span>
                                    <?php endif; ?>
                                    
                                    <a href="?perpanjang=<?php echo $p['id_peminjaman']; ?>" 
                                       onclick="return confirm('Perpanjang masa pinjam 7 hari ke depan?')"
                                       class="text-xs font-bold text-blue-600 border border-blue-600 px-4 py-2 rounded-xl hover:bg-blue-600 hover:text-white transition whitespace-nowrap">
                                        Perpanjang
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white py-12 px-6 rounded-3xl border border-dashed border-slate-300 text-center">
                        <p class="text-slate-400 mb-4 italic">Anda tidak memiliki peminjaman yang sedang aktif.</p>
                        <a href="../katalog" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-blue-200">Mulai Pinjam Buku</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <h2 class="text-xl font-bold text-slate-800">Selesai Baru-baru Ini</h2>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-5">
                    <?php if(mysqli_num_rows($riwayat) > 0): ?>
                        <?php while($r = mysqli_fetch_assoc($riwayat)): ?>
                            <div class="flex items-center gap-3 group">
                                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-500 group-hover:bg-green-500 group-hover:text-white transition">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate leading-tight"><?php echo htmlspecialchars($r['judul']); ?></p>
                                    <p class="text-[10px] text-slate-400 italic">Kembali: <?php echo date('d/m/y', strtotime($r['tanggal_dikembalikan'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada riwayat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const btn = document.getElementById('menu-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    </script>
</body>
</html>