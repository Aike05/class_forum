<?php
/**
 * 班级论坛网站 - 注册页面
 */
require_once __DIR__ . '/config.php';

// 如果已登录，直接跳转
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pageTitle = '用户注册';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawUsername = trim($_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $rawNickname = trim($_POST['nickname']);
    $rawEmail = trim($_POST['email'] ?? '');

    // 验证（使用原始输入，确保长度和格式检查准确）
    if (empty($rawUsername) || empty($password) || empty($rawNickname)) {
        $error = '请填写所有必填字段！';
    } elseif (mb_strlen($rawUsername) < 3 || mb_strlen($rawUsername) > 50) {
        $error = '用户名长度应为3-50个字符！';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少为6个字符！';
    } elseif ($password !== $password2) {
        $error = '两次输入的密码不一致！';
    } elseif (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $rawUsername)) {
        $error = '用户名只能包含字母、数字、下划线和中文！';
    } else {
        // SQL 转义仅在存入数据库时使用
        $username = cleanInput($rawUsername);
        $nickname = cleanInput($rawNickname);
        $email = cleanInput($rawEmail);
        // 检查用户名是否已存在
        $checkResult = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
        if (mysqli_num_rows($checkResult) > 0) {
            $error = '该用户名已被注册，请换一个！';
        } else {
            // 创建用户
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, nickname, email) VALUES ('$username', '$hashedPassword', '$nickname', '$email')";
            
            if (mysqli_query($conn, $sql)) {
                $success = '注册成功！请登录。';
            } else {
                $error = '注册失败，请重试！';
            }
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="auth-page">
    <div class="auth-box">
        <h2>📝 用户注册</h2>
        <p class="subtitle">加入班级论坛，一起交流学习</p>

        <?php if ($error): ?>
            <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;text-align:center;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#e8f8f0;color:#27ae60;padding:12px 16px;border-radius:8px;margin-bottom:20px;text-align:center;">
                <?= $success ?>
            </div>
            <meta http-equiv="refresh" content="2;url=login.php">
            <p style="text-align:center;color:#999;">页面将在2秒后跳转到登录页...</p>
        <?php else: ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">用户名 *</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="3-50个字符，字母/数字/下划线/中文" required
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="nickname">昵称 *</label>
                <input type="text" id="nickname" name="nickname" class="form-control" 
                       placeholder="你的显示名称" required
                       value="<?= isset($_POST['nickname']) ? htmlspecialchars($_POST['nickname']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="选填"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">密码 *</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="至少6个字符" required>
            </div>

            <div class="form-group">
                <label for="password2">确认密码 *</label>
                <input type="password" id="password2" name="password2" class="form-control" 
                       placeholder="再次输入密码" required>
            </div>

            <button type="submit" class="btn btn-primary">注 册</button>
        </form>

        <p class="switch-link">
            已有账号？<a href="login.php">立即登录 →</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
