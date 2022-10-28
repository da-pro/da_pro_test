<script src="/js/jquery/jquery-3.4.1.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
<style>
div, form, input, select, textarea, label, table, tr, th, td, span, a {margin:0; padding:0; border:0; vertical-align:baseline;}
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
div.container {width:100%; padding:10px; float:left; box-sizing:border-box;}
input[type=button], a.reload {width:300px; float:left; color:#ffffff; background:#66bb66; font:bold 1.3em/35px 'Open Sans', sans-serif; border-radius:3px; box-shadow:0 -2px 0 #009900 inset;}
input[type=button]:hover, input[type=button]:focus {background:#009900; outline:none; cursor:pointer;}
input[type=button]::-moz-focus-inner {border:0;}
a.reload {width:auto; margin:0 0 0 10px; padding:0 10px; background:#6699cc; text-align:center; box-shadow:0 -2px 0 #006699 inset; display:none;}
a.reload:hover {text-decoration:none; cursor:pointer;}
div.container>input[type=text] {width:150px; height:35px; margin:0 0 0 10px; padding:0 5px; float:right; color:#242424; background:#f0f0f0; font:1.3em/35px 'Open Sans', sans-serif; border:2px solid #708090; border-radius:3px; box-sizing:border-box;}
div.container>input[type=text]:focus {background:#ffffff; outline:none;}
div.container>input[type=text]::placeholder {text-align:center; opacity:1;}
form.invoice {width:100%; margin:10px 0; padding:0 10px; float:left; background:#ffffff; border-radius:3px; box-shadow:0 2px 4px #708090; box-sizing:border-box; cursor:default;}
form.invoice input[type=text], form.invoice select, form.invoice textarea {width:100%; color:#242424; background:#cceeff; font:1.3em/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box;}
form.invoice input[type=text] {height:30px; padding:0 5px;}
form.invoice select {height:30px; padding:0 0 0 1px;}
form.invoice textarea {width:100%; height:180px; padding:5px; line-height:21px; resize:none;}
form.invoice input[type=text]:focus, form.invoice select:focus, form.invoice textarea:focus {background:#ffffff; outline:none;}
form.invoice select:-moz-focusring {color:transparent!important; text-shadow:0 0 0 #242424;}
form.invoice table {width:100%; border-collapse:separate; border-spacing:0 10px;}
form.invoice table tr th {width:45%; padding:0 10px 0 0; color:#242424; font:1.3em/30px 'Open Sans', sans-serif; text-align:right; box-sizing:border-box; border:0;}
form.invoice table tr td {width:55%; margin:0; padding:0!important; border:0;}
form.invoice div.note {padding:0 0 10px; text-align:center;}
form.invoice div.note label {text-align:center; cursor:default;}
form.invoice table.items {width:100%; border-collapse:separate; border-spacing:0 10px;}
form.invoice table.items tr th {width:auto; color:#242424; font:1.3em/30px 'Open Sans', sans-serif; text-align:center; box-sizing:border-box; border:0;}
form.invoice table.items tr th:nth-child(1) {width:20%;}
form.invoice table.items tr th:nth-child(2) {width:56%;}
form.invoice table.items tr th:nth-child(3) {width:6%;}
form.invoice table.items tr th:nth-child(4) {width:8%;}
form.invoice table.items tr th:nth-child(5) {width:10%;}
form.invoice table.items tr td {width:auto; border-right:2px solid #ffffff; border-left:2px solid #ffffff;}
form.invoice table.items tr td:nth-child(1) {border-left:10px solid #ffffff;}
form.invoice table.items tr td:nth-child(5) {border-right:0;}
form.invoice table.items tr td input.right {text-align:right;}
label {width:100%; padding:0 25px 0 0; position:relative; font:1.3em/30px 'Open Sans', sans-serif; text-align:right; box-sizing:border-box; display:block; cursor:pointer;}
label>input[type=radio] {display:none;}
label>input[type=radio]+span {width:22px; height:22px; position:absolute; top:5px; right:0; background:#ffffff; border:2px solid #99ddff; border-radius:50%; box-sizing:border-box;}
label>input[type=radio]+span::before {width:10px; height:10px; position:absolute; top:4px; left:4px; background:#99ddff; border-radius:50%; display:none; content:'';}
label>input[type=radio]:checked+span::before {display:block;}
</style>
<?php if ($data): ?>
<div class="container">
	<input type="button" value="preview invoice &numero; <?= $data['data']['Номер документ'] ?>">
	<a data-reload-invoice="/documents/invoice_en/<?= $data['data']['Номер документ'] ?>/1" data-reload-json="/documents/invoice_en/<?= $data['data']['Номер документ'] ?>/0" class="reload"></a>
	<input type="text" name="sale_id" maxlength="10" placeholder="&#x1F50E; sale number">
	<input type="text" name="invoice_id" maxlength="10" placeholder="&#x1F50E; invoice number">
	<div class="table">
		<div class="row">
			<form class="invoice">
				<div class="_6 _6_">
					<div class="_6 _6_">
						<table>
							<tr>
								<th>Client</th>
								<td><input type="text" name="client" value="<?= htmlentities(transliterate($data['data']['Клиент'])) ?>"></td>
							</tr>
							<tr>
								<th>Address</th>
								<td><input type="text" name="client_address" value="<?= htmlentities(transliterate($data['data']['Адрес на регистрация'])) ?>"></td>
							</tr>
							<tr>
								<th>Company Registration No.</th>
								<td><input type="text" name="client_registration_number"></td>
							</tr>
							<tr>
								<th>VAT-No.</th>
								<td><input type="text" name="client_vat_number" value="<?= $data['data']['Данъчен номер'] ?>"></td>
							</tr>
							<tr>
								<th>Responsible for the operation</th>
								<td><input type="text" name="client_mol" value="<?= transliterate($data['data']['МОЛ']) ?>"></td>
							</tr>
							<tr>
								<th>Currency</th>
								<td>
									<div class="_3 _3_">
										<label><input type="radio" name="currency" value="bgn" checked><span></span>BGN</label>
									</div>
									<div class="_3 _3_">
										<label><input type="radio" name="currency" value="eur"><span></span>EUR</label>
									</div>
									<div class="_3 _3_">
										<label><input type="radio" name="currency" value="gbp"><span></span>GBP</label>
									</div>
									<div class="_3 _3_">
										<label><input type="radio" name="currency" value="usd"><span></span>USD</label>
									</div>
								</td>
							</tr>
							<tr>
								<th>Exchange Rate</th>
								<td><input type="text" name="exchange_rate" value="1"></td>
							</tr>
							<tr>
								<th>VAT Percent</th>
								<td><input type="text" name="vat_percent" value="<?= (100 * $data['data']['Ставка ДДС']) ?>"></td>
							</tr>
						</table>
					</div>
					<div class="_6 _6_">
						<table>
							<tr>
								<th>Supplier</th>
								<td><input type="text" name="supplier" value="JAR Ltd."></td>
							</tr>
							<tr>
								<th>Address</th>
								<td><input type="text" name="supplier_address" value="Sofia, Svoboda r.d. bl. 5"></td>
							</tr>
							<tr>
								<th>Company ID</th>
								<td><input type="text" name="company_id" value="131418803"></td>
							</tr>
							<tr>
								<th>VAT-No.</th>
								<td><input type="text" name="supplier_vat_number" value="BG131418803"></td>
							</tr>
							<tr>
								<th>Bank Account</th>
								<td>
									<select name="bank_account">
										<option value="" selected>select</option>
									</select>
								</td>
							</tr>
							<tr>
								<th>IBAN</th>
								<td><input type="text" name="iban"></td>
							</tr>
							<tr>
								<th>SWIFT Code</th>
								<td><input type="text" name="swift_code"></td>
							</tr>
							<tr>
								<th>Responsible for the operation</th>
								<td><input type="text" name="supplier_mol" value="Borislav Dimitrov"></td>
							</tr>
						</table>
					</div>
					<div class="_12 _6_ note">
						<label>Note</label>
						<textarea name="note" spellcheck="false">
PO P####<br>
The delivery is not a subject of VAT taxation in Bulgaria in accordance with art 53, par. in connection to art 7 par of the Law on Value Added Tax.<br>
Payment method: bank transfer<br>
Dear customers, JAR Ltd. issues invoices without signature and stamp. According to Art. Article 8 of the Law of accounting, Art. 114 of the Law on Value Added Tax and Art. Article 78 of the Staff Regulations application of VAT stamp is not a compulsory requisite of the invoice, and the signatures are replaced by identification ciphers.
						</textarea>
					</div>
				</div>
				<div class="_6 _6_">
					<table class="items">
						<tr>
							<th>Module</th>
							<th>Name</th>
							<th>Qty</th>
							<th>Price</th>
							<th>Total</th>
						</tr>
						<tbody id="js-details">
<?php
$counter = 0;

foreach ($data['details'] as $value):

++$counter;
?>
						<tr data-id="<?= $counter ?>">
							<td><input type="text" name="product_module_<?= $counter ?>" value="<?= $value['Name'] ?>"></td>
							<td><input type="text" name="product_name_<?= $counter ?>" value="<?= htmlentities($value['Наименование']) ?>"></td>
							<td><input type="text" name="product_qty_<?= $counter ?>" value="<?= $value['Брой'] ?>" class="right"></td>
							<td><input type="text" name="product_price_<?= $counter ?>" value="<?= $value['Ед.цена'] ?>" class="right"></td>
							<td><input type="text" name="product_total_<?= $counter ?>" value="<?= $value['Общо'] ?>" class="right"></td>
						</tr>
<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
var invoice = {
	json : <?= $data['json'] ?>,
	details : <?= json_encode($data['details'], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) ?>,
	id : '<?= $data['data']['Кредитно (дебитно) номер'] ?: $data['data']['Номер документ'] ?>',
	id_date : '<?= $data['data']['Кредитно (дебитно) номер'] ? $data['data']['Кредитно (дебитно) дата'] : $data['data']['Дата'] ?>',
	credit_id : '<?= $data['data']['Кредитно (дебитно) номер'] ? $data['data']['Номер документ'] : '' ?>',
	credit_date : '<?= $data['data']['Кредитно (дебитно) номер'] ? $data['data']['Дата'] : '' ?>',
	is_reload_json : <?= ($this->uri->segment(4) == 1) ? 'true' : 'false' ?>,
	is_invoice_searching : false,
	search_type : '',
	id_have_to_be_positive_number : 'value must be a positive number',
	current_rate : 1,
	rates : {
		bgn : 1,
		eur : 1.95583,
		gbp : 2.20,
		usd : 1.70
	},
	banks : {
		dsk_bgn : {
			bank_account : 'DSK BANK EAD (BGN)',
			iban : 'BG06STSA93000024625566',
			swift_code : 'STSABGSF'
		},
		dsk_eur : {
			bank_account : 'DSK BANK EAD (EUR)',
			iban : 'BG95STSA93000025268626',
			swift_code : 'STSABGSF'
		},
		dsk_usd : {
			bank_account : 'DSK BANK EAD (USD)',
			iban : 'BG41STSA93000024816026',
			swift_code : 'STSABGSF'
		}
	},
	construct : function()
	{
		let banks = '';

		for (let i in this.banks)
		{
			banks += `<option value="${i}">${this.banks[i].bank_account}</option>`;
		}

		$('select[name=bank_account]>option').after(banks);

		$('input[name=sale_id], input[name=invoice_id]').on('focusin', function()
		{
			invoice.is_invoice_searching = true;
			invoice.search_type = $(this).attr('name');
		});

		$('input[name=sale_id], input[name=invoice_id]').on('focusout', function()
		{
			invoice.is_invoice_searching = false;
			invoice.search_type = '';
		});

		$('input[name=currency]').on('click', function()
		{
			let value = $(this).val().trim();

			if (invoice.rates.hasOwnProperty(value))
			{
				invoice.current_rate = invoice.rates[value];

				$('input[name=exchange_rate]').val(invoice.current_rate);

				invoice.setProductDetails();
			}
		});

		$('input[name=exchange_rate]').on('input', function()
		{
			let value = $(this).val().trim();

			if (value == Number(value) && Number(value) >= 1 && Number(value) <= 10)
			{
				if (invoice.current_rate !== 1)
				{
					invoice.current_rate = Number(value);
					invoice.setProductDetails();
				}
			}
			else
			{
				alert('Exchange Rate must be between 1 and 10');
			}
		});

		$('select[name=bank_account]').on('change', function()
		{
			let value = $(this).val().trim();

			if (invoice.banks.hasOwnProperty(value))
			{
				$('input[name=iban]').val(invoice.banks[value].iban);
				$('input[name=swift_code]').val(invoice.banks[value].swift_code);
			}
			else
			{
				$('input[name=iban]').val('');
				$('input[name=swift_code]').val('');
			}
		});

		$('input[type=button]').on('click', function()
		{
			if ($('input[name=vat_percent]').val() != Number($('input[name=vat_percent]').val()))
			{
				alert('VAT Percent has to be a number');

				return;
			}

			let object = {
				id : invoice.id,
				id_date : invoice.id_date,
				credit_id : invoice.credit_id,
				credit_date : invoice.credit_date,
				client : $('input[name=client]').val(),
				client_address : $('input[name=client_address]').val(),
				client_registration_number : $('input[name=client_registration_number]').val(),
				client_vat_number : $('input[name=client_vat_number]').val(),
				client_mol : $('input[name=client_mol]').val(),
				supplier : $('input[name=supplier]').val(),
				supplier_address : $('input[name=supplier_address]').val(),
				company_id : $('input[name=company_id]').val(),
				supplier_vat_number : $('input[name=supplier_vat_number]').val(),
				bank_account_id : $('select[name=bank_account]').val(),
				bank_account : (invoice.banks.hasOwnProperty($('select[name=bank_account]').val())) ? invoice.banks[$('select[name=bank_account]').val()].bank_account.substr(0, 12) : '',
				iban : $('input[name=iban]').val(),
				swift_code : $('input[name=swift_code]').val(),
				supplier_mol : $('input[name=supplier_mol]').val(),
				note : $('textarea[name=note]').val(),
				currency : $('input[name=currency]:checked').val(),
				exchange_rate : $('input[name=exchange_rate]').val(),
				vat_percent : $('input[name=vat_percent]').val(),
				details : {}
			};

			let counter = 0;

			$('table.items tr:gt(0)').each(function()
			{
				++counter;

				object.details[counter] = {
					module : $('input[name=product_module_'+counter+']').val(),
					name : $('input[name=product_name_'+counter+']').val(),
					quantity : $('input[name=product_qty_'+counter+']').val(),
					price : $('input[name=product_price_'+counter+']').val(),
					total : $('input[name=product_total_'+counter+']').val()
				};
			});

			let session = {invoice_en : JSON.stringify(object)};

			$.post({
				url : '/documents/set_invoice_en',
				dataType : 'json',
				data : session,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.open(`/documents/print_invoice_en`, '_blank');
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error);
					}
				}
			});
		});

		if (Object.keys(this.json).length)
		{
			$('a.reload').attr('href', $('a.reload').data('reload-invoice')).text('reload by default').show();

			$('input[name=client]').val(this.json.client);
			$('input[name=client_address]').val(this.json.client_address);
			$('input[name=client_registration_number]').val(this.json.client_registration_number);
			$('input[name=client_vat_number]').val(this.json.client_vat_number);
			$('input[name=client_mol]').val(this.json.client_mol);
			$('input[name=supplier]').val(this.json.supplier);
			$('input[name=supplier_address]').val(this.json.supplier_address);
			$('input[name=company_id]').val(this.json.company_id);
			$('input[name=supplier_vat_number]').val(this.json.supplier_vat_number);
			$('select[name=bank_account]').val(this.json.bank_account_id);
			$('input[name=iban]').val(this.json.iban);
			$('input[name=swift_code]').val(this.json.swift_code);
			$('input[name=supplier_mol]').val(this.json.supplier_mol);
			$('input[name=currency][value='+this.json.currency+']').attr('checked', true);
			$('input[name=exchange_rate]').val(this.json.exchange_rate);
			$('input[name=vat_percent]').val(this.json.vat_percent);
			$('textarea[name=note]').val(this.json.note);

			let json = this.json.details,
				tbody = '';

			$('tbody#js-details').html(tbody);

			setTimeout(function()
			{
				for (let i in json)
				{
					let name = json[i].name.replace(/"/g, '&quot;'),
						total = Number(json[i].quantity) * Number(json[i].price);

					tbody += `
					<tr data-id="${i}">
					<td><input type="text" name="product_module_${i}" value="${json[i].module}"></td>
					<td><input type="text" name="product_name_${i}" value="${name}"></td>
					<td><input type="text" name="product_qty_${i}" value="${json[i].quantity}" class="right"></td>
					<td><input type="text" name="product_price_${i}" value="${json[i].price}" class="right"></td>
					<td><input type="text" name="product_total_${i}" value="${total}" class="right"></td>
					</tr>
					`;
				}

				$('tbody#js-details').html(tbody);
			}, 100);
		}

		if (this.is_reload_json)
		{
			$('a.reload').attr('href', $('a.reload').data('reload-json')).text('reload last preview').show();
		}
	},
	setProductDetails : function()
	{
		for (let i in invoice.details)
		{
			let quantity = parseInt(invoice.details[i]['Брой']),
				price = Number(invoice.details[i]['Ед.цена']) * this.current_rate;

			$('input[name=product_qty_'+(Number(i) + 1)+']').val(quantity);
			$('input[name=product_price_'+(Number(i) + 1)+']').val(price.toFixed(2));
			$('input[name=product_total_'+(Number(i) + 1)+']').val((quantity * price).toFixed(2));
		}
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if (invoice.is_invoice_searching)
				{
					let object = {
						type : invoice.search_type,
						id : $('input[name='+invoice.search_type+']').val().trim()
					};

					if (object.id == Number(object.id) && object.id.length >= 1 && Number(object.id) >= 1)
					{
						$.post({
							url : '/documents/get_invoice_number',
							dataType : 'json',
							data : object,
							success : function(response)
							{
								if (response.hasOwnProperty('number'))
								{
									window.location = `/documents/invoice_en/${response.number}`;
								}

								if (response.hasOwnProperty('error'))
								{
									alert(response.error);
								}
							}
						});
					}
					else
					{
						alert(invoice.id_have_to_be_positive_number);
					}

					return false;
				}
			}
		});
	}()
};
$(invoice.construct());
</script>
<?php else: ?>
няма фактура с този номер
<?php endif; ?>