# 🎓 班级论坛网站

PHP + MySQL 动态网站开发技术课程设计作品

**W2400305 艾科拜尔江 & W2400304 艾合麦提江 | W24003 班**

---

## ✨ 功能特性

- 🔐 用户系统 - 注册/登录/注销，bcrypt 密码加密
- 📝 帖子管理 - 发布/编辑/删除，分类筛选，分页
- 💬 留言互动 - 为帖子留言，支持分页
- 📊 学生信息 - 50 人数据的增删改查
- 👥 用户管理 - 管理员专属
- 🎨 美观界面 - 渐变配色、响应式设计

## 🚀 快速开始

1. 导入 class.sql 到 MySQL
2. 修改 config.php 中数据库密码
3. 访问 setup_db.php 初始化
4. 测试账号：admin/admin123

## 📁 项目文件

| 核心功能 | 文件 |
|:---|:---|
| 登录注册 | login.php, register.php |
| 帖子浏览 | browse.php, index.php |
| 帖子发布 | add_post.php, edit_post.php, delete_post.php |
| 留言 | messages.php |
| 学生管理 | students.php, add_student.php, edit_student.php |
| 用户管理 | users.php |
| 数据库 | class.sql, setup_db.php |
| 样式 | style.css |
