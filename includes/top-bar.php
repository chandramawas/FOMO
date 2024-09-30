<?php
// Cek apakah user sudah login atau belum
if (!isset($_SESSION['user_id'])) {

    // Jika user BELUM login, akan dialihkan ke halaman login
    header('Location: /project-sea/pages/login.php');
} //Jika user SUDAH login, maka user bisa mengakses

?>

<div class="top-bar">
    <div class="logo">
        <a href="/project-sea/">
            <h1 style="font-size: 40px; color: #2c6ac9;">FOMO</h1>
        </a>
    </div>
    <div class="search-bar">
        <form action="/project-sea/pages/search.php" method="get">
            <input type="text" name="search" id="search" placeholder="Cari..."
                minlength="<?php echo MIN_SEARCH_LENGTH ?>"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" required>
        </form>
    </div>
    <div class="exit">
        <form action="/project-sea/config/end_session.php" method="post">
            <button type="submit" class="exit-button"></button>
        </form>
    </div>
</div>