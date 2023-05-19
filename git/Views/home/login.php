<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div id="left-frame"></div>
<div id="right-frame">
	<h1><?= $title ?></h1>
	<form class="login">
		<p></p>
		<label for="username">Потребител</label>
		<input type="text" name="username" maxlength="60" id="username" autofocus>
		<label for="password">Парола</label>
		<input type="password" name="password" maxlength="20" id="password">
		<input type="button" value="вход">
	</form>
<?php if ($display_demo_users): ?>
<style>
div.demo-users {min-width:300px; max-width:450px; margin:0 auto;}
div.demo-users>span {margin:0 10px 0 0; padding:0 5px; float:left; color:#454545; background:#f0f0f0; font:12px/20px open_sans_medium, sans-serif; border:1px solid #454545; border-radius:3px; cursor:pointer;}
</style>
<div class="demo-users">
	<span data-user="Админ" data-pass="config_pass_1">Администратор</span>
	<span data-user="Технолог" data-pass="wine_pass_23">Потребител</span>
</div>
<script>
$('div.demo-users>span').on('click', function()
{
	$('input[name=username]').val($(this).data('user'));
	$('input[name=password]').val($(this).data('pass'));
});
</script>
<?php endif; ?>
</div>
<script>
$(common.login());
</script>
<?= $this->endSection() ?>