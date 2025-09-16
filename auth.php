<?php
session_start();
require_once 'config.php';

// ログイン状態をチェック
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ログインが必要なページでのチェック
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// 現在のユーザー情報を取得
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ログイン処理
function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

// ログアウト処理
function logout() {
    session_destroy();
    redirect('index.php');
}

// ユーザー登録処理
function register($username, $password) {
    global $pdo;
    
    // パスワードをハッシュ化
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $username . '@example.com', $hashed_password]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// 投稿者チェック
function isPostAuthor($post_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $post && $post['user_id'] == $user_id;
}
?>