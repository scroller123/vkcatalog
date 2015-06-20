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

if ($TOTAL_ITEMS = memcache_get($memcache_obj, 'vk-' . $_GET[order] . $_GET[direction] . '-count')) {
	$TOTAL_PAGES = round($TOTAL_ITEMS / PAGE_SIZE);

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