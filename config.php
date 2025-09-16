<?php
// MAMP用データベース接続設定
$host = 'localhost';
$port = '8889'; // MAMPのデフォルトMySQLポート
$dbname = 'blog_system';
$username = 'root';
$password = 'root'; // MAMPのデフォルトパスワード

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}

// 共通関数
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>