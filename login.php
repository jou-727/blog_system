<?php
require_once 'auth.php';

// 既にログインしている場合はリダイレクト
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {
        if (login($username, $password)) {
            redirect('index.php');
        } else {
            $error = 'ユーザー名またはパスワードが間違っています。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - ブログシステム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🔑 ログイン</h1>
            <nav>
                <a href="index.php" class="nav-btn">ホーム</a>
                <a href="register.php" class="nav-btn">新規登録</a>
            </nav>
        </header>

        <main>
            <div class="auth-container">
                <div class="auth-form">
                    <h2>ログイン</h2>
                    
                    <?php if ($error): ?>
                        <div class="error-messages">
                            <p class="error">❌ <?php echo h($error); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="username">ユーザー名</label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo h($_POST['username'] ?? ''); ?>" 
                                   placeholder="ユーザー名を入力してください" required>
                        </div>

                        <div class="form-group">
                            <label for="password">パスワード</label>
                            <input type="password" id="password" name="password" 
                                   placeholder="パスワード" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">ログイン</button>
                        </div>
                    </form>

                    <div class="auth-links">
                        <p>アカウントをお持ちでない方は <a href="register.php">こちらから登録</a></p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>