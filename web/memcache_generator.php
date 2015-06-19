<?php
die('Blocked');
/**
 * Generator memcache indexes
 */
require_once 'init.php';
require_once 'dbconnect.php';
include_once 'functions.php';
fix_time();
mt_srand(time());

if (!($memcache_obj = memcache_connect(MEMCACHE_SERVER, MEMCACHE_PORT))) {
	$ERROR = 'Сервер memcache недоступен';
}

if (empty($ERROR)) {
	foreach (array('id','price') as $key) {
		foreach (array('ASC','DESC') as $order) {
			$index = 1;
			$result = mysql_query("SELECT `id` FROM `catalog` ORDER BY `{$key}` {$order}");

			/*
			 * Set count & ID of the first element
			 */
			memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-count', mysql_num_rows($result), 0, 0);
			$line = mysql_fetch_array($result, MYSQL_ASSOC);
			memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-first', array('cur'=>$line['id']), 0, 0);
			mysql_query("DELETE * FROM `catalog_" . strtolower($key . $order) . "`");

			while ($next = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$var_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$line['id']);

				/*
				 * Set next & prev prop's for each element in list
				 */
				if(!empty($var_key)) {
					memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$line['id'], array('next'=>is_array($next) ? $next['id'] : null, 'prev'=>is_array($prev) ? $prev['id'] : null), 0, 0);
				}else{
					memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$line['id'], array('next'=>is_array($next) ? $next['id'] : null, 'prev'=>is_array($prev) ? $prev['id'] : null), 0, 0);
				}

				/*
				 * Save keys of the first element at each page (items per page = PAGE_SIZE)
				 */
				if ($index++ % PAGE_SIZE == 1) {
			 		mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`value`) VALUES ('{$line['id']}')");
				}

				$prev = $line;
				$line = $next;
			}

			/*
			 * Set up last element
			 */
			memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$line['id'], array('cur'=>$line['id'], 'next'=>null, 'prev'=>is_array($prev) ? $prev['id'] : null), 0, 0);
		}
	}
} else {
	print $ERROR . '<br/>';
}

print get_execution_time();
?>