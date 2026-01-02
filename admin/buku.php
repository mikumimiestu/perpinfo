<?php
require_once '../config/database.php';

// Proteksi Halaman
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login');
    exit();
}

// Fitur Hapus Buku
if(isset($_GET['hapus'])) {
    $id = escape($conn, $_GET['hapus']);
    
    // Ambil info foto untuk dihapus dari folder
    $file_query = mysqli_query($conn, "SELECT foto FROM buku WHERE id_buku='$id'");
    $file_data = mysqli_fetch_assoc($file_query);
    if($file_data['foto'] && file_exists('../uploads/buku/' . $file_data['foto'])) {
        unlink('../uploads/buku/' . $file_data['foto']);
    }
    
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku='$id'");
    header('Location: buku?success=hapus');
    exit();
}

// Fitur Pencarian
$search = isset($_GET['search']) ? escape($conn, $_GET['search']) : '';
$where = $search ? "WHERE b.judul LIKE '%$search%' OR b.pengarang LIKE '%$search%' OR b.isbn LIKE '%$search%'" : "";

$query = "SELECT b.*, k.nama_kategori 
          FROM buku b 
          LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
          $where 
          ORDER BY b.id_buku DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Perpinfo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-white sticky top-0 h-screen">
        <div class="p-6">
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 p-2 rounded-lg text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="text-xl font-bold tracking-tight">Perpinfo</span>
            </div>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="index" class="flex items-center gap-3 text-slate-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="buku" class="flex items-center gap-3 bg-blue-600 text-white px-4 py-3 rounded-xl transition-all shadow-lg shadow-blue-900/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kelola Buku
            </a>
            <a href="anggota" class="flex items-center gap-3 text-slate-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Anggota
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <a href="../logout" class="flex items-center gap-3 text-red-400 hover:bg-red-500/10 px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <main class="flex-1 p-4 md:p-8 overflow-x-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Koleksi Buku</h2>
                <p class="text-slate-500">Manajemen stok dan data pustaka Perpinfo</p>
            </div>
            <div class="flex gap-3">
                <form action="" method="GET" class="relative">
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari judul/ISBN..." 
                           class="pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none w-64 transition-all">
                    <svg class="w-5 h-5 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>
                <a href="index" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </a>
            </div>
        </div>

        <?php if(isset($_GET['success']) && $_GET['success'] == 'hapus'): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>
            <span class="font-medium">Buku telah berhasil dihapus dari sistem.</span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Buku</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Stok</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Lokasi Rak</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-16 rounded-md bg-slate-200 overflow-hidden flex-shrink-0 shadow-sm">
                                            <?php if($row['foto']): ?>
                                                <img src="../uploads/buku/<?php echo $row['foto']; ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-slate-400 uppercase font-bold text-[10px]">No Cover</div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-tight mb-1"><?php echo $row['judul']; ?></p>
                                            <p class="text-xs text-slate-500"><?php echo $row['pengarang']; ?> • <?php echo $row['penerbit']; ?></p>
                                            <p class="text-[10px] text-blue-500 font-mono mt-1">ISBN: <?php echo $row['isbn']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                                        <?php echo $row['nama_kategori'] ?? 'Uncategorized'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col">
                                        <span class="text-sm font-bold <?php echo $row['jumlah_tersedia'] > 0 ? 'text-slate-800' : 'text-red-500'; ?>">
                                            <?php echo $row['jumlah_tersedia']; ?>
                                        </span>
                                        <span class="text-[10px] text-slate-400">Tersedia dari <?php echo $row['jumlah_total']; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 text-slate-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span class="text-sm font-medium"><?php echo $row['lokasi_rak']; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="edit_buku?id=<?php echo $row['id_buku']; ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <a href="?hapus=<?php echo $row['id_buku']; ?>" onclick="return confirm('Hapus buku ini secara permanen?')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus Buku">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="bg-slate-100 p-4 rounded-full mb-4">
                                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-800">Buku tidak ditemukan</h3>
                                        <p class="text-slate-500">Coba kata kunci lain atau periksa daftar koleksi Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                <p class="text-xs text-slate-500 italic">Menampilkan total <?php echo mysqli_num_rows($result); ?> judul buku.</p>
            </div>
        </div>
    </main>
</body>
</html>