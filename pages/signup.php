<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/sweet_alert.php";
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
        Buat Akun -
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
                <form action="" method="POST" onsubmit="return validateForm()">
                    <h2>Daftar Akun Baru</h2>
                    <p style="margin-bottom: 12px;">Sudah mempunyai akun? <a href="/project-sea/login/">Masuk</a></p>
                    <div class="input-container">
                        <img class="icon" src="/project-sea/images/email.png" alt="Email">
                        <input type="email" name="email" id="email" placeholder="Email" required>
                    </div>
                    <div class="input-container">
                        <img class="icon" src="/project-sea/images/user.png" alt="User">
                        <input type="text" name="username" id="username" placeholder="Buat Username"
                            oninput="this.value = this.value.replace(/\s/g, '');" required>
                    </div>
                    <div class="input-container">
                        <img class="icon" src="/project-sea/images/password.png" alt="Password">
                        <input type="password" name="password" id="password" placeholder="Buat Password"
                            minlength="<?php echo MIN_PASSWORD_LENGTH ?>" maxlength="<?php echo MAX_PASSWORD_LENGTH ?>"
                            required>
                        <img id="togglePassword" src="/project-sea/images/eyes-closed.png" alt="Toggle Password">
                    </div>
                    <div class="input-container" style="margin-bottom: 16px;">
                        <img class="icon" src="/project-sea/images/password.png" alt="Password">
                        <input type="password" name="confirmPassword" id="confirmPassword"
                            placeholder="Konfirmasi Password" minlength="<?php echo MIN_PASSWORD_LENGTH ?>"
                            maxlength="<?php echo MAX_PASSWORD_LENGTH ?>" required>
                        <img id="toggleConfirmPassword" src="/project-sea/images/eyes-closed.png" alt="Toggle Password">
                    </div>
                    <button type="submit" class="color-button">Buat Akun</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/project-sea/js/script.js"></script>
</body>

</html>
<?php
//REQUEST POST SIGNUP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Mengambil data yang diinput
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //Cek apakah username dan email ada di database, karena mereka harus unique
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika username atau email SUDAH terdaftar
    if ($result->num_rows > 0) {
        $userSearch = $conn->query("SELECT * FROM users WHERE username = '$username'");
        $emailSearch = $conn->query("SELECT * FROM users WHERE email = '$email'");

        if ($userSearch->num_rows > 0) {
            echo "<script>usernameRegistered()</script>";
            return false;
        } elseif ($emailSearch->num_rows > 0) {
            echo "<script>emailRegistered()</script>";
            return false;
        }
    }

    //Jika username atau email belum terdaftar
    else {
        //Menambahkan user ke database
        $sql = "INSERT INTO users(email, username, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $username, $password);
        $stmt->execute();

        echo "<script>successRegister()</script>";
    }
}
?>