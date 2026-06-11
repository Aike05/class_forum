<?php
/**
 * 班级论坛网站 - 删除帖子
 */
require_once __DIR__ . '/config.php';

// 必须登录
if (!isLoggedIn()) {
    showMessage('请先登录！', 'login.php', 'error');
}

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($postId <= 0) {
    showMessage('无效的帖子ID！', 'browse.php', 'error');
}

// 获取帖子信息
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id = $postId");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    showMessage('帖子不存在！', 'browse.php', 'error');
}

// 获取当前用户
$currentUser = getCurrentUser();

// 检查权限
if ($currentUser['id'] != $post['user_id'] && $currentUser['role'] != 'admin') {
    showMessage('你没有权限删除此帖子！', 'browse.php', 'error');
}

// 执行删除（messages表的外键设置了CASCADE，相关留言会自动删除）
$sql = "DELETE FROM posts WHERE id = $postId";
if (mysqli_query($conn, $sql)) {
    showMessage('帖子已成功删除！', 'browse.php');
} else {
    showMessage('删除失败，请重试！', "messages.php?post_id=$postId", 'error');
}
