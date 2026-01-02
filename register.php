<?php
require_once 'config/database.php';

$error = '';
$success = '';

if(isset($_POST['register'])) {
    $username = escape($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = escape($conn, $_POST['nama_lengkap']);
    $email = escape($conn, $_POST['email']);
    $no_telp = escape($conn, $_POST['no_telp']);
    $alamat = escape($conn, $_POST['alamat']);
    
    // Cek username sudah ada atau belum
    $check = mysqli_query($conn, "SELECT * FROM anggota WHERE username='$username'");
    if(mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO anggota (username, password, nama_lengkap, email, no_telp, alamat) 
                  VALUES ('$username', '$password', '$nama_lengkap', '$email', '$no_telp', '$alamat')";
        
        if(mysqli_query($conn, $query)) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Registrasi gagal: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Anggota - Perpustakaan Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        input:focus, textarea:focus {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="antialiased bg-slate-900 flex items-center justify-center min-h-screen py-12 px-4" 
      style="background-image: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('https://agincourtresources.com/wp-content/uploads/2021/09/Untitled-design-23-1.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">

    <div class="w-full max-w-2xl">
        <div class="glass rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-8 md:p-12">
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-2xl mb-4 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900">Gabung Anggota</h2>
                    <p class="text-gray-500 mt-2">Lengkapi data diri Anda untuk mulai meminjam buku</p>
                </div>

                <?php if($error): ?>
                <div class="mb-6 flex items-center bg-red-50 border-l-4 border-red-500 p-4 rounded-xl animate-pulse">
                    <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <?php if($success): ?>
                <div class="mb-6 flex items-center bg-green-50 border-l-4 border-green-500 p-4 rounded-xl">
                    <div class="flex-1">
                        <p class="text-sm text-green-700 font-bold"><?php echo $success; ?></p>
                    </div>
                    <a href="login" class="ml-3 text-sm bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Login</a>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" placeholder="pilih username" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200" required>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" placeholder="••••••••" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200" required>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" placeholder="Nama sesuai identitas" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200" required>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">Email</label>
                            <input type="email" name="email" placeholder="contoh@email.com" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">No. Telepon</label>
                            <input type="text" name="no_telp" placeholder="0812xxxx" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200">
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-sm font-semibold text-gray-700 ml-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" placeholder="Tuliskan alamat domisili Anda..." 
                                      class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition-all duration-200 resize-none"></textarea>
                        </div>
                    </div>

                    <button type="submit" name="register" 
                            class="w-full py-4 px-6 mt-4 bg-green-600 text-white rounded-xl font-bold text-lg shadow-lg hover:bg-green-700 hover:shadow-green-200 transition-all duration-300 transform active:scale-95">
                        Daftar Sebagai Anggota
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between text-sm">
                    <p class="text-gray-500">Sudah punya akun? <a href="login" class="text-green-600 font-bold hover:underline">Login di sini</a></p>
                    <a href="index" class="mt-4 md:mt-0 inline-flex items-center text-gray-400 hover:text-gray-600 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
        <p class="text-center text-slate-500 text-xs mt-8">
            &copy; <?php echo date('Y'); ?> Perpinfo System - Layanan Perpustakaan Digital Terpercaya.
        </p>
    </div>
</body>
</html>