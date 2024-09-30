<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/db/db.php";
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
        Masuk -
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
                    <h2>Masuk</h2>
                    <p style="margin-bottom: 12px;">Belum mempunyai akun? <a href="/project-sea/pages/signup.php">Buat
                            akun</a></p>
                    <div class="input-container">
                        <img class="icon" src="/project-sea/images/user.png" alt="User">
                        <input type="text" name="username" id="username" placeholder="Username"
                            minlength="<?php echo MIN_USERNAME_LENGTH ?>" maxlength="<?php echo MAX_USERNAME_LENGTH ?>"
                            oninput="this.value = this.value.replace(/\s/g, '');" required>
                    </div>
                    <div class="input-container" style="margin-bottom: 4px;">
                        <img class="icon" src="/project-sea/images/password.png" alt="Password">
                        <input type="password" name="password" id="password" placeholder="Password"
                            minlength="<?php echo MIN_PASSWORD_LENGTH ?>" maxlength="<?php echo MAX_PASSWORD_LENGTH ?>"
                            required>
                        <img id="togglePassword" src="/project-sea/images/eyes-closed.png" alt="Toggle Password">
                    </div>
                    <p style="text-align: right; margin-bottom: 16px;"><a
                            href="/project-sea/pages/forgot-password.php">Lupa Password?</a></p>
                    <button type="submit" class="color-button">Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/project-sea/js/script.js"></script>
</body>

</html>

<?php
//FORM REQUEST POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Memasukkan value form login ke dalam variable 
    $username = $_POST['username'];
    $password = $_POST['password'];

    //Mencari data users di database sesuai dengan username yang di input 
    $stmt = $conn->prepare(query: " SELECT users.* FROM users WHERE username = ? ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika value username terdapat di record database
    if ($result->num_rows > 0) {
        //Mengambil semua record dari username tsb
        $row = $result->fetch_assoc();

        //Jika password cocok
        if (password_verify(password: $password, hash: $row['password'])) {
            //Mengambil data dari database untuk SESSION
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            ?>
            <script>location.href = "/project-sea/";</script>
            <?php
        }

        //Jika password TIDAK cocok
        else {
            echo "<script>wrongPassword()</script>";
            return false;
        }
    }

    //Jika value username TIDAK terdapat di record database
    else {
        echo "<script>noUsername('$username')</script>";
        return false;
    }
}
?>