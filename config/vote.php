<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/fomo/config/db.php";

$postId = $_POST['postId'];
$userId = $_POST['userId'];
$vote = $_POST['vote'];

//cek apakah user sudah vote atau belum
$stmt = $conn->prepare("SELECT * FROM user_votes WHERE postId = ? AND userId = ?");
$stmt->bind_param("ii", $postId, $userId);
$stmt->execute();
$result = $stmt->get_result();

//jika user sudah vote di post tersebut
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    //Jika user vote dengan nilai yang sama
    if ($row["vote"] == $vote) {
        $stmt = $conn->prepare("DELETE FROM user_votes WHERE postId = ? AND userId = ?");
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
    }
    //Jika user vote dengan nilai yang berbeda
    else {
        $stmt = $conn->prepare("UPDATE user_votes SET vote = ? WHERE postId = ? AND userId = ?");
        $stmt->bind_param("iii", $vote, $postId, $userId);
        $stmt->execute();
    }
}

//jika user BELUM vote di post tersebut
else {
    $stmt = $conn->prepare("INSERT INTO user_votes (postId, userId, vote) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $postId, $userId, $vote);
    $stmt->execute();
}

echo "<script>history.back()</script>";
exit;