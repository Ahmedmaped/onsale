
<?php
$redis = new Redis();
$redis->connect('198.251.67.150', 6379);  // عنوان الـ IP الخارجي
$redis->auth('11225588');  // كلمة المرور
echo "Connected to Redis-----";
?>
