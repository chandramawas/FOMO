<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/config/time.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/FOMO/includes/top-bar.php";
include $_SERVER['DOCUMENT_ROOT'] . "/FOMO/includes/side-bar.php";
?>

<?php
//Jika ada kode username " ?u=#username "
if (isset($_GET['u'])) {
    //Mengambil kode
    $u_username = $_GET['u'];

    //Mengambil semua details dari database
    $stmt = $conn->prepare("
    SELECT
        users.id,
        users.username,
        DATE_FORMAT(createdAt, '%d/%m/%Y') AS date,
        IFNULL(posts_count.total_posts, 0) AS total_posts,
        IFNULL(comments_count.total_comments, 0) AS total_comments,
        IFNULL(votes_count.total_votes, 0) AS total_votes
    FROM users
    LEFT JOIN (
        SELECT userId, COUNT(*) AS total_posts
        FROM posts
        GROUP BY userId
    ) AS posts_count ON posts_count.userId = users.id
    LEFT JOIN (
        SELECT userId, COUNT(*) AS total_comments
        FROM comments
        GROUP BY userId
    ) AS comments_count ON comments_count.userId = users.id
    LEFT JOIN (
        SELECT posts.userId, SUM(user_votes.vote) AS total_votes
        FROM user_votes
        INNER JOIN posts ON user_votes.postId = posts.id
        GROUP BY posts.userId
    ) AS votes_count ON votes_count.userId = users.id
    WHERE users.username = ?
    ");
    $stmt->bind_param("s", $u_username);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika data ditemukan di database 
    if ($result->num_rows > 0) {
        // Fetch user data
        $row = $result->fetch_assoc();
        $u_id = $row["id"];
        $u_username = $row["username"];
        $u_since = $row["date"];
        $u_totalPosts = $row["total_posts"];
        $u_totalComments = $row["total_comments"];
        $u_totalVotes = $row["total_votes"];

        $stmt = $conn->prepare("
        SELECT
            posts.*,
            communities.name AS community,
            IFNULL(votes_count.total_votes, 0) AS total_votes,
            IFNULL(comments_count.total_comments, 0) AS total_comments,
            user_votes.vote AS user_vote
        FROM posts
        JOIN communities ON posts.`communityId` = communities.id
        LEFT JOIN (
            SELECT postId, SUM(vote) AS total_votes
            FROM user_votes
            GROUP BY postId
        ) AS votes_count ON votes_count.postId = posts.id
        LEFT JOIN (
            SELECT postId, COUNT(*) AS total_comments
            FROM comments
            GROUP BY postId
        ) AS comments_count ON comments_count.postId = posts.id
        LEFT JOIN (
            SELECT postId, vote
            FROM user_votes
            WHERE userId = ?
        ) AS user_votes ON user_votes.postId = posts.id
        WHERE posts.userId = ?
        ORDER BY createdAt DESC;
        ");
        $stmt->bind_param("ii", $u_id, $u_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = $result->fetch_all(MYSQLI_ASSOC);
    }

    //Jika data TIDAK ditemukan di database
    else {
        ?>
        <script>alert("Username tidak ditemukan"); location.href = "/FOMO/"</script>
        <?php
    }
    $stmt->close();
}

//Jika TIDAK ada kode username " ?u=#username "
else {
    ?>
    <script>location.href = "/FOMO/";</script>
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
    <link rel="stylesheet" href="/FOMO/style.css">
    <title>
        <?php echo $u_username ?> -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="main">
        <div class="page">
            <div class="header">
                <img src="/FOMO/images/profile.png" alt="Profile">
                <div class="details">
                    <h3><?php echo $u_username ?></h3>
                    <p>Bermasalah sejak <?php echo $u_since ?> • <?php echo $u_totalPosts ?> postingan •
                        <?php echo $u_totalComments ?> komentar • <?php echo $u_totalVotes ?> aura
                    </p>
                </div>
            </div>
            <div class="content-user">
                <?php if (empty($posts)): ?>
                    <h6>User belum pernah post.</h6>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="container">
                            <div class="top">
                                <a href="/FOMO/circle/<?php echo $post['communityId'] ?>">
                                    <h5>c/<?php echo $post['community'] ?>
                                </a>&#x2022; <h6><?php echo timestamp($post['createdAt']) ?>
                                </h6>
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
                <?php endif ?>
            </div>
        </div>
    </div>
</body>

</html>