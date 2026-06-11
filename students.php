<?php
/**
 * 班级论坛网站 - 学生信息浏览/查询/编辑/删除
 */
$pageTitle = '学生信息';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

// 删除操作
if (isset($_GET['del']) && isLoggedIn()) {
    $delId = (int)$_GET['del'];
    mysqli_query($conn, "DELETE FROM students WHERE xsid = $delId");
    echo '<script>location.href="students.php";</script>';
    exit;
}

$keyword = isset($_GET['keyword']) ? cleanInput(trim($_GET['keyword'])) : '';

if ($keyword) {
    $result = mysqli_query($conn, "SELECT * FROM students WHERE XM LIKE '%$keyword%' ORDER BY xsid");
} else {
    $result = mysqli_query($conn, "SELECT * FROM students ORDER BY xsid");
}
?>
<div class="container">
    <div class="page-header">
        <h1>📋 学生信息管理</h1>
        <p>查看、查询、添加、编辑和删除学生信息</p>
    </div>

    <div class="card" style="padding:16px;margin-bottom:20px;">
        <form method="GET" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="text" name="keyword" class="form-control" style="width:200px;"
                   placeholder="输入姓名查询" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">🔍 查询</button>
            <a href="students.php" class="btn btn-outline">显示全部</a>
            <a href="add_student.php" class="btn btn-primary">➕ 添加学生</a>
            <a href="index.php" class="btn btn-outline">🏠 返回主页</a>
        </form>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <div class="table-wrap card" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>学号</th>
                    <th>姓名</th>
                    <th>性别</th>
                    <th>出生日期</th>
                    <th>班级</th>
                    <th>专业</th>
                    <th>生源地</th>
                    <th>电话</th>
                    <th>特长爱好</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($s = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $s['xsid'] ?></td>
                    <td><?= htmlspecialchars($s['XH']) ?></td>
                    <td><strong><?= htmlspecialchars($s['XM']) ?></strong></td>
                    <td><?= $s['XB'] ?></td>
                    <td><?= $s['CSRQ'] ?></td>
                    <td><?= htmlspecialchars($s['BJ']) ?></td>
                    <td><?= htmlspecialchars($s['ZY'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['SYD'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['TEL'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($s['Skill'] ?? '-') ?></td>
                    <td style="white-space:nowrap;">
                        <a href="edit_student.php?id=<?= $s['xsid'] ?>" class="btn btn-outline btn-sm">✏️ 编辑</a>
                        <a href="students.php?del=<?= $s['xsid'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('确定删除该学生记录吗？')">🗑️ 删除</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <p style="text-align:right;color:#888;font-size:13px;margin-top:8px;">
        共 <?= mysqli_num_rows($result) ?> 条记录
    </p>
    <?php else: ?>
    <div class="card" style="text-align:center;padding:60px 30px;">
        <div style="font-size:48px;margin-bottom:16px;">📭</div>
        <p style="color:#999;font-size:16px;">
            <?= $keyword ? '没有找到匹配的学生' : '暂无学生信息，请先添加' ?>
        </p>
        <a href="add_student.php" class="btn btn-primary" style="margin-top:12px;">➕ 添加学生</a>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
