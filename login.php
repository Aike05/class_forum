<?php
/**
 * 班级论坛网站 - 登录页面
 */
require_once __DIR__ . '/config.php';

// 如果已登录，直接跳转
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pageTitle = '用户登录';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码！';
    } else {
        // 查询用户
        $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' LIMIT 1");
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            // 登录成功
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            showMessage('登录成功！欢迎回来，' . h($user['nickname']), 'index.php');
        } else {
            $error = '用户名或密码错误！';
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="auth-page">
    <div class="auth-box">
        <h2>🔑 用户登录</h2>
        <p class="subtitle">欢迎回到班级论坛</p>

        <?php if ($error): ?>
            <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;text-align:center;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="请输入用户名" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="请输入密码" required>
            </div>

            <button type="submit" class="btn btn-primary">登 录</button>
        </form>

        <p class="switch-link">
            还没有账号？<a href="register.php">立即注册 →</a>
        </p>

        <div style="margin-top:30px;padding:15px;background:#f8f9fa;border-radius:10px;font-size:13px;color:#888;">
            <strong>提示：</strong>如果忘记密码，请联系管理员重置。
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
