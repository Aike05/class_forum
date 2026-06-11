<?php
// Test MySQL connection and env vars
echo "<h2>Environment Variables:</h2>";
echo "MYSQLHOST: " . getenv('MYSQLHOST') . "<br>";
echo "MYSQLUSER: " . getenv('MYSQLUSER') . "<br>";
echo "MYSQLDATABASE: " . getenv('MYSQLDATABASE') . "<br>";
echo "MYSQLPORT: " . getenv('MYSQLPORT') . "<br>";
echo "Has MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'yes' : 'no') . "<br>";
echo "Has MYSQL_ROOT_PASSWORD: " . (getenv('MYSQL_ROOT_PASSWORD') ? 'yes' : 'no') . "<br>";

echo "<h2>MySQL Connection Test:</h2>";
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '123456';
$db = getenv('MYSQLDATABASE') ?: 'class_forum';

echo "Connecting to: $host ($user@$db)...<br>";
$conn = @mysqli_connect($host, $user, $pass, $db);
if ($conn) {
    echo "<b style='color:green'>MySQL connection OK!</b><br>";
    mysqli_close($conn);
} else {
    echo "<b style='color:red'>MySQL error: " . mysqli_connect_error() . "</b><br>";
}
echo "<hr><h2>All environment vars:</h2><pre>";
foreach ($_ENV as $k => $v) { echo "$k = $v\n"; }
echo "</pre>";
