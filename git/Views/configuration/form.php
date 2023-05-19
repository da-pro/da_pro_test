<form id="js-configuration">
	<p></p>
	<label for="ename">Име</label>
	<input type="text" name="ename" value="<?= $ename ?>" id="ename">
	<label for="val">Стойност</label>
	<input type="text" name="val" value="<?= $val ?>" id="val">
	<label for="note">Забележка</label>
	<input type="text" name="note" value="<?= $note ?>" id="note">
	<input type="button" value="<?= $id == 0 ? 'създай' : 'обнови' ?>" data-id="<?= $id ?>">
</form>