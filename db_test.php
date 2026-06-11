<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MySQL Connection Test</h2>";
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '123456';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: '3306';

echo "Host: $host<br>";
echo "User: $user<br>";
echo "DB: $db<br>";
echo "Port: $port<br>";
echo "Password set: " . ($pass ? 'yes('.strlen($pass).' chars)' : 'no') . "<br><br>";

$conn = @mysqli_connect($host, $user, $pass, $db, (int)$port);
if ($conn) {
    echo "<b style='color:green'>MySQL connected!</b><br>";
    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
    $row = mysqli_fetch_assoc($r);
    echo "users count: " . $row['c'] . "<br>";
    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM students");
    $row = mysqli_fetch_assoc($r);
    echo "students count: " . $row['c'] . "<br>";
    mysqli_close($conn);
} else {
    echo "<b style='color:red'>Error: " . mysqli_connect_error() . "</b><br>";
}
