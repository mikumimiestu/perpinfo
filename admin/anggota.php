<?php
require_once '../config/database.php';

// Proteksi Halaman
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login');
    exit();
}

// Fitur Update Status (Aktif/Nonaktif)
if(isset($_GET['toggle_status'])) {
    $id = escape($conn, $_GET['id']);
    $status_baru = $_GET['toggle_status'] == 'aktif' ? 'nonaktif' : 'aktif';
    mysqli_query($conn, "UPDATE anggota SET status='$status_baru' WHERE id_anggota='$id'");
    header('Location: anggota?success=status_updated');
    exit();
}

// Fitur Hapus Anggota
if(isset($_GET['hapus'])) {
    $id = escape($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota='$id'");
    header('Location: anggota?success=hapus');
    exit();
}

// Fitur Pencarian
$search = isset($_GET['search']) ? escape($conn, $_GET['search']) : '';
$where = $search ? "WHERE nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%'" : "";

$query = "SELECT * FROM anggota $where ORDER BY nama_lengkap ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Anggota - Perpinfo</title>
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
            <a href="buku" class="flex items-center gap-3 text-slate-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kelola Buku
            </a>
            <a href="anggota" class="flex items-center gap-3 bg-blue-600 text-white px-4 py-3 rounded-xl transition-all shadow-lg shadow-blue-900/20">
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
                <h2 class="text-3xl font-bold text-slate-800">Daftar Anggota</h2>
                <p class="text-slate-500">Kelola informasi dan akses akun anggota perpustakaan</p>
            </div>
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari nama/username..." 
                       class="pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none w-64 md:w-80 transition-all shadow-sm">
                <svg class="w-5 h-5 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>
        </div>

        <?php if(isset($_GET['success'])): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-xl flex items-center gap-3 animate-fade-in">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-medium">Perubahan data anggota berhasil disimpan.</span>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Anggota</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kontak</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold uppercase">
                                            <?php echo substr($row['nama_lengkap'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-tight"><?php echo $row['nama_lengkap']; ?></p>
                                            <p class="text-xs text-slate-500">@<?php echo $row['username']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="text-slate-700"><?php echo $row['email'] ?: '-'; ?></p>
                                        <p class="text-xs text-slate-400"><?php echo $row['no_telp'] ?: '-'; ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-600 truncate max-w-[200px]" title="<?php echo $row['alamat']; ?>">
                                        <?php echo $row['alamat'] ?: '-'; ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($row['status'] == 'aktif'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-500 text-xs font-bold rounded-full">
                                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="?toggle_status=<?php echo $row['status']; ?>&id=<?php echo $row['id_anggota']; ?>" 
                                           class="p-2 <?php echo $row['status'] == 'aktif' ? 'text-orange-400 hover:text-orange-600 hover:bg-orange-50' : 'text-green-400 hover:text-green-600 hover:bg-green-50'; ?> rounded-lg transition-all" 
                                           title="<?php echo $row['status'] == 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'; ?>">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </a>
                                        <a href="?hapus=<?php echo $row['id_anggota']; ?>" 
                                           onclick="return confirm('Hapus anggota ini? Data peminjaman mungkin akan terpengaruh.')" 
                                           class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus Permanen">
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
                                        <h3 class="text-lg font-bold text-slate-800">Tidak ada anggota</h3>
                                        <p class="text-slate-500">Daftar anggota akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>