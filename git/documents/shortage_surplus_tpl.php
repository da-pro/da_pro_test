<script src="/js/jquery/jquery-3.4.1.js"></script>
<script src="/js/jquery/jquery-ui.js"></script>
<link rel="stylesheet" href="/js/jquery/jquery-ui.css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
<style>
div, form, input, label, table, tr, th, td, span {margin:0; padding:0; border:0; vertical-align:baseline;}
div.table {width:100%; display:table;}
div.table>div.row {width:inherit; display:table-row;}
._1, ._2, ._3, ._4, ._5, ._6, ._7, ._8, ._9, ._10, ._11, ._12 {width:100%; padding:0; float:left; box-sizing:border-box; display:table-cell;}
@media only screen and (min-width:768px)
{
._1_ {width:16.66%;}
._2_ {width:33.33%;}
._3_ {width:50%;}
._4_ {width:66.66%;}
._5_ {width:83.33%;}
._6_ {width:100%;}
}
@media only screen and (min-width:1200px)
{
._1 {width:8.33%;}
._2 {width:16.66%;}
._3 {width:25%;}
._4 {width:33.33%;}
._5 {width:41.66%;}
._6 {width:50%;}
._7 {width:58.33%;}
._8 {width:66.66%;}
._9 {width:75%;}
._10 {width:83.33%;}
._11 {width:91.66%;}
._12 {width:100%;}
}
div.container {width:100%; padding:10px; position:relative; box-sizing:border-box;}
input[type=button] {width:400px; float:left; color:#ffffff; background:#66bb66; font:bold 1.3em/35px 'Open Sans', sans-serif; border-radius:3px; box-shadow:0 -2px 0 #009900 inset;}
input[type=button][name$='_pdf'] {width:400px; margin:0 0 10px; color:#242424; background:#f0f0f0; box-shadow:0 0 10px #cccccc inset; display:none;}
input[type=button]:hover, input[type=button]:focus {background:#009900; outline:none; cursor:pointer;}
input[type=button][name$='_pdf']:hover, input[type=button][name$='_pdf']:focus {background:#cccccc; outline:none;}
input[type=button]::-moz-focus-inner {border:0;}
form.shortage-surplus {width:100%; margin:10px 0; padding:0 10px; float:left; background:#ffffff; border-radius:3px; box-shadow:0 0 4px #708090; box-sizing:border-box; cursor:default;}
form.shortage-surplus.range {width:400px; box-shadow:0 0 4px #99ddff;}
form.shortage-surplus input[type=text] {width:100%; height:30px; padding:0 5px; color:#242424; background:#cceeff; font:1.3em/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box;}
form.shortage-surplus input[type=text][name$='date'] {text-align:center;}
form.shortage-surplus input[type=text]:focus {background:#ffffff; outline:none;}
form.shortage-surplus table {width:100%; border-collapse:separate; border-spacing:0 10px;}
form.shortage-surplus table tr th {width:45%; padding:0 10px 0 0; color:#242424; font:1.3em/30px 'Open Sans', sans-serif; text-align:right; box-sizing:border-box; border:0;}
form.shortage-surplus table tr td {width:55%; margin:0; padding:0!important; border:0;}
form.shortage-surplus.range table tr th {width:40%;}
form.shortage-surplus.range table tr td {width:60%;}
form.shortage-surplus table tr td.bordered {border-top:1px solid #242424;}
label {width:100%; padding:0 0 0 30px; position:relative; font:1.3em/30px 'Open Sans', sans-serif; box-sizing:border-box; display:block; cursor:pointer;}
label>input[type=checkbox], label>input[type=radio] {display:none;}
label>input[type=checkbox]+span {width:22px; height:22px; position:absolute; top:5px; left:0; background:#ffffff; border:2px solid #99ddff; box-sizing:border-box; cursor:pointer;}
label>input[type=checkbox]+span::before, label>input[type=checkbox]+span::after {width:4px; height:16px; position:absolute; top:1px; left:7px; background:#99ddff; display:none; content:'';}
label>input[type=checkbox]+span::before {-ms-transform:rotate(45deg); -webkit-transform:rotate(45deg); transform:rotate(45deg);}
label>input[type=checkbox]+span::after {-ms-transform:rotate(135deg); -webkit-transform:rotate(135deg); transform:rotate(135deg);}
label>input[type=checkbox]:checked+span::before, label>input[type=checkbox]:checked+span::after {display:block;}
label>input[type=radio]+span {width:22px; height:22px; position:absolute; top:5px; left:0; background:#ffffff; border:2px solid #99ddff; border-radius:50%; box-sizing:border-box;}
label>input[type=radio]+span::before {width:10px; height:10px; position:absolute; top:4px; left:4px; background:#99ddff; border-radius:50%; display:none; content:'';}
label>input[type=radio]:checked+span::before {display:block;}
div.background {width:100%; height:100%; position:absolute; top:0; left:0; background:rgba(0, 0, 0, 0.5); display:none; cursor:default; z-index:100;}
div.background>div.loading {width:50px; height:50px; margin:auto; position:absolute; top:0; right:0; bottom:0; left:0; background:transparent; border:3px solid #ffffff; border-top:3px solid #ff6666; border-radius:50%; box-sizing:border-box; animation:rotate 1s linear infinite;}
@keyframes rotate {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
.ui-widget {font-size:1.3em;}
</style>
<div class="container">
	<input type="button" name="preview" value="<?= $button ?>">
	<div class="table">
		<div class="row">
			<form class="shortage-surplus">
				<div class="_3 _6_">
					<table>
						<tr>
							<th>Организация</th>
							<td><input type="text" name="company" value="ЖАР ЕООД"></td>
						</tr>
						<tr>
							<th>На Обект/МОЛ</th>
							<td><input type="text" name="mol" value="ЖАР Сердика"></td>
						</tr>
						<tr>
							<th>По Заповед</th>
							<td><input type="text" name="mol_order" value="&numero; <?= $id ?> / <?= date('d.m.Y') ?>"></td>
						</tr>
						<tr>
							<th>За Вид Активи</th>
							<td><input type="text" name="type_assets"></td>
						</tr>
						<tr>
							<th>В Резултат На</th>
							<td><input type="text" name="order_reason"></td>
						</tr>
						<tr>
							<th>Други Причини</th>
							<td>
								<label><input type="checkbox" name="reason_inventory" checked><span></span>Инвентаризация</label>
								<label><input type="checkbox" name="reason_theft"><span></span>Кражба</label>
								<label><input type="checkbox" name="reason_blaze"><span></span>Пожар</label>
								<label><input type="checkbox" name="reason_flood"><span></span>Наводнение</label>
							</td>
						</tr>
					</table>
				</div>
				<div class="_9 _6_">
					<table>
<?php if ($type === 'sale'): ?>
						<tr>
							<th>Установените Липси ще бъдат за сметка на</th>
							<td>
								<div class="_6 _3_">
									<label><input type="radio" name="shortage_surplus_action" value="1" checked><span></span>Отписването им на разход</label>
								</div>
								<div class="_6 _3_">
									<label><input type="radio" name="shortage_surplus_action" value="0"><span></span>Вземане от подотчетно лице</label>
								</div>
							</td>
						</tr>
						<tr>
							<th></th>
							<td class="bordered"></td>
						</tr>
						<tr>
							<th>По Тяхната</th>
							<td>
								<div class="_6 _3_">
									<label><input type="radio" name="type_value" value="1" checked><span></span>Отчетна Стойност</label>
								</div>
								<div class="_6 _3_">
									<label><input type="radio" name="type_value" value="0"><span></span>Справедлива Стойност</label>
								</div>
							</td>
						</tr>
<?php else: ?>
						<tr>
							<th>Установените Излишъци ще бъдат заприходени в Обект и/или на МОЛ</th>
							<td><input type="text" name="shortage_surplus_action" value="ЖАР Сердика"></td>
						</tr>
						<tr>
							<th></th>
							<td class="bordered"></td>
						</tr>
						<tr>
							<th>По Тяхната</th>
							<td>
								<div class="_6 _3_">
									<label><input type="radio" name="type_value" value="1"><span></span>Отчетна Стойност</label>
								</div>
								<div class="_6 _3_">
									<label><input type="radio" name="type_value" value="0" checked><span></span>Справедлива Стойност</label>
								</div>
							</td>
						</tr>
<?php endif; ?>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="container">
	<div class="background">
		<div class="loading"></div>
	</div>
	<input type="button" name="preview_range" value="<?= $button_range ?>">
	<div class="table">
		<div class="row">
			<form class="shortage-surplus range">
				<div class="_6 _6_">
					<table>
						<tr>
							<th>От</th>
							<td><input type="text" name="begin_date" value="<?= date('01.01.Y') ?>" maxlength="10"></td>
						</tr>
					</table>
				</div>
				<div class="_6 _6_">
					<table>
						<tr>
							<th>До</th>
							<td><input type="text" name="end_date" value="<?= date('d.m.Y') ?>" maxlength="10"></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
		<div class="row">
			<input type="button" name="preview_pdf" value="Преглед PDF">
		</div>
		<div class="row">
			<input type="button" name="print_pdf" value="Печат (Принтер Каса)">
		</div>
	</div>
</div>
<script>
var shortage_surplus = {
	type : '<?= $type ?>',
	id : '<?= $id ?>',
	construct : function()
	{
		$('input[name=begin_date], input[name=end_date]').datepicker({dateFormat : 'dd.mm.yy'});

		$('input[name=preview]').on('click', function()
		{
			let object = {
				type : shortage_surplus.type,
				id : shortage_surplus.id,
				company : $('input[name=company]').val(),
				mol : $('input[name=mol]').val(),
				mol_order : $('input[name=mol_order]').val(),
				type_assets : $('input[name=type_assets]').val(),
				order_reason : $('input[name=order_reason]').val(),
				reason_inventory : ($('input[name=reason_inventory]').prop('checked') ? 1 : 0),
				reason_theft : ($('input[name=reason_theft]').prop('checked') ? 1 : 0),
				reason_blaze : ($('input[name=reason_blaze]').prop('checked') ? 1 : 0),
				reason_flood : ($('input[name=reason_flood]').prop('checked') ? 1 : 0),
				type_value : $('input[name=type_value]:checked').val()
			};

			if (shortage_surplus.type === 'sale')
			{
				object.shortage_surplus_action = $('input[name=shortage_surplus_action]:checked').val();
			}
			else
			{
				object.shortage_surplus_action = $('input[name=shortage_surplus_action]').val();
			}

			let session = {shortage_surplus : JSON.stringify(object)};

			$.post({
				url : '/documents/set_shortage_surplus',
				dataType : 'json',
				data : session,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.open('/documents/print_shortage_surplus');
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error);
					}
				}
			});
		});

		$('input[name=preview_range]').on('click', function()
		{
			shortage_surplus.setLoading();

			$('input[type=button][name$=_pdf]').hide();

			let object = {
				type : shortage_surplus.type,
				begin_date : $('input[name=begin_date]').val(),
				end_date : $('input[name=end_date]').val()
			};

			$.post({
				url : '/documents/set_shortage_surplus_range',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					shortage_surplus.unsetLoading();

					if (response.hasOwnProperty('success'))
					{
						$('input[type=button][name$=_pdf]').data('path', response.success).show();
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error.join('\n'));
					}
				}
			});
		});

		$('input[name=preview_pdf]').on('click', function()
		{
			window.open(`/${$(this).data('path')}`, '_blank');
		});

		$('input[name=print_pdf]').on('click', function()
		{
			$.post({
				url : '/documents/print_pdf',
				dataType : 'json',
				data : {path : $(this).data('path')},
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						alert(response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error);
					}
				}
			});
		});
	},
	setLoading : function()
	{
		$('div.background').show();
	},
	unsetLoading : function()
	{
		$('div.background').hide();
	}
};
$(shortage_surplus.construct());
</script>