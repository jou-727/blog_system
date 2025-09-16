<?php
// MAMP用データベース接続設定
$host = 'localhost';
$port = '8889'; // MAMPのデフォルトMySQLポート
$dbname = 'blog_system';
$username = 'admin';
$password = 'admin'; // MAMPのデフォルトパスワード

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // データベースを作成
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    // usersテーブルを作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // postsテーブルを作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // tagsテーブルを作成
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // post_tagsテーブルを作成（多対多の関係）
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            tag_id INT NOT NULL,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
            UNIQUE KEY unique_post_tag (post_id, tag_id)
        )
    ");
    
    // 初期タグデータを挿入
    $initial_tags = ['技術', 'プログラミング', 'PHP', 'JavaScript', 'CSS', 'HTML', '日記', 'その他'];
    
    foreach ($initial_tags as $tag) {
        $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tag]);
    }
    
    echo "データベースとテーブルが正常に作成されました！";
    
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>