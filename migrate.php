<?php
// MAMP用データベース接続設定
$host = 'localhost';
$port = '8889';
$dbname = 'blog_system';
$username = 'admin';
$password = 'admin';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>データベース更新中...</h2>";
    
    // usersテーブルが存在するかチェック
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        // usersテーブルを作成
        $pdo->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✅ usersテーブルを作成しました<br>";
    }
    
    // postsテーブルにuser_idカラムが存在するかチェック
    $result = $pdo->query("SHOW COLUMNS FROM posts LIKE 'user_id'");
    if ($result->rowCount() == 0) {
        // user_idカラムを追加
        $pdo->exec("ALTER TABLE posts ADD COLUMN user_id INT DEFAULT 1 AFTER id");
        echo "✅ postsテーブルにuser_idカラムを追加しました<br>";
        
        // デフォルトユーザーを作成（既存の記事用）
        $default_password = password_hash('admin', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT IGNORE INTO users (id, username, email, password) VALUES (1, 'admin', 'admin@example.com', ?)")
            ->execute([$default_password]);
        echo "✅ デフォルトユーザー（admin / admin123）を作成しました<br>";
        
        // 外部キー制約を追加
        try {
            $pdo->exec("ALTER TABLE posts ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
            echo "✅ 外部キー制約を追加しました<br>";
        } catch (PDOException $e) {
            echo "⚠️ 外部キー制約の追加をスキップしました（既に存在する可能性があります）<br>";
        }
    }
    
    // 初期タグデータを挿入
    $initial_tags = ['技術', 'プログラミング', 'PHP', 'JavaScript', 'CSS', 'HTML', '日記', 'その他'];
    
    foreach ($initial_tags as $tag) {
        $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tag]);
    }
    echo "✅ 初期タグデータを追加しました<br>";
    
    echo "<h3>🎉 データベースの更新が完了しました！</h3>";
    echo "<p><a href='index.php'>ブログシステムにアクセス</a></p>";
    echo "<p>デフォルトユーザー: <strong>admin</strong> / パスワード: <strong>admin123</strong></p>";
    
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>