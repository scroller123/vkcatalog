<?php
global $memcache_obj;

ob_start();

?><div><a href="?<?php print http_build_query($_SESSION['page'])?>">&larr; Вернуться</a></div><?php

$data = mysql_fetch_assoc(mysql_query("SELECT `id`, `name`, `description`, `price`, `image_url` FROM `catalog` WHERE `id` = " . mysql_escape_string($_GET['id'])));
if (!empty($data)) {

	mysql_query("START TRANSACTION");

	$sql = "DELETE FROM `catalog` WHERE `id` = " . $data['id'];

	if (mysql_query($sql)) {
		foreach (array('id','price') as $key) {
			foreach (array('ASC','DESC') as $order) {

				$count = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-count');
				memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-count', $count - 1, 0, 0);

				$element_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$data['id']);
				if (!empty($element_key)) {

					// finding page
					$page = null;
					$cur_key['next'] = $data['id'];
					while (!($page = mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `value` = " . $cur_key['next'])))) {
						$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
						if (empty($cur_key['next']))
							break;
					}

					if (!empty($page)) {
						$pages = mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `id` >= " . $page['id'] . " ORDER BY `id` ASC");
						$insert_values = array();
						while($page = mysql_fetch_assoc($pages)) {
							$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$page['value']);
							if (!empty($cur_key['next'])) {
								$insert_values[] = "({$page['id']},{$cur_key['next']})";
							}
							$lastpage = $page;
						}
						mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`id`,`value`) VALUES " . implode(",", $insert_values) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");


						if (($count - 1) % PAGE_SIZE == 0) {
							mysql_query("DELETE FROM `catalog_" . strtolower($key . $order) . "` WHERE `id` >= " . $lastpage['id']);
						}

					}

					// docking the gap
					if (!empty($element_key['prev'])) {
						$element_prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev']);
						memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev'], array('next'=>$element_key['next'], 'prev'=>$element_prev['prev']), 0, 0);
					}

					if (!empty($element_key['next'])) {
						$element_next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next']);
						memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next'], array('next'=>$element_next['next'], 'prev'=>$element_key['prev']), 0, 0);
					}

				}
			}
		}

		mysql_query("COMMIT");
		$_SESSION['infomessage'] = 'Товар удален';
	} else {
		mysql_query("ROLLBACK");
		$_SESSION['infomessage'] = 'Ошибка удаления товара! '.mysql_error();
	}

	header("Location: http://{$_SERVER['HTTP_HOST']}/?" . http_build_query($_SESSION['page']));
	exit();

}else{
	print 'Объект не найден';
}
global $CONTENT;
$CONTENT = ob_get_contents();
ob_clean();
?>