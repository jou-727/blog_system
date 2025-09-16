<?php
require_once 'auth.php';

// ログインチェック
requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $selected_tags = $_POST['tags'] ?? [];
    
    // バリデーション
    if (empty($title)) {
        $errors[] = 'タイトルを入力してください。';
    }
    if (empty($content)) {
        $errors[] = '本文を入力してください。';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 記事を挿入（user_idを追加）
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $content]);
            $post_id = $pdo->lastInsertId();
            
            // タグを関連付け
            if (!empty($selected_tags)) {
                $tag_stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    $tag_stmt->execute([$post_id, $tag_id]);
                }
            }
            
            $pdo->commit();
            $success = true;
            
            // 3秒後にリダイレクト
            header("refresh:3;url=index.php");
            
        } catch (PDOException $e) {
            $pdo->rollback();
            $errors[] = "エラーが発生しました: " . $e->getMessage();
        }
    }
}

// 利用可能なタグを取得
$tag_stmt = $pdo->query("SELECT * FROM tags ORDER BY name");
$tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規投稿 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📝 新規投稿</h1>
            <nav>
                <a href="index.php" class="nav-btn">← ホームに戻る</a>
            </nav>
        </header>

        <main>
            <?php if ($success): ?>
                <div class="success-message">
                    <h3>✅ 記事が投稿されました！</h3>
                    <p>3秒後にホームページに移動します...</p>
                    <a href="index.php">すぐに移動する</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p class="error">❌ <?php echo h($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="post-form">
                    <div class="form-group">
                        <label for="title">タイトル</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo h($_POST['title'] ?? ''); ?>" 
                               placeholder="記事のタイトルを入力してください" required>
                    </div>

                    <div class="form-group">
                        <label for="content">本文</label>
                        <textarea id="content" name="content" rows="10" 
                                  placeholder="記事の内容を書いてください" required><?php echo h($_POST['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>タグ（複数選択可）</label>
                        <div class="tag-selection">
                            <?php foreach ($tags as $tag): ?>
                                <label class="tag-checkbox">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                           <?php echo in_array($tag['id'], $_POST['tags'] ?? []) ? 'checked' : ''; ?>>
                                    <span class="checkmark"><?php echo h($tag['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">投稿する</button>
                        <a href="index.php" class="btn btn-secondary">キャンセル</a>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>