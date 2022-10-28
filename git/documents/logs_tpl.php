<script src="/js/jquery/jquery-3.4.1.js"></script>
<script src="/js/jquery/jquery-ui.js"></script>
<link rel="stylesheet" href="/js/jquery/jquery-ui.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
<style>
form, input, table, caption, thead, tbody, tfoot, tr, th, td, p, a, span {margin:0; padding:0; border:0; vertical-align:baseline;}
form.documents {width:328px; margin:20px auto; padding:10px; background:#ffffff; border-radius:3px; box-shadow:0 0 3px #363636; box-sizing:border-box; cursor:default;}
form.documents>p {width:100%; margin:0 0 10px!important; padding:0!important; float:left; color:#ffffff!important; background:#ff6666; font:bold 12px/24px 'Open Sans', sans-serif; text-align:center; text-transform:uppercase; border:0!important; border-radius:3px; display:none; white-space:pre-line;}
form.documents>input[type=text] {width:148px; height:30px; padding:0 30px; color:#242424; background:#cceeff; font:16px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box; display:inline-block;}
form.documents>input[name=from_date] {margin:0 8px 0 0;}
form.documents input[type=text]::placeholder {text-align:center; opacity:1;}
form.documents input[type=text]:focus {background:#ffffff; outline:none;}
form.documents>input[type=button] {width:100px; height:30px; margin:10px auto 0; padding:0 0 2px; color:#ffffff; background:#708090; font:bold 14px/0 'Open Sans', sans-serif; border:1px solid #556677; border-radius:3px; box-sizing:border-box; display:block;}
form.documents>input[type=button]:hover, form.documents>input[type=button]:focus {background:#242424; border-color:#242424; outline:none; cursor:pointer;}
form.documents>input[type=button]::-moz-focus-inner {border:0;}
table.logs {width:100%; border-collapse:separate; border-spacing:1px 0; cursor:default;}
table.logs caption {margin:0 1px 1px; position:relative; background:#708090; font:18px/40px 'Open Sans', sans-serif; text-align:center; border:0;}
table.logs caption a {margin:3px 0 3px 3px; padding:0 10px; position:relative; float:left; color:#ffffff; background:#66bb66; font:14px/34px 'Open Sans', sans-serif; text-decoration:none; z-index:1}
table.logs caption span {position:absolute; right:0; left:0; color:#ffffff;}
table.logs tr th {height:40px; color:#ffffff; background:#6699cc; font:bold 14px/40px 'Open Sans', sans-serif; text-align:center;}
table.logs tr td {color:#242424; font:bold 12px/30px 'Open Sans', sans-serif; text-align:center; vertical-align:middle;}
table.logs tr td.left {padding:0 0 0 10px!important; text-align:left;}
table.logs tr:nth-child(odd) td {background:#fafafa;}
table.logs tr:nth-child(even) td {background:#f0f0f0;}
table.logs tr:nth-child(odd).inactive td {background:#ffe6e6;}
table.logs tr:nth-child(even).inactive td {background:#ffcccc;}
table.logs tr:hover td {background:#cceeff!important;}
.ui-widget {font-size:1.3em;}
</style>
<form class="documents">
	<p></p>
	<input type="text" name="from_date" placeholder="начална дата" maxlength="10">
	<input type="text" name="to_date" placeholder="крайна дата" maxlength="10">
	<input type="button" name="submit_date_log" value="търси">
</form>
<table class="logs">
	<caption>
<?php foreach ($other_employee_types as $link => $name): ?>
	<a href="/documents/logs/<?= $link ?>"><?= $name ?> служители</a>
<?php endforeach; ?>
	<span>Операции с Документи за (<b><?= $employee_type ?> служители</b>)</span>
	</caption>
	<tr>
		<th>Име</th>
		<th>Запис</th>
		<th>Запис (презаписване)</th>
		<th>Копиране</th>
		<th>Копиране (презаписване)</th>
		<th>Изтриване</th>
		<th>Всички</th>
	</tr>
	<tbody id="js-date-log"></tbody>
</table>
<script>
var date_log = {
	is_active_employee : <?= $is_active_employee ?>,
	is_precise_date : <?= $is_precise_date ? 'true' : 'false' ?>,
	construct : function()
	{
		this.getLog(false);

		let date_input_format_from_date = 'dd.mm.yy',
			date_input_format_to_date = 'dd.mm.yy';

		if (this.is_precise_date)
		{
			date_input_format_from_date = 'dd.mm.yy 09:00:00';
			date_input_format_to_date = 'dd.mm.yy 20:00:00';

			$('input[name=from_date], input[name=to_date]').css({'padding' : '0 7px', 'font-size' : '14px'}).attr('maxlength', 19).addClass('special');
		}

		$('input[name=from_date]').datepicker({dateFormat: date_input_format_from_date});

		$('input[name=to_date]').datepicker({dateFormat: date_input_format_to_date});

		$('input[name=submit_date_log]', 'form.documents').on('click', function()
		{
			$('form.documents>p').hide().text('');

			date_log.getLog(true);
		});
	},
	getLog : function(is_interval)
	{
		let object = {};

		if (is_interval)
		{
			object.from_date = $('input[name=from_date]', 'form.documents').val();
			object.to_date = $('input[name=to_date]', 'form.documents').val();
		}

		$.post({
			url : `/documents/get_log/${this.is_active_employee}`,
			dataType : 'json',
			data : object,
			success : function(response)
			{
				if (response.hasOwnProperty('data'))
				{
					date_log.setLogs(response.data);
				}

				if (response.hasOwnProperty('error'))
				{
					$('form.documents>p').css('display', 'block').text(response.error.join('\n'));
				}
			}
		});
	},
	setLogs : function(data)
	{
		let tbody = '';

		$('tbody#js-date-log').html(tbody);

		let raw_data = [];

		for (let i in data)
		{
			raw_data.push([Number(i), data[i].name]);
		}

		raw_data.sort(function(a, b)
		{
			let first = a[1],
				second = b[1];

			if (first < second)
			{
				return -1;
			}

			if (first > second)
			{
				return 1;
			}

			return 0;
		});

		for (let r of raw_data)
		{
			let i = r[0],
				tr_class = (data[i].active == 0) ? ' class="inactive"' : '',
				save_file = (typeof data[i].types[1] === 'undefined') ? '&bullet;' : data[i].types[1],
				force_save_file = (typeof data[i].types[2] === 'undefined') ? '&bullet;' : data[i].types[2],
				copy_file = (typeof data[i].types[3] === 'undefined') ? '&bullet;' : data[i].types[3],
				force_copy_file = (typeof data[i].types[4] === 'undefined') ? '&bullet;' : data[i].types[4],
				delete_file = (typeof data[i].types[5] === 'undefined') ? '&bullet;' : data[i].types[5];

			tbody += `
			<tr${tr_class}>
			<td class="left">${data[i].name}</td>
			<td>${save_file}</td>
			<td>${force_save_file}</td>
			<td>${copy_file}</td>
			<td>${force_copy_file}</td>
			<td>${delete_file}</td>
			<td>${data[i].total}</td>
			</tr>
			`;
		}

		$('tbody#js-date-log').html(tbody);
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if ($('input[name=submit_date_log]', 'form.documents').length)
				{
					$('input[name=submit_date_log]', 'form.documents').trigger('click').focus();
				}

				return false;
			}
		});
	}()
};
$(date_log.construct());
</script>