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
					<?php foreach ($CATALOG as $row):?>
						<tr>
							<td valign="top">
								<?php if ($row['image_url']):?>
									<img src="<?=$row['image_url']?>" width="30" />
								<?php endif;?>
							</td>
							<td>
								<div class="title"><?php print $row['name']?></div>
								<div class="description"><?php print $row['description']?></div>
							</td>
							<td>
								<?php print $row['price']?>
							</td>
						</tr>
					<?php endforeach;?>
					</table>
				<?php endif;?>
			<?php endif;?>

			<br/>
			Создано за <?php print get_execution_time();?> сек.
		</div>
	</div>


</body>
</html>