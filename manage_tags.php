<?php
require_once 'auth.php';

// ログインチェック
requireLogin();

$message = '';
$error = '';

// タグ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $tag_name = trim($_POST['tag_name'] ?? '');
    
    if (empty($tag_name)) {
        $error = 'タグ名を入力してください。';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
            $stmt->execute([$tag_name]);
            $message = "タグ「{$tag_name}」を追加しました。";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // 重複エラー
                $error = 'このタグは既に存在します。';
            } else {
                $error = 'エラーが発生しました: ' . $e->getMessage();
            }
        }
    }
}

// タグ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tag'])) {
    $tag_id = intval($_POST['tag_id']);
    
    try {
        // タグが使用されているか確認
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM post_tags WHERE tag_id = ?");
        $check_stmt->execute([$tag_id]);
        $usage_count = $check_stmt->fetchColumn();
        
        if ($usage_count > 0) {
            $error = "このタグは {$usage_count} 個の記事で使用されているため削除できません。";
        } else {
            $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
            $stmt->execute([$tag_id]);
            $message = 'タグを削除しました。';
        }
    } catch (PDOException $e) {
        $error = 'エラーが発生しました: ' . $e->getMessage();
    }
}

// タグ一覧を取得
$stmt = $pdo->query("
    SELECT t.*, COUNT(pt.post_id) as usage_count 
    FROM tags t 
    LEFT JOIN post_tags pt ON t.id = pt.tag_id 
    GROUP BY t.id 
    ORDER BY t.name
");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タグ管理 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏷️ タグ管理</h1>
            <nav>
                <a href="index.php" class="nav-btn">← ホームに戻る</a>
            </nav>
        </header>

        <main>
            <?php if ($message): ?>
                <div class="success-message">
                    <h3>✅ <?php echo h($message); ?></h3>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-messages">
                    <p class="error">❌ <?php echo h($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="tag-management">
                <h2>新しいタグを追加</h2>
                <form method="POST" class="post-form">
                    <div class="form-group">
                        <label for="tag_name">タグ名</label>
                        <input type="text" id="tag_name" name="tag_name" 
                               placeholder="新しいタグ名を入力してください" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_tag" class="btn btn-primary">タグを追加</button>
                    </div>
                </form>
            </div>

            <div class="tag-management">
                <h2>既存のタグ</h2>
                <?php if (empty($tags)): ?>
                    <p>タグがありません。</p>
                <?php else: ?>
                    <div class="tag-list">
                        <?php foreach ($tags as $tag): ?>
                            <div class="tag-list-item">
                                <div class="tag-info">
                                    <strong><?php echo h($tag['name']); ?></strong>
                                    <small>(<?php echo $tag['usage_count']; ?>個の記事で使用)</small>
                                </div>
                                <div class="tag-actions">
                                    <?php if ($tag['usage_count'] == 0): ?>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('このタグを削除してもよろしいですか？')">
                                            <input type="hidden" name="tag_id" value="<?php echo $tag['id']; ?>">
                                            <button type="submit" name="delete_tag" class="btn btn-danger">削除</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="tag" style="background: #fed7d7; color: #742a2a;">使用中</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>