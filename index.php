<?php
/**
 * 班级论坛网站 - 主页
 */
$pageTitle = '首页';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

// 统计信息
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
$postCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM posts"))['cnt'];
$msgCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages"))['cnt'];

// 最新帖子
$latestPosts = mysqli_query($conn, "
    SELECT p.*, u.nickname, u.username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 6
");

// 热门帖子（按浏览量）
$hotPosts = mysqli_query($conn, "
    SELECT p.*, u.nickname, u.username,
        (SELECT COUNT(*) FROM messages m WHERE m.post_id = p.id) as msg_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.views DESC 
    LIMIT 5
");
?>

<div class="container">

    <!-- 欢迎区域 -->
    <div class="page-header" style="text-align:center;padding:40px 0 20px;">
        <h1>🎓 欢迎来到班级论坛</h1>
        <p>同学们交流学习、分享生活的温馨家园</p>
    </div>

    <!-- 统计数据 -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num"><?= $userCount ?></div>
            <div class="stat-label">👥 班级成员</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= $postCount ?></div>
            <div class="stat-label">📝 帖子总数</div>
        </div>
        <div class="stat-card">
            <div class="stat-num"><?= $msgCount ?></div>
            <div class="stat-label">💬 留言总数</div>
        </div>
    </div>

    <!-- 功能入口 -->
    <div class="feature-grid">
        <a href="browse.php" class="feature-card">
            <div class="icon">📋</div>
            <h3>浏览帖子</h3>
            <p>查看班级所有讨论内容</p>
        </a>
        <a href="add_post.php" class="feature-card">
            <div class="icon">✍️</div>
            <h3>发布新帖</h3>
            <p>分享你的想法和问题</p>
        </a>
        <?php if (!isLoggedIn()): ?>
        <a href="login.php" class="feature-card">
            <div class="icon">🔑</div>
            <h3>登录账号</h3>
            <p>已有账号？立即登录</p>
        </a>
        <a href="register.php" class="feature-card">
            <div class="icon">📝</div>
            <h3>注册加入</h3>
            <p>成为班级论坛的一员</p>
        </a>
        <?php endif; ?>
    </div>

    <!-- 最新帖子 -->
    <div class="page-header">
        <h1>📌 最新帖子</h1>
        <p>最近发布的内容</p>
    </div>

    <?php if (mysqli_num_rows($latestPosts) > 0): ?>
        <?php while ($post = mysqli_fetch_assoc($latestPosts)): ?>
        <div class="card">
            <div class="card-title">
                <a href="messages.php?post_id=<?= $post['id'] ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </div>
            <div class="card-meta">
                <span>👤 <?= htmlspecialchars($post['nickname']) ?></span>
                <span>📂 <?= htmlspecialchars($post['category']) ?></span>
                <span>👁️ <?= $post['views'] ?> 浏览</span>
                <span>🕐 <?= date('m-d H:i', strtotime($post['created_at'])) ?></span>
            </div>
            <div class="card-content">
                <?= mb_substr(strip_tags(htmlspecialchars($post['content'])), 0, 150) ?>...
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card" style="text-align:center;color:#999;">
            <p>还没有帖子，快来发布第一个吧！</p>
        </div>
    <?php endif; ?>

    <!-- 热门帖子 -->
    <?php if (mysqli_num_rows($hotPosts) > 0): ?>
    <div class="page-header" style="margin-top:20px;">
        <h1>🔥 热门帖子</h1>
        <p>大家最关注的内容</p>
    </div>
    <div class="table-wrap card" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>标题</th>
                    <th>作者</th>
                    <th>分类</th>
                    <th>浏览</th>
                    <th>留言</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = mysqli_fetch_assoc($hotPosts)): ?>
                <tr>
                    <td>
                        <a href="messages.php?post_id=<?= $post['id'] ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($post['nickname']) ?></td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($post['category']) ?></span></td>
                    <td><?= $post['views'] ?></td>
                    <td><?= $post['msg_count'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
