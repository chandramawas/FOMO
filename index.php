<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/time.php";
require $_SERVER['DOCUMENT_ROOT'] . "/FOMO/includes/top-bar.php";
include $_SERVER['DOCUMENT_ROOT'] . "/FOMO/includes/side-bar.php";
?>

<?php
//Mencari semua post dan details
$stmt = $conn->prepare("
SELECT
    posts.id,
    posts.title,
    posts.content,
    posts.createdAt,
    posts.communityId,
    communities.name AS community,
    users.username AS username,
    IFNULL(comment_counts.total_comments, 0) AS total_comments,
    IFNULL(vote_counts.total_votes, 0) AS total_votes,
    user_votes.vote AS user_vote
FROM posts
JOIN communities ON posts.communityId = communities.id
JOIN users ON posts.userId = users.id
LEFT JOIN (
    SELECT postId, COUNT(*) AS total_comments
    FROM comments
    GROUP BY postId
) AS comment_counts ON comment_counts.postId = posts.id
LEFT JOIN (
    SELECT postId, SUM(vote) AS total_votes
    FROM user_votes
    GROUP BY postId
) AS vote_counts ON vote_counts.postId = posts.id
LEFT JOIN (
        SELECT postId, vote
        FROM user_votes
        WHERE userId = ?
    ) AS user_votes ON user_votes.postId = posts.id
ORDER BY createdAt DESC;
");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

//Fetch semua data dan detail dari post
$posts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Reddit+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/FOMO/style.css">
    <title>
        <?php echo SITE_NAME ?> -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="main">
        <?php foreach ($posts as $post): ?>
            <div class="container">
                <div class="top">
                    <a href="/FOMO/circle/<?php echo $post['communityId'] ?>">
                        <button class="hover-underline">c/<?php echo $post['community'] ?></button>
                    </a>
                    <a href="/FOMO/u/<?php echo $post['username'] ?>">
                        <h5>u/<?php echo $post['username'] ?>
                    </a> &#x2022; <h6> <?php echo timestamp($post['createdAt']) ?></h6>
                    </h5>
                </div>
                <a href="/FOMO/post/<?php echo $post['id'] ?>">
                    <div class="mid">
                        <h2><?php echo $post['title'] ?></h2>
                    </div>
                </a>
                <div class="bot">
                    <div class="vote">
                        <form id="voteForm" action="/FOMO/config/vote.php" method="post">
                            <input type="hidden" name="postId" value="<?php echo $post['id'] ?>">
                            <input type="hidden" name="userId" value="<?php echo $_SESSION['user_id'] ?>">
                            <button type="submit" name="vote" value="1" id="upvote"
                                class="<?php echo ($post['user_vote'] == 1) ? 'active' : ''; ?>"></button>
                            <p><?php echo $post['total_votes'] ?></p>
                            <button type="submit" name="vote" value="-1" id="downvote"
                                class="<?php echo ($post['user_vote'] == -1) ? 'active' : ''; ?>"></button>
                        </form>
                    </div>
                    <div class="vote">
                        <img src="/FOMO/images/comment.png" alt="Comment">
                        <p><?php echo $post['total_comments'] ?> komentar</p>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</body>

</html>