<?php
/**
 * 班级论坛网站 - 数据库配置文件
 */
session_start();

// 数据库连接参数（防止重复定义报错）
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '123456');
if (!defined('DB_NAME')) define('DB_NAME', 'class_forum');

// 创建数据库连接
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 检查连接
if (!$conn) {
    $errno = mysqli_connect_errno();
    $error = mysqli_connect_error();
    // 数据库不存在时给出安装指引
    if ($errno == 1049) {
        $setupUrl = dirname($_SERVER['SCRIPT_NAME']) . '/setup_db.php';
        $setupUrl = str_replace('\\', '/', $setupUrl);
        die("
        <!DOCTYPE html><html><head><meta charset='utf-8'><title>数据库未安装</title>
        <style>body{display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:'Microsoft YaHei',sans-serif;background:#f5f6fa;}
        .box{text-align:center;padding:40px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.1);max-width:500px;}
        h2{color:#e67e22;margin-bottom:12px;}p{color:#666;margin-bottom:20px;}
        .btn{display:inline-block;padding:12px 30px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:30px;text-decoration:none;font-weight:bold;}
        .btn:hover{transform:translateY(-2px);box-shadow:0 10px 25px rgba(102,126,234,.4);}
        code{background:#f0f0f0;padding:2px 8px;border-radius:4px;font-size:13px;}</style></head><body>
        <div class='box'><h2>⚠️ 数据库未安装</h2>
        <p>请先运行数据库安装脚本：</p>
        <a class='btn' href='$setupUrl'>🚀 点此安装数据库</a>
        <p style='margin-top:16px;font-size:13px;color:#999;'>或手动访问：<code>$setupUrl</code></p></div></body></html>");
    }
    die("数据库连接失败($errno): $error");
}

// 设置字符集
mysqli_set_charset($conn, 'utf8mb4');

// ===== mbstring 兼容（部分 PHP 环境未启用 mbstring 扩展） =====
if (!function_exists('mb_substr')) {
    function mb_substr($str, $start, $length = null, $encoding = 'UTF-8') {
        if ($length === null) {
            return substr($str, $start);
        }
        return substr($str, $start, $length);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $encoding = 'UTF-8') {
        return strlen($str);
    }
}

/**
 * 检查用户是否已登录
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 获取当前登录用户信息
 */
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $userId = (int)$_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId");
    return mysqli_fetch_assoc($result);
}

/**
 * 根据用户ID生成头像颜色
 */
function getAvatarColor($userId) {
    $colors = [
        '#667eea', '#764ba2', '#f093fb', '#f5576c',
        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7',
        '#fa709a', '#fee140', '#a18cd1', '#fbc2eb',
        '#ffecd2', '#fcb69f', '#ff9a9e', '#fad0c4'
    ];
    return $colors[$userId % count($colors)];
}

/**
 * 过滤输入数据（仅用于SQL安全，不转义HTML）
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

/**
 * 输出时安全转义HTML（$jsSafe=true 时转义JS字符串中的引号）
 */
function h($str, $jsSafe = false) {
    $safe = htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    if ($jsSafe) {
        $safe = str_replace(["'", "\n", "\r"], ["\\'", '\\n', '\\r'], $safe);
    }
    return $safe;
}

/**
 * 显示提示信息并跳转
 */
function showMessage($msg, $url = '', $type = 'success') {
    $color = $type === 'error' ? '#e74c3c' : '#27ae60';
    $safeMsg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>提示</title>
    <style>body{display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:'Microsoft YaHei',sans-serif;background:#f5f6fa;}
    .msg-box{text-align:center;padding:40px 60px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.1);}
    .msg-box h2{color:$color;margin-bottom:20px;}
    .msg-box p{color:#666;margin-bottom:20px;}
    .btn{display:inline-block;padding:10px 24px;background:#3498db;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;}
    .btn:hover{background:#2980b9;}</style></head><body>
    <div class='msg-box'><h2>$safeMsg</h2>";
    if ($url) {
        echo "<p>页面将在3秒后自动跳转...</p><a class='btn' href='$safeUrl'>立即跳转</a>";
        echo "<script>setTimeout(function(){window.location.href='$safeUrl';},3000);</script>";
    } else {
        echo "<a class='btn' href='javascript:history.back();'>返回</a>";
    }
    echo "</div></body></html>";
    exit;
}
?>
