<?php
global $memcache_obj;

ob_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!empty($_POST['form']['name'])
			&& !empty($_POST['form']['description'])
			&& isset($_POST['form']['price'])
			&& preg_match("/^[0-9]+(\.[0-9]{1,2})?$/", $_POST['form']['price'])
	) {

		$sql = "INSERT INTO `catalog` "
			 . "(`id`, `name`, `description`, `price`, `image_url`) VALUES "
			 . "(NULL,"
			 . "'" . mysql_escape_string($_POST['form']['name']) . "',"
			 . "'" . mysql_escape_string($_POST['form']['description']) . "',"
			 . "'" . mysql_escape_string($_POST['form']['price']) . "',"
			 . "'" . mysql_escape_string($_POST['form']['image']) . "')";

		if (mysql_query($sql)) {
			$ID = mysql_insert_id();

			foreach (array('id','price') as $key) {
				foreach (array('ASC','DESC') as $order) {

					$count = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-count');
					memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-count', $count + 1, 0, 0);

					$look_after = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `catalog` "
							. " WHERE `id` <> " . $ID
							. " 	  AND `" . $key . "` " . ($order=='ASC' ? '>' : '<') . "= '" . ($key=='id' ? $ID : mysql_escape_string($_POST['form']['price'])) . "'"
							. " ORDER BY `" . $key . "` " . $order . "" . ($key!='id' ? ', `id` DESC' : '')
							. " LIMIT 1"));


					if (empty($look_after)) {
						/*
						 * is last element
						 */

						$last_page = mysql_fetch_assoc(mysql_query("SELECT `value` FROM `catalog_" . strtolower($key . $order) . "` ORDER BY `id` DESC LIMIT 1"));
						$last_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$last_page['value']);
						$lastID = $last_page['value'];

						$n = 1;
						while (!is_null($last_key['next'])) {
							$lastID = $last_key['next'];
							$last_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$last_key['next']);
							$n++;
						}

						memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$ID, array('next'=>null, 'prev'=>$lastID), 0, 0);
	 					memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$lastID, array('next'=>$ID, 'prev'=>$last_key['prev']), 0, 0);

	 					// generate last page if needed
	 					if ($n == PAGE_SIZE)
	 						mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`value`) VALUES ('{$ID}')");
					} else {
						$var_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id']);
						if (empty($var_key['prev'])) {
							/*
							 *	is first element
							 */

							memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$ID, array('next'=>$look_after['id'], 'prev'=>null), 0, 0);
							memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id'], array('next'=>$var_key['next'], 'prev'=>$ID), 0, 0);

							$pages = mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` ORDER BY `id` ASC");
							$insert_values = array();
							while($page = mysql_fetch_assoc($pages)) {
								$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$page['value']);
								$insert_values[] = "({$page['id']},{$cur_key['prev']})";
							}
							mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`id`,`value`) VALUES " . implode(",", $insert_values) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");

							// generate last page if needed
							$n = 1;
							while (!is_null($cur_key['next'])) {
								$lastID = $cur_key['next'];
								$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
								$n++;
							}

							if ($n == PAGE_SIZE)
								mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`value`) VALUES ('{$lastID}')");
						} else {
							/*
							 * is middle element
							*/

							$nextID = $look_after['id'];
							$prevID = $var_key['prev'];
							$prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$prevID);
							$next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$nextID);

							memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$ID, array('next'=>$nextID, 'prev'=>$prevID), 0, 0);
							memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$prevID, array('next'=>$ID, 'prev'=>$prev['prev']), 0, 0);
							memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$nextID, array('next'=>$next['next'], 'prev'=>$ID), 0, 0);

							// getting the next page key
							$page = null;
							$cur_key['next'] = $nextID;
							while (!($page = mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `value` = " . $cur_key['next'])))) {
								$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
								if (empty($cur_key['next']))
									break;
							}

							if (!is_null($page)) {
								$pages = mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `id` >= " . $page['id'] . " ORDER BY `id` ASC");
								$insert_values = array();
								while($page = mysql_fetch_assoc($pages)) {
									$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$page['value']);
// 									mysql_query("UPDATE `catalog_" . strtolower($key . $order) . "` SET `value` = " . $cur_key['prev'] . " WHERE `id` = " . $page['id']);
									$insert_values[] = "({$page['id']},{$cur_key['prev']})";
								}
								mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`id`,`value`) VALUES " . implode(",", $insert_values) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");

	 							// generate last page if needed
								$n = 1;
								while (!is_null($cur_key['next'])) {
									$lastID = $cur_key['next'];
									$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
									$n++;
								}

								if ($n == PAGE_SIZE)
									mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`value`) VALUES ('{$lastID}')");
							}

						}
					}
				}
			}

			$_SESSION['infomessage'] = 'Товар добавлен';
		}else{
			$_SESSION['infomessage'] = 'Ошибка добавления товара!';
		}



		header("Location: http://{$_SERVER['HTTP_HOST']}/?add");
		exit();
	}
}

?><div><a href="?<?php print http_build_query($_SESSION['page'])?>">&larr; Вернуться</a></div><?php
print '<h3>Добавление товара</h3>';
include 'form_add_edit.php';

global $CONTENT;
$CONTENT = ob_get_contents();
ob_clean();
?>