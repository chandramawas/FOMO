<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/db/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/sweet_alert.php";
require $_SERVER['DOCUMENT_ROOT'] . "/project-sea/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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
        Lupa Password -
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
                    <h2>Lupa Password</h2>
                    <p style="margin-bottom: 12px;">Belum mempunyai akun? <a href="/project-sea/login/">Masuk</a></p>
                    <div class="input-container" style="margin-bottom: 16px;">
                        <img class="icon" src="/project-sea/images/email.png" alt="Email">
                        <input type="email" name="email" id="email" placeholder="Email" required>
                    </div>
                    <button type="submit" class="color-button">Kirim Email</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<?php
//REQUEST POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    //Mencari email di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika email ada di database
    if ($result->num_rows > 0) {
        // Buat token unik
        $token = bin2hex(random_bytes(50));

        // Simpan token dan masa waktunya ke database
        $stmt = $conn->prepare("UPDATE users SET resetToken = ?, token_expiration = NOW() + INTERVAL 1 HOUR WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        //Mengirim email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'chandramawastore@gmail.com';
            $mail->Password = 'fvlo sors esrk hliu';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('chandramawastore@gmail.com', 'FOMO');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click on the link to reset your password: <a href='localhost/project-sea/resetpassword/?token=$token'>Reset Password</a>";
            $mail->AltBody = "Copy and paste the following URL to reset your password: localhost/project-sea/resetpassword/?token=$token";

            $mail->send();

            echo "<script>emailSent()</script>";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    }

    //Jika email TIDAK ada di database
    else {
        echo "<script>noEmail()</script>";
    }
}
?>