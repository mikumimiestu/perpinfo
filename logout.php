<?php
// Memulai session agar bisa mengakses data yang akan dihapus
session_start();

// Menghapus semua variabel session
$_SESSION = array();

// Jika ingin menghapus cookie session juga (opsional tapi disarankan untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session secara total
session_destroy();

// Mengarahkan pengguna kembali ke landing page (index.php)
header('Location: index');
exit();
?>