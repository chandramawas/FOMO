<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/fomo/config/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/fomo/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/fomo/config/time.php";
require $_SERVER['DOCUMENT_ROOT'] . "/fomo/includes/top-bar.php";
include $_SERVER['DOCUMENT_ROOT'] . "/fomo/includes/side-bar.php";
?>

<?php
//Jika ada kode circle " ?c=#id "
if (isset($_GET['c'])) {
    //Mengambil kode
    $c_id = $_GET['c'];

    //Mengambil semua details dari database
    $stmt = $conn->prepare("
    SELECT
        communities.*,
        DATE_FORMAT(communities.createdAt, '%d/%m/%Y') AS date,
        users.username AS creator,
        IFNULL(posts_count.total_posts, 0) AS total_posts,
        IFNULL(users_count.total_users, 0) AS total_users,
        IFNULL(votes_count.total_votes, 0) AS total_votes
    FROM communities
    JOIN users ON users.id = communities.`creatorId`
    LEFT JOIN (
        SELECT communityId, COUNT(*) AS total_posts
        FROM posts
        GROUP BY communityId
    ) AS posts_count ON posts_count.communityId = communities.id
    LEFT JOIN (
        SELECT communityId, COUNT(DISTINCT userId) AS total_users
        FROM posts
        GROUP BY communityId
    ) AS users_count ON users_count.communityId = communities.id
    LEFT JOIN (
        SELECT posts.communityId, SUM(IFNULL(user_votes.vote, 0)) AS total_votes
        FROM posts
        LEFT JOIN user_votes ON user_votes.postId = posts.id
        GROUP BY posts.communityId
    ) AS votes_count ON votes_count.communityId = communities.id
    WHERE communities.id = ?;
    ");
    $stmt->bind_param("i", $c_id);
    $stmt->execute();
    $result = $stmt->get_result();

    //Jika data ditemukan di database
    if ($result->num_rows > 0) {
        //Fetch data circle
        $row = $result->fetch_assoc();
        $c_id = $row["id"];
        $c_name = $row["name"];
        $c_since = $row["date"];
        $c_totalPosts = $row["total_posts"];
        $c_totalUsers = $row["total_users"];
        $c_totalVotes = $row["total_votes"];
        $c_creator = $row["creator"];
        $c_description = $row["description"];

        //query details
        $stmt = $conn->prepare("
        SELECT
            posts.*,
            users.username AS username,
            IFNULL(votes_count.total_votes, 0) AS total_votes,
            IFNULL(comments_count.total_comments, 0) AS total_comments,
            user_votes.vote AS user_vote
        FROM posts
        JOIN users ON posts.userId = users.id
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
        WHERE posts.communityId = ?
        ORDER BY createdAt DESC;
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $c_id);
        $stmt->execute();
        $result = $stmt->get_result();

        //fetch semua details
        $posts = $result->fetch_all(MYSQLI_ASSOC);
    }

    //Jika data TIDAK ditemukan di database
    else {
        ?>
        <script>alert("ID Circle tidak ditemukan."); location.href = "/fomo/"</script>
        <?php
    }
}
//Jika TIDAK ada kode circle " ?c=#id "
else {
    ?>
    <script>location.href = "/fomo/";</script>
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
    <link rel="stylesheet" href="/fomo/style.css">
    <title>
        <?php echo $c_name ?> -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="main">
        <div class="page">
            <div class="header">
                <img src="/fomo/images/circle.png" alt="Circle">
                <div class="details">
                    <h3><?php echo $c_name ?></h3>
                    <p>Tikum sejak <?php echo $c_since ?> • <?php echo $c_totalPosts ?> postingan dari
                        <?php echo $c_totalUsers ?> user • <?php echo $c_totalVotes ?> aura
                    </p>
                </div>
            </div>
            <div class="content-circle">
                <?php if (empty($posts)): ?>
                    <h6>Circle belum ada post.</h6>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="container">
                            <div class="top">
                                <a href="/fomo/u/<?php echo $post['username'] ?>">
                                    <h5>u/<?php echo $post['username'] ?>
                                </a> &#x2022; <h6><?php echo timestamp($post['createdAt']) ?>
                                </h6>
                                </h5>
                            </div>
                            <a href="/fomo/post/<?php echo $post['id'] ?>">
                                <div class="mid">
                                    <h2><?php echo $post['title'] ?></h2>
                                </div>
                            </a>
                            <div class="bot">
                                <div class="vote">
                                    <form id="voteForm" action="/fomo/config/vote.php" method="post">
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
                                    <img src="/fomo/images/comment.png" alt="Comment">
                                    <p><?php echo $post['total_comments'] ?> komentar</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
            <div class="info">
                <div class="info-container">
                    <h5>Pembuat</h5>
                    <a href="/fomo/u/<?php echo $c_creator ?>">
                        <p><?php echo $c_creator ?></p>
                    </a>
                </div>

                <button class="color-button" style="margin-bottom: 12px;"
                    onclick="createPost('<?php echo $c_name ?>')">Buat Post</button>
                <form id="postForm" action="" method="POST" style="display:none;">
                    <input type="hidden" name="form_type" value="createPost">
                    <input type="hidden" name="postTitle" id="postTitle">
                    <input type="hidden" name="postContent" id="postContent">
                </form>

                <div class="info-container">
                    <h5>Deskripsi</h5>
                    <p><?php echo $c_description ?></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
//REQUEST POST BUAT POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formType = $_POST['form_type'];

    if ($formType === 'createPost') {
        $title = $_POST['postTitle'];
        $content = $_POST['postContent'];
        $postUserId = $_SESSION['user_id'];
        $postCircleId = $c_id;

        //Memasukkan post baru ke database
        $stmt = $conn->prepare('INSERT INTO posts (title, content, userId, communityId) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssii', $title, $content, $postUserId, $postCircleId);
        $stmt->execute();
        $stmt->close();

        echo "<script>window.location.href='/fomo/circle/$postCircleId'; </script>";
    }
}
?>