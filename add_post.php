<?php
/**
 * 班级论坛网站 - 发布新帖
 */
require_once __DIR__ . '/config.php';

// 必须登录
if (!isLoggedIn()) {
    showMessage('请先登录后再发布帖子！', 'login.php', 'error');
}

$pageTitle = '发布新帖';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = cleanInput($_POST['title']);
    $content = cleanInput($_POST['content']);
    $category = cleanInput($_POST['category']);
    $userId = (int)$_SESSION['user_id'];

    if (empty(trim($title)) || empty(trim($_POST['content']))) {
        $error = '请填写标题和内容！';
    } elseif (mb_strlen($_POST['title']) > 200) {
        $error = '标题不能超过200个字符！';
    } else {
        $sql = "INSERT INTO posts (title, content, user_id, category) VALUES ('$title', '$content', $userId, '$category')";
        if (mysqli_query($conn, $sql)) {
            $postId = mysqli_insert_id($conn);
            showMessage('帖子发布成功！', "messages.php?post_id=$postId");
        } else {
            $error = '发布失败，请重试！';
        }
    }
}

require_once __DIR__ . '/header.php';

// 获取所有分类
$catResult = mysqli_query($conn, "SELECT DISTINCT category FROM posts ORDER BY category");
$categories = ['综合讨论', '学习交流', '活动通知', '生活分享', '求助答疑'];
?>

<div class="container">

    <div class="page-header">
        <h1>✍️ 发布新帖</h1>
        <p>分享你的想法、问题或通知</p>
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
                       placeholder="请输入帖子标题（不超过200字）" required
                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="category">分类</label>
                <select id="category" name="category" class="form-control">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">帖子内容 *</label>
                <textarea id="content" name="content" class="form-control" 
                          placeholder="请输入帖子内容，可以换行..." required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            </div>

            <div class="action-group">
                <button type="submit" class="btn btn-primary">📮 发布帖子</button>
                <a href="browse.php" class="btn btn-outline">取消</a>
            </div>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
