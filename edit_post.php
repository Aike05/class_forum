<?php
/**
 * 班级论坛网站 - 编辑帖子
 */
require_once __DIR__ . '/config.php';

// 必须登录
if (!isLoggedIn()) {
    showMessage('请先登录！', 'login.php', 'error');
}

$pageTitle = '编辑帖子';
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

// 获取当前用户（必须在 config.php 加载后才能调用）
$currentUser = getCurrentUser();

// 检查权限：只有作者或管理员可以编辑
if ($currentUser['id'] != $post['user_id'] && $currentUser['role'] != 'admin') {
    showMessage('你没有权限编辑此帖子！', 'browse.php', 'error');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = cleanInput($_POST['title']);
    $content = cleanInput($_POST['content']);
    $category = cleanInput($_POST['category']);

    if (empty(trim($title)) || empty(trim($_POST['content']))) {
        $error = '请填写标题和内容！';
    } elseif (mb_strlen($_POST['title']) > 200) {
        $error = '标题不能超过200个字符！';
    } else {
        $sql = "UPDATE posts SET title='$title', content='$content', category='$category' WHERE id=$postId";
        if (mysqli_query($conn, $sql)) {
            showMessage('帖子更新成功！', "messages.php?post_id=$postId");
        } else {
            $error = '更新失败，请重试！';
        }
    }
}

require_once __DIR__ . '/header.php';

$categories = ['综合讨论', '学习交流', '活动通知', '生活分享', '求助答疑'];
?>

<div class="container">

    <div class="page-header">
        <h1>✏️ 编辑帖子</h1>
        <p>修改帖子内容</p>
    </div>

    <?php if ($error): ?>
        <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">帖子标题 *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       placeholder="请输入帖子标题" required
                       value="<?= htmlspecialchars($_POST['title'] ?? $post['title']) ?>">
            </div>

            <div class="form-group">
                <label for="category">分类</label>
                <select id="category" name="category" class="form-control">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($post['category'] === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">帖子内容 *</label>
                <textarea id="content" name="content" class="form-control" 
                          placeholder="请输入帖子内容" required><?= htmlspecialchars($_POST['content'] ?? $post['content']) ?></textarea>
            </div>

            <div class="action-group">
                <button type="submit" class="btn btn-primary">💾 保存修改</button>
                <a href="messages.php?post_id=<?= $postId ?>" class="btn btn-outline">取消</a>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
