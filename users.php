<?php
/**
 * 班级论坛网站 - 用户管理页面
 * 功能：用户列表查看、添加用户、编辑用户、删除用户
 * 权限：仅管理员可访问
 */
require_once __DIR__ . '/config.php';

// 仅管理员可访问
if (!isLoggedIn()) {
    showMessage('请先登录！', 'login.php', 'error');
}
$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'admin') {
    showMessage('仅管理员可访问用户管理！', 'index.php', 'error');
}

$pageTitle = '用户管理';
$error = '';
$success = '';

// ========== 处理用户操作 ==========

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 添加用户
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $newUsername = cleanInput($_POST['new_username']);
    $newPassword = $_POST['new_password'];
    $newNickname = cleanInput($_POST['new_nickname']);
    $newEmail = cleanInput($_POST['new_email'] ?? '');
    $newRole = in_array($_POST['new_role'], ['user', 'admin']) ? $_POST['new_role'] : 'user';

    if (empty($newUsername) || empty($newPassword) || empty($newNickname)) {
        $error = '请填写所有必填字段！';
    } elseif (strlen($newPassword) < 6) {
        $error = '密码长度至少6个字符！';
    } else {
        $checkResult = mysqli_query($conn, "SELECT id FROM users WHERE username = '$newUsername' LIMIT 1");
        if (mysqli_num_rows($checkResult) > 0) {
            $error = '用户名 "' . h($newUsername) . '" 已存在！';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, nickname, email, role) 
                    VALUES ('$newUsername', '$hashedPassword', '$newNickname', '$newEmail', '$newRole')";
            if (mysqli_query($conn, $sql)) {
                $success = '用户 "' . h($newNickname) . '" 添加成功！';
            } else {
                $error = '添加失败：' . mysqli_error($conn);
            }
        }
    }
}

// 编辑用户
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $editId = (int)$_POST['edit_id'];
    $editNickname = cleanInput($_POST['edit_nickname']);
    $editEmail = cleanInput($_POST['edit_email'] ?? '');
    $editRole = in_array($_POST['edit_role'], ['user', 'admin']) ? $_POST['edit_role'] : 'user';
    $editPassword = $_POST['edit_password'] ?? '';

    if (empty($editNickname)) {
        $error = '昵称不能为空！';
    } else {
        $updateSql = "UPDATE users SET nickname='$editNickname', email='$editEmail', role='$editRole'";
        // 如果填写了新密码则更新密码
        if (!empty($editPassword)) {
            if (strlen($editPassword) < 6) {
                $error = '密码长度至少6个字符！';
                goto skipEdit;
            }
            $hashedPassword = password_hash($editPassword, PASSWORD_DEFAULT);
            $updateSql .= ", password='$hashedPassword'";
        }
        $updateSql .= " WHERE id=$editId";
        if (mysqli_query($conn, $updateSql)) {
            $success = '用户信息更新成功！';
        } else {
            $error = '更新失败：' . mysqli_error($conn);
        }
        skipEdit:
    }
}

// 删除用户
if ($action === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    if ($deleteId == $currentUser['id']) {
        $error = '不能删除自己的账号！';
    } else {
        $checkResult = mysqli_query($conn, "SELECT role FROM users WHERE id = $deleteId");
        $targetUser = mysqli_fetch_assoc($checkResult);
        if (!$targetUser) {
            $error = '用户不存在！';
        } else {
            if (mysqli_query($conn, "DELETE FROM users WHERE id = $deleteId")) {
                $success = '用户已成功删除！（其发布的帖子和留言也将一并删除）';
            } else {
                $error = '删除失败：' . mysqli_error($conn);
            }
        }
    }
}

// ========== 分页获取用户列表 ==========
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = $searchRaw !== '' ? cleanInput($searchRaw) : '';
$where = "1=1";
if ($search) {
    $where .= " AND (username LIKE '%$search%' OR nickname LIKE '%$search%' OR email LIKE '%$search%')";
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE $where");
$totalRows = mysqli_fetch_assoc($countResult)['cnt'];
$totalPages = ceil($totalRows / $perPage);

$users = mysqli_query($conn, "
    SELECT u.*, 
        (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) as post_count,
        (SELECT COUNT(*) FROM messages m WHERE m.user_id = u.id) as msg_count
    FROM users u 
    WHERE $where
    ORDER BY u.created_at DESC 
    LIMIT $offset, $perPage
");

require_once __DIR__ . '/header.php';
?>

<div class="container">

    <div class="page-header">
        <h1>👥 用户管理</h1>
        <p>管理论坛所有注册用户（管理员功能）</p>
    </div>

    <!-- 提示信息 -->
    <?php if ($error): ?>
        <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
            ⚠️ <?= h($error) ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="background:#e8f8f0;color:#27ae60;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
            ✅ <?= h($success) ?>
        </div>
    <?php endif; ?>

    <!-- 搜索和添加按钮 -->
    <div class="card">
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
            <form method="GET" action="" style="display:flex;gap:10px;flex:1;">
                <input type="text" name="search" class="form-control" 
                       placeholder="🔍 搜索用户名/昵称/邮箱..." 
                       value="<?= h($searchRaw) ?>"
                       style="flex:1;">
                <button type="submit" class="btn btn-primary">搜索</button>
                <?php if ($searchRaw): ?>
                    <a href="users.php" class="btn btn-outline">清除</a>
                <?php endif; ?>
            </form>
            <button class="btn btn-success" onclick="document.getElementById('addUserForm').style.display='block';this.style.display='none';">
                ➕ 添加新用户
            </button>
        </div>

        <!-- 添加用户表单（默认隐藏） -->
        <div id="addUserForm" style="display:none;margin-top:20px;padding:20px;background:#f8f9fa;border-radius:12px;">
            <h3 style="margin-bottom:16px;">➕ 添加新用户</h3>
            <form method="POST" action="">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                    <div class="form-group">
                        <label>用户名 *</label>
                        <input type="text" name="new_username" class="form-control" 
                               placeholder="登录用户名" required>
                    </div>
                    <div class="form-group">
                        <label>昵称 *</label>
                        <input type="text" name="new_nickname" class="form-control" 
                               placeholder="显示名称" required>
                    </div>
                    <div class="form-group">
                        <label>密码 *</label>
                        <input type="password" name="new_password" class="form-control" 
                               placeholder="至少6个字符" required>
                    </div>
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="email" name="new_email" class="form-control" 
                               placeholder="选填">
                    </div>
                    <div class="form-group">
                        <label>角色</label>
                        <select name="new_role" class="form-control">
                            <option value="user">普通用户</option>
                            <option value="admin">管理员</option>
                        </select>
                    </div>
                </div>
                <div class="action-group" style="margin-top:10px;">
                    <button type="submit" name="add_user" class="btn btn-success">✅ 确认添加</button>
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('addUserForm').style.display='none';document.querySelector('.btn-success').style.display='inline-block';">取消</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 统计 -->
    <p style="color:#888;margin-bottom:15px;">
        共 <strong><?= $totalRows ?></strong> 名用户
        <?php if ($searchRaw): ?>（搜索："<?= h($searchRaw) ?>"）<?php endif; ?>
    </p>

    <!-- 用户列表 -->
    <div class="card" style="padding:0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>昵称</th>
                        <th>邮箱</th>
                        <th>角色</th>
                        <th>帖子数</th>
                        <th>留言数</th>
                        <th>注册时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>#<?= $user['id'] ?></td>
                            <td>
                                <div class="message-avatar" style="display:inline-block;width:28px;height:28px;font-size:12px;line-height:28px;background:<?= getAvatarColor($user['id']) ?>;vertical-align:middle;margin-right:6px;">
                                    <?= mb_substr($user['nickname'], 0, 1) ?>
                                </div>
                                <?= h($user['username']) ?>
                            </td>
                            <td><?= h($user['nickname']) ?></td>
                            <td><?= h($user['email'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'badge-danger' : 'badge-primary' ?>">
                                    <?= $user['role'] === 'admin' ? '管理员' : '普通用户' ?>
                                </span>
                            </td>
                            <td><?= $user['post_count'] ?></td>
                            <td><?= $user['msg_count'] ?></td>
                            <td><?= date('m-d H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn btn-outline btn-sm" 
                                            onclick="openEditModal(<?= $user['id'] ?>, <?= json_encode($user['nickname'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($user['email'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($user['role']) ?>)">
                                        ✏️ 编辑
                                    </button>
                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                    <a href="users.php?action=delete&id=<?= $user['id'] ?>&search=<?= urlencode($searchRaw) ?>&page=<?= $page ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm(<?= json_encode('确定要删除用户 ' . $user['nickname'] . ' 吗？\n该用户的所有帖子和留言也将被删除！', JSON_UNESCAPED_UNICODE) ?>)">
                                        🗑️ 删除
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#999;">
                                📭 暂无用户
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($searchRaw) ?>">← 上一页</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php elseif (abs($i - $page) <= 2 || $i == 1 || $i == $totalPages): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($searchRaw) ?>"><?= $i ?></a>
            <?php elseif (abs($i - $page) == 3): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($searchRaw) ?>">下一页 →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- 编辑用户弹窗 -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:16px;padding:30px;width:90%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
        <h3 style="margin-bottom:20px;">✏️ 编辑用户信息</h3>
        <form method="POST" action="">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="form-group">
                <label>昵称 *</label>
                <input type="text" name="edit_nickname" id="edit_nickname" class="form-control" required>
            </div>
            <div class="form-group">
                <label>邮箱</label>
                <input type="email" name="edit_email" id="edit_email" class="form-control">
            </div>
            <div class="form-group">
                <label>角色</label>
                <select name="edit_role" id="edit_role" class="form-control">
                    <option value="user">普通用户</option>
                    <option value="admin">管理员</option>
                </select>
            </div>
            <div class="form-group">
                <label>新密码（留空则不修改）</label>
                <input type="password" name="edit_password" class="form-control" placeholder="至少6个字符">
            </div>
            <div class="action-group" style="justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">取消</button>
                <button type="submit" name="edit_user" class="btn btn-primary">💾 保存修改</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, nickname, email, role) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nickname').value = nickname;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
// 点击遮罩关闭
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
