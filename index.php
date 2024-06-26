<?php
// DB接続
$dsn = "mysql:host=localhost;dbname=test_db;charset=utf8";
$user = 'root';
$password = 'root';
$pdo = new PDO($dsn, $user, $password);

// 投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["title"], $_POST["message"])) {
        $title = $_POST["title"];
        $message = $_POST["message"];
        $errors = []; // エラーメッセージを格納する配列

        if (empty(trim($title))) {
            $errors[] = "タイトルを入力してください。";
        }

        if (strlen($title) > 30) {
            $errors[] = "タイトルは30文字以内で入力してください。";
        }

        if (empty(trim($message))) {
            $errors[] = "投稿内容を入力してください。";
        }

        if (empty($errors)) { // データベースに投稿を挿入
            $stmt = $pdo->prepare("INSERT INTO posts (title, message) VALUES (:title, :message)");
            $stmt->execute(['title' => $title, 'message' => $message]);
            header("Location: index.php");
            exit();
        }
    }
}

// 投稿一覧機能
$postDetails = []; // 投稿データを保存するための配列
$stmt = $pdo->prepare("SELECT id, title, message FROM posts ORDER BY id DESC");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // 取得したデータを $postDetails 配列に格納
    $postDetails[] = ['id' => $row['id'], 'title' => $row['title'], 'message' => $row['message']];
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laravel News</title>
    </head>
    <body>
        <h1><a href="./index.php">Laravel News</a></h1>
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
        <form action="./index.php" method="post" onsubmit="return confirm('本当に投稿しますか？');">
            <label for="title">タイトル:</label>
            <input type="text" id="title" name="title">
            <br><br>
            <label for="message">投稿内容:</label>
            <textarea id="message" name="message"></textarea>
            <br><br>
            <input type="submit" value="投稿">
        </form>
        <?php
        $index = 0;
        while ($index < count($postDetails)):
            $post = $postDetails[$index];
        ?>
            <h3><a href='show.php?id=<?= $post['id']; ?>'>タイトル:<?= $post['title']; ?></a></h3>
            <p>投稿内容: <?= $post['message']; ?></p>
        <?php
            $index++;
        endwhile;
        ?>
    </body>
</html>
