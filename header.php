<?php
/**
 * 班级论坛网站 - 公共头部
 */
require_once __DIR__ . '/config.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>班级论坛</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <span class="icon">🎓</span> 班级论坛
        </a>
        <ul class="nav-links">
            <li><a href="index.php">🏠 首页</a></li>
            <li><a href="browse.php">📋 浏览帖子</a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="add_post.php">✍️ 发布帖子</a></li>
            <li><a href="students.php">📋 学生信息</a></li>
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <li><a href="users.php">👥 用户管理</a></li>
            <?php endif; ?>
            <li>
                <div class="nav-user">
                    <div class="avatar-circle"><?= mb_substr($currentUser['nickname'], 0, 1) ?></div>
                    <span><?= htmlspecialchars($currentUser['nickname']) ?></span>
                    <a href="logout.php" style="color:rgba(255,255,255,.6);font-size:12px;text-decoration:none;">退出</a>
                </div>
            </li>
            <?php else: ?>
            <li><a href="login.php" class="btn-nav">🔑 登录</a></li>
            <li><a href="register.php">📝 注册</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
