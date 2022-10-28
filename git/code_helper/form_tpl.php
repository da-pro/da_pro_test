<?php
$add_viewer = '';
$span_class = '';
$viewers = '';

if ($code['right'] === 'write')
{
	$add_viewer = '<span title="Добави" class="add-viewer">&#65291;</span>';

	foreach ($code['viewed_by'] as $key => $value)
	{
		$viewers .= '<i>' . $value . '<b data-id="' . $key . '" title="Изтриване">&mdash;</b></i>';
	}
}
else
{
	$span_class = ' no-add-button';

	foreach ($code['viewed_by'] as $value)
	{
		$viewers .= '<i class="no-remove-button">' . $value . '</i>';
	}
}
?>
<form class="code-helper">
	<p></p>
	<div class="table">
		<div class="row">
			<div class="_6 position-relative no-padding-left no-padding-bottom">
				<div class="_2">
					<label class="left">Код</label>
				</div>
				<div class="_2">
					<input type="button" name="reset" value="изчисти код">
				</div>
				<div class="_2">
					<label class="right">Заглавие</label>
				</div>
				<div class="_6">
					<textarea name="body" title="<?= htmlentities($code['body']) ?>" spellcheck="false"><?= $code['body'] ?></textarea>
				</div>
				<div class="_12 custom-positioned">
					<textarea name="code" spellcheck="false"><?= $code['code'] ?></textarea>
				</div>
				<div class="_12 created-by">
					<span><b>Създаден от:</b><span class="created-by"><?= $code['created_by'] ?></span></span>
				</div>
			</div>
			<div class="_6 no-padding-right no-padding-bottom">
				<div class="_2">
					<label class="left">Преглед</label>
				</div>
				<div class="_2">
					<input type="button" name="select" value="копирай">
				</div>
				<div class="_2">
					<label class="right">Език</label>
				</div>
				<div class="_2">
					<select name="lang">
<?php foreach ($language as $base_name => $view_name): ?>
						<option value="<?= $base_name ?>"<?= ($code['lang'] === $base_name) ? ' selected' : '' ?>><?= $view_name ?></option>
<?php endforeach; ?>
					</select>
				</div>
				<div class="_4 custom-buttons">
					<span>
						<span>справка</span>
						<input type="button" name="preview_inquiry" value="преглед">
						<input type="button" name="create_inquiry" value="създай">
					</span>
				</div>
				<section>
					<pre><code id="selectable"></code></pre>
				</section>
				<div class="_12 viewed-by">
					<span><b>Видим от:</b><span class="viewers<?= $span_class ?>"><?= $add_viewer . $viewers ?></span></span>
				</div>
			</div>
			<div class="_12 position-relative no-padding">
<?php if ($id !== 0): ?>
				<input type="button" name="new_code" value="нов код">
<?php endif; ?>

<?php if ($code['right'] === 'write'): ?>
				<input type="button" name="submit_code" value="<?= ($id > 0) ? 'обнови' : 'създай' ?>">
<?php endif; ?>

<?php if ($id !== 0 && $code['right'] === 'write'): ?>
				<input type="button" name="delete_code" value="изтрий">
<?php endif; ?>
			</div>
		</div>
	</div>
</form>
<script>
code_helper.id = <?= $id ?>;
code_helper.viewed_by = [<?= implode(',', array_keys($code['viewed_by'])) ?>];
</script>