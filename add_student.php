<?php
/**
 * 班级论坛网站 - 添加学生信息
 */
$pageTitle = '添加学生';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    showMessage('请先登录！', 'login.php', 'error');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xh   = cleanInput($_POST['XH']);
    $xm   = cleanInput($_POST['XM']);
    $xb   = cleanInput($_POST['XB'] ?? '男');
    $csrq = cleanInput($_POST['CSRQ']);
    $bj   = cleanInput($_POST['BJ']);
    $zy   = cleanInput($_POST['ZY'] ?? '');
    $syd  = cleanInput($_POST['SYD'] ?? '');
    $tel  = cleanInput($_POST['TEL'] ?? '');
    $skill = isset($_POST['Skill']) ? implode('、', array_map('cleanInput', $_POST['Skill'])) : '';
    $grjj = cleanInput($_POST['Grjj'] ?? '');

    if (empty($xh) || empty($xm) || empty($csrq) || empty($bj)) {
        $error = '请填写学号、姓名、出生日期和班级！';
    } else {
        $sql = "INSERT INTO students (XH, XM, XB, CSRQ, BJ, ZY, SYD, TEL, Skill, Grjj) 
                VALUES ('$xh', '$xm', '$xb', '$csrq', '$bj', '$zy', '$syd', '$tel', '$skill', '$grjj')";
        if (mysqli_query($conn, $sql)) {
            showMessage('学生信息添加成功！', 'students.php');
        } else {
            $error = '添加失败：' . mysqli_error($conn);
        }
    }
}

$cities = ['','北京','上海','广州','深圳','杭州','南京','成都','武汉','西安','重庆','新疆','其他'];
$skillList = ['编程','篮球','足球','音乐','绘画','阅读','羽毛球','游泳'];
?>
<div class="container">
    <div class="page-header">
        <h1>➕ 添加学生信息</h1>
        <p>录入新同学的个人信息</p>
    </div>

    <?php if ($error): ?>
        <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label>学号 <span style="color:red">*</span></label>
                <input type="text" name="XH" class="form-control" required maxlength="8" placeholder="如 W2400305">
            </div>
            <div class="form-group">
                <label>姓名 <span style="color:red">*</span></label>
                <input type="text" name="XM" class="form-control" required maxlength="20">
            </div>
            <div class="form-group">
                <label>性别</label>
                <label style="margin-right:16px;"><input type="radio" name="XB" value="男" checked> 男</label>
                <label><input type="radio" name="XB" value="女"> 女</label>
            </div>
            <div class="form-group">
                <label>出生日期 <span style="color:red">*</span></label>
                <input type="date" name="CSRQ" class="form-control" required>
            </div>
            <div class="form-group">
                <label>班级 <span style="color:red">*</span></label>
                <input type="text" name="BJ" class="form-control" required maxlength="6" placeholder="如 W24003">
            </div>
            <div class="form-group">
                <label>专业</label>
                <input type="text" name="ZY" class="form-control" placeholder="计算机网络技术">
            </div>
            <div class="form-group">
                <label>生源地</label>
                <select name="SYD" class="form-control">
                    <?php foreach ($cities as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?: '请选择' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>电话号码</label>
                <input type="text" name="TEL" class="form-control" maxlength="11" placeholder="11位手机号">
            </div>
            <div class="form-group">
                <label>特长爱好（可多选）</label>
                <div>
                    <?php foreach ($skillList as $sk): ?>
                    <label style="margin-right:14px;display:inline-flex;align-items:center;gap:3px;">
                        <input type="checkbox" name="Skill[]" value="<?= $sk ?>"> <?= $sk ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>个人简介</label>
                <textarea name="Grjj" class="form-control" rows="4" placeholder="请输入个人简介..."></textarea>
            </div>
            <div class="action-group">
                <button type="submit" class="btn btn-primary">💾 保存提交</button>
                <a href="students.php" class="btn btn-outline">返回列表</a>
                <a href="index.php" class="btn btn-outline">返回首页</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
