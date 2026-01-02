<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login');
    exit();
}

$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='dipinjam'"))['total'];
$total_terlambat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status='terlambat'"))['total'];

$peminjaman_query = mysqli_query($conn, "
    SELECT p.*, a.nama_lengkap, b.judul 
    FROM peminjaman p
    JOIN anggota a ON p.id_anggota = a.id_anggota
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.status = 'dipinjam'
    ORDER BY p.tanggal_pinjam DESC
    LIMIT 5
");

if(isset($_POST['tambah_buku'])) {
    $judul = escape($conn, $_POST['judul']);
    $pengarang = escape($conn, $_POST['pengarang']);
    $penerbit = escape($conn, $_POST['penerbit']);
    $tahun = escape($conn, $_POST['tahun_terbit']);
    $isbn = escape($conn, $_POST['isbn']);
    $kategori = escape($conn, $_POST['id_kategori']);
    $jumlah = escape($conn, $_POST['jumlah_total']);
    $lokasi = escape($conn, $_POST['lokasi_rak']);
    
    $foto = NULL;
    $upload_success = true;

    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['foto']['size'];
   
        if(!in_array(strtolower($filetype), $allowed)) {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
            $upload_success = false;
        } elseif($filesize > 5000000) { 
            $error = "Ukuran file terlalu besar. Maksimal 5MB.";
            $upload_success = false;
        } else {
            $newname = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = '../uploads/buku/' . $newname;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $newname;
            } else {
                $error = "Gagal upload foto.";
                $upload_success = false;
            }
        }
    }
    
    if($upload_success) {
        $query = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, id_kategori, jumlah_total, jumlah_tersedia, lokasi_rak, foto) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun', '$isbn', '$kategori', '$jumlah', '$jumlah', '$lokasi', " . ($foto ? "'$foto'" : "NULL") . ")";
        mysqli_query($conn, $query);
        header('Location: index?success=buku_ditambahkan');
        exit();
    }
}

if(isset($_GET['kembalikan'])) {
    $id = escape($conn, $_GET['kembalikan']);
    $pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_peminjaman='$id'"));
    mysqli_query($conn, "UPDATE peminjaman SET status='dikembalikan', tanggal_dikembalikan=CURDATE() WHERE id_peminjaman='$id'");
    mysqli_query($conn, "UPDATE buku SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id_buku='{$pinjam['id_buku']}'");
    header('Location: index?success=buku_dikembalikan');
    exit();
}

$kategori_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpinfo</title>
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
            <a href="index" class="flex items-center gap-3 bg-blue-600 text-white px-4 py-3 rounded-xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="buku" class="flex items-center gap-3 text-slate-400 hover:bg-slate-800 hover:text-white px-4 py-3 rounded-xl transition-all">
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

    <main class="flex-1 p-4 md:p-8">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-slate-800">Ringkasan Statistik</h2>
                <p class="text-slate-500">Halo, <?php echo $_SESSION['nama_lengkap']; ?>. Berikut adalah aktivitas hari ini.</p>
            </div>
            <a href="../index" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                Lihat Beranda
            </a>
        </header>

        <?php if(isset($_GET['success'])): ?>
        <div class="mb-6 flex items-center bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-medium">
                <?php 
                if($_GET['success'] == 'buku_ditambahkan') echo 'Buku baru berhasil masuk ke koleksi.';
                if($_GET['success'] == 'buku_dikembalikan') echo 'Status pengembalian buku berhasil diproses.';
                ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="bg-blue-50 w-10 h-10 rounded-lg flex items-center justify-center text-blue-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Total Koleksi</p>
                <p class="text-2xl font-bold text-slate-800"><?php echo $total_buku; ?></p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="bg-green-50 w-10 h-10 rounded-lg flex items-center justify-center text-green-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Anggota Aktif</p>
                <p class="text-2xl font-bold text-slate-800"><?php echo $total_anggota; ?></p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="bg-orange-50 w-10 h-10 rounded-lg flex items-center justify-center text-orange-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Dipinjam</p>
                <p class="text-2xl font-bold text-slate-800"><?php echo $total_dipinjam; ?></p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                <div class="bg-red-50 w-10 h-10 rounded-lg flex items-center justify-center text-red-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Terlambat</p>
                <p class="text-2xl font-bold text-slate-800"><?php echo $total_terlambat; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Koleksi Baru</h3>
                </div>
                <form method="POST" action="" enctype="multipart/form-data" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Judul Buku</label>
                            <input type="text" name="judul" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pengarang</label>
                            <input type="text" name="pengarang" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Penerbit</label>
                            <input type="text" name="penerbit" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">ISBN</label>
                            <input type="text" name="isbn" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                            <select name="id_kategori" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" required>
                                <option value="">Pilih...</option>
                                <?php 
                                mysqli_data_seek($kategori_query, 0);
                                while($kat = mysqli_fetch_assoc($kategori_query)): 
                                ?>
                                    <option value="<?php echo $kat['id_kategori']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                            <input type="number" name="jumlah_total" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lokasi Rak</label>
                            <input type="text" name="lokasi_rak" placeholder="Misal: A-01" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cover Buku</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-blue-400 transition-colors cursor-pointer bg-slate-50">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    <div class="flex text-sm text-slate-600">
                                        <input type="file" name="foto" class="w-full text-xs">
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG up to 5MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="tambah_buku" class="mt-6 w-full py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">Simpan Koleksi</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Peminjaman Terbaru</h3>
                </div>
                <div class="flex-1 p-6 overflow-y-auto max-h-[600px] space-y-4">
                    <?php if(mysqli_num_rows($peminjaman_query) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($peminjaman_query)): ?>
                            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50/30 hover:bg-slate-50 transition-colors">
                                <p class="text-slate-800 font-bold leading-tight"><?php echo $p['judul']; ?></p>
                                <p class="text-blue-600 text-xs font-medium mt-1 uppercase tracking-tight"><?php echo $p['nama_lengkap']; ?></p>
                                <div class="flex justify-between items-end mt-3">
                                    <div class="text-[10px] text-slate-400">
                                        <p>Pinjam: <?php echo date('d M', strtotime($p['tanggal_pinjam'])); ?></p>
                                        <p>Deadline: <?php echo date('d M', strtotime($p['tanggal_kembali'])); ?></p>
                                    </div>
                                    <a href="?kembalikan=<?php echo $p['id_peminjaman']; ?>" 
                                       onclick="return confirm('Konfirmasi pengembalian?')"
                                       class="px-3 py-1.5 bg-green-100 text-green-700 text-xs font-bold rounded-lg hover:bg-green-600 hover:text-white transition-all">
                                        Selesai
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <p class="text-slate-400 text-sm italic">Tidak ada peminjaman aktif.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>