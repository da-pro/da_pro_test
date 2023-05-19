<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="top-side">
	<a data-tab="bottles" class="active">бутилки</a>
<?php if (getUserID() === -1): ?>
	<a data-tab="configurations">конфигурация</a>
<?php endif; ?>
	<a href="/logout" class="logout float-right">изход</a>
	<a data-tab="change-password" class="float-right">смени парола</a>
	<span>Потребител <strong><?= getUserName() ?></strong><b title="Изтичане на сесия">00:00:00</b></span>
</div>
<div class="bottom-side">
<?php
echo view('bottle/index');

if (getUserID() === -1)
{
	echo view('configuration/index');
}

include 'change_password.php';
?>
</div>
<div data-new-configuration="Нова Конфигурация" data-edit-configuration="Обнови Конфигурация" id="wrapper"></div>
<script>
common.session_expire = <?= $_SESSION['authenticate']['session_expire'] - time() ?>;
common.auth = '<?= getUserName() ?>';
common.wrapper = $('div#wrapper');
$(common.init());
</script>
<?= $this->endSection() ?>