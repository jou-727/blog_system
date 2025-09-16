<?php
require_once 'auth.php';

// 既にログインしている場合はリダイレクト
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // バリデーション
    if (empty($username)) {
        $errors[] = 'ユーザー名を入力してください。';
    } elseif (strlen($username) < 3) {
        $errors[] = 'ユーザー名は3文字以上で入力してください。';
    } elseif (strlen($username) > 50) {
        $errors[] = 'ユーザー名は50文字以下で入力してください。';
    }
    
    if (empty($password)) {
        $errors[] = 'パスワードを入力してください。';
    } elseif (strlen($password) < 6) {
        $errors[] = 'パスワードは6文字以上で入力してください。';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'パスワードが一致しません。';
    }
    
    // 重複チェック
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'このユーザー名は既に使用されています。';
        }
    }
    
    // 登録処理
    if (empty($errors)) {
        if (register($username, $password)) {
            $success = true;
        } else {
            $errors[] = '登録中にエラーが発生しました。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録 - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📝 ユーザー登録</h1>
            <nav>
                <a href="index.php" class="nav-btn">ホーム</a>
                <a href="login.php" class="nav-btn">ログイン</a>
            </nav>
        </header>

        <main>
            <div class="auth-container">
                <div class="auth-form">
                    <?php if ($success): ?>
                        <div class="success-message">
                            <h3>✅ 登録が完了しました！</h3>
                            <p><a href="login.php" class="btn btn-primary">ログインページへ</a></p>
                        </div>
                    <?php else: ?>
                        <h2>新規ユーザー登録</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="error-messages">
                                <?php foreach ($errors as $error): ?>
                                    <p class="error">❌ <?php echo h($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group">
                                <label for="username">ユーザー名</label>
                                <input type="text" id="username" name="username" 
                                       value="<?php echo h($_POST['username'] ?? ''); ?>" 
                                       placeholder="ユーザー名（3-50文字）" required>
                            </div>

                            <div class="form-group">
                                <label for="password">パスワード</label>
                                <input type="password" id="password" name="password" 
                                       placeholder="パスワード（6文字以上）" required>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">パスワード確認</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       placeholder="パスワードを再入力" required>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">登録する</button>
                            </div>
                        </form>

                        <div class="auth-links">
                            <p>既にアカウントをお持ちの方は <a href="login.php">こちらからログイン</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>