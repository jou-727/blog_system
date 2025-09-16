<?php
require_once 'auth.php';

// ログインチェック
requireLogin();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('index.php');
}

// 記事が存在するか確認
$stmt = $pdo->prepare("SELECT title FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    redirect('index.php');
}

// 投稿者チェック
if (!isPostAuthor($id, $_SESSION['user_id'])) {
    redirect('index.php');
}

try {
    // 記事を削除（外部キー制約により関連するpost_tagsも自動削除される）
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    
    // セッションでメッセージを設定（本来はセッション使用推奨）
    $message = "記事「" . $post['title'] . "」を削除しました。";
    
} catch (PDOException $e) {
    $message = "削除中にエラーが発生しました: " . $e->getMessage();
}

// リダイレクト前にメッセージを表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>削除完了 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🗑️ 削除完了</h1>
        </header>

        <main>
            <div class="success-message">
                <h3>✅ <?php echo h($message); ?></h3>
                <p>3秒後にホームページに移動します...</p>
                <a href="index.php" class="btn btn-primary">すぐに移動する</a>
            </div>
        </main>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html> posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    redirect('index.php');
}

// 投稿者チェック
if (!isPostAuthor($id, $_SESSION['user_id'])) {
    redirect('index.php');
} posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    redirect('index.php');
}

try {
    // 記事を削除（外部キー制約により関連するpost_tagsも自動削除される）
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    
    // セッションでメッセージを設定（本来はセッション使用推奨）
    $message = "記事「" . $post['title'] . "」を削除しました。";
    
} catch (PDOException $e) {
    $message = "削除中にエラーが発生しました: " . $e->getMessage();
}

// リダイレクト前にメッセージを表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>削除完了 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🗑️ 削除完了</h1>
        </header>

        <main>
            <div class="success-message">
                <h3>✅ <?php echo h($message); ?></h3>
                <p>3秒後にホームページに移動します...</p>
                <a href="index.php" class="btn btn-primary">すぐに移動する</a>
            </div>
        </main>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</body>
</html>