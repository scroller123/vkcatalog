<?php
global $memcache_obj;
global $CATALOG;

$page_link = mysql_fetch_assoc(mysql_query("SELECT `id`, `value` FROM `catalog_" . $_SESSION[order] . $_SESSION[direction] . "` WHERE `id` = " . (empty($_GET['page']) ? 1 : mysql_escape_string($_GET['page']))));
$key = memcache_get($memcache_obj, 'vk-' . $_SESSION[order] . $_SESSION[direction] . '-' . $page_link['value']);
$in = $page_link['value'];
for ($i=0; $i < PAGE_SIZE - 1; $i++) {
	$in .= ',' . $key['next'];
	$key = memcache_get($memcache_obj, 'vk-' . $_SESSION[order] . $_SESSION[direction] . '-' . $key['next']);
}

$CATALOG = array();

$result = mysql_query("SELECT `id`, `name`, `description`, `price`, `image_url` FROM `catalog` WHERE `id` IN ({$in}) ORDER BY `{$_SESSION[order]}` {$_SESSION[direction]}");
while ($row = mysql_fetch_assoc($result)) {
	array_push($CATALOG, $row);
}

?>