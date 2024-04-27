<?php
// DB接続
$dsn = "mysql:host=localhost;dbname=test_db;charset=utf8";
$user = 'root';
$password = 'root';
$pdo = new PDO($dsn, $user, $password);
// 投稿IDを事前に取得
$postId = $_GET['id'];

// 投稿詳細表示機能
$stmt = $pdo->prepare("SELECT title, message FROM posts WHERE id = :id");
$stmt->execute(['id' => $postId]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $post_title = $row['title'];
    $post_message = $row['message'];
}

// POSTメソッドが呼ばれた時の処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["comment"])) { // コメント投稿の処理
        $comment = $_POST["comment"];
        $errors = [];

        if (empty($comment)) {
            $errors[] = "コメントを入力してください。";
        }

        if (strlen($comment) > 50) {
            $errors[] = "コメントは50文字以内で入力してください。";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, comment) VALUES (:post_id, :comment)");
            $stmt->execute(['post_id' => $postId, 'comment' => $comment]);
            header("Location: show.php?id=$postId");
            exit();
        }
    }

    if (isset($_POST['delete_comment_id'])) { // コメント削除の処理
        $deleteCommentId = $_POST['delete_comment_id'];
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
        $stmt->execute(['comment_id' => $deleteCommentId]);
        header("Location: show.php?id=$postId");
        exit();
    }
}

// コメント一覧機能
$commentDetails = [];
$stmt = $pdo->prepare("SELECT id, comment, post_id FROM comments ORDER BY id DESC");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // 取得したデータを $postDetails 配列に格納
    if ($row['post_id'] == $postId) { // この投稿IDに関連するコメントのみを格納
        $commentDetails[] = ['id' => $row['id'],'commentText' => $row['comment'],'postId' => $row['post_id']];
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>投稿詳細</title>
        <script>
            function confirmSubmit() {
                return confirm('本当にコメントしますか？');
            }

            function confirmDelete() {
                return confirm('本当にこのコメントを削除しますか？');
            }
        </script>
    </head>
    <body>
        <h1><a href="./index.php">Laravel News</a></h1>
        <h2>投稿詳細</h2>
        <h3><?= $post_title; ?></h3>
        <p><?= $post_message; ?></p>
        <br>
        <h3>コメント投稿</h3>
        <?php
        if (!empty($errors)):
            $index = 0;
            while ($index < count($errors)):
        ?>
                <p><?= $errors[$index]; ?></p>
        <?php
                $index++;
            endwhile;
        endif;
        ?>
        <form action="show.php?id=<?= $postId; ?>" method="post" onsubmit="return confirmSubmit();">
            <label for="comment">コメント:</label>
            <textarea id="comment" name="comment"></textarea>
            <br>
            <input type="submit" value="コメント投稿">
        </form>
        <br>
        <h3>コメント一覧</h3>
        <?php
        $index = 0;
        while ($index < count($commentDetails)):
            $comment = $commentDetails[$index];
        ?>
            <p><?= $comment['commentText']; ?></p>
            <form method="post" action="show.php?id=<?= $postId; ?>" onsubmit="return confirmDelete();">
                <input type="hidden" name="delete_comment_id" value="<?= $comment['id']; ?>">
                <input type="submit" value="削除">
            </form>
        <?php
            $index++;
        endwhile;
        ?>
    </body>
</html>
