<?php
/**
 * 班级论坛网站 - 浏览帖子页面
 * 支持搜索、分类筛选、分页
 */
$pageTitle = '浏览帖子';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

// 获取分类列表用于筛选
$catResult = mysqli_query($conn, "SELECT DISTINCT category FROM posts ORDER BY category");
$categories = [];
while ($row = mysqli_fetch_assoc($catResult)) {
    $categories[] = $row['category'];
}

// 搜索和筛选参数（$searchRaw/$categoryRaw 用于回显，$searchSql/$categorySql 用于SQL）
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryRaw = isset($_GET['category']) ? trim($_GET['category']) : '';
$searchSql = $searchRaw !== '' ? cleanInput($searchRaw) : '';
$categorySql = $categoryRaw !== '' ? cleanInput($categoryRaw) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 构建查询条件
$where = "1=1";
if ($searchSql) {
    $where .= " AND (p.title LIKE '%$searchSql%' OR p.content LIKE '%$searchSql%')";
}
if ($categorySql) {
    $where .= " AND p.category = '$categorySql'";
}

// 获取总记录数
$countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM posts p WHERE $where");
$totalRows = mysqli_fetch_assoc($countResult)['cnt'];
$totalPages = ceil($totalRows / $perPage);

// 获取帖子列表
$posts = mysqli_query($conn, "
    SELECT p.*, u.nickname, u.username,
        (SELECT COUNT(*) FROM messages m WHERE m.post_id = p.id) as msg_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE $where
    ORDER BY p.created_at DESC 
    LIMIT $offset, $perPage
");
?>

<div class="container">

    <div class="page-header">
        <h1>📋 浏览帖子</h1>
        <p>搜索和浏览班级所有讨论</p>
    </div>

    <!-- 搜索和筛选栏 -->
    <div class="card">
        <form method="GET" action="" class="search-bar">
            <input type="text" name="search" class="form-control" 
                   placeholder="🔍 搜索帖子标题或内容..." 
                   value="<?= htmlspecialchars($searchRaw) ?>">
            <select name="category" class="form-control">
                <option value="">📂 全部分类</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryRaw === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">搜索</button>
            <?php if ($searchRaw || $categoryRaw): ?>
                <a href="browse.php" class="btn btn-outline">清除筛选</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- 搜索结果统计 -->
    <p style="color:#888;margin-bottom:20px;">
        共找到 <strong><?= $totalRows ?></strong> 篇帖子
        <?php if ($searchRaw): ?>（搜索："<?= htmlspecialchars($searchRaw) ?>"）<?php endif; ?>
        <?php if ($categoryRaw): ?>（分类：<?= htmlspecialchars($categoryRaw) ?>）<?php endif; ?>
    </p>

    <!-- 帖子列表 -->
    <?php if (mysqli_num_rows($posts) > 0): ?>
        <?php while ($post = mysqli_fetch_assoc($posts)): ?>
        <div class="card">
            <div class="card-title">
                <a href="messages.php?post_id=<?= $post['id'] ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </div>
            <div class="card-meta">
                <span>👤 <?= htmlspecialchars($post['nickname']) ?></span>
                <span>
                    <a href="?category=<?= urlencode($post['category']) ?>">
                        📂 <?= htmlspecialchars($post['category']) ?>
                    </a>
                </span>
                <span>👁️ <?= $post['views'] ?> 浏览</span>
                <span>💬 <?= $post['msg_count'] ?> 留言</span>
                <span>🕐 <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></span>
            </div>
            <div class="card-content">
                <?= mb_substr(strip_tags(htmlspecialchars($post['content'])), 0, 200) ?>...
            </div>
            <?php if (isLoggedIn() && $currentUser && $currentUser['id'] == $post['user_id']): ?>
            <div class="action-group" style="margin-top:16px;">
                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-outline btn-sm">✏️ 编辑</a>
                <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-danger btn-sm" 
                   onclick="return confirm('确定要删除这篇帖子吗？删除后无法恢复！')">🗑️ 删除</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>

        <!-- 分页 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($searchRaw) ?>&category=<?= urlencode($categoryRaw) ?>">← 上一页</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php elseif (abs($i - $page) <= 2 || $i == 1 || $i == $totalPages): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($searchRaw) ?>&category=<?= urlencode($categoryRaw) ?>"><?= $i ?></a>
                <?php elseif (abs($i - $page) == 3): ?>
                    <span>...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($searchRaw) ?>&category=<?= urlencode($categoryRaw) ?>">下一页 →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="card" style="text-align:center;padding:60px 30px;">
            <div style="font-size:48px;margin-bottom:16px;">📭</div>
            <p style="color:#999;font-size:16px;">没有找到相关帖子</p>
            <?php if ($searchRaw || $categoryRaw): ?>
                <a href="browse.php" class="btn btn-outline" style="margin-top:12px;">清除筛选条件</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
