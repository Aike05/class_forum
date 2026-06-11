<?php
/**
 * 班级论坛网站 - 数据库安装脚本
 * 访问此文件即可自动创建数据库和数据表
 *
 * 注意：此脚本独立运行，不依赖 config.php
 * 因为数据库可能在首次运行时尚不存在
 */

// 数据库连接参数（与 config.php 保持一致）
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_NAME', 'class_forum');

// 先连接 MySQL 服务器（不指定数据库）
$tempConn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);
if (!$tempConn) {
    die("<p style='color:red;'>MySQL连接失败: " . mysqli_connect_error() . "</p>");
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($tempConn, $sql)) {
    echo "<p style='color:green;'>✓ 数据库 '" . DB_NAME . "' 创建成功</p>";
} else {
    die("<p style='color:red;'>数据库创建失败: " . mysqli_error($tempConn) . "</p>");
}
mysqli_close($tempConn);

// 连接到新创建的数据库
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("<p style='color:red;'>无法连接到数据库 '" . DB_NAME . "': " . mysqli_connect_error() . "</p>");
}
mysqli_set_charset($conn, 'utf8mb4');

// 创建用户表
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nickname VARCHAR(50) NOT NULL,
    email VARCHAR(100) DEFAULT '',
    avatar VARCHAR(255) DEFAULT '',
    role ENUM('user','admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $sql);

// 创建帖子表
$sql = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    category VARCHAR(50) DEFAULT '综合讨论',
    views INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $sql);

// 创建留言表
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $sql);

// 插入管理员账号
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, nickname, role) VALUES ('admin', '$adminPass', '管理员', 'admin')";
mysqli_query($conn, $sql);

// 插入测试用户
$userPass = password_hash('123456', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, nickname, role) VALUES ('test', '$userPass', '测试同学', 'user')";
mysqli_query($conn, $sql);

// 插入示例帖子
$sql = "SELECT COUNT(*) as cnt FROM posts";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
if ($row['cnt'] == 0) {
    $samplePosts = [
        ["新学期报到帖", "大家好！新学期开始了，欢迎各位同学来报到，说说自己的新学期目标吧！", 1, "综合讨论"],
        ["数学作业交流", "第二章的微积分题目大家做得怎么样了？有什么不会的可以在这里讨论。", 2, "学习交流"],
        ["班级春游活动提议", "春天来了，我们是不是组织一次班级春游？大家有什么好的地点推荐吗？", 1, "活动通知"],
        ["英语四级备考经验", "分享一些英语四级备考的心得，希望能帮到正在准备考试的同学们。", 2, "学习交流"],
        ["运动会报名通知", "学校运动会下周开始报名，希望大家踊跃参加，为班级争光！", 1, "活动通知"],
    ];
    foreach ($samplePosts as $post) {
        $title = mysqli_real_escape_string($conn, $post[0]);
        $content = mysqli_real_escape_string($conn, $post[1]);
        $userId = (int)$post[2];
        $category = mysqli_real_escape_string($conn, $post[3]);
        mysqli_query($conn, "INSERT INTO posts (title, content, user_id, category) VALUES ('$title', '$content', $userId, '$category')");
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据库安装 - 班级论坛</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Microsoft YaHei', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1 { color: #2c3e50; margin-bottom: 10px; font-size: 28px; }
        p { color: #666; margin: 8px 0; font-size: 16px; }
        .success { color: #27ae60; font-weight: bold; }
        .info { background: #f0f8ff; border-left: 4px solid #3498db; padding: 15px 20px; margin: 20px 0; text-align: left; border-radius: 0 8px 8px 0; }
        .info strong { display: block; margin-bottom: 6px; color: #2c3e50; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: transform .2s, box-shadow .2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,.4); }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 班级论坛网站</h1>
        <p>数据库安装完成！</p>
        <div class="info">
            <strong>📋 数据库信息：</strong>
            数据库名：<?= DB_NAME ?><br>
            用户表、帖子表、留言表 已创建<br>
            <br>
            <strong>👤 测试账号：</strong>
            管理员：admin / admin123<br>
            普通用户：test / 123456
        </div>
        <a href="index.php" class="btn">🚀 进入论坛首页</a>
    </div>
</body>
</html>
