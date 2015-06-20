<?php
header('Content-type: text/html; charset=utf-8');
session_start();

define('PAGE_SIZE', 50);

define('MEMCACHE_SERVER', '91.221.36.6');
define('MEMCACHE_PORT', 12721);

define('DB_HOST', 'localhost');
define('DB_NAME', 'vkcatalog');
define('DB_USER', 'vkcatalog');
define('DB_PASSWORD', 'YdNsvDRKm7FfFRwK');


global $memcache_obj;
if (!($memcache_obj = memcache_connect(MEMCACHE_SERVER, MEMCACHE_PORT))) {
	$ERROR = 'Сервер memcache недоступен';
}

?>