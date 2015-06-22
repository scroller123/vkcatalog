<?php
include_once '../src/init.php';

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$link) {
	$ERROR = 'Невозможно подключиться к серверу баз данных :-(';
}

if (!mysql_select_db(DB_NAME, $link)) {
	$ERROR = 'База данных недоступна :`-(';
}

if (empty($ERROR)) {
	mysql_query("SET NAMES UTF8");
}


?>