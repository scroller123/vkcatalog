<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vkcatalog');
define('DB_USER', 'vkcatalog');
define('DB_PASSWORD', 'YdNsvDRKm7FfFRwK');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$link) {
	$ERROR = 'Невозможно подключиться к серверу баз данных :-(';
}

if (!mysql_select_db(DB_NAME, $link)) {
	$ERROR = 'База данных недоступна :`-(';
}


?>