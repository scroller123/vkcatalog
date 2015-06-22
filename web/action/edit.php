<?php
global $memcache_obj;

ob_start();

?><div><a href="?<?php print http_build_query($_SESSION['page'])?>">&larr; Вернуться</a></div><?php

$data = mysql_fetch_assoc(mysql_query("SELECT `id`, `name`, `description`, `price`, `image_url` FROM `catalog` WHERE `id` = " . mysql_escape_string($_GET['id'])));
if (!empty($data)) {
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!empty($_POST['form']['name'])
				&& !empty($_POST['form']['description'])
				&& isset($_POST['form']['price'])
				&& preg_match("/^[0-9]+(\.[0-9]{1,2})?$/", $_POST['form']['price'])
		) {

			mysql_query("START TRANSACTION");

			$sql = "UPDATE `catalog` SET "
				. "`name` = '" . mysql_escape_string($_POST['form']['name']) . "',"
				. "`description` = '" . mysql_escape_string($_POST['form']['description']) . "',"
				. "`price` = '" . mysql_escape_string($_POST['form']['price']) . "',"
				. "`image_url` = '" . mysql_escape_string($_POST['form']['image']) . "' "
				. "WHERE `id` = " . $data['id'];

			if (mysql_query($sql)) {
				// price changed?
				if ($_POST['form']['price'] != $data['price']) {
					$key = 'price'; // change only price page keys, id page key remain the same
					foreach (array('ASC','DESC') as $order) {
						$element_key = memcache_get($memcache_obj, 'vk-' . strtolower('price' . $order) . '-'.$data['id']);
						if (!empty($element_key)) {

							$look_after = mysql_fetch_assoc(mysql_query("SELECT `id`,`price` FROM `catalog`"
									. " WHERE `id` <> " . $data['id']
									. " 	  AND `" . $key . "` " . ($order=='ASC' ? '>' : '<') . "= '" . mysql_escape_string($_POST['form']['price']) . "'"
									. " ORDER BY `" . $key . "` " . $order . ", `id` DESC"
									. " LIMIT 1"));

							// PRICE PAGE KEYS UPDATE
							if (empty($look_after) && !empty($element_key['next']) || !empty($look_after) && $look_after['id'] != $element_key['next'])
							{
								// getting the page key at last position
								$pageFrom = null;
								$cur_key['next'] = $data['id'];
								while (!($pageFrom = mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `value` = " . $cur_key['next'])))) {
									$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
									if (empty($cur_key['next']))
										break;
								}
								if (!empty($pageFrom)) {
									$pageFromIsPageKey =  mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `value` = " . $data['id']));
								}

								// getting the page key at new position
								$pageTo = null;
								if (!empty($look_after)) {
									$look_after_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id']);
									$cur_key['next'] = $look_after['id'];
									while (!($pageTo = mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `value` = " . $cur_key['next'])))) {
										$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$cur_key['next']);
										if (empty($cur_key['next']))
											break;
									}
								} else {
									$last_page = mysql_fetch_assoc(mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` ORDER BY `id` DESC LIMIT 1"));
									$last_page_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$last_page['value']);
								}

								// update price page keys
								if (!empty($pageFrom) && !empty($pageTo) && $pageFrom['id'] < $pageTo['id'] || !empty($pageFrom) && empty($pageTo)) {
									$pages = mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `id` >= " . $pageFrom['id'] . (!empty($pageTo['id']) ? " AND `id` < " . $pageTo['id'] : "") . " ORDER BY `id` ASC");
									$insert_values = array();
									while($page = mysql_fetch_assoc($pages)) {
										$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$page['value']);

										if (!empty($look_after) && $page['value'] == $look_after_key['prev'] || empty($look_after) && $last_page['id'] == $page['id'] && empty($last_page_key['next'])) {
// 											mysql_query("UPDATE `catalog_" . strtolower($key . $order) . "` SET `value` = " . $data['id'] . " WHERE `id` = " . $page['id']);
											$insert_values[] = "({$page['id']},{$data['id']})";
										} else {
// 											mysql_query("UPDATE `catalog_" . strtolower($key . $order) . "` SET `value` = " . $cur_key['next'] . " WHERE `id` = " . $page['id']);
											$insert_values[] = "({$page['id']},{$cur_key['next']})";
										}
									}
									mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`id`,`value`) VALUES " . implode(",", $insert_values) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");

								} else if (!empty($pageFrom) &&  $pageTo['id'] < $pageFrom['id'] || empty($pageFrom)) {
									$pages = mysql_query("SELECT `id`,`value` FROM `catalog_" . strtolower($key . $order) . "` WHERE `id` >= " . $pageTo['id'] . (!empty($pageFrom['id']) ? " AND `id` <" . (!empty($pageFromIsPageKey)?"=":"") . $pageFrom['id'] : "") . " ORDER BY `id` ASC");
									$insert_values = array();
									while($page = mysql_fetch_assoc($pages)) {
										$cur_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$page['value']);

										if (!empty($look_after) && $page['value'] == $look_after['id'] /*|| empty($look_after_key['prev']) && $page['value'] == $look_after['id']*/) {
// 											mysql_query("UPDATE `catalog_" . strtolower($key . $order) . "` SET `value` = " . $data['id'] . " WHERE `id` = " . $page['id']);
											$insert_values[] = "({$page['id']},{$data['id']})";
										} else {
// 											mysql_query("UPDATE `catalog_" . strtolower($key . $order) . "` SET `value` = " . $cur_key['prev'] . " WHERE `id` = " . $page['id']);
											$insert_values[] = "({$page['id']},{$cur_key['prev']})";
										}
									}
									mysql_query("INSERT INTO `catalog_" . strtolower($key . $order) . "` (`id`,`value`) VALUES " . implode(",", $insert_values) . " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
								}
							}


							// MEMCACHE UPDATE
							if (empty($look_after) && !empty($element_key['next'])) { // element has been moved in price sort
								/*
								 * is last element
								*/

								// docking the gap
								if (!empty($element_key['prev'])) {
									$element_prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev']);
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev'], array('next'=>$element_key['next'], 'prev'=>$element_prev['prev']), 0, 0);
								}

								if (!empty($element_key['next'])) {
									$element_next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next']);
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next'], array('next'=>$element_next['next'], 'prev'=>$element_key['prev']), 0, 0);
								}

								// update link keys
								$last_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$last_page['value']);
								$lastID = $last_page['value'];

								$n = 1;
								while (!empty($last_key['next'])) { // null
									$lastID = $last_key['next'];
									$last_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$last_key['next']);
									$n++;
								}

								memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-' . $data['id'], array('next'=>null, 'prev'=>$lastID), 0, 0);
								memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-' . $lastID, array('next'=>$data['id'], 'prev'=>$last_key['prev']), 0, 0);


							} else if (!empty($look_after) && $look_after['id'] != $element_key['next']) { // element has been moved in price sort
								$var_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id']);
								if (empty($var_key['prev'])) {
									/*
									 *	is first element
									*/

									// docking the gap
									if (!empty($element_key['prev'])) {
										$element_prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev']);
										memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev'], array('next'=>$element_key['next'], 'prev'=>$element_prev['prev']), 0, 0);
									}

									if (!empty($element_key['next'])) {
										$element_next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next']);
										memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next'], array('next'=>$element_next['next'], 'prev'=>$element_key['prev']), 0, 0);
									}

									$var_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id']);

									// update link keys
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$data['id'], array('next'=>$look_after['id'], 'prev'=>null), 0, 0);
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id'], array('next'=>$var_key['next'], 'prev'=>$data['id']), 0, 0);

								} else {
									/*
									 * is middle element
									*/

									// docking the gap
									if (!empty($element_key['prev'])) {
										$element_prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev']);
										memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['prev'], array('next'=>$element_key['next'], 'prev'=>$element_prev['prev']), 0, 0);
									}

									if (!empty($element_key['next'])) {
										$element_next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next']);
										memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$element_key['next'], array('next'=>$element_next['next'], 'prev'=>$element_key['prev']), 0, 0);
									}

									$var_key = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$look_after['id']);

									$nextID = $look_after['id'];
									$prevID = $var_key['prev'];
									$prev = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$prevID);
									$next = memcache_get($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$nextID);

									memcache_set($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$data['id'], array('next'=>$nextID, 'prev'=>$prevID), 0, 0);
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$prevID, array('next'=>$data['id'], 'prev'=>$prev['prev']), 0, 0);
									memcache_replace($memcache_obj, 'vk-' . strtolower($key . $order) . '-'.$nextID, array('next'=>$next['next'], 'prev'=>$data['id']), 0, 0);


								}
							}

						}
					}
				}

				mysql_query("COMMIT");
				$_SESSION['infomessage'] = 'Товар сохранен';
			} else {
				mysql_query("ROLLBACK");
				$_SESSION['infomessage'] = 'Ошибка сохранения товара!';
			}

			header("Location: http://{$_SERVER['HTTP_HOST']}/?edit&id={$_GET['id']}");
			exit();
		}
	}

	print '<h3>Редактирование товара</h3>';
	include '../src/form_add_edit.php';

}else{
	print 'Объект не найден';
}
global $CONTENT;
$CONTENT = ob_get_contents();
ob_clean();
?>