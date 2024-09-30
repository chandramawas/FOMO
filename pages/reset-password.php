<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/db/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/sweet_alert.php";
?>

<?php
// Cek apakah token ada atau tidak di url
if (isset($_GET['token'])) {
    //Jika ada, selanjutnya cek apakah token benar
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT username FROM users WHERE resetToken = ? AND token_expiration > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        ?>
                <script>alert("Token salah atau sudah kadaluarsa."); location.href = "/project-sea/forgotpassword/";</script>
                <?php
    } else {
        $row = $result->fetch_assoc();
        $username = $row['username'];
    }

} else {
    // Jika TIDAK ada, akan dialihkan ke halaman login
    ?>
        <script>location.href = "/project-sea/login/";</script>
        <?php
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Reddit+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/project-sea/css/start.css">
    <title>
        Reset Password -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="container">
        <div class="left">
            <img src="/project-sea/images/poster.png">
        </div>
        <div class="right">
            <div class="logo">
                <h1 style="font-size: 40px; color: #2c6ac9;">FOMO</h1>
            </div>
            <div class="form-container">
                <form action="" method="POST">
                    <h2 style="margin-bottom: 12px;">Buat Ulang Password</h2>
                    <input type="hidden" name="token" id="token"
                        value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    <div class="input-container read">
                        <img class="icon" src="/project-sea/images/user.png" alt="User">
                        <input type="text" name="username" id="username" placeholder="Username"
                            value="<?php echo $username ?>" readonly>
                    </div>
                    <div class="input-container">
                        <img class="icon" src="/project-sea/images/password.png" alt="Password">
                        <input type="password" name="password" id="password" placeholder="Buat Password Baru"
                            minlength="<?php echo MIN_PASSWORD_LENGTH ?>" maxlength="<?php echo MAX_PASSWORD_LENGTH ?>"
                            required>
                        <img id="togglePassword" src="/project-sea/images/eyes-closed.png" alt="Toggle Password">
                    </div>
                    <div class="input-container" style="margin-bottom: 16px;">
                        <img class="icon" src="/project-sea/images/password.png" alt="Password">
                        <input type="password" name="confirmPassword" id="confirmPassword"
                            placeholder="Konfirmasi Password Baru" minlength="<?php echo MIN_PASSWORD_LENGTH ?>"
                            maxlength="<?php echo MAX_PASSWORD_LENGTH ?>" required>
                        <img id="toggleConfirmPassword" src="/project-sea/images/eyes-closed.png" alt="Toggle Password">
                    </div>
                    <button type="submit" class="color-button">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/project-sea/js/script.js"></script>
</body>

</html>

<?php
//REQUEST POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //Update password baru
    $stmt = $conn->prepare('UPDATE users SET password = ?, resetToken = NULL, token_expiration = NULL WHERE resetToken = ?');
    $stmt->bind_param('ss', $password, $token);
    $stmt->execute();

    echo "<script>passwordChange()</script>";
}

?>