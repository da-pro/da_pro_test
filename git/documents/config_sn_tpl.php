<script src="/js/jquery/jquery-3.4.1.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
<style>
form, input, table, tr, th, td {margin:0; padding:0; border:0; vertical-align:baseline;}
form.serial-number {width:100%; position:relative; float:left; background:#ffffff; border-radius:3px; box-shadow:0 2px 4px #708090; cursor:default;}
form.serial-number input[type=text] {width:110px; height:30px; padding:0 5px; color:#242424; background:#cceeff; font:1.3em/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box;}
form.serial-number input[type=text]:focus {background:#ffffff; outline:none;}
form.serial-number table {width:100%; margin:50px 0; border-collapse:separate; border-spacing:0 10px;}
form.serial-number table tr th {width:50%; padding:0 10px 0 0; color:#242424; font:1.3em/30px 'Open Sans', sans-serif; text-align:right; box-sizing:border-box; border:0;}
form.serial-number table tr th span {margin:0 10px 0 0; color:#ff3333; font:bold 1.2em/30px arial; cursor:pointer;}
form.serial-number table tr td {width:50%; margin:0; padding:0!important; border:0;}
input[type=button] {width:250px; margin:auto; position:absolute; right:0; left:0; color:#ffffff; font:bold 1.3em/35px 'Open Sans', sans-serif; border-radius:3px;}
input[type=button][name=add] {top:10px; background:#6699cc; box-shadow:0 -2px 0 #006699 inset;}
input[type=button][name=preview] {bottom:10px; background:#66bb66; box-shadow:0 -2px 0 #009900 inset;}
input[type=button][name=add]:hover, input[type=button][name=add]:focus {background:#006699; outline:none; cursor:pointer;}
input[type=button][name=preview]:hover, input[type=button][name=preview]:focus {background:#009900; outline:none; cursor:pointer;}
input[type=button]:disabled, input[type=button]:disabled:hover {background:#cccccc; box-shadow:0 -2px 0 #999999 inset; cursor:not-allowed;}
input[type=button]::-moz-focus-inner {border:0;}
label {width:125px; padding:0 0 0 30px; position:relative; font:1.3em/30px 'Open Sans', sans-serif; text-align:left; box-sizing:border-box; display:inline-block; cursor:pointer;}
label>input[type=radio] {display:none;}
label>input[type=radio]+span {width:24px; height:24px; position:absolute; top:3px; left:0; background:#ffffff; border:2px solid #99ddff; border-radius:50%; box-sizing:border-box;}
label>input[type=radio]+span::before {width:12px; height:12px; position:absolute; top:4px; left:4px; background:#99ddff; border-radius:50%; display:none; content:'';}
label>input[type=radio]:checked+span::before {display:block;}
</style>
<form class="serial-number">
	<input type="button" name="add" value="добави друга поредица СН">
	<table>
		<tr>
			<th>Избери размер на схема</th>
			<td>
				<label><input type="radio" name="grid" value="10" checked><span></span>4 x 10</label>
				<label><input type="radio" name="grid" value="14"><span></span>4 x 14</label>
			</td>
		</tr>
		<tr>
			<th>Въведи начален и краен СН</th>
			<td>
				<input type="text" placeholder="от" maxlength="10" class="js-from-1">
				&mdash;
				<input type="text" placeholder="до" maxlength="10" class="js-to-1">
			</td>
		</tr>
	</table>
	<input type="button" name="preview" value="преглед на въведените СН" disabled>
</form>
<script>
var sn = {
	added_series : 1,
	series : {
		1 : {
			from : '',
			to : ''
		}
	},
	construct : function()
	{
		$('input[name=add]').on('click', function()
		{
			++sn.added_series;

			sn.series[sn.added_series] = {
				from : '',
				to : ''
			};

			let row = `
			<tr>
			<th><span title="Премахни" data-id="${sn.added_series}">[-]</span>Друг начален и краен СН</th>
			<td>
			<input type="text" placeholder="от" maxlength="10" class="js-from-${sn.added_series}">
			&mdash;
			<input type="text" placeholder="до" maxlength="10" class="js-to-${sn.added_series}">
			</td>
			</tr>
			`;

			$('form.serial-number table').append(row);
		});

		$('body').on('input', 'input[class^=js-from], input[class^=js-to]', function()
		{
			let input_class = $(this).attr('class').split('-');

			sn.series[input_class[2]][input_class[1]] = $(this).val().trim();

			sn.isPreviewDisabled();
		});

		$('body').on('click', 'form.serial-number table tr th span', function()
		{
			$(this).parent().parent().remove();

			delete sn.series[$(this).data('id')];

			sn.isPreviewDisabled();
		});

		$('input[name=preview]').on('click', function()
		{
			if (!window.navigator.userAgent.toLowerCase().includes('chrome'))
			{
				alert('Използвай Google Chrome');

				return;
			}

			$(this).prop('disabled', true);

			let object = {
				grid : $('input[name=grid]:checked').val(),
				series : JSON.stringify(sn.series)
			};

			$.post({
				url : '/documents/get_config_sn',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.open('/documents/print_config_sn', '_blank');
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error.join('\n\n'));
					}

					$('input[name=preview]').prop('disabled', false);
				}
			});
		});
	},
	isPreviewDisabled : function()
	{
		let is_disabled = true;

		for (let i in this.series)
		{
			if (this.series[i].from !== '' && this.series[i].to !== '')
			{
				is_disabled = false;

				break;
			}
		}

		$('input[name=preview]').prop('disabled', is_disabled);
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if (!$('input[name=preview]').prop('disabled'))
				{
					$('input[name=preview]').trigger('click');
				}
			}
		});
	}()
};
$(sn.construct());
</script>