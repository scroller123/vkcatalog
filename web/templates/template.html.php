<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>VK CATALOG</title>
	<link rel="stylesheet" href="templates/css/styles.css" type="text/css" />
</head>
<body>
	<div class="content_wrap">
		<div class="content">
			<h1>VK CATALOG</h1>
			<?php if ($ERROR):?>
				<div class="error"><?php print $ERROR;?></div>
			<?php else:?>

				<?php if (!empty($CATALOG)):?>
					<table class="catalog">
					<tr class="head">
						<td class="id">
							<a href="?order=id&direction=<?php if ($_GET['order'] == 'id' && $_GET['direction'] == 'asc'):?>desc<?php else:?>asc<?php endif;?>">ID</a>
							<?php if ($_GET['order']=='id'):?>
								<?php if ($_GET['direction']=='asc'):?>
									&darr;
								<?php else:?>
									&uarr;
								<?php endif;?>
							<?php endif;?>

						</td>
						<td class="image">
						<td class="name">Название</td>
						<td class="price">
							<a href="?order=price&direction=<?php if ($_GET['order'] == 'price' && $_GET['direction'] == 'asc'):?>desc<?php else:?>asc<?php endif;?>">Цена</a>
							<?php if ($_GET['order']=='price'):?>
								<?php if ($_GET['direction']=='asc'):?>
									&darr;
								<?php else:?>
									&uarr;
								<?php endif;?>
							<?php endif;?>
						</td>
					</tr>
					<?php foreach ($CATALOG as $row):?>
						<tr>
							<td class="id">
								<?php print $row['id']?>
							</td>
							<td class="image">
								<?php if ($row['image_url']):?>
									<img src="<?=$row['image_url']?>" width="30" />
								<?php endif;?>
							</td>
							<td class="name">
								<div class="title"><?php print $row['name']?></div>
								<div class="description"><?php print $row['description']?></div>
							</td>
							<td class="price">
								<?php print $row['price']?>
							</td>
						</tr>
					<?php endforeach;?>
					</table>

					<div class="pages">
					<?php
					$params = $_GET;
					unset($params['page']);

					$used_pages = array();

					for($p = 1; $p <= 3; $p++) {
						if ($p <= $TOTAL_PAGES) {
							array_push($used_pages, $p);
							print '<a href="?' . http_build_query($params) . '&page=' . $p . '" ' . ($p == $_GET['page'] ? 'class="active"' : '') . '>' . $p . '</a> ';
						}
					}

					if ($_GET['page'] >= 5) {
						print ' ... ';
					}

					for($p = $_GET['page'] - 2; $p <= $_GET['page'] + 2; $p++) {
						if ($p >= 1 && $p <= $TOTAL_PAGES && !in_array($p, $used_pages)) {
							array_push($used_pages, $p);
							print '<a href="?' . http_build_query($params) . '&page=' . $p . '" ' . ($p == $_GET['page'] ? 'class="active"' : '') . '>' . $p . '</a> ';
						}
					}

					if ($_GET['page'] <= $TOTAL_PAGES-6) {
						print ' ... ';
					}

					for($p = $TOTAL_PAGES-2; $p <= $TOTAL_PAGES; $p++) {
						if ($p >= 1 && !in_array($p, $used_pages)) {
							array_push($used_pages, $p);
							print '<a href="?' . http_build_query($params) . '&page=' . $p . '" ' . ($p == $_GET['page'] ? 'class="active"' : '') . '>' . $p . '</a> ';
						}
					}

					?>
					</div>



				<?php endif;?>
			<?php endif;?>

			<br/>
			Создано за <?php print get_execution_time();?> сек.
		</div>
	</div>


</body>
</html>