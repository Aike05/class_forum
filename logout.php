<?php
/**
 * 班级论坛网站 - 注销
 */
session_start();
session_destroy();
header('Location: index.php');
exit;
