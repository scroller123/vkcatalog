<form action="<?php print isset($_GET['add']) ? '?add' : '?edit&id=' . $_GET['id']?>" method="post">
		<div class="field">
			<div class="label">Название *</div>
			<div class="input"><input type="text" class="form-control" name="form[name]" value="<?php print isset($_POST['form']['name']) ? $_POST['form']['name'] : $data['name']?>" /></div>
			<?php if (!empty($_POST) && empty($_POST['form']['name'])):?><div class="error">Введите название товара</div><?php endif;?>
		</div>

		<div class="field">
			<div class="label">Описание *</div>
			<div class="input"><textarea class="form-control" name="form[description]"><?php print isset($_POST['form']['description']) ? $_POST['form']['description'] : $data['description']?></textarea></div>
			<?php if (!empty($_POST) && empty($_POST['form']['description'])):?><div class="error">Введите описание товара</div><?php endif;?>
		</div>

		<div class="field">
			<div class="label">Цена *</div>
			<div class="input"><input type="text" class="form-control price" name="form[price]" value="<?php print isset($_POST['form']['price']) ? $_POST['form']['price'] : $data['price']?>" /></div>
			<?php if (!empty($_POST) && !isset($_POST['form']['price'])):?><div class="error">Укажите стоимость</div><?php endif;?>
			<?php if (!empty($_POST) && isset($_POST['form']['price']) && !preg_match("/^[0-9]+(\.[0-9]{1,2})?$/", $_POST['form']['price'])):?><div class="error">Неверный формат, пример: 1024.12</div><?php endif;?>
		</div>

		<div class="field">
			<div class="label">Изображение</div>
			<div class="input"><input type="text" class="form-control" name="form[image]" value="<?php print isset($_POST['form']['image']) ? $_POST['form']['image'] : $data['image_url']?>" /></div>
		</div>

	    <input type="submit" value="<?php print isset($_GET['add']) ? 'Добавить' : 'Редактировать' ?>" class="btn btn-success" onclick="disableButton(this)">
</form>

<script type="text/javascript">
function disableButton (button) {
	button.style.display = "none";
	var div = document.createElement("DIV");
	div.innerHTML = "<input type='button' value='Загрузка...' disabled='disabled'/>";
	button.parentNode.insertBefore(div, button.nextSibling);
}
</script>