<?php
require_once 'auth.php';

// ログインチェック
requireLogin();

$id = intval($_GET['id'] ?? 0);
$errors = [];
$success = false;

if ($id <= 0) {
    redirect('index.php');
}

// 記事を取得
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    redirect('index.php');
}

// 投稿者チェック
if (!isPostAuthor($id, $_SESSION['user_id'])) {
    redirect('index.php');
}

// 現在のタグを取得
$tag_stmt = $pdo->prepare("
    SELECT t.id FROM tags t 
    JOIN post_tags pt ON t.id = pt.tag_id 
    WHERE pt.post_id = ?
");
$tag_stmt->execute([$id]);
$current_tags = $tag_stmt->fetchAll(PDO::FETCH_COLUMN);

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
            
            // 記事を更新
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
            $stmt->execute([$title, $content, $id]);
            
            // 既存のタグ関連付けを削除
            $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$id]);
            
            // 新しいタグを関連付け
            if (!empty($selected_tags)) {
                $tag_stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($selected_tags as $tag_id) {
                    $tag_stmt->execute([$id, $tag_id]);
                }
            }
            
            $pdo->commit();
            $success = true;
            
            // 更新後のタグを再取得
            $tag_stmt = $pdo->prepare("
                SELECT t.id FROM tags t 
                JOIN post_tags pt ON t.id = pt.tag_id 
                WHERE pt.post_id = ?
            ");
            $tag_stmt->execute([$id]);
            $current_tags = $tag_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // データを更新
            $post['title'] = $title;
            $post['content'] = $content;
            
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
    <title>記事編集 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ 記事編集</h1>
            <nav>
                <a href="index.php" class="nav-btn">← ホームに戻る</a>
                <a href="view.php?id=<?php echo $id; ?>" class="nav-btn">記事を見る</a>
            </nav>
        </header>

        <main>
            <?php if ($success): ?>
                <div class="success-message">
                    <h3>✅ 記事が更新されました！</h3>
                </div>
            <?php endif; ?>

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
                           value="<?php echo h($_POST['title'] ?? $post['title']); ?>" 
                           placeholder="記事のタイトルを入力してください" required>
                </div>

                <div class="form-group">
                    <label for="content">本文</label>
                    <textarea id="content" name="content" rows="10" 
                              placeholder="記事の内容を書いてください" required><?php echo h($_POST['content'] ?? $post['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>タグ（複数選択可）</label>
                    <div class="tag-selection">
                        <?php foreach ($tags as $tag): ?>
                            <?php 
                            $is_checked = in_array($tag['id'], $current_tags) || in_array($tag['id'], $_POST['tags'] ?? []);
                            ?>
                            <label class="tag-checkbox">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                       <?php echo $is_checked ? 'checked' : ''; ?>>
                                <span class="checkmark"><?php echo h($tag['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">更新する</button>
                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">キャンセル</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>