<script src="/js/jquery/jquery-3.4.1.js"></script>
<script src="/js/jquery/jquery-ui.js"></script>
<link rel="stylesheet" href="/js/jquery/jquery-ui.css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
<style>
div, form, input, select, textarea, table, thead, tbody, tr, th, td, h1, p, span, b, a, img {margin:0; padding:0; border:0;}
form {padding:10px; background:#ffffff; border-radius:3px; box-shadow:0 0 3px #363636; box-sizing:border-box; cursor:default;}
form>h1 {width:100%; height:16px; margin:0 0 10px; padding:10px 0 0; color:#363636; font:16px/0 'Open Sans', sans-serif; text-align:center; text-transform:uppercase; border-bottom:1px solid #363636; display:block;}
form>p {width:100%; margin:0 0 10px!important; padding:0!important; float:left; color:#ffffff!important; font:bold 13px/26px 'Open Sans', sans-serif; text-align:center; text-transform:uppercase; white-space:pre-line; border:0!important; border-radius:3px; display:none;}
form>p.warning {background:#ff6666; display:block;}
form>p.success {background:#66bb66; display:block;}
form>input[type=text], form>input[type=number], form>select {height:30px; color:#242424; background:#cceeff; font:14px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box;}
form>input[type=text] {padding:0 8px;}
form>input::placeholder {opacity:0.8;}
form>select {padding:0 5px;}
form>input[type=text]:focus, form>input[type=number]:focus {background:#ffffff; outline:none;}
form>input[type=button] {height:30px; padding:0 0 2px; color:#ffffff; background:#708090; font:bold 14px/0 'Open Sans', sans-serif; border:1px solid #556677; border-radius:3px; box-sizing:border-box;}
form>input[type=button]:hover, form>input[type=button]:focus {background:#242424; border-color:#242424; outline:none; cursor:pointer;}
form>input[type=button]::-moz-focus-inner {border:0;}
form#browse {width:380px; margin:20px 0 0 20px; float:left;}
form#browse>select {width:100%;}
form#upload {width:680px; margin:20px 20px 0 0; float:right;}
form#upload>input, form#upload>select {width:calc((100% - 30px) / 4); margin:0 10px 0 0; float:left;}
form#upload>input[type=button] {margin:0;}
div.period {margin:20px; padding:10px 10px 0 0; float:left; background:#ffffff; border-radius:3px; box-shadow:0 0 3px #363636; display:none; clear:both;}
div.period>span {margin:0 0 10px 10px; padding:0 10px; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; display:inline-block; cursor:pointer;}
div.period>span>b {margin:0 0 0 8px; padding:0 5px; color:#ffffff; background:#708090; border-radius:3px; display:inline;}
div.period>span.active>b, div.period>span.active:hover>b {color:#242424; background:#ffffff;}
div.period>span.active, div.period>span.active:hover {color:#ffffff; background:#66bb66; border-color:#009900; cursor:pointer;}
div.period>span:not(.active):hover {border-color:#708090; box-shadow:0 0 0 3px rgba(112, 128, 144, 0.4); cursor:pointer;}
div.images {width:100%; float:left; display:block;}
div.quarter {width:25%; margin:0 0 10px 0; float:left; display:block;}
div.quarter>img {width:90%; margin:0 auto; border:5px solid #ffffff; box-sizing:border-box; display:block;}
div.quarter>img:hover {border-color:#6ecaff; cursor:pointer;}
div.quarter>input[type=button] {height:26px; margin:0 auto 5px; padding:0 10px 2px; color:#333333; background:#f0f0f0; font:14px/0 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:block;}
div.quarter>input[type=button]:hover {background:#cccccc; outline:none; cursor:pointer;}
div#read-image {margin:0; padding:0; display:none;}
div#read-image textarea {width:100%; height:100%; padding:5px 10px; color:#242424; background:#cceeff; font:14px/20px sans-serif; box-sizing:border-box; resize:none;}
div.scanned-images {width:100%; height:100%; position:fixed; top:0; left:0; background:#d1d1d1; display:none;}
div.scanned-images>div.left-side {width:calc(100% - 780px); height:100%; float:left; display:block; overflow-y:scroll;}
div.scanned-images>div.left-side>div.navigation {width:100%; height:50px; position:fixed; top:0; background:#ffffff; border-left:1px solid #d1d1d1; box-sizing:border-box;}
div.scanned-images>div.left-side>div.image {width:100%; margin:50px 0 0; padding:10px 0 10px 10px; float:left; border-left:1px solid #ffffff; box-sizing:border-box; display:block;}
div.scanned-images>div.left-side>div.image>img {width:100%; float:left; color:#333333; font:bold 12px 'Open Sans', sans-serif; text-align:center; display:block;}
div.scanned-images>div.right-side {width:780px; float:left; display:block;}
div.scanned-images>div.right-side>div.navigation {width:100%; height:50px; position:relative; background:#ffffff; display:block;}
div.navigation>a {height:30px; margin:10px 0 10px 10px; padding:0 10px; float:left; color:#ffffff; background:#66aaff; font:bold 12px/30px arial, sans-serif; text-transform:uppercase; text-decoration:none; border:1px solid #66aaff; border-radius:3px; box-sizing:border-box; display:block; user-select:none;}
div.navigation>a.stack-of-files, div.navigation>a.stack-of-files:hover {color:#333333; background:#cccccc; font-size:14px; border-color:#cccccc; cursor:default;}
div.navigation>a.delete-file {background:#ff6666; border-color:#ff6666;}
div.navigation>a.delete-file:hover {color:#ff6666;}
div.navigation>a.close-file {position:fixed; top:0; right:10px;}
div.navigation>a.active, div.navigation>a:hover {color:#66aaff; background:#ffffff; cursor:pointer;}
div.right-side>div[class^='js-'] {display:none;}
div.right-side>div[class^='js-'].active {display:block;}
form.documents {width:760px; margin:10px; float:left; box-shadow:none;}
form.documents input[type=text], form.documents input[type=number] {width:calc((100% - 20px) / 3); float:left; display:block;}
form.documents input[type=text] {margin:0 10px 10px 0; padding:0 4px;}
form.documents input[type=text][name=id]+input[type=button] {box-shadow:0 0 0 2px rgba(204, 204, 204, 0.5);}
form.documents input[type=number] {margin:0 0 10px; padding:0 4px;}
form.documents>input[type=button] {margin:0 10px 10px 0; float:left; color:#333333; background:#f0f0f0; border-color:#cccccc;}
form.documents>input[type=button]:hover, form.documents>input[type=button]:focus {background:#cccccc;}
form.documents>input[type=button].half {width:calc((100% - 20px) / 3);}
form.documents>input[type=button].third {width:calc((100% - 30px - ((100% - 20px) / 3)) / 3);}
form.documents>input[type=button][name=move], form.documents>input[type=button][name=copy] {width:calc((100% - 20px) / 3); margin:0 10px 0 0;}
form.documents>p>input[type=button][name=force] {margin:0 5px 0 0; padding:0 10px; float:none; color:#333333; background:#f0f0f0; font:bold 13px/26px 'Open Sans', sans-serif; border-color:#cccccc; border-radius:3px;}
form.documents>p>input[type=button][name=force]:hover {background:#cccccc; cursor:pointer;}
form.documents hr {width:100%; margin:0 0 10px; float:left; border:0; border-top:1px solid #708090; opacity:0.5;}
form.documents div.table-container {width:100%; max-height:460px; display:block; overflow-y:auto;}
form.documents table {width:100%; margin:0 0 10px; border-collapse:separate; border-spacing:1px 0; cursor:default;}
form.documents table thead {position:sticky; top:0;}
form.documents table tr th {height:20px; color:#ffffff; background:#6699cc; font:bold 13px/20px 'Open Sans', sans-serif; text-align:center; box-shadow:0 0 0 1px #ffffff;}
form.documents.purchase-warranty table tr th {background:#9999ff;}
form.documents table tr th hr {width:60px; margin:0 auto; float:none; border-color:#ffffff;}
form.documents table tr th[colspan='2'] {width:28%;}
form.documents table tr td {padding:0 0 0 5px!important; color:#242424; font:14px/28px arial, sans-serif; text-align:left;}
form.documents table tr td.center {padding:0!important; text-align:center;}
form.documents table tr td.right {padding:0 5px 0 0!important; text-align:right;}
form.documents table tr td a {color:#006699;}
form.documents table tr td a:hover {text-decoration:underline;}
form.documents table tr td input[type=radio] {width:20px; height:20px; margin:4px 0; float:left; cursor:pointer;}
form.documents table tr td input[type=radio]+a {margin:0 0 0 20px;}
form.documents table tr td span {width:30px; margin:0 20px 0 0; float:left; text-align:right;}
form.documents table tr.editable td input[type=text] {width:100%; height:28px; margin:0!important; padding:0!important; float:left; color:#242424; background:#ffffff; font:14px/28px arial, sans-serif; text-align:center; border:1px solid #242424; border-radius:0; box-sizing:border-box;}
form.documents table tr.editable td input[type=text]:focus {border-color:#ff6666; outline:none;}
form.documents table tr.editable td.no-padding {padding:0!important;}
form.documents table tr.editable td input[type=button] {width:80px; height:28px; margin:0; float:right; padding:0 0 2px; color:#ffffff; background:#708090; font:14px/0 'Open Sans', sans-serif; border:0; border-radius:0; box-sizing:border-box; display:block; visibility:hidden;}
form.documents table tr.editable td input[type=button]:hover {background:#242424; cursor:pointer;}
form.documents table tr td a[href*='gallery'] {width:calc(100% + 5px); margin:0 0 0 -5px; color:#009900; font-weight:bold; text-align:center; display:block;}
form.documents table tr:nth-child(odd) td {background:#f0f0f0;}
form.documents table tr:nth-child(even) td {background:#fafafa;}
form.documents table tr.invoice-notice td {background:#ffcccc;}
form.documents table tr.checked td {background:#ddffcc;}
.no-margin {margin:0!important;}
.visibility-hidden {visibility:hidden!important;}
.visibility-visible {visibility:visible!important;}
div.background {width:100%; height:100%; position:fixed; top:0; left:0; background:rgba(0, 0, 0, 0.5); display:none; cursor:default; z-index:100;}
div.background>div.loading {width:50px; height:50px; margin:auto; position:absolute; top:0; right:0; bottom:0; left:0; background:transparent; border:3px solid #ffffff; border-top:3px solid #ff6666; border-radius:50%; box-sizing:border-box; animation:rotate 1s linear infinite;}
@keyframes rotate {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
.ui-widget {font-size:1.3em;}
</style>
<form id="browse">
	<h1>разгледай сканирани документи</h1>
	<p></p>
	<select name="folder">
		<option value="" selected>избери папка</option>
<?php foreach ($folders as $value): ?>
		<option value="<?= $value ?>"><?= $value ?></option>
<?php endforeach; ?>
	</select>
</form>
<form id="upload" enctype="multipart/form-data">
	<h1>качване в сканирани документи или конвертиране на pdf към jpg</h1>
	<p></p>
	<input type="file" name="upload_file" title="Документи от тип PDF или JPG">
	<select name="scanned_folder">
		<option value="" selected>избери папка</option>
<?php foreach ($folders as $value): ?>
		<option value="<?= $value ?>"><?= $value ?></option>
<?php endforeach; ?>
	</select>
	<input type="text" name="watermark" placeholder="воден знак" maxlength="20" title="Номер на Покупка, Разход, Приход или друг текст">
	<input type="button" name="upload_document" value="качване">
</form>
<div class="background">
	<div class="loading"></div>
</div>
<div class="period"></div>
<div class="images"></div>
<div title="Резултат от разчитане" id="read-image">
	<textarea spellcheck="false"></textarea>
</div>
<div class="scanned-images">
	<div class="left-side">
		<div class="navigation">
			<a class="stack-of-files"></a>
			<a class="delete-file">изтрий документ</a>
			<a class="js-read-file">разчети картинка</a>
			<a class="js-previous-file">&laquo; предишен</a>
			<a class="js-next-file">следващ &raquo;</a>
		</div>
		<div class="image">
			<img alt="няма изображение" onload="folder.setImage();">
		</div>
	</div><!-- END LEFT SIDE -->
	<div class="right-side">
		<div class="navigation">
			<a class="active js-purchase-invoice">фактури</a>
			<a class="js-purchase-warranty">гаранционни карти</a>
			<a class="js-cost">разходи</a>
			<a class="js-profit">приходи</a>
			<a class="js-payroll">ведомости</a>
			<a class="js-other">други</a>
			<a class="close-file">затвори</a>
		</div>
		<div class="active js-purchase-invoice">
			<form class="documents purchase-invoice">
				<input type="text" name="id" placeholder="търси по номер" maxlength="10">
				<input type="button" name="search_purchase_invoice" data-name="purchase_id" value="покупка" class="half">
				<input type="button" name="search_purchase_invoice" data-name="invoice_id" value="фактура" class="half no-margin">
				<p id="input"></p>
				<hr>
				<div class="table-container">
					<table>
						<tr>
							<th rowspan="2">Покупка</th>
							<th colspan="2">Фактура</th>
							<th rowspan="2">Доставчик</th>
							<th rowspan="2">Сума</th>
							<th rowspan="2" title="Сканирани Документи">СД</th>
						</tr>
						<tr>
							<th>Номер</th>
							<th>Дата</th>
						</tr>
						<tbody id="js-purchase-invoice"></tbody>
					</table>
				</div>
				<hr>
				<p id="output"></p>
				<input type="text" name="purchase_number" placeholder="номер на покупка" maxlength="10">
				<input type="text" name="document_number" placeholder="номер на фактура" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
		<div class="js-purchase-warranty">
			<form class="documents purchase-warranty">
				<input type="text" name="id" placeholder="търси по номер" maxlength="30">
				<input type="button" name="search_purchase_warranty" data-name="purchase_id" value="покупка" class="third">
				<input type="button" name="search_purchase_warranty" data-name="warranty_id" value="гаранционна карта" class="third">
				<input type="button" name="search_purchase_warranty" data-name="serial_number" value="сериен номер" class="third no-margin">
				<p id="input"></p>
				<hr>
				<div class="table-container">
					<table>
						<tr>
							<th rowspan="2">Покупка</th>
							<th colspan="2">Гаранционна Карта</th>
							<th rowspan="2">Доставчик</th>
							<th rowspan="2" title="Сканирани Документи">СД</th>
						</tr>
						<tr>
							<th>Номер</th>
							<th>Дата</th>
						</tr>
						<tbody id="js-purchase-warranty"></tbody>
					</table>
				</div>
				<hr>
				<p id="output"></p>
				<input type="text" name="purchase_number" placeholder="номер на покупка" maxlength="10">
				<input type="text" name="document_number" placeholder="номер на гаранционна карта" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
		<div class="js-cost">
			<form class="documents cost">
				<input type="text" name="id" placeholder="търси по номер или име" maxlength="30">
				<input type="button" name="search_cost" data-name="cost_id" value="разход" class="third">
				<input type="button" name="search_cost" data-name="invoice_id" value="фактура" class="third">
				<input type="button" name="search_cost" data-name="client" value="клиент" class="third no-margin">
				<p id="input"></p>
				<hr>
				<div class="table-container">
					<table>
						<thead>
							<tr>
								<th rowspan="2">Разход</th>
								<th colspan="2">Фактура</th>
								<th rowspan="2">Клиент</th>
								<th rowspan="2">Сума</th>
								<th rowspan="2" title="Сканирани Документи">СД</th>
							</tr>
							<tr>
								<th>Номер</th>
								<th>Дата</th>
							</tr>
						</thead>
						<tbody id="js-cost"></tbody>
					</table>
				</div>
				<hr>
				<p id="output"></p>
				<input type="text" name="cost_number" placeholder="номер на разход" maxlength="10">
				<input type="text" name="invoice_number" placeholder="номер на фактура" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
		<div class="js-profit">
			<form class="documents profit">
				<input type="text" name="id" placeholder="търси по номер или име" maxlength="30">
				<input type="button" name="search_profit" data-name="profit_id" value="приход" class="third">
				<input type="button" name="search_profit" data-name="invoice_id" value="фактура" class="third">
				<input type="button" name="search_profit" data-name="client" value="клиент" class="third no-margin">
				<p id="input"></p>
				<hr>
				<div class="table-container">
					<table>
						<thead>
							<tr>
								<th rowspan="2">Приход</th>
								<th colspan="2">Фактура</th>
								<th rowspan="2">Клиент</th>
								<th rowspan="2">Сума</th>
								<th rowspan="2" title="Сканирани Документи">СД</th>
							</tr>
							<tr>
								<th>Номер</th>
								<th>Дата</th>
							</tr>
						</thead>
						<tbody id="js-profit"></tbody>
					</table>
				</div>
				<hr>
				<p id="output"></p>
				<input type="text" name="profit_number" placeholder="номер на приход" maxlength="10">
				<input type="text" name="invoice_number" placeholder="номер на фактура" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
		<div class="js-payroll">
			<form class="documents payroll">
				<input type="text" name="id" placeholder="търси по номер или име" maxlength="10">
				<input type="button" name="search_payroll" data-name="payroll_id" value="ред" class="third">
				<input type="button" name="search_payroll" data-name="invoice_id" value="фактура" class="third">
				<input type="button" name="search_payroll" data-name="client" value="клиент" class="third no-margin">
				<p id="input"></p>
				<hr>
				<table>
					<tr>
						<th rowspan="2">Ред</th>
						<th colspan="2">Фактура</th>
						<th rowspan="2">Клиент</th>
						<th colspan="2">Сума</th>
						<th rowspan="2">Валута</th>
						<th rowspan="2" title="Сканирани Документи">СД</th>
					</tr>
					<tr>
						<th>Номер</th>
						<th>Дата</th>
						<th>От</th>
						<th>Към</th>
					</tr>
					<tbody id="js-payroll"></tbody>
				</table>
				<hr>
				<p id="output"></p>
				<input type="text" name="payroll_number" placeholder="номер на ред" maxlength="10">
				<input type="text" name="invoice_number" placeholder="номер на фактура" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
		<div class="js-other">
			<form class="documents other">
				<input type="text" name="id" placeholder="търси по номер" maxlength="10">
				<input type="button" name="search_other" data-name="purchase_id" value="покупка" class="half">
				<input type="button" name="search_other" data-name="sale_id" value="продажба" class="half no-margin">
				<p id="input"></p>
				<hr>
				<table>
					<tr>
						<th rowspan="2">Покупка<hr>Продажба</th>
						<th colspan="2">Фактура</th>
						<th rowspan="2">Доставчик<hr>Клиент</th>
						<th rowspan="2">Сума</th>
						<th rowspan="2" title="Сканирани Документи">СД</th>
					</tr>
					<tr>
						<th>Номер</th>
						<th>Дата</th>
					</tr>
					<tbody id="js-other"></tbody>
				</table>
				<hr>
				<p id="output"></p>
				<input type="text" name="other_number" placeholder="номер на покупка или продажба" maxlength="10">
				<input type="text" name="invoice_number" placeholder="номер на фактура" maxlength="10">
				<input type="number" name="document_page" value="1" step="1" min="1" max="99" placeholder="страница на документ">
				<input type="button" name="move" value="запис документ">
				<input type="button" name="copy" value="копиране документ">
			</form>
		</div>
	</div><!-- END RIGHT SIDE -->
</div>
<script>
var folder = {
	strings : {
		select_documents_by_date : 'избери документи по дата',
		read_image : 'разчети картинка',
		confirm_delete_file : 'Потвърди изтриване на документ!',
		update : 'обнови',
	},
	json : [],
	data : {},
	data_images : {},
	data_key : [],
	base_url : '<?= $is_outside ? '/documents/image?path=' : 'http://docs.jarnet' ?>',
	image_path : '',
	is_search_ready : false,
	construct : function()
	{
		$('body').on('focus', 'input[name=invoice_date], input[name=warranty_date]', function()
		{
			$(this).datepicker({dateFormat : 'yy-mm-dd'});
		});

		let that = this,
			form_browse = 'form#browse',
			form_upload = 'form#upload';

		$('select[name=folder]', form_browse).val('').on('change', function()
		{
			that.unsetMessage(`${form_browse}>p`);
			that.data_key = [];

			$('div.period').html('').hide();
			$('div.images').html('');

			$.post({
				url : '/documents/get_folder',
				dataType : 'json',
				data : {folder : $(this).val()},
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						that.data = response.data;
						that.data_images = response.data_images;

						let dates = '';

						$('div.period').html(dates).hide();

						for (let i in that.data)
						{
							dates += `<span data-date="${i}">${i}<b>${that.data[i].length}</b></span>`;
						}

						$('div.period').html(dates).show();

						that.setMessage(false, `${form_browse}>p`, that.strings.select_documents_by_date);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form_browse}>p`, response.error);
					}
				}
			});
		});

		$('input[name=upload_document]', form_upload).on('click', function()
		{
			that.setLoading();
			that.unsetMessage(`${form_upload}>p`);

			let form_data = new FormData(),
				object = {
					upload_file : $('input[name=upload_file]', form_upload).prop('files')[0],
					scanned_folder : $('select[name=scanned_folder]', form_upload).val(),
					watermark : $('input[name=watermark]', form_upload).val()
				};

			for (let i in object)
			{
				form_data.append(i, object[i]);
			}

			$.post({
				url : '/documents/upload_document',
				dataType : 'json',
				data : form_data,
				cache : false,
				contentType : false,
				processData : false,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form_upload}>p`, response.success);

						$('input[name=upload_file]', form_upload).val('');
						$('select[name=folder]', form_browse).val(object.scanned_folder).trigger('change');
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form_upload}>p`, response.error);
					}

					that.unsetLoading();
				}
			});
		});

		$('body').on('click', 'div.period>span', function()
		{
			let date = $(this).data('date');

			if ($(this).hasClass('active'))
			{
				$(this).removeClass('active');

				if (that.data_key.includes(date))
				{
					that.data_key.splice(that.data_key.indexOf(date), 1);
				}
			}
			else
			{
				$(this).addClass('active');

				that.data_key.push(date);
			}

			that.json = [];

			for (let i in that.data)
			{
				if (that.data_key.includes(i))
				{
					that.json = that.json.concat(that.data[i]);
				}
			}

			let images = '';

			$('div.images').html(images);

			for (let i of that.json)
			{
				images += `
				<div class="quarter">
				<input type="button" name="read_image" value="${that.strings.read_image}" data-path="${i}">
				<img src="${that.base_url}${i}" data-path="${i}">
				</div>
				`;
			}

			$('div.images').html(images);
		});

		$('body').on('click', 'input[name=read_image]', function()
		{
			$.post({
				url : '/documents/get_image_document_data',
				dataType : 'json',
				data : {path : $(this).data('path')},
				success : function(response)
				{
					if (response.hasOwnProperty('message'))
					{
						$('div#read-image textarea').val(response.message);
						$('div#read-image').dialog({
							width : 800,
							height : 500
						});
					}
				}
			});
		});

		$('body').on('click', 'div.quarter>img', function()
		{
			that.image_path = $(this).data('path');

			that.setImageSource();

			$('form.documents').each(function()
			{
				this.reset();

				let form_class = $(this).attr('class').split(' ')[1];

				$(`tbody[id=js-${form_class}]`).html('');

				that.unsetMessage(`form.${form_class}>p`);
			});

			$('body').css('overflow', 'hidden');
			$('div.scanned-images').show();
		});

		$('div.right-side>div.navigation>a:not(.close-file)').on('click', function()
		{
			if (!$(this).hasClass('active'))
			{
				$('div.right-side>div.navigation>a').removeClass('active');
				$('div.right-side>div').removeClass('active');

				let get_class = $(this).attr('class');

				$(this).addClass('active');
				$(`div.${get_class}`).addClass('active');
			}
		});

		$('div.navigation>a.delete-file').on('click', function()
		{
			if (confirm(that.strings.confirm_delete_file))
			{
				$.post({
					url : '/documents/delete_document',
					dataType : 'json',
					data : {image_path : that.image_path},
					success : function(response)
					{
						if (response.hasOwnProperty('success'))
						{
							alert(response.success);

							that.setAvailableImage();
						}

						if (response.hasOwnProperty('error'))
						{
							alert(response.error);
						}
					}
				});
			}
		});

		$('div.navigation>a.js-read-file').on('click', function()
		{
			$.post({
				url : '/documents/get_image_document_data',
				dataType : 'json',
				data : {path : that.image_path},
				success : function(response)
				{
					if (response.hasOwnProperty('message'))
					{
						$('div#read-image textarea').val(response.message);
						$('div#read-image').dialog({
							width : 800,
							height : 500
						});
					}

					if (response.hasOwnProperty('action'))
					{
						if (response.action.hasOwnProperty('purchase'))
						{
							$('a.js-purchase-invoice').trigger('click');

							if (response.action.purchase.hasOwnProperty('id'))
							{
								$('input[name=id]', 'form.purchase-invoice').val(response.action.purchase.id);

								$('input[name=search_purchase_invoice][data-name=purchase_id]', 'form.purchase-invoice').trigger('click').focus();
							}

							if (response.action.purchase.hasOwnProperty('invoice_number'))
							{
								$('input[name=id]', 'form.purchase-invoice').val(response.action.purchase.invoice_number);

								$('input[name=search_purchase_invoice][data-name=invoice_id]', 'form.purchase-invoice').trigger('click').focus();
							}
						}

						if (response.action.hasOwnProperty('cost'))
						{
							$('a.js-cost').trigger('click');

							if (response.action.cost.hasOwnProperty('id'))
							{
								$('input[name=id]', 'form.cost').val(response.action.cost.id);

								$('input[name=search_cost][data-name=cost_id]', 'form.cost').trigger('click').focus();
							}

							if (response.action.cost.hasOwnProperty('invoice_number'))
							{
								$('input[name=id]', 'form.cost').val(response.action.cost.invoice_number);

								$('input[name=search_cost][data-name=invoice_id]', 'form.cost').trigger('click').focus();
							}

							if (response.action.cost.hasOwnProperty('client'))
							{
								$('input[name=id]', 'form.cost').val(response.action.cost.client);

								$('input[name=search_cost][data-name=client]', 'form.cost').trigger('click').focus();
							}
						}
					}

					if (response.hasOwnProperty('error'))
					{
						$('div#read-image textarea').val(response.error);
						$('div#read-image').dialog({
							width : 800,
							height : 100
						});
					}
				}
			});
		});

		$('div.navigation>a.js-previous-file').on('click', function()
		{
			let position = that.json.indexOf(that.image_path);

			if (position > -1)
			{
				let previous = position - 1;

				if (typeof that.json[previous] === 'string')
				{
					that.image_path = that.json[previous];

					that.setImageSource();
				}
			}
		});

		$('div.navigation>a.js-next-file').on('click', function()
		{
			let position = that.json.indexOf(that.image_path);

			if (position > -1)
			{
				let next = position + 1;

				if (typeof that.json[next] === 'string')
				{
					that.image_path = that.json[next];

					that.setImageSource();
				}
			}
		});

		$('div.navigation>a.close-file').on('click', function()
		{
			$('body').css('overflowY', 'scroll');
			$('div.scanned-images').hide();

			$('select[name=folder]', form_browse).trigger('change');
		});

		$('form.purchase-invoice>input[name=search_purchase_invoice], form.purchase-warranty>input[name=search_purchase_warranty]').on('click', function()
		{
			let name = $(this).attr('name'),
				form = '',
				tbody_id = '',
				path = '',
				object = {
					type : $(this).data('name')
				};

			switch (name)
			{
				case 'search_purchase_invoice':
					form = 'form.purchase-invoice';
					tbody_id = 'tbody#js-purchase-invoice';
					path = 'invoice';
				break;

				case 'search_purchase_warranty':
					form = 'form.purchase-warranty';
					tbody_id = 'tbody#js-purchase-warranty';
					path = 'warranty';
				break;
			}

			object.id = $('input[name=id]', form).val();

			that.unsetMessage(`${form}>p`);

			$(tbody_id).html('');
			$('input[name=purchase_number], input[name=document_number]', form).val('');
			$('input[name=document_page]', form).val('1');

			$.post({
				url : `/documents/get_delivery_document/${path}`,
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						that.is_search_ready = true;

						let json = response.data,
							tbody = '';

						if (path === 'invoice')
						{
							if (Object.keys(json).length === 1)
							{
								tbody = `
								<tr class="editable">
								<td>
								<a href="/spravcho/tmpl/1/${json[0].purchase_id}" target="_blank">${json[0].purchase_id}</a>
								<input type="button" name="update_invoice" value="${that.strings.update}" data-purchase-id="${json[0].purchase_id}">
								</td>
								<td class="no-padding"><input type="text" name="invoice_id" value="${json[0].invoice_id}" maxlength="10"></td>
								<td class="no-padding"><input type="text" name="invoice_date" value="${json[0].invoice_date}" maxlength="10"></td>
								<td><a href="/spravcho/tmpl/3/${json[0].provider_id}" target="_blank">${json[0].provider}</a></td>
								<td class="right">${json[0].invoice_sum}</td>
								<td><a href="/documents/gallery/${json[0].purchase_id}" target="_blank">${json[0].scanned_invoice}</a></td>
								</tr>
								`;

								$('input[name=purchase_number]', form).val(json[0].purchase_id);
								$('input[name=document_number]', form).val(json[0].invoice_id);
								$('input[name=document_page]', form).val(++json[0].scanned_invoice);
							}
							else
							{
								for (let i in json)
								{
									tbody += `
									<tr>
									<td>
									<input type="radio" name="set_purchase" data-purchase-id="${json[i].purchase_id}" data-form="${form}" data-name="${name}">
									<a href="/spravcho/tmpl/1/${json[i].purchase_id}" target="_blank">${json[i].purchase_id}</a>
									</td>
									<td class="center">${json[i].invoice_id}</td>
									<td class="center">${json[i].invoice_date}</td>
									<td><a href="/spravcho/tmpl/3/${json[i].provider_id}" target="_blank">${json[i].provider}</a></td>
									<td class="right">${json[i].invoice_sum}</td>
									<td><a href="/documents/gallery/${json[i].purchase_id}" target="_blank">${json[i].scanned_invoice}</a></td>
									</tr>
									`;
								}
							}
						}
						else
						{
							if (Object.keys(json).length === 1)
							{
								tbody = `
								<tr class="editable">
								<td>
								<a href="/spravcho/tmpl/1/${json[0].purchase_id}" target="_blank">${json[0].purchase_id}</a>
								<input type="button" name="update_warranty" value="${that.strings.update}" data-purchase-id="${json[0].purchase_id}">
								</td>
								<td class="no-padding"><input type="text" name="warranty_id" value="${json[0].warranty_id}" maxlength="10"></td>
								<td class="no-padding"><input type="text" name="warranty_date" value="${json[0].warranty_date}" maxlength="10"></td>
								<td><a href="/spravcho/tmpl/3/${json[0].provider_id}" target="_blank">${json[0].provider}</a></td>
								<td><a href="/documents/gallery/${json[0].purchase_id}" target="_blank">${json[0].scanned_warranty}</a></td>
								</tr>
								`;

								$('input[name=purchase_number]', form).val(json[0].purchase_id);
								$('input[name=document_number]', form).val(json[0].warranty_id);
								$('input[name=document_page]', form).val(++json[0].scanned_warranty);
							}
							else
							{
								for (let i in json)
								{
									tbody += `
									<tr>
									<td>
									<input type="radio" name="set_purchase" data-purchase-id="${json[i].purchase_id}" data-form="${form}" data-name="${name}">
									<a href="/spravcho/tmpl/1/${json[i].purchase_id}" target="_blank">${json[i].purchase_id}</a>
									</td>
									<td class="center">${json[i].warranty_id}</td>
									<td class="center">${json[i].warranty_date}</td>
									<td><a href="/spravcho/tmpl/3/${json[i].provider_id}" target="_blank">${json[i].provider}</a></td>
									<td><a href="/documents/gallery/${json[i].purchase_id}" target="_blank">${json[i].scanned_warranty}</a></td>
									</tr>
								`;
								}
							}
						}

						$(tbody_id).html(tbody);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error);
					}
				}
			});
		});

		$('body').on('click', 'table tr td input[name=set_purchase]', function()
		{
			let form = $(this).data('form'),
				name = $(this).data('name'),
				purchase_id = $(this).data('purchase-id');

			$('input[name=id]', form).val(purchase_id);
			$(`input[name=${name}][data-name=purchase_id]`, form).trigger('click').focus();
		});

		$('body').on('input change', 'tr.editable input[type=text]', function()
		{
			let button = $(this).attr('name').includes('invoice') ? 'input[name=update_invoice]' : 'input[name=update_warranty]';

			if (!$(button).hasClass('visibility-visible'))
			{
				$(button).addClass('visibility-visible');
			}
		});

		$('body').on('click', 'form.purchase-invoice input[name=update_invoice], form.purchase-warranty input[name=update_warranty]', function()
		{
			let name = $(this).attr('name'),
				form = '',
				object = {
					invoice_id : null,
					invoice_date : null,
					warranty_id : null,
					warranty_date : null,
					purchase_id : $(this).data('purchase-id')
				};

			switch (name)
			{
				case 'update_invoice':
					form = 'form.purchase-invoice';

					object.invoice_id = $('input[name=invoice_id]', form).val().trim();
					object.invoice_date = $('input[name=invoice_date]', form).val();
					object.document_type = 'I';
				break;

				case 'update_warranty':
					form = 'form.purchase-warranty';

					object.warranty_id = $('input[name=warranty_id]', form).val().trim();
					object.warranty_date = $('input[name=warranty_date]', form).val();
					object.document_type = 'W';
				break;
			}

			that.unsetMessage(`${form}>p`);

			$(`input[name=${name}]`, form).removeClass('visibility-visible');

			$.post({
				url : '/documents/set_purchase',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form}>p#input`, response.success);

						switch (name)
						{
							case 'update_invoice':
								$('input[name=document_number]', form).val(object.invoice_id);
							break;

							case 'update_warranty':
								$('input[name=document_number]', form).val(object.warranty_id);
							break;
						}
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error.join('\n'));
					}
				}
			});
		});

		$('body').on('click', 'form.purchase-invoice input[name=move], form.purchase-invoice input[name=copy], form.purchase-invoice input[name=force]', function()
		{
			that.setLoading();

			let name = $(this).attr('name'),
				form = 'form.purchase-invoice',
				object = {
					document_type : 'I',
					purchase_number : $('input[name=purchase_number]', form).val(),
					document_number : $('input[name=document_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0,
					image_rewrite : 0
				};

			switch (name)
			{
				case 'copy':
					object.image_copy = 1;
				break;

				case 'force':
					object.image_copy = $(this).data('image-copy');
					object.image_rewrite = 1;
				break;
			}

			that.unsetMessage(`${form}>p`);

			$.post({
				url : '/documents/set_delivery_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.is_search_ready = false;

						$('input[name=id]', form).val(object.purchase_number);
						$('input[name=search_purchase_invoice][data-name=purchase_id]', form).trigger('click').focus();

						let set_interval = setInterval(function()
						{
							if (that.is_search_ready)
							{
								clearInterval(set_interval);

								that.setMessage(false, `${form}>p#output`, response.success);

								if (!object.image_copy)
								{
									that.setAvailableImage();
								}

								that.unsetLoading();
							}
						}, 200);
					}

					if (response.hasOwnProperty('special_case'))
					{
						that.setMessage(true, `${form}>p#output`, response.special_case);

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});

		$('body').on('click', 'form.purchase-warranty input[name=move], form.purchase-warranty input[name=copy], form.purchase-warranty input[name=force]', function()
		{
			that.setLoading();

			let name = $(this).attr('name'),
				form = 'form.purchase-warranty',
				object = {
					document_type : 'W',
					purchase_number : $('input[name=purchase_number]', form).val(),
					document_number : $('input[name=document_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0,
					image_rewrite : 0
				};

			switch (name)
			{
				case 'copy':
					object.image_copy = 1;
				break;

				case 'force':
					object.image_copy = $(this).data('image-copy');
					object.image_rewrite = 1;
				break;
			}

			that.unsetMessage(`${form}>p`);

			$.post({
				url : '/documents/set_delivery_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.is_search_ready = false;

						$('input[name=id]', form).val(object.purchase_number);
						$('input[name=search_purchase_warranty][data-name=purchase_id]', form).trigger('click').focus();

						let set_interval = setInterval(function()
						{
							if (that.is_search_ready)
							{
								clearInterval(set_interval);

								that.setMessage(false, `${form}>p#output`, response.success);

								if (!object.image_copy)
								{
									that.setAvailableImage();
								}

								that.unsetLoading();
							}
						}, 200);
					}

					if (response.hasOwnProperty('special_case'))
					{
						that.setMessage(true, `${form}>p#output`, response.special_case);

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});

		$('form.cost>input[name=search_cost]').on('click', function()
		{
			let form = 'form.cost',
				tbody_id = 'tbody#js-cost',
				object = {
					id : $('input[name=id]', form).val(),
					type : $(this).data('name')
				};

			that.unsetMessage(`${form}>p`);

			$(tbody_id).html('');
			$('input[name=cost_number], input[name=invoice_number]', form).val('');
			$('input[name=document_page]', form).val('1');

			$.post({
				url : '/documents/get_cost_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						let json = response.data,
							tbody = '';

							if (Object.keys(json).length === 1)
							{
								let paging = {
									links : '-',
									total : 0
								};

								if (typeof json[0].scanned_document === 'object')
								{
									paging = that.setDocumentPages(json[0].scanned_document);
								}

								tbody = `
								<tr>
								<td><a href="/safe/edit_cost/${json[0].cost_id}" target="_blank">${json[0].cost_id}</a></td>
								<td class="center">${json[0].invoice_id}</td>
								<td class="center">${json[0].invoice_date}</td>
								<td><a href="/spravcho/tmpl/3/${json[0].client_id}" target="_blank">${json[0].client}</a></td>
								<td class="right">${json[0].invoice_sum}</td>
								<td class="center js-cost-${json[0].cost_id}">${paging.links}</td>
								</tr>
								`;

								$('input[name=cost_number]', form).val(json[0].cost_id);
								$('input[name=invoice_number]', form).val(json[0].invoice_id);
								$('input[name=document_page]', form).val(++paging.total);
							}
							else
							{
								let counter = 0;

								for (let i in json)
								{
									++counter;

									let tr_class = (json[i].repeat_invoice_id > 1) ? ' class="invoice-notice"' : '',
										paging = {
											links : '-',
											total : 0
										};

								if (typeof json[i].scanned_document === 'object')
								{
									paging = that.setDocumentPages(json[i].scanned_document);
								}

									tbody += `
									<tr${tr_class}>
									<td>
									<input type="radio" name="set_cost" data-cost-id="${json[i].cost_id}" data-invoice="${json[i].invoice_id}">
									<span>${counter}</span>
									<a href="/safe/edit_cost/${json[i].cost_id}" target="_blank">${json[i].cost_id}</a>
									</td>
									<td class="center">${json[i].invoice_id}</td>
									<td class="center">${json[i].invoice_date}</td>
									<td><a href="/spravcho/tmpl/3/${json[i].client_id}" target="_blank">${json[i].client}</a></td>
									<td class="right">${json[i].invoice_sum}</td>
									<td data-page="${paging.total}" class="center js-cost-${json[i].cost_id}">${paging.links}</td>
									</tr>
									`;
								}
							}

						$(tbody_id).html(tbody);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error);
					}
				}
			});
		});

		$('body').on('click', 'table tr td input[name=set_cost]', function()
		{
			$('tbody#js-cost tr').removeClass('checked');
			$(this).parent().parent().addClass('checked');

			that.unsetMessage('form.cost>p');

			let cost_id = $(this).data('cost-id'),
				page_number = Number($(`td.js-cost-${cost_id}`).data('page'));

			$('input[name=cost_number]', 'form.cost').val(cost_id);
			$('input[name=invoice_number]', 'form.cost').val($(this).data('invoice'));
			$('input[name=document_page]', 'form.cost').val(++page_number);
		});

		$('form.cost>input[name=move], form.cost>input[name=copy]').on('click', function()
		{
			that.setLoading();

			let form = 'form.cost',
				object = {
					cost_number : $('input[name=cost_number]', form).val(),
					invoice_number : $('input[name=invoice_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0
				};

			if ($(this).attr('name') === 'copy')
			{
				object.image_copy = 1;
			}

			$.post({
				url : '/documents/set_cost_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form}>p#output`, response.success);

						$(`td.js-cost-${object.cost_number}`).html(that.setDocumentPages(response.documents).links);

						if (!object.image_copy)
						{
							that.setAvailableImage();
						}

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});

		$('form.profit>input[name=search_profit]').on('click', function()
		{
			let form = 'form.profit',
				tbody_id = 'tbody#js-profit',
				object = {
					id : $('input[name=id]', form).val(),
					type : $(this).data('name')
				};

			that.unsetMessage(`${form}>p`);

			$(tbody_id).html('');
			$('input[name=profit_number], input[name=invoice_number]', form).val('');
			$('input[name=document_page]', form).val('1');

			$.post({
				url : '/documents/get_profit_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						let json = response.data,
							tbody = '';

							if (Object.keys(json).length === 1)
							{
								let paging = {
										links : '-',
										total : 0
									};

								if (typeof json[0].scanned_document === 'object')
								{
									paging = that.setDocumentPages(json[0].scanned_document);
								}

								tbody = `
								<tr>
								<td><a href="/safe/edit_profit/${json[0].profit_id}" target="_blank">${json[0].profit_id}</a></td>
								<td class="center">${json[0].invoice_id}</td>
								<td class="center">${json[0].invoice_date}</td>
								<td><a href="/spravcho/tmpl/3/${json[0].client_id}" target="_blank">${json[0].client}</a></td>
								<td class="right">${json[0].invoice_sum}</td>
								<td class="center js-profit-${json[0].profit_id}">${paging.links}</td>
								</tr>
								`;

								$('input[name=profit_number]', form).val(json[0].profit_id);
								$('input[name=invoice_number]', form).val(json[0].invoice_id);
								$('input[name=document_page]', form).val(++paging.total);
							}
							else
							{
								let counter = 0;

								for (let i in json)
								{
									++counter;

									let tr_class = (json[i].repeat_invoice_id > 1) ? ' class="invoice-notice"' : '',
										paging = {
											links : '-',
											total : 0
										};

									if (typeof json[i].scanned_document === 'object')
									{
										paging = that.setDocumentPages(json[i].scanned_document);
									}

									tbody += `
									<tr${tr_class}>
									<td>
									<input type="radio" name="set_profit" data-profit-id="${json[i].profit_id}" data-invoice="${json[i].invoice_id}">
									<span>${counter}</span>
									<a href="/safe/edit_profit/${json[i].profit_id}" target="_blank">${json[i].profit_id}</a>
									</td>
									<td class="center">${json[i].invoice_id}</td>
									<td class="center">${json[i].invoice_date}</td>
									<td><a href="/spravcho/tmpl/3/${json[i].client_id}" target="_blank">${json[i].client}</a></td>
									<td class="right">${json[i].invoice_sum}</td>
									<td data-page="${paging.total}" class="center js-profit-${json[i].profit_id}">${paging.links}</td>
									</tr>
									`;
								}
							}

						$(tbody_id).html(tbody);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error);
					}
				}
			});
		});

		$('body').on('click', 'table tr td input[name=set_profit]', function()
		{
			$('tbody#js-profit tr').removeClass('checked');
			$(this).parent().parent().addClass('checked');

			that.unsetMessage('form.profit>p');

			let profit_id = $(this).data('profit-id'),
				page_number = Number($(`td.js-profit-${profit_id}`).data('page'));

			$('input[name=profit_number]', 'form.profit').val(profit_id);
			$('input[name=invoice_number]', 'form.profit').val($(this).data('invoice'));
			$('input[name=document_page]', 'form.profit').val(++page_number);
		});

		$('form.profit>input[name=move], form.profit>input[name=copy]').on('click', function()
		{
			that.setLoading();

			let form = 'form.profit',
				object = {
					profit_number : $('input[name=profit_number]', form).val(),
					invoice_number : $('input[name=invoice_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0
				};

			if ($(this).attr('name') === 'copy')
			{
				object.image_copy = 1;
			}

			$.post({
				url : '/documents/set_profit_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form}>p#output`, response.success);

						$(`td.js-profit-${object.profit_number}`).html(that.setDocumentPages(response.documents).links);

						if (!object.image_copy)
						{
							that.setAvailableImage();
						}

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});

		$('form.payroll>input[name=search_payroll]').on('click', function()
		{
			let form = 'form.payroll',
				tbody_id = 'tbody#js-payroll',
				object = {
					id : $('input[name=id]', form).val(),
					type : $(this).data('name')
				};

			that.unsetMessage(`${form}>p`);

			$(tbody_id).html('');
			$('input[name=payroll_number], input[name=invoice_number]', form).val('');
			$('input[name=document_page]', form).val('1');

			$.post({
				url : '/documents/get_payroll_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						let json = response.data,
							tbody = '';

						if (Object.keys(json).length === 1)
						{
							let paging = {
									links : '-',
									total : 0
								};

							if (typeof json[0].scanned_document === 'object')
							{
								paging = that.setDocumentPages(json[0].scanned_document);
							}

							tbody = `
							<tr>
							<td><a href="/safe/transfer_of_accounts?filter[id]=${json[0].payroll_id}" target="_blank">${json[0].payroll_id}</a></td>
							<td class="center">${json[0].invoice_id}</td>
							<td class="center">${json[0].invoice_date}</td>
							<td><a href="/spravcho/tmpl/3/${json[0].client_id}" target="_blank">${json[0].client}</a></td>
							<td class="right">${json[0].from_sum}</td>
							<td class="right">${json[0].to_sum}</td>
							<td class="center">${json[0].currency}</td>
							<td class="center js-payroll-${json[0].payroll_id}">${paging.links}</td>
							</tr>
							`;

							$('input[name=payroll_number]', form).val(json[0].payroll_id);
							$('input[name=invoice_number]', form).val(json[0].invoice_id);
							$('input[name=document_page]', form).val(++paging.total);
						}
						else
						{
							let counter = 0;

							for (let i in json)
							{
								++counter;

								let tr_class = (json[i].repeat_invoice_id > 1) ? ' class="invoice-notice"' : '',
									paging = {
										links : '-',
										total : 0
									};

								if (typeof json[i].scanned_document === 'object')
								{
									paging = that.setDocumentPages(json[i].scanned_document);
								}

								tbody += `
								<tr${tr_class}>
								<td>
								<input type="radio" name="set_payroll" data-payroll-id="${json[i].payroll_id}" data-invoice="${json[i].invoice_id}">
								<span>${counter}</span>
								<a href="/safe/transfer_of_accounts?filter[id]=${json[i].payroll_id}" target="_blank">${json[i].payroll_id}</a>
								</td>
								<td class="center">${json[i].invoice_id}</td>
								<td class="center">${json[i].invoice_date}</td>
								<td><a href="/spravcho/tmpl/3/${json[i].client_id}" target="_blank">${json[i].client}</a></td>
								<td class="right">${json[i].from_sum}</td>
								<td class="right">${json[i].to_sum}</td>
								<td class="center">${json[i].currency}</td>
								<td data-page="${paging.total}" class="center js-payroll-${json[i].payroll_id}">${paging.links}</td>
								</tr>
								`;
							}
						}

						$(tbody_id).html(tbody);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error);
					}
				}
			});
		});

		$('body').on('click', 'table tr td input[name=set_payroll]', function()
		{
			$('tbody#js-payroll tr').removeClass('checked');
			$(this).parent().parent().addClass('checked');

			that.unsetMessage('form.payroll>p');

			let payroll_id = $(this).data('payroll-id'),
				page_number = Number($(`td.js-payroll-${payroll_id}`).data('page'));

			$('input[name=payroll_number]', 'form.payroll').val(payroll_id);
			$('input[name=invoice_number]', 'form.payroll').val($(this).data('invoice'));
			$('input[name=document_page]', 'form.payroll').val(++page_number);
		});

		$('form.payroll>input[name=move], form.payroll>input[name=copy]').on('click', function()
		{
			that.setLoading();

			let form = 'form.payroll',
				object = {
					payroll_number : $('input[name=payroll_number]', form).val(),
					invoice_number : $('input[name=invoice_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0
				};

			if ($(this).attr('name') === 'copy')
			{
				object.image_copy = 1;
			}

			$.post({
				url : '/documents/set_payroll_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form}>p#output`, response.success);

						$(`td.js-payroll-${object.payroll_number}`).html(that.setDocumentPages(response.documents).links);

						if (!object.image_copy)
						{
							that.setAvailableImage();
						}

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});

		$('form.other>input[name=search_other]').on('click', function()
		{
			let form = 'form.other',
				tbody_id = 'tbody#js-other',
				type = $(this).val(),
				object = {
					id : $('input[name=id]', form).val(),
					type : $(this).data('name')
				};

			that.unsetMessage(`${form}>p`);

			$(tbody_id).html('');
			$('input[name=other_number], input[name=invoice_number]', form).val('');
			$('input[name=document_page]', form).val('1');

			$.post({
				url : '/documents/get_other_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						let json = response.data,
							tbody = '';

						switch (object.type)
						{
							case 'purchase_id':
								tbody = `
								<tr>
								<td>${type} <a href="/spravcho/tmpl/1/${json.other_number}" target="_blank">${json.other_number}</a></td>
								<td class="center">${json.invoice_number}</td>
								<td class="center">${json.invoice_date}</td>
								<td><a href="/spravcho/tmpl/3/${json.provider_id}" target="_blank">${json.provider}</a></td>
								<td class="right">${json.invoice_sum}</td>
								<td class="js-other-purchase-${json.other_number}"><a href="/documents/other_gallery/purchase/${json.other_number}" target="_blank">${json.scanned_document}</a></td>
								</tr>
								`;
							break;

							case 'sale_id':
								tbody = `
								<tr>
								<td>${type} <a href="/spravcho/tmpl/2/${json.other_number}" target="_blank">${json.other_number}</a></td>
								<td class="center">${json.invoice_number}</td>
								<td class="center">${json.invoice_date}</td>
								<td><a href="/spravcho/tmpl/3/${json.client_id}" target="_blank">${json.client}</a></td>
								<td class="right">${json.invoice_sum}</td>
								<td class="js-other-sale-${json.other_number}"><a href="/documents/other_gallery/sale/${json.other_number}" target="_blank">${json.scanned_document}</a></td>
								</tr>
								`;
							break;
						}

						$('input[name=other_number]', form).val(json.other_number);
						$('input[name=invoice_number]', form).val(json.invoice_number);
						$('input[name=document_page]', form).val(++json.scanned_document);

						$(tbody_id).html(tbody);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#input`, response.error);
					}
				}
			});
		});

		$('form.other>input[name=move], form.other>input[name=copy]').on('click', function()
		{
			that.setLoading();

			let form = 'form.other',
				object = {
					other_number : $('input[name=other_number]', form).val(),
					invoice_number : $('input[name=invoice_number]', form).val(),
					document_page : $('input[name=document_page]', form).val(),
					image_path : that.image_path,
					image_copy : 0
				};

			if ($(this).attr('name') === 'copy')
			{
				object.image_copy = 1;
			}

			$.post({
				url : '/documents/set_other_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.setMessage(false, `${form}>p#output`, response.success);

						let page_number = $(`td.js-other-${response.type}-${object.other_number}>a`).text();

						$(`td.js-other-${response.type}-${object.other_number}>a`).text(Number(page_number) + 1);

						if (!object.image_copy)
						{
							that.setAvailableImage();
						}

						that.unsetLoading();
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage(true, `${form}>p#output`, response.error.join('\n'));

						that.unsetLoading();
					}
				}
			});
		});
	},
	setMessage : function(is_error, selector, message)
	{
		let css_class = is_error ? 'warning' : 'success';

		$(selector).removeClass().addClass(css_class).html(message);
	},
	unsetMessage : function(selector)
	{
		$(selector).removeClass('warning success').html('');
	},
	setImage : function()
	{
		let current_pointer = this.json.indexOf(this.image_path) + 1,
			total_images = this.json.length;

		$('div.navigation>a.stack-of-files').text(`${current_pointer} / ${total_images}`);

		$('form.purchase-invoice>input[name=id], form.cost>input[name=id]').val('');

		if (Object.keys(this.data_images).includes(this.image_path))
		{
			let object = this.data_images[this.image_path],
				type = object.type,
				id = object.id,
				invoice = object.invoice,
				client = object.client;

			switch (type)
			{
				case 'P':
					if (id.length)
					{
						$('input[name=id]', 'form.purchase-invoice').val(id);
						//$('input[name=search_purchase_invoice][data-name=purchase_id]', 'form.purchase-invoice').focus();
					}
					else
					{
						$('input[name=id]', 'form.purchase-invoice').val(invoice);
						//$('input[name=search_purchase_invoice][data-name=invoice_id]', 'form.purchase-invoice').focus();
					}
				break;

				case 'C':
					if (id.length)
					{
						$('input[name=id]', 'form.cost').val(id);
						//$('input[name=search_cost][data-name=cost_id]', 'form.cost').focus();
					}
					else if (invoice.length)
					{
						$('input[name=id]', 'form.cost').val(invoice);
						//$('input[name=search_cost][data-name=invoice_id]', 'form.cost').focus();
					}
					else
					{
						$('input[name=id]', 'form.cost').val(client);
						//$('input[name=search_cost][data-name=client]', 'form.cost').focus();
					}
				break;
			}
		}
	},
	setAvailableImage : function()
	{
		if (this.json.includes(this.image_path))
		{
			let position = this.json.indexOf(this.image_path);

			this.json.splice(this.json.indexOf(this.image_path), 1);

			position = (typeof this.json[position] === 'string') ? position : position - 1;

			if (typeof this.json[position] === 'string')
			{
				this.image_path = this.json[position];

				this.setImageSource();
			}
			else
			{
				$('div.navigation>a.close-file').trigger('click');
			}
		}
	},
	setImageSource : function()
	{
		let version = new Date().getTime();

		$('div.left-side>div.image>img').attr('src', this.base_url+this.image_path+'?version='+version);

		let position = this.json.indexOf(this.image_path);

		if (position === 0)
		{
			$('div.navigation>a.js-previous-file').addClass('visibility-hidden');
		}
		else
		{
			$('div.navigation>a.js-previous-file').removeClass('visibility-hidden');
		}

		if (position === this.json.length - 1)
		{
			$('div.navigation>a.js-next-file').addClass('visibility-hidden');
		}
		else
		{
			$('div.navigation>a.js-next-file').removeClass('visibility-hidden');
		}
	},
	setDocumentPages : function(array)
	{
		let page = 0,
			document_link = [];

		for (let i of array)
		{
			++page;

			document_link.push(`<a href="${this.base_url}${i}" target="_blank">${page}</a>`);
		}

		let paging = {
				links : document_link.join(', '),
				total : page
			};

		return paging;
	},
	setLoading : function()
	{
		$('div.background').show();
	},
	unsetLoading : function()
	{
		$('div.background').hide();
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if ($('a.js-purchase-invoice').hasClass('active'))
				{
					$('input[name=search_purchase_invoice][data-name=purchase_id]', 'form.purchase-invoice').trigger('click').focus();
				}

				if ($('a.js-purchase-warranty').hasClass('active'))
				{
					$('input[name=search_purchase_warranty][data-name=purchase_id]', 'form.purchase-warranty').trigger('click').focus();
				}

				if ($('a.js-cost').hasClass('active'))
				{
					$('input[name=search_cost][data-name=cost_id]', 'form.cost').trigger('click').focus();
				}

				if ($('a.js-profit').hasClass('active'))
				{
					$('input[name=search_profit][data-name=profit_id]', 'form.profit').trigger('click').focus();
				}

				if ($('a.js-payroll').hasClass('active'))
				{
					$('input[name=search_payroll][data-name=payroll_id]', 'form.payroll').trigger('click').focus();
				}

				if ($('a.js-other').hasClass('active'))
				{
					$('input[name=search_other][data-name=purchase_id]', 'form.other').trigger('click').focus();
				}

				return false;
			}

			if (event.which === 27)
			{
				$('div.navigation>a.close-file').trigger('click');
			}

			if (event.which === 37)
			{
				$('a.js-previous-file', 'div.navigation').trigger('click');
			}

			if (event.which === 39)
			{
				$('a.js-next-file', 'div.navigation').trigger('click');
			}
		});
	}()
};
$(folder.construct());
</script>