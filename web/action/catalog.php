<?php
global $memcache_obj;
global $CATALOG;
global $TOTAL_ITEMS;

if (!isset($_GET['order']) || ($_GET['order'] != 'id' && $_GET['order'] != 'price'))
	$_GET['order'] = 'id';

if (!isset($_GET['direction']) || ($_GET['direction'] != 'asc' && $_GET['direction'] != 'desc'))
	$_GET['direction'] = 'asc';

if (empty($_GET['page']) || is_nan($_GET['page']))
	$_GET['page'] = 1;


/*
 * remove it!
 */
// print sprintf("%0.10f", get_execution_time())."<br>";
// $res = mysql_query("select * from catalog where id mod 50 = 1 ORDER BY id ASC");
// while($row = mysql_fetch_assoc($res)) {}
// $res = mysql_query("select * from catalog where id mod 50 = 1 ORDER BY id DESC");
// while($row = mysql_fetch_assoc($res)) {}
// $res = mysql_query("select * from catalog where id mod 50 = 1 ORDER BY price ASC");
// while($row = mysql_fetch_assoc($res)) {}
// $res = mysql_query("select * from catalog where id mod 50 = 1 ORDER BY price DESC");
// while($row = mysql_fetch_assoc($res)) {}

// print sprintf("%0.10f", get_execution_time())."<br>";
// fix_time();

// $ID = mt_rand(0, 999999);
// $price = mt_rand(1, 99999);
// foreach (array('id','price') as $key) {
// 	foreach (array('ASC','DESC') as $order) {
// 		$look_after = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `catalog` "
// 				. " WHERE `id` <> " . $ID
// 				. " 	  AND `" . $key . "` " . ($order=='ASC' ? '>' : '<') . "= '" . ($key=='id' ? $ID : $price) . "'"
// 				. " ORDER BY `" . $key . "` " . $order . "" . ($key!='id' ? ', `id` DESC' : '')
// 				. " LIMIT 1"));


// 		for($i=0; $i<20000; $i++) {
// 			$last_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.mt_rand(1, 999999));
// 		}
// 	}
// }
// print sprintf("%0.10f", get_execution_time())."<br>";


$_SESSION['page']['order'] = $_GET['order'];
$_SESSION['page']['direction'] = $_GET['direction'];
$_SESSION['page']['page'] = $_GET['page'];

if ($TOTAL_ITEMS = memcache_get($memcache_obj, 'vk-' . $_GET[order] . $_GET[direction] . '-count')) {
	$TOTAL_PAGES = ceil($TOTAL_ITEMS / PAGE_SIZE);

	$page_link = mysql_fetch_assoc(mysql_query("SELECT `id`, `value` FROM `catalog_" . $_GET[order] . $_GET[direction] . "` WHERE `id` = " . (empty($_GET['page']) ? 1 : mysql_escape_string($_GET['page']))));
	$key = memcache_get($memcache_obj, 'vk-' . $_GET[order] . $_GET[direction] . '-' . $page_link['value']);
	$in = $page_link['value'];

	for ($i=0; $i < PAGE_SIZE - 1; $i++) {
		if (!empty($key['next']))
			$in .= ',' . $key['next'];
		$key = memcache_get($memcache_obj, 'vk-' . $_GET[order] . $_GET[direction] . '-' . $key['next']);
	}

	$CATALOG = array();

	$result = mysql_query("SELECT `id`, `name`, `description`, `price`, `image_url` FROM `catalog` WHERE `id` IN ({$in}) ORDER BY `{$_GET[order]}` {$_GET[direction]}");
	while ($row = mysql_fetch_assoc($result)) {
		array_push($CATALOG, $row);
	}
}

?>