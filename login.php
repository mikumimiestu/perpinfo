<?php
require_once 'config/database.php';

$error = '';

if(isset($_POST['login'])) {
    $username = escape($conn, $_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if($user_type == 'admin') {
        $query = "SELECT * FROM admin WHERE username='$username'";
    } else {
        $query = "SELECT * FROM anggota WHERE username='$username' AND status='aktif'";
    }
    
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user_type == 'admin' ? $user['id_admin'] : $user['id_anggota'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['user_type'] = $user_type;
            
            if($user_type == 'admin') {
                header('Location: admin/index');
            } else {
                header('Location: member/index');
            }
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan atau akun tidak aktif!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="antialiased bg-gray-900 flex items-center justify-center min-h-screen p-4" 
      style="background-image: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://agincourtresources.com/wp-content/uploads/2021/09/Untitled-design-23-1.jpg'); background-size: cover; background-position: center;">

    <div class="w-full max-w-md animate-footer">
        <div class="glass rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="p-8">
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-2xl shadow-lg mb-4 transform -rotate-6 hover:rotate-0 transition-transform duration-300">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Selamat Datang</h2>
                    <p class="text-gray-500 mt-2">Silakan masuk ke akun Perpinfo Anda</p>
                </div>

                <?php if($error): ?>
                <div class="mb-6 flex items-center bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Tipe Pengguna</label>
                        <select name="user_type" class="block w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none appearance-none" required>
                            <option value="member">Anggota</option>
                            <option value="admin">Admin / Pustakawan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text" name="username" placeholder="Masukkan username" 
                                   class="block w-full pl-10 pr-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="••••••••" 
                                   class="block w-full pl-10 pr-12 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" required>
                            
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600 transition-colors">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform active:scale-[0.98] transition-all duration-150">
                        Masuk Ke Akun
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 space-y-3 text-center">
                    <p class="text-sm text-gray-600">
                        Belum punya akun? 
                        <a href="register" class="text-blue-600 hover:text-blue-500 font-bold decoration-2 underline-offset-4 hover:underline">Daftar Sekarang</a>
                    </p>
                    <a href="index" class="inline-flex items-center text-xs font-medium text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
        
        <p class="text-center text-gray-400 text-xs mt-8">
            &copy; <?php echo date('Y'); ?> Perpinfo System. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</body>
</html>