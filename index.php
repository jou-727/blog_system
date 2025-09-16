<?php
require_once 'auth.php';

// タグでの検索処理
$search_tag = isset($_GET['tag']) ? $_GET['tag'] : '';

// 記事を取得（投稿者情報も含める）
$sql = "
    SELECT p.*, COALESCE(u.username, 'Unknown') as username, GROUP_CONCAT(t.name) as tag_names 
    FROM posts p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN post_tags pt ON p.id = pt.post_id
    LEFT JOIN tags t ON pt.tag_id = t.id
";

if ($search_tag) {
    $sql .= " WHERE p.id IN (
        SELECT DISTINCT p2.id FROM posts p2
        JOIN post_tags pt2 ON p2.id = pt2.post_id
        JOIN tags t2 ON pt2.tag_id = t2.id
        WHERE t2.name = :search_tag
    )";
}

$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search_tag) {
    $stmt->bindParam(':search_tag', $search_tag);
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 全タグを取得
$tag_stmt = $pdo->query("SELECT * FROM tags ORDER BY name");
$tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);

// 現在のユーザー情報を取得
$current_user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🚀 マイブログ</h1>
            <nav>
                <a href="index.php" class="nav-btn">ホーム</a>
                <?php if (isLoggedIn()): ?>
                    <a href="create.php" class="nav-btn create-btn">新規投稿</a>
                    <a href="manage_tags.php" class="nav-btn">タグ管理</a>
                    <span class="user-info">👤 <?php echo h($current_user['username']); ?></span>
                    <a href="logout.php" class="nav-btn btn-danger">ログアウト</a>
                <?php else: ?>
                    <a href="login.php" class="nav-btn">ログイン</a>
                    <a href="register.php" class="nav-btn create-btn">新規登録</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="search-section">
            <h3>タグで検索</h3>
            <div class="tag-filter">
                <a href="index.php" class="tag-item <?php echo !$search_tag ? 'active' : ''; ?>">すべて</a>
                <?php foreach ($tags as $tag): ?>
                    <a href="index.php?tag=<?php echo urlencode($tag['name']); ?>" 
                       class="tag-item <?php echo $search_tag === $tag['name'] ? 'active' : ''; ?>">
                        <?php echo h($tag['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($search_tag): ?>
                <p class="search-info">「<?php echo h($search_tag); ?>」でフィルタ中</p>
            <?php endif; ?>
        </div>

        <main>
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <h3>📝 記事がありません</h3>
                    <p>最初の記事を書いてみましょう！</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="create.php" class="create-btn">新規投稿</a>
                    <?php else: ?>
                        <a href="login.php" class="create-btn">ログインして投稿</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <header class="post-header">
                                <h2><a href="view.php?id=<?php echo $post['id']; ?>"><?php echo h($post['title']); ?></a></h2>
                                <div class="post-meta">
                                    <span class="author">👤 <?php echo h($post['username']); ?></span>
                                    <span class="date"><?php echo date('Y年m月d日 H:i', strtotime($post['created_at'])); ?></span>
                                </div>
                            </header>
                            
                            <div class="post-content">
                                <?php 
                                $content = h($post['content']);
                                echo mb_strlen($content) > 100 ? mb_substr($content, 0, 100) . '...' : $content;
                                ?>
                            </div>
                            
                            <?php if ($post['tag_names']): ?>
                                <div class="post-tags">
                                    <?php 
                                    $tag_names = explode(',', $post['tag_names']);
                                    foreach ($tag_names as $tag_name): 
                                    ?>
                                        <span class="tag"><?php echo h(trim($tag_name)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <footer class="post-actions">
                                <a href="view.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">読む</a>
                                <?php if (isLoggedIn() && isset($post['user_id']) && $current_user['id'] == $post['user_id']): ?>
                                    <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">編集</a>
                                    <button onclick="deletePost(<?php echo $post['id']; ?>)" class="btn btn-danger">削除</button>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function deletePost(id) {
            if (confirm('この記事を削除してもよろしいですか？')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }
    </script>
</body>
</html>