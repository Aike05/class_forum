<?php
echo "<h1>Simple Test</h1>";
echo "MYSQLHOST: " . (getenv("MYSQLHOST") ?: "(not set)") . "<br>";
echo "PORT: " . (getenv("PORT") ?: "(not set)") . "<br>";
echo "<br>PHP version: " . phpversion();
