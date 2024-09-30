<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/db/db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/project-sea/config/time.php";
require $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/top-bar.php";
include $_SERVER['DOCUMENT_ROOT'] . "/project-sea/includes/side-bar.php";
?>

<?php

//Jika ada kode " ?s=# "
if (isset($_GET["s"])) {
    $search = $_GET["s"];

    //Query untuk circles
    $stmt = $conn->prepare("
    SELECT
        communities.id,
        communities.name,
        DATE_FORMAT(communities.createdAt, '%d/%m/%Y') AS date,
        IFNULL(posts_count.total_posts, 0) AS total_posts,
        IFNULL(users_count.total_users, 0) AS total_users,
        IFNULL(votes_count.total_votes, 0) AS total_votes
    FROM communities
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
    WHERE MATCH(name, description)
        AGAINST( ? IN NATURAL LANGUAGE MODE);
    ");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $circles = $result->fetch_all(MYSQLI_ASSOC);

    //Query untuk post
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
    WHERE MATCH(title, content)
        AGAINST( ? WITH QUERY EXPANSION);
    ");
    $stmt->bind_param("is", $_SESSION['user_id'], $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

    //Query untuk users
    $stmt = $conn->prepare("
    SELECT
    users.username,
    DATE_FORMAT(createdAt, '%d/%m/%Y') AS date,
    IFNULL(posts_count.total_posts, 0) AS total_posts,
    IFNULL(votes_count.total_votes, 0) AS total_votes
    FROM users
    LEFT JOIN (
        SELECT userId, COUNT(*) AS total_posts
        FROM posts
        GROUP BY userId
    ) AS posts_count ON posts_count.userId = users.id
    LEFT JOIN (
        SELECT posts.userId, SUM(user_votes.vote) AS total_votes
        FROM user_votes
            INNER JOIN posts ON user_votes.postId = posts.id
        GROUP BY posts.userId
    ) AS votes_count ON votes_count.userId = users.id
    WHERE users.username LIKE ? ;
    ");
    $search = "%{$search}%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $search = $_GET["s"];
}

//Jika ada kode " ?s=# "
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
        <?php echo $search ?> -
        <?php echo SITE_SHORTNAME ?>
    </title>
</head>

<body>
    <div class="main">
        <div class="option">
            <button id="circle" class="hover-blue">Circle</button>
            <button id="user" class="hover-blue">User</button>
            <button id="post" class="hover-blue active">Post</button>
        </div>

        <!-- Divs -->
        <div id="circle-div" class="search" style="display: none;">
            <?php if (empty($circles)): ?>
                <h6>Circle tidak ditemukan.</h6>
            <?php else: ?>
                <?php foreach ($circles as $circle): ?>
                    <a href="/project-sea/circle/<?php echo $circle['id'] ?>">
                        <div class="search-container">
                            <h3><?php echo $circle['name'] ?></h3>
                            <p>Sejak <?php echo $circle['date'] ?> • <?php echo $circle['total_posts'] ?> postingan dari
                                <?php echo $circle['total_users'] ?> user • <?php echo $circle['total_votes'] ?> aura
                            </p>
                        </div>
                    </a>
                <?php endforeach ?>
            <?php endif ?>

        </div>
        <div id="user-div" class="search" style="display:none;">
            <?php if (empty($users)): ?>
                <h6>User tidak ditemukan.</h6>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <a href="/project-sea/u/<?php echo $user['username'] ?>">
                        <div class="search-container">
                            <h3><?php echo $user['username'] ?></h3>
                            <p>Sejak <?php echo $user['date'] ?> • <?php echo $user['total_posts'] ?> postingan •
                                <?php echo $user['total_votes'] ?> aura
                            </p>
                        </div>
                    </a>
                <?php endforeach ?>
            <?php endif ?>
        </div>
        <div id="post-div" class="search">
            <?php if (empty($posts)): ?>
                <h6>Post tidak ditemukan.</h6>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class=" container">
                        <div class="top">
                            <a href="/project-sea/circle/<?php echo $post['communityId'] ?>">
                                <button class="hover-underline">c/<?php echo $post['community'] ?></button>
                            </a>
                            <a href="/project-sea/u/<?php echo $post['username'] ?>">
                                <h5>u/<?php echo $post['username'] ?>
                            </a> &#x2022; <h6> <?php echo timestamp($post['createdAt']) ?></h6>
                            </h5>
                        </div>
                        <a href="/project-sea/post/<?php echo $post['id'] ?>">
                            <div class="mid">
                                <h2><?php echo $post['title'] ?></h2>
                            </div>
                        </a>
                        <div class="bot">
                            <div class="vote">
                                <form id="voteForm" action="/project-sea/config/vote.php" method="post">
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
                                <img src="/project-sea/images/comment.png" alt="Comment">
                                <p><?php echo $post['total_comments'] ?> komentar</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>

    <script>
        // Mendapat element dari button dan div
        const circle = document.getElementById('circle');
        const user = document.getElementById('user');
        const post = document.getElementById('post');

        const circleDiv = document.getElementById('circle-div');
        const userDiv = document.getElementById('user-div');
        const postDiv = document.getElementById('post-div');

        const buttons = [circle, user, post];
        const divs = [circleDiv, userDiv, postDiv];

        // Fungsi untuk mengubah div dan button yang aktif
        function switchDiv(index) {
            divs.forEach((div, i) => {
                div.style.display = i === index ? 'block' : 'none';
            });
            buttons.forEach((button, i) => {
                button.classList.toggle('active', i === index);
            });
        }

        circle.addEventListener('click', () => switchDiv(0));
        user.addEventListener('click', () => switchDiv(1));
        post.addEventListener('click', () => switchDiv(2));
    </script>
</body>

</html>