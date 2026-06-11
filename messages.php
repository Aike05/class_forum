<?php
/**
 * 班级论坛网站 - 帖子详情与留言页面
 * 功能：查看帖子详情、查看留言列表、发布留言
 */
require_once __DIR__ . '/config.php';

$pageTitle = '帖子详情';
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
if ($postId <= 0) {
    showMessage('无效的帖子ID！', 'browse.php', 'error');
}

// ===== 留言发布处理（必须在 header.php 输出 HTML 之前完成） =====
$msgError = '';
$msgRedirect = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_content'])) {
    if (!isLoggedIn()) {
        $msgError = '请先登录后再留言！';
    } else {
        $rawContent = trim($_POST['message_content']);
        if ($rawContent === '') {
            $msgError = '留言内容不能为空！';
        } else {
            $msgContent = cleanInput($rawContent);
            $msgUserId = (int)$_SESSION['user_id'];
            $sql = "INSERT INTO messages (content, user_id, post_id) VALUES ('$msgContent', $msgUserId, $postId)";
            if (mysqli_query($conn, $sql)) {
                $msgRedirect = true;
            } else {
                $msgError = '留言失败，请重试！';
            }
        }
    }
}

require_once __DIR__ . '/header.php';

// 留言发布成功，JS 刷新避免重复提交
if ($msgRedirect) {
    echo '<script>window.location.href="messages.php?post_id=' . $postId . '";</script>';
    require_once __DIR__ . '/footer.php';
    exit;
}

// 获取帖子信息并增加浏览量
mysqli_query($conn, "UPDATE posts SET views = views + 1 WHERE id = $postId");
$result = mysqli_query($conn, "SELECT p.*, u.nickname, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = $postId");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    showMessage('帖子不存在！', 'browse.php', 'error');
}

// 分页获取留言
$msgPage = isset($_GET['m_page']) ? max(1, (int)$_GET['m_page']) : 1;
$msgPerPage = 10;
$msgOffset = ($msgPage - 1) * $msgPerPage;

$msgCountResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE post_id = $postId");
$msgTotalRows = mysqli_fetch_assoc($msgCountResult)['cnt'];
$msgTotalPages = ceil($msgTotalRows / $msgPerPage);

$messages = mysqli_query($conn, "
    SELECT m.*, u.nickname, u.username 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.post_id = $postId 
    ORDER BY m.created_at ASC 
    LIMIT $msgOffset, $msgPerPage
");
?>

<div class="container">

    <!-- 帖子详情 -->
    <div class="card post-detail">
        <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
        <div class="card-meta">
            <span>👤 <?= htmlspecialchars($post['nickname']) ?></span>
            <span>📂 <span class="badge badge-primary"><?= htmlspecialchars($post['category']) ?></span></span>
            <span>👁️ <?= $post['views'] ?> 浏览</span>
            <span>💬 <?= $msgTotalRows ?> 留言</span>
            <span>🕐 发布于 <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
            <?php if ($post['updated_at'] != $post['created_at']): ?>
                <span>✏️ 更新于 <?= date('Y-m-d H:i', strtotime($post['updated_at'])) ?></span>
            <?php endif; ?>
        </div>

        <!-- 作者操作按钮 -->
        <?php if (isLoggedIn() && $currentUser && ($currentUser['id'] == $post['user_id'] || $currentUser['role'] == 'admin')): ?>
        <div class="action-group" style="margin-top:16px;">
            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">✏️ 编辑帖子</a>
            <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" 
               onclick="return confirm('确定要删除这篇帖子吗？删除后无法恢复！')">🗑️ 删除帖子</a>
        </div>
        <?php endif; ?>

        <div class="post-body">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
    </div>

    <!-- 留言区域 -->
    <div class="card">
        <h2 style="margin-bottom:20px;color:#1a1a2e;">💬 留言 (<span id="msgCount"><?= $msgTotalRows ?></span>)</h2>

        <!-- 留言列表 -->
        <?php if (mysqli_num_rows($messages) > 0): ?>
            <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
            <div class="message-item">
                <div class="message-header">
                    <div class="message-avatar" style="background:<?= getAvatarColor($msg['user_id']) ?>">
                        <?= mb_substr($msg['nickname'], 0, 1) ?>
                    </div>
                    <div>
                        <span class="message-author"><?= htmlspecialchars($msg['nickname']) ?></span>
                        <span class="message-time"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                </div>
                <div class="message-content">
                    <?= nl2br(htmlspecialchars($msg['content'])) ?>
                </div>
            </div>
            <?php endwhile; ?>

            <!-- 留言分页 -->
            <?php if ($msgTotalPages > 1): ?>
            <div class="pagination" style="margin-top:20px;">
                <?php if ($msgPage > 1): ?>
                    <a href="?post_id=<?= $postId ?>&m_page=<?= $msgPage-1 ?>">← 上一页</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $msgTotalPages; $i++): ?>
                    <?php if ($i == $msgPage): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?post_id=<?= $postId ?>&m_page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($msgPage < $msgTotalPages): ?>
                    <a href="?post_id=<?= $postId ?>&m_page=<?= $msgPage+1 ?>">下一页 →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align:center;padding:40px;color:#999;">
                <div style="font-size:40px;margin-bottom:12px;">💭</div>
                <p>暂无留言，快来发表第一条留言吧！</p>
            </div>
        <?php endif; ?>

        <!-- 发布留言表单 -->
        <div style="margin-top:30px;padding-top:20px;border-top:2px solid #f0f0f0;">
            <h3 style="margin-bottom:16px;color:#1a1a2e;">✍️ 发表留言</h3>
            
            <?php if ($msgError): ?>
                <div style="background:#fdedec;color:#e74c3c;padding:10px 14px;border-radius:8px;margin-bottom:14px;">
                    <?= $msgError ?>
                </div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <textarea name="message_content" class="form-control" 
                              placeholder="写下你的想法..." rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">💬 发布留言</button>
            </form>
            <?php else: ?>
            <div style="text-align:center;padding:30px;background:#f8f9fa;border-radius:12px;">
                <p style="color:#888;margin-bottom:14px;">请登录后发表留言</p>
                <a href="login.php" class="btn btn-primary">🔑 立即登录</a>
                <a href="register.php" class="btn btn-outline" style="margin-left:10px;">📝 注册账号</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 返回 -->
    <div style="margin-top:20px;text-align:center;">
        <a href="browse.php" class="btn btn-outline">← 返回帖子列表</a>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php';
?>
