<?php
/**
 * 班级论坛网站 - 编辑学生信息
 */
$pageTitle = '编辑学生';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    showMessage('请先登录！', 'login.php', 'error');
}

$id = (int)($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM students WHERE xsid = $id");
$student = mysqli_fetch_assoc($result);

if (!$student) {
    showMessage('学生记录不存在', 'students.php', 'error');
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
        $error = '请填写必填项！';
    } else {
        $sql = "UPDATE students SET XH='$xh', XM='$xm', XB='$xb', CSRQ='$csrq', BJ='$bj', 
                ZY='$zy', SYD='$syd', TEL='$tel', Skill='$skill', Grjj='$grjj' WHERE xsid=$id";
        if (mysqli_query($conn, $sql)) {
            showMessage('修改成功！', 'students.php');
        } else {
            $error = '修改失败：' . mysqli_error($conn);
        }
    }
}

$currentSkills = explode('、', $student['Skill'] ?? '');
$cities = ['','北京','上海','广州','深圳','杭州','南京','成都','武汉','西安','重庆','新疆','其他'];
$skillList = ['编程','篮球','足球','音乐','绘画','阅读','羽毛球','游泳'];
?>
<div class="container">
    <div class="page-header">
        <h1>✏️ 编辑学生信息</h1>
        <p>修改「<?= htmlspecialchars($student['XM']) ?>」的资料</p>
    </div>

    <?php if ($error): ?>
        <div style="background:#fdedec;color:#e74c3c;padding:12px 16px;border-radius:8px;margin-bottom:20px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label>学号 <span style="color:red">*</span></label>
                <input type="text" name="XH" class="form-control" required maxlength="8"
                       value="<?= htmlspecialchars($student['XH']) ?>">
            </div>
            <div class="form-group">
                <label>姓名 <span style="color:red">*</span></label>
                <input type="text" name="XM" class="form-control" required maxlength="20"
                       value="<?= htmlspecialchars($student['XM']) ?>">
            </div>
            <div class="form-group">
                <label>性别</label>
                <label style="margin-right:16px;"><input type="radio" name="XB" value="男" <?= $student['XB']==='男'?'checked':'' ?>> 男</label>
                <label><input type="radio" name="XB" value="女" <?= $student['XB']==='女'?'checked':'' ?>> 女</label>
            </div>
            <div class="form-group">
                <label>出生日期 <span style="color:red">*</span></label>
                <input type="date" name="CSRQ" class="form-control" required value="<?= $student['CSRQ'] ?>">
            </div>
            <div class="form-group">
                <label>班级 <span style="color:red">*</span></label>
                <input type="text" name="BJ" class="form-control" required maxlength="6"
                       value="<?= htmlspecialchars($student['BJ']) ?>">
            </div>
            <div class="form-group">
                <label>专业</label>
                <input type="text" name="ZY" class="form-control" value="<?= htmlspecialchars($student['ZY'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>生源地</label>
                <select name="SYD" class="form-control">
                    <?php foreach ($cities as $c): ?>
                    <option value="<?= $c ?>" <?= ($student['SYD']??'')===$c?'selected':'' ?>><?= $c ?: '请选择' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>电话号码</label>
                <input type="text" name="TEL" class="form-control" maxlength="11"
                       value="<?= htmlspecialchars($student['TEL'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>特长爱好（可多选）</label>
                <div>
                    <?php foreach ($skillList as $sk): ?>
                    <label style="margin-right:14px;display:inline-flex;align-items:center;gap:3px;">
                        <input type="checkbox" name="Skill[]" value="<?= $sk ?>"
                            <?= in_array($sk, $currentSkills) ? 'checked' : '' ?>> <?= $sk ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>个人简介</label>
                <textarea name="Grjj" class="form-control" rows="4"><?= htmlspecialchars($student['Grjj'] ?? '') ?></textarea>
            </div>
            <div class="action-group">
                <button type="submit" class="btn btn-primary">💾 保存修改</button>
                <a href="students.php" class="btn btn-outline">返回列表</a>
                <a href="index.php" class="btn btn-outline">返回首页</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
