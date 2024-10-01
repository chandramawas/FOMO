<?php
//Mengubah semua data session menjadi array
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hapus Session
session_destroy();

// Redirect ke login page
header("Location: /FOMO/login/");
exit();
?>