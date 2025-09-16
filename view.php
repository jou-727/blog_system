<?php
require_once 'auth.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('index.php');
}

// 記事とタグ、投稿者情報を取得
$stmt = $pdo->prepare("
    SELECT p.*, u.username, GROUP_CONCAT(t.name) as tag_names 
    FROM posts p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN post_tags pt ON p.id = pt.post_id
    LEFT JOIN tags t ON pt.tag_id = t.id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    redirect('index.php');
}

// 現在のユーザー情報を取得
$current_user = getCurrentUser();
$is_author = isLoggedIn() && isPostAuthor($id, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($post['title']); ?> - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📖 記事詳細</h1>
            <nav>
                <a href="index.php" class="nav-btn">← ホームに戻る</a>
                <?php if ($is_author): ?>
                    <a href="edit.php?id=<?php echo $id; ?>" class="nav-btn">編集</a>
                    <button onclick="deletePost(<?php echo $id; ?>)" class="nav-btn btn-danger">削除</button>
                <?php endif; ?>
            </nav>
        </header>

        <main>
            <article class="post-detail">
                <header class="post-header">
                    <h1 class="post-title"><?php echo h($post['title']); ?></h1>
                    <div class="post-meta">
                        <div class="author-info">
                            <span class="author">👤 投稿者: <?php echo h($post['username']); ?></span>
                        </div>
                        <div class="date-info">
                            <span class="created">作成日: <?php echo date('Y年m月d日 H:i', strtotime($post['created_at'])); ?></span>
                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                <span class="updated">更新日: <?php echo date('Y年m月d日 H:i', strtotime($post['updated_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($post['tag_names']): ?>
                        <div class="post-tags">
                            <?php 
                            $tag_names = explode(',', $post['tag_names']);
                            foreach ($tag_names as $tag_name): 
                            ?>
                                <a href="index.php?tag=<?php echo urlencode(trim($tag_name)); ?>" 
                                   class="tag"><?php echo h(trim($tag_name)); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <div class="post-content">
                    <?php echo nl2br(h($post['content'])); ?>
                </div>
            </article>

            <div class="post-actions">
                <?php if ($is_author): ?>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary">編集する</a>
                    <button onclick="deletePost(<?php echo $id; ?>)" class="btn btn-danger">削除する</button>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">一覧に戻る</a>
            </div>
        </main>
    </div>

    <script>
        function deletePost(id) {
            if (confirm('この記事を削除してもよろしいですか？\n削除後は元に戻せません。')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }
    </script>
</body>
</html>