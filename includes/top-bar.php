<?php
// Cek apakah user sudah login atau belum
if (!isset($_SESSION['user_id'])) {

    // Jika user BELUM login, akan dialihkan ke halaman login
    header('Location: /fomo/login/');
} //Jika user SUDAH login, maka user bisa mengakses

?>

<div class="top-bar">
    <div class="logo">
        <a href="/fomo/">
            <h1 style="font-size: 40px; color: #2c6ac9;">fomo</h1>
        </a>
    </div>
    <div class="search-bar">
        <form action="/fomo/search/" method="get">
            <input type="text" name="s" id="search" placeholder="Cari..." minlength="<?php echo MIN_SEARCH_LENGTH ?>"
                value="<?php echo isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '' ?>" required>
        </form>
    </div>
    <div class="exit">
        <form action="/fomo/config/end_session.php" method="post">
            <button type="submit" class="exit-button"></button>
        </form>
    </div>
</div>