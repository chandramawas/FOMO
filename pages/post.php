<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/db/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/time.php";
require $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/top-bar.php";
include $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/side-bar.php";
?>

<?php
//Jika ada kode " ?p=#id "
if (isset($_GET["p"])) {
    $p_id = $_GET["p"];

    //Cari details post di database
    $stmt = $conn->prepare("
    SELECT 
        posts.*,
        communities.name AS community,
        users.username AS username,
        IFNULL(comments_count.total_comments, 0) AS total_comments,
        IFNULL(votes_count.total_votes, 0) AS total_votes,
        user_votes.vote AS user_vote
        FROM posts
        JOIN communities ON communities.id = posts.communityId
        JOIN users ON users.id = posts.userId
        LEFT JOIN (
            SELECT postId, COUNT(*) AS total_comments
            FROM comments
            GROUP BY postId
        ) AS comments_count ON comments_count.postId = posts.id
        LEFT JOIN (
            SELECT postId, SUM(vote) AS total_votes
            FROM user_votes
            GROUP BY postId
        ) AS votes_count ON votes_count.postId = posts.id
        LEFT JOIN (
            SELECT postId, vote
            FROM user_votes
            WHERE userId = ?
        ) AS user_votes ON user_votes.postId = posts.id
        WHERE posts.id = ?;
    ");
    $stmt->bind_param("ii", $_SESSION["user_id"], $p_id);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika id post ada di database
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $p_id = $row['id'];
        $p_title = $row['title'];
        $p_content = $row['content'];
        $p_username = $row['username'];
        $p_community = $row['community'];
        $p_communityId = $row['communityId'];
        $p_totalComments = $row['total_comments'];
        $p_totalVotes = $row['total_votes'];
        $p_timestamp = timestamp($row['createdAt']);
        $p_userVote = $row['user_vote'];

        //Mencari comment post
        $stmt = $conn->prepare('
        SELECT
            comments.*,
            users.username AS username
        FROM comments
        JOIN users ON users.id = comments.userId
        WHERE postId = ?
        ORDER BY createdAt;
        ');
        $stmt->bind_param('i', $p_id);
        $stmt->execute();
        $result = $stmt->get_result();

        //Fetch semua comment
        $comments = $result->fetch_all(MYSQLI_ASSOC);
    }

    //Jika id post ada di database
    else {
        ?>
        <script>alert("Post tidak ditemukan."); location.href = "/project-sea/"</script>
        <?php
    }

}

//Jika TIDAK ada kode " ?p=#id "
else {
    ?>
    <script>location.href = "/project-sea/";</script>
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
    <link rel="stylesheet" href="/project-sea/style.css">
    <title>
        <?php echo $p_title ?> -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="main">
        <div class="container">
            <div class="top">
                <a href="/project-sea/circle/<?php echo $p_communityId ?>">
                    <button class="hover-underline">c/<?php echo $p_community ?></button>
                </a>
                <a href="/project-sea/u/<?php echo $p_username ?>">
                    <h5>u/<?php echo $p_username ?>
                </a> &#x2022; <h6> <?php echo $p_timestamp ?></h6>
                </h5>
            </div>
            <div class="post">
                <h2><?php echo $p_title ?></h2>
                <p><?php echo $p_content ?></p>
            </div>
            <div class="bot">
                <div class="vote">
                    <form id="voteForm" action="/project-sea/config/vote.php" method="post">
                        <input type="hidden" name="postId" value="<?php echo $p_id ?>">
                        <input type="hidden" name="userId" value="<?php echo $_SESSION['user_id'] ?>">
                        <button type="submit" name="vote" value="1" id="upvote"
                            class="<?php echo ($p_userVote == 1) ? 'active' : ''; ?>"></button>
                        <p><?php echo $p_totalVotes ?></p>
                        <button type="submit" name="vote" value="-1" id="downvote"
                            class="<?php echo ($p_userVote == -1) ? 'active' : ''; ?>"></button>
                    </form>
                </div>
                <div class="vote">
                    <img src="/project-sea/images/comment.png" alt="Comment">
                    <p><?php echo $p_totalComments ?> komentar</p>
                </div>
            </div>
        </div>
        <div class="create-comment">
            <form action="" method="POST">
                <input type="hidden" name="form_type" value="createComment">
                <input type="text" name="comment" id="comment" placeholder="Tambahkan komentar...">
            </form>
        </div>
        <div class="comment">
            <?php if (empty($comments)): ?>
                <h6>Belum ada komentar.</h6>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="container">
                        <div class="top">
                            <a href="/project-sea/u/<?php echo $comment['username'] ?>">
                                <h5>u/<?php echo $comment['username'] ?>
                            </a> &#x2022; <h6> <?php echo timestamp($comment['createdAt']) ?></h6>
                            </h5>
                        </div>
                        <div class="mid">
                            <p><?php echo $comment['comment'] ?></p>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formType = $_POST['form_type'];

    if ($formType === 'createComment') {
        $comment = $_POST['comment'];
        $userId = $_SESSION['user_id'];
        $postId = $p_id;

        $stmt = $conn->prepare('INSERT INTO comments (comment, userId, postId) VALUES (?, ?, ?)');
        $stmt->bind_param('sii', $comment, $userId, $postId);

        if ($stmt->execute()) {
            echo "<script>window.location.href='/project-sea/post/$postId'; </script>";
        } else {
            echo "<script>alert('Failed to create comment!'); history.back();</script>";
        }

        $stmt->close();
    }
}
?>