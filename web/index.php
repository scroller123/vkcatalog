<?php
include_once 'init.php';
include_once 'dbconnect.php';
include_once 'functions.php';

if (!($memcache_obj = memcache_connect(MEMCACHE_SERVER, MEMCACHE_PORT))) {
	$ERROR = 'Сервер memcache недоступен';
}


include 'templates/template.html.php';
?>