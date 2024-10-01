<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/fomo/includes/sweet_alert.php";

//Top posts berdasarkan total vote+comment yang dibuat dalam seminggu terakhir 
$stmt = $conn->prepare("
SELECT
    posts.id,
    posts.title,
    IFNULL(comment_counts.total_comments, 0) AS total_comments,
    IFNULL(vote_counts.total_votes, 0) AS total_votes
FROM posts
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
    SELECT postId, COUNT(*) AS recent_comments
    FROM comments
    WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    GROUP BY postId
) AS recent_comment_counts ON recent_comment_counts.postId = posts.id
LEFT JOIN (
    SELECT postId, SUM(vote) AS recent_votes
    FROM user_votes
    WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    GROUP BY postId
) AS recent_vote_counts ON recent_vote_counts.postId = posts.id
WHERE IFNULL(vote_counts.total_votes, 0) > 0
OR IFNULL(comment_counts.total_comments, 0) > 0
ORDER BY (total_comments + total_votes) DESC
LIMIT 5;
");
$stmt->execute();
$result = $stmt->get_result();
$topPosts = $result->fetch_all(MYSQLI_ASSOC);

//Top community berdasarkan total post dalam seminggu terakhir
$stmt = $conn->prepare("
SELECT
    communities.id,
    communities.name,
    IFNULL(posts_count.total_posts, 0) AS total_posts
FROM communities
LEFT JOIN (
    SELECT communityId, COUNT(*) AS total_posts
    FROM posts
    GROUP BY communityId
) AS posts_count ON posts_count.communityId = communities.id
LEFT JOIN (
    SELECT communityId, COUNT(*) AS recent_posts
    FROM posts
    WHERE createdAt >= NOW() - INTERVAL 7 DAY
    GROUP BY communityId
) AS recent_posts_count ON recent_posts_count.communityId = communities.id
WHERE IFNULL(posts_count.total_posts, 0) > 0
ORDER BY total_posts DESC, total_posts DESC
LIMIT 5;
");
$stmt->execute();
$result = $stmt->get_result();
$topCircles = $result->fetch_all(MYSQLI_ASSOC);

//Top user berdasarkan total post+vote
$stmt = $conn->prepare("
SELECT
    users.username,
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
WHERE IFNULL(posts_count.total_posts, 0) > 0
   OR IFNULL(votes_count.total_votes, 0) > 0
ORDER BY (total_posts + total_votes) DESC
LIMIT 5;
");
$stmt->execute();
$result = $stmt->get_result();
$topUsers = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="side-bar">
    <?php if (!isset($_GET['u']) || $_GET['u'] !== $_SESSION['username']) {
        ?>
        <div class="container">
            <h4>Akun Saya</h4>
            <a href="/fomo/u/<?php echo $_SESSION['username'] ?>">
                <div class="user">
                    <img src="/fomo/images/profile-blue.png" alt="Profile">
                    <h3><?php echo $_SESSION['username'] ?></h3>
                </div>
            </a>
        </div>
    <?php } ?>

    <?php if ($_SESSION['roles'] === 'admin') { ?>
        <?php if (isset($_GET['u']) && $_GET['u'] !== $_SESSION['username']) { ?>
            <div class="side-button">
                <button class="red" onclick="deleteData('<?php echo $_GET['u'] ?>')">Hapus Pengguna</button>
                <form id="deleteData" action="" method="POST" style="display:none;">
                    <input type="hidden" name="form_type" value="deleteUser">
                    <input type="hidden" name="id" value="<?php echo $_GET['u'] ?>">
                    <input type="hidden" name="confirmPassword" id="confirmPassword">
                </form>
            </div>
        <?php } ?>
        <?php if (isset($_GET['c'])) {
            $stmt = $conn->prepare('SELECT name FROM communities WHERE id = ?');
            $stmt->bind_param('i', $_GET['c']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $cname = $row['name'];
            ?>
            <div class="side-button">
                <button class="red" onclick="deleteData('<?php echo $cname ?>')">Hapus Circle</button>
                <form id="deleteData" action="" method="POST" style="display:none;">
                    <input type="hidden" name="form_type" value="deleteCircle">
                    <input type="hidden" name="id" value="<?php echo $_GET['c'] ?>">
                    <input type="hidden" name="confirmPassword" id="confirmPassword">
                </form>
            </div>
        <?php } ?>
        <?php if (isset($_GET['p'])) { ?>
            <div class="side-button">
                <button class="red" onclick="deleteData('Post')">Hapus Post</button>
                <form id="deleteData" action="" method="POST" style="display:none;">
                    <input type="hidden" name="form_type" value="deletePost">
                    <input type="hidden" name="id" value="<?php echo $_GET['p'] ?>">
                    <input type="hidden" name="confirmPassword" id="confirmPassword">
                </form>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (empty($topPosts)): ?>
    <?php else: ?>
        <div class="container">
            <h4>Masalah Rame-Rame</h4>
            <?php foreach ($topPosts as $topPost): ?>
                <a href="/fomo/post/<?php echo $topPost['id'] ?>">
                    <div class="side-content">
                        <h5><?php echo $topPost['title'] ?></h5>
                        <p><?php echo $topPost['total_votes'] ?> aura • <?php echo $topPost['total_comments'] ?> komentar</p>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="side-button">
        <button class="hover-blue" onclick="createCircle()">Buat Circle Baru</button>
        <form id="circleForm" action="" method="POST" style="display:none;">
            <input type="hidden" name="form_type" value="createCircle">
            <input type="hidden" name="circleName" id="circleName">
            <input type="hidden" name="circleDescription" id="circleDescription">
        </form>
    </div>

    <?php if (empty($topCircles)): ?>
    <?php else: ?>
        <div class="container">
            <h4>Circle Paling Berisik</h4>
            <?php foreach ($topCircles as $topCircle): ?>
                <a href="/fomo/circle/<?php echo $topCircle['id'] ?>">
                    <div class="side-content">
                        <h5><?php echo $topCircle['name'] ?></h5>
                        <p><?php echo $topCircle['total_posts'] ?> Postingan</p>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php if (empty($topUsers)): ?>
    <?php else: ?>
        <div class="container">
            <h4>Orang Paling fomo</h4>
            <?php foreach ($topUsers as $topUser): ?>
                <a href="/fomo/u/<?php echo $topUser['username'] ?>">
                    <div class="side-content">
                        <h5><?php echo $topUser['username'] ?></h5>
                        <p><?php echo $topUser['total_posts'] ?> postingan • <?php echo $topUser['total_votes'] ?> aura</p>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>

<?php
//REQUEST POST BUAT CIRCLE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formType = $_POST['form_type'];

    if ($formType === 'createCircle') {
        $circleName = $_POST['circleName'];
        $circleDescription = $_POST['circleDescription'];
        $circleCreatorId = $_SESSION['user_id'];

        //Memasukkan data circle baru ke database
        $stmt = $conn->prepare('INSERT INTO communities (name, description, creatorId) VALUES (?, ?, ?)');
        $stmt->bind_param('ssi', $circleName, $circleDescription, $circleCreatorId);
        $stmt->execute();

        //redirect ke community yang baru dibuat
        $stmt = $conn->prepare('SELECT * FROM communities WHERE name = ? AND description = ? AND creatorId = ? ');
        $stmt->bind_param('ssi', $circleName, $circleDescription, $circleCreatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $c_id = $row['id'];

        echo "<script>successCircle('$c_id')</script>";
    } elseif ($formType === "deleteUser") {
        $id = $_POST["id"];
        $password = $_POST["confirmPassword"];

        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $stmt = $conn->prepare('DELETE FROM users WHERE username = ?');
            $stmt->bind_param('s', $id);
            $stmt->execute();

            echo '<script>alert("Berhasil Menghapus User ' . $id . '"); location.href = "/fomo/";</script>';
        } else {
            echo '<script>deleteError()</script>';
        }
    } elseif ($formType === "deleteCircle") {
        $id = $_POST["id"];
        $password = $_POST["confirmPassword"];

        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $stmt = $conn->prepare('DELETE FROM communities WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();

            echo '<script>alert("Berhasil Menghapus Circle ' . $cname . '"); location.href = "/fomo/";</script>';
        } else {
            echo '<script>deleteError()</script>';
        }
    } elseif ($formType === "deletePost") {
        $id = $_POST["id"];
        $password = $_POST["confirmPassword"];

        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $stmt = $conn->prepare('DELETE FROM posts WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();

            echo '<script>alert("Berhasil Menghapus Post"); location.href = "/fomo/";</script>';
        } else {
            echo '<script>deleteError()</script>';
        }
    }
}
?>