<?php
$host = 'localhost';
$port = 8889;
$dbname = 'test_db';
$user = 'root';
$password = 'root';
$charset = 'utf8';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$pdo = new PDO($dsn, $user, $password);

$post_title = "<h2>投稿が見つかりません。</h2>";
$post_message = "<p>指定されたIDの投稿は存在しません。</p>";
$postId = $_GET['id']; // 投稿IDを事前に取得

// 投稿詳細表示機能
$stmt = $pdo->prepare("SELECT title, message FROM posts WHERE id = :id");
$stmt->execute(['id' => $postId]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $post_title = "<h2>".$row['title']."</h2>";
    $post_message = "<p>".$row['message']."</p>";
}

// コメント投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment = $_POST["comment"];
    $errors = [];

    if (empty($comment)) {
        $errors[] = "コメントを入力してください。";
    }

    if (strlen($comment) > 50) {
        $errors[] = "コメントは50文字以内で入力してください。";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO comments (post_id, comment) VALUES (:post_id, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['post_id' => $postId, 'comment' => $comment]);
        header("Location: show.php?id=$postId");
        exit();
    } else {
        $error = current($errors);
        while ($error !== false) { // 現在の要素がfalseでない間ループ
            echo "<p style='color: red;'>{$error}</p>";
            $error = next($errors);
        }
    }
}

// コメント一覧機能
$commentDetails = [];

$sql = "SELECT id, comment, post_id FROM comments ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// 取得したデータを $postDetails 配列に格納
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['post_id'] == $postId) { // この投稿IDに関連するコメントのみを格納
        $commentDetails[] = [
            'id' => $row['id'],
            'commentText' => $row['comment'],
            'postId' => $row['post_id']
        ];
    }
}

// コメント削除機能
if (isset($_GET['deleteCommentId'])) {
    $deleteCommentId = $_GET['deleteCommentId'];

    // 削除するコメントの投稿IDを取得し、それに紐づく投稿IDを特定
    $stmt = $pdo->prepare("SELECT post_id FROM comments WHERE id = :commentId");
    $stmt->execute([':commentId' => $deleteCommentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentPostId = $comment ? $comment['post_id'] : '';

    if ($currentPostId) {
        // コメントを削除する
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :commentId");
        $stmt->execute([':commentId' => $deleteCommentId]);
        header("Location: show.php?id=$currentPostId");
        exit();
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
    </script>
</head>
<body>
    <h1><a href="./index.php">Laravel News</a></h1>
    <h2>投稿詳細</h2>
    <?php echo $post_title; ?>
    <?php echo $post_message; ?>
    <h3>コメント投稿</h3>
    <form action="show.php?id=<?php echo $postId; ?>" method="post" onsubmit="return confirmSubmit();">
        <label for="comment">コメント:</label>
        <textarea id="comment" name="comment"></textarea>
        <br>
        <input type="submit" value="コメント投稿">
    </form>
    <br>
    <h3>コメント一覧</h3>
    <?php
    if (empty($commentDetails)) {
        echo "<p>まだコメントがありません。</p>";
    } else {
        $index = 0;
        while ($index < count($commentDetails)) {
            $comment = $commentDetails[$index];
            echo "<p>".$comment['commentText']."<a href='show.php?id=".$postId."&deleteCommentId=".$comment['id']."' onclick='return confirm(\"本当に削除しますか？\");'>削除</a></p>";
            $index++;
        }
    }
    ?>
</body>
</html>
