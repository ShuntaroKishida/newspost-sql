<?php
// データベース接続設定
$host = 'localhost'; // データベースのホスト名またはIPアドレス
$port = 8889; // MAMPのデフォルトMySQLポート
$dbname = 'test_db'; // データベース名
$user = 'root'; // データベースのユーザ名
$password = 'root'; // ユーザのパスワード
$charset = 'utf8';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$pdo = new PDO($dsn, $user, $password);

// 投稿機能
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    if (empty($errors)) {
        // データベースに投稿を挿入
        $sql = "INSERT INTO posts (title, message) VALUES (:title, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['title' => $title, 'message' => $message]);

        header("Location: index.php");
        exit();
    } else {
        $error = current($errors);
        while ($error !== false) {
            echo "<p style='color: red;'>{$error}</p>";
            $error = next($errors);
        }
    }
}

// 投稿一覧機能
$postDetails = []; // 投稿データを保存するための配列
// SQL文を使って投稿データをデータベースから取得
$sql = "SELECT id, title, message FROM posts ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// 取得したデータを $postDetails 配列に格納
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $postDetails[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'message' => $row['message']
    ];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel News</title>
    <script>
        function confirmSubmit() {
            return confirm('本当に投稿しますか？');
        }
    </script>
</head>
<body>
    <h1><a href="./index.php">Laravel News</a></h1>
    <form action="./index.php" method="post" onsubmit="return confirmSubmit();">
        <label for="title">タイトル:</label>
        <input type="text" id="title" name="title">
        <br><br>
        <label for="message">投稿内容:</label>
        <textarea id="message" name="message"></textarea>
        <br><br>
        <input type="submit" value="投稿">
    </form>
    <?php
    if (empty($postDetails)) {
        echo "<p>まだ投稿がありません。</p>";
    } else {
        $index = 0;
        while ($index < count($postDetails)) {
            $post = $postDetails[$index];
            echo "<p><a href='show.php?id=".$post['id']."'>タイトル: ".$post['title']."</a><br>投稿内容: ".$post['message']."</p>";
            $index++;
        }
    }
    ?>
</body>
</html>
