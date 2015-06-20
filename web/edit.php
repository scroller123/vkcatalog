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

// 			mysql_query("UPDATE `catalog` SET")

			if ($_POST['price'] != $data['price']) {
				foreach (array('ASC','DESC') as $order) {
					$var_key = memcache_get($memcache_obj, 'vk-' . strtolower('price' . $order) . '-'.$data['id']);
					if (!empty($var_key)) {
						$look_behind = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `catalog` WHERE "));
					}
				}
			}

			$_SESSION['infomessage'] = 'Товар сохранен';
			header("Location: http://{$_SERVER['HTTP_HOST']}/?edit&id={$_GET['id']}");
			exit();
		}
	}

	print '<h3>Редактирование товара</h3>';
	include 'form_add_edit.php';

}else{
	print 'Объект не найден';
}
global $CONTENT;
$CONTENT = ob_get_contents();
ob_clean();
?>