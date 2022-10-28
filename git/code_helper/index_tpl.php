<script src="/js/jquery/jquery-3.4.1.js"></script>
<script src="/js/jquery/jquery-ui.js"></script>
<link rel="stylesheet" href="/js/jquery/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
<script src="/js/jquery/highlight.min.js"></script>
<link rel="stylesheet" href="/js/jquery/agate.min.css">
<style>
section, div, form, input, select, textarea, label, table, tbody, tr, th, td, p, ul, li, a, span, i, b {margin:0; padding:0; border:0; vertical-align:baseline;}
.no-padding {padding:0!important;}
.no-padding-top {padding-top:0!important;}
.no-padding-right {padding-right:0!important;}
.no-padding-bottom {padding-bottom:0!important;}
.no-padding-left {padding-left:0!important;}
.position-relative {position:relative!important;}
div.table {width:100%; display:table;}
div.table>div.row {width:inherit; display:table-row;}
._1, ._2, ._3, ._4, ._5, ._6, ._7, ._8, ._9, ._10, ._11, ._12 {width:100%; padding:0 5px 10px; float:left; box-sizing:border-box; display:table-cell;}
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
div.row>div[class^='_']>div[class^='_'] {padding:0;}
section#code-helper {width:100%; height:560px; position:relative;}
form.code-helper {width:100%; margin:0 auto 10px; padding:10px; float:left; background:#ffffff; border-radius:3px; box-shadow:0 0 6px #708090; box-sizing:border-box; display:block; cursor:default;}
form.code-helper>p {width:500px; padding:0; color:#ffffff; font:bold 14px/27px 'Open Sans', sans-serif; text-align:center; border:0; border-radius:3px; box-sizing:border-box; display:block;}
form.code-helper>p.success {margin:0 auto 10px; background:#66bb66;}
form.code-helper>p.error {margin:0 auto 10px; background:#ff6666;}
form.code-helper select, form.code-helper textarea {width:100%; margin:0 0 10px; color:#242424; background:#cceeff; font:14px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box; display:block;}
form.code-helper select {height:30px; padding:0 0 0 2px;}
form.code-helper textarea[name=body] {height:30px; min-height:30px; max-height:180px; padding:0 5px; position:relative; line-height:29px; resize:none; z-index:1000;}
form.code-helper textarea[name=code] {height:450px; padding:4px 5px; font-size:13px; font-family:'Lucida Grande', monospace; line-height:17px; word-wrap:break-word; word-break:break-all; resize:none;}
form.code-helper select.notice, form.code-helper textarea.notice {background:rgba(255, 102, 102, 0.05); border:1px solid #ff6666;}
form.code-helper select:focus, form.code-helper textarea:focus {background:#fffaf0; border-color:#ffcc66; outline:none;}
form.code-helper select:-moz-focusring {color:transparent!important; text-shadow:0 0 0 #242424;}
form.code-helper label {width:100%; color:#242424; font:14px/30px 'Open Sans', sans-serif; box-sizing:border-box; display:block;}
form.code-helper label.right {padding:0 10px 0 0; text-align:right;}
form.code-helper label.left {font-weight:bold; text-align:left;}
form.code-helper input[type=button] {width:100px; height:30px; padding:0 0 3px; color:#ffffff; font:bold 14px/27px 'Open Sans', sans-serif; border-radius:3px; display:block;}
form.code-helper input[type=button]:hover, form.code-helper input[type=button]:focus {outline:none; cursor:pointer;}
form.code-helper input[type=button]::-moz-focus-inner {border:0;}
form.code-helper input[name=reset], form.code-helper input[name=select] {color:#333333; background:#f0f0f0; border:1px solid #cccccc; box-sizing:border-box; display:block; visibility:hidden;}
form.code-helper input[name=reset]:hover, form.code-helper input[name=select]:hover {background:#cccccc;}
form.code-helper input[name=new_code] {padding:0 0 1px; position:absolute; bottom:0; left:0; background:#66ccff; box-shadow:0 -2px 0 #0088cc inset;}
form.code-helper input[name=new_code]:hover, form.code-helper input[name=new_code]:focus {background:#0088cc;}
form.code-helper input[name=submit_code] {margin:10px auto 0; padding:0 0 1px;}
form.code-helper input[name=submit_code][value='създай'] {background:#66ccff; box-shadow:0 -2px 0 #0088cc inset;}
form.code-helper input[name=submit_code][value='създай']:hover, form.code-helper input[name=submit_code][value='създай']:focus {background:#0088cc;}
form.code-helper input[name=submit_code][value='обнови'] {background:#66bb66; box-shadow:0 -2px 0 #009900 inset;}
form.code-helper input[name=submit_code][value='обнови']:hover, form.code-helper input[name=submit_code][value='обнови']:focus {background:#009900;}
form.code-helper input[name=delete_code] {padding:0 0 1px; position:absolute; right:0; bottom:0; background:#ff6666; box-shadow:0 -2px 0 #ff3333 inset;}
form.code-helper input[name=delete_code]:hover, form.code-helper input[name=delete_code]:focus {background:#ff3333;}
form.code-helper div.custom-buttons {display:block; visibility:hidden;}
form.code-helper div.custom-buttons>span {float:right; background:#cccccc; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:inline-block;}
form.code-helper div.custom-buttons>span>span {padding:0 10px; color:#333333; font:bold 14px/27px 'Open Sans', sans-serif; text-align:left; cursor:default;}
form.code-helper div.custom-buttons>span>input[type=button] {width:auto; height:27px; margin:0 0 0 1px; padding:0 10px; float:right; color:#333333; background:#f0f0f0; font:bold 14px/27px 'Open Sans', sans-serif; text-align:center; border:0; border-radius:0; box-sizing:border-box;}
form.code-helper div.custom-buttons>span>input[type=button]:hover {background:#cccccc;}
form.code-helper div.custom-positioned {width:100%; position:absolute; top:40px; border-right:5px solid rgba(0,0,0,0);}
form.code-helper div.created-by {position:absolute; top:500px; right:5px;}
form.code-helper div.created-by>span {width:auto; height:28px; margin:0; padding:0 4px; float:right; color:#333333; background:#f0f0f0; font:13px/28px 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; display:block; cursor:default;}
form.code-helper div.created-by>span>b {margin:0 4px 0 0;}
form.code-helper div.created-by>span>span.created-by {height:18px; padding:0 4px; color:#333333; background:#ffffff; line-height:18px; border-radius:3px; border:1px solid #cccccc; display:inline-block;}
form.code-helper div.viewed-by>span {width:auto; min-height:28px; margin:10px 0 0; padding:0 4px; float:left; color:#333333; background:#f0f0f0; font:13px/28px 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; display:block; cursor:default;}
form.code-helper div.viewed-by>span>b {float:left;}
form.code-helper div.viewed-by>span>span.viewers {min-height:20px; margin:4px 0 0; padding:0 0 0 24px; position:relative; line-height:20px; display:inline;}
form.code-helper div.viewed-by>span>span.no-add-button {padding:0;}
form.code-helper div.viewed-by>span>span.viewers>span.add-viewer {width:20px; height:20px; position:absolute; top:-1px; left:4px; color:#ffffff; background:#66bb66; font-weight:bold; line-height:16px; text-align:center; border:1px solid #009900; border-radius:3px; box-sizing:border-box; display:block;}
form.code-helper div.viewed-by>span>span.viewers>span.add-viewer:hover {background:#009900; cursor:pointer;}
form.code-helper div.viewed-by>span>span.viewers>i {margin:0 0 4px 4px; padding:0 24px 0 4px; position:relative; color:#333333; background:#ffffff; font-style:normal; line-height:18px; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:inline-block;}
form.code-helper div.viewed-by>span>span.viewers>i.no-remove-button {padding:0 4px;}
form.code-helper div.viewed-by>span>span.viewers>i>b {width:20px; height:20px; position:absolute; top:-1px; right:-1px; color:#ffffff; background:#ff6666; font-weight:bold; line-height:16px; text-align:center; border:1px solid #ff3333; border-radius:0 3px 3px 0; box-sizing:border-box;}
form.code-helper div.viewed-by>span>span.viewers>i>b:hover {background:#ff3333; cursor:pointer;}
form.code-helper section {width:100%; height:450px; padding:0; float:left; background:#f0f0f0; border-radius:3px; box-sizing:border-box; display:block; overflow-y:auto;}
form.code-helper section>pre {font-size:13px; display:none;}
form.code-helper section>pre>code.urls {width:100%; padding:5px 6px!important; color:#ffffff; background:#333333; word-wrap:break-word; word-break:break-all; white-space:pre-wrap; box-sizing:border-box; display:block;}
form.code-helper section>pre>code.urls>br {display:block;}
form.code-helper section>pre>code.urls>a {color:#62c8f3; font-size:13px; font-family:'Lucida Grande', monospace; line-height:17px;}
form.code-helper section>pre>code.urls>a:hover {text-decoration:underline; cursor:pointer;}
code.selectable {-webkit-touch-callout:all; -webkit-user-select:all; -khtml-user-select:all; -moz-user-select:all; -ms-user-select:all; user-select:all;}
code.hljs {padding:5px 6px!important; word-wrap:break-word; word-break:break-all; white-space:pre-wrap;}
table.code-helper {width:100%; border-collapse:separate; border-spacing:1px; cursor:default;}
table.code-helper tr th {height:40px; color:#ffffff; background:#6699cc; font:bold 14px/40px 'Open Sans', sans-serif; text-align:center; vertical-align:middle;}
table.code-helper tr.filter th {height:30px; line-height:30px;}
table.code-helper tr.filter th:nth-child(1) {width:50px;}
table.code-helper tr.filter th:nth-child(1):hover {background:#ff3300; cursor:pointer;}
table.code-helper tr.filter th input[type=text], table.code-helper tr.filter th select {width:100%; height:30px; color:#242424; background:#ffffff; font:14px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:0; box-sizing:border-box;}
table.code-helper tr.filter th input[type=text] {padding:0 5px;}
table.code-helper tr.filter th select {padding:0 1px;}
table.code-helper tr.filter th input[type=text]:focus, table.code-helper tr.filter th select:focus {background:#fffaf0; border-color:#ffcc66; box-shadow:0 0 6px #ffcc66 inset; outline:none;}
table.code-helper tr td {padding:0 0 0 5px!important; color:#242424; font:12px/30px 'Open Sans', sans-serif; text-align:left; vertical-align:middle;}
table.code-helper tr td.view-code:hover {background:#99ddff; cursor:pointer;}
table.code-helper tr td:nth-child(1) {padding:0 0 0 52px!important; font-weight:bold; line-height:20px; white-space:pre-line;}
table.code-helper tr td:nth-child(2) {font-weight:bold;}
table.code-helper tr td:nth-child(5) {line-height:20px; white-space:pre-line;}
table.code-helper tr td>i {font-size:12px;}
table.code-helper tr td>i.fa-edit, table.code-helper tr td>i.fa-eye {width:50px; margin:0 0 0 -52px; float:left; line-height:20px; text-align:center;}
table.code-helper tr td>i.fa-edit {color:#009900;}
table.code-helper tr:nth-child(odd) td {background:#fafafa;}
table.code-helper tr:nth-child(even) td {background:#f0f0f0;}
table.code-helper tr.active td, table.code-helper tr:hover td {background:#cceeff;}
div#select-staff {margin:0; padding:0; display:none;}
div#select-staff select {width:95%; height:30px; margin:10px auto 0; color:#242424; background:#cceeff; font:14px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; display:block;}
div#select-staff select:focus {background:#fffaf0; border-color:#ffcc66; outline:none;}
.ui-widget {font-size:1.3em;}
</style>
<section id="code-helper"></section>
<div id="select-staff">
	<select>
		<option value="" selected>избери</option>
<?php foreach ($staff as $key => $value): ?>
		<optgroup label="<?= $key ?>">
<?php foreach ($value as $id => $name): ?>
			<option value="<?= $id ?>"<?= ($id === $user_id) ? ' disabled' : '' ?>><?= $name ?></option>
<?php endforeach; ?>
		</optgroup>
<?php endforeach; ?>
	</select>
</div>
<table class="code-helper">
	<tr>
		<th>Право</th>
		<th>Заглавие</th>
		<th>Език</th>
		<th>Код</th>
		<th>Създаден от</th>
		<th>Видим от</th>
	</tr>
	<tr class="filter">
		<th title="Премахни Филтрите" id="js-remove-filters">&#x2716;</th>
		<th><input type="text" name="body"></th>
		<th>
			<select name="lang_name">
				<option value="" selected>избери</option>
<?php foreach ($language as $view_name): ?>
				<option value="<?= $view_name ?>"><?= $view_name ?></option>
<?php endforeach; ?>
			</select>
		</th>
		<th><input type="text" name="code"></th>
		<th><input type="text" name="created_by"></th>
		<th><input type="text" name="viewed_by"></th>
	</tr>
	<tbody id="js-code-helper"></tbody>
</table>
<script>
var code_helper = {
	strings : {
		remove : 'Изтриване',
		edit : 'Редакция',
		review : 'Преглед',
		confirm_save_inquiry : 'Потвърди създаване на справка',
	},
	json : [],
	is_json_ready : false,
	code : '',
	language : 'plaintext',
	author : <?= $user_id ?>,
	is_filtered_event : false,
	is_textarea_focused : false,
	construct : function()
	{
		let that = this;

		that.getCode();

		$('section#code-helper').load('/code_helper/form/0', function()
		{
			$('form.code-helper').fadeIn(200);
		});

		$('body').on('input', 'form.code-helper textarea[name=body]', function()
		{
			that.is_textarea_focused = true;

			let new_lines = $('form.code-helper textarea[name=body]').val().match(new RegExp('\n', 'g'));

			if (new_lines === null)
			{
				that.is_textarea_focused = false;
			}
		});

		$('body').on('focusin', 'form.code-helper textarea[name=body]', function()
		{
			let new_lines = $('form.code-helper textarea[name=body]').val().match(new RegExp('\n', 'g')),
				height = 1;

			if (new_lines !== null)
			{
				that.is_textarea_focused = true;

				height = new_lines.length > 5 ? 5 : new_lines.length;
			}

			$(this).animate({'height': '+='+(height * 30)+'px'}, 200);
		});

		$('body').on('focusout', 'form.code-helper textarea[name=body]', function()
		{
			that.is_textarea_focused = false;

			$(this).animate({'height': '30px'}, 200);
		});

		$('tr.filter th>input').on('focusin', function()
		{
			that.is_filtered_event = true;
		});

		$('tr.filter th>input').on('focusout', function()
		{
			that.is_filtered_event = false;
		});

		$('tr.filter th>select').on('change', function()
		{
			that.setFilteredCode();
		});

		$('tr.filter th#js-remove-filters').on('click', function()
		{
			$('tr.filter th>input').val('');
			$('tr.filter th>select').val('');

			that.setCode();
		});

		$('body').on('click', 'td.view-code', function()
		{
			$('tbody#js-code-helper tr').removeClass('active');
			$(this).parent().addClass('active');

			that.id = Number($(this).data('id'));

			$('section#code-helper').load(`/code_helper/form/${that.id}`, function()
			{
				$('form.code-helper').fadeIn(200);

				that.init();
			});
		});

		$('body').on('input', 'form.code-helper textarea[name=code]', function()
		{
			that.init();
		});

		$('body').on('change', 'form.code-helper select[name=lang]', function()
		{
			that.init();
		});

		$('body').on('click', 'div>span>span.viewers>i>b', function()
		{
			let id = Number($(this).data('id'));

			$(this).parent().remove();

			if (that.viewed_by.includes(id))
			{
				that.viewed_by.splice(that.viewed_by.indexOf(id), 1);
			}
		});

		$('body').on('click', 'div>span>span.viewers>span.add-viewer', function()
		{
			$('div#select-staff').dialog({
				width : 400,
				height : 160,
				title : 'Избери служител',
				buttons : {
					'Confirm' : {
						'text' : 'Добави',
						'click' : function()
						{
							let user_id = Number($('div#select-staff>select').val()),
								user_name = $('div#select-staff>select option:selected').text();

							if (user_id > 0 && user_id !== that.author && !that.viewed_by.includes(user_id))
							{
								that.viewed_by.push(Number(user_id));

								$('div>span>span.viewers').append(`<i>${user_name}<b data-id="${user_id}" title="${that.strings.remove}">&mdash;</b></i>`);

								$('div#select-staff').dialog('destroy');
								$('div#select-staff>select').val('');
							}
						}
					}
				},
				close : function()
				{
					$('div#select-staff').dialog('destroy');
				}
			});
		});

		$('body').on('click', 'form.code-helper input[name=reset]', function()
		{
			$('form.code-helper textarea[name=code]').val('');

			that.init();
		});

		$('body').on('click', 'form.code-helper input[name=select]', function()
		{
			window.getSelection().selectAllChildren(document.getElementById('selectable'));

			document.execCommand('copy');
		});

		$('body').on('click', 'form.code-helper input[name=create_inquiry], form.code-helper input[name=preview_inquiry]', function()
		{
			let object = {
				body : $('form.code-helper textarea[name=body]').val(),
				code : $('form.code-helper textarea[name=code]').val(),
				is_create_inquiry : 0
			};

			if ($(this).attr('name') === 'create_inquiry')
			{
				object.is_create_inquiry = 1;

				if (!confirm(that.strings.confirm_save_inquiry))
				{
					return false;
				}
			}

			$.post({
				url : '/code_helper/set_inquiry',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.open(`/spravcho/show/${response.id}`, '_blank');
					}

					if (response.hasOwnProperty('error'))
					{
						that.setError(response.error);
					}
				}
			});
		});

		$('body').on('click', 'form.code-helper input[name=new_code]', function()
		{
			$('section#code-helper').load('/code_helper/form/0', function()
			{
				$('form.code-helper').fadeIn(200);
			});
		});

		$('body').on('click', 'form.code-helper input[name=delete_code]', function()
		{
			if (confirm(`Потвърди изтриване на:\n\n${that.json[that.id].body}`))
			{
				$.getJSON({
					url : `/code_helper/unset_code/${that.id}`,
					success : function(response)
					{
						if (response.hasOwnProperty('success'))
						{
							that.getCode();

							$('section#code-helper').load('/code_helper/form/0', function()
							{
								$('form.code-helper>p').addClass('success').text(response.success);

								$('form.code-helper').fadeIn(200);
							});
						}

						if (response.hasOwnProperty('error'))
						{
							that.setError(response.error);
						}
					}
				});
			}
		});

		$('body').on('click', 'form.code-helper input[name=submit_code]', function()
		{
			that.setError({});

			let object = {
				id : that.id,
				body : $('form.code-helper textarea[name=body]').val(),
				code : $('form.code-helper textarea[name=code]').val(),
				lang : $('form.code-helper select[name=lang]').val(),
				viewed_by : JSON.stringify(that.viewed_by)
			};

			$.post({
				url : '/code_helper/set_code',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getCode();

						$('section#code-helper').load(`/code_helper/form/${response.success.id}`, function()
						{
							$('form.code-helper>p').addClass('success').text(response.success.message);

							$('form.code-helper').fadeIn(200);

							that.init();
						});
					}

					if (response.hasOwnProperty('error'))
					{
						that.setError(response.error);
					}

					$('html').animate({scrollTop : 0}, 400);
				}
			});
		});
	},
	init : function()
	{
		this.code = $('form.code-helper textarea[name=code]').val().trim();
		this.language = $('form.code-helper select[name=lang]').val();

		$('form.code-helper div.custom-buttons').css('visibility', 'hidden');

		if (this.code.length)
		{
			this.code = this.code.replace(/[<>]/g, m => ({'<':'&lt;', '>':'&gt;'})[m]);

			if (this.language === 'link')
			{
				let string_urls = this.code.split('\n'),
					html_urls = [];

				for (let i of string_urls)
				{
					let text = i.trim();

					if (text.length)
					{
						if (new RegExp(/^http(s)?:\/\//, 'i').test(text))
						{
							html_urls.push(`<a href="${text}" target="_blank">${text}</a>`);
						}
						else
						{
							html_urls.push(text);
						}
					}
					else
					{
						html_urls.push('<br>');
					}
				}

				$('form.code-helper section>pre>code').removeClass().addClass('urls').html(html_urls.join('\n'));
			}
			else
			{
				if (this.language === 'sql')
				{
					$('form.code-helper div.custom-buttons').css('visibility', 'visible');
				}

				$('form.code-helper section>pre>code').removeClass().addClass(`language-${this.language}`).html(this.code);

				hljs.highlightAll();
			}

			$('form.code-helper input[name=reset], form.code-helper input[name=select]').css('visibility', 'visible');
			$('form.code-helper section>pre').fadeIn();
		}
		else
		{
			$('form.code-helper input[name=reset], form.code-helper input[name=select]').css('visibility', 'hidden');
			$('form.code-helper section>pre').fadeOut();
		}

		$('html').animate({scrollTop : 0}, 400);
	},
	setError : function(object)
	{
		if (Object.keys(object).length)
		{
			$('form.code-helper>p').addClass('error').text(Object.values(object).join(', '));

			for (let i in object)
			{
				$(`form.code-helper select[name=${i}], form.code-helper textarea[name=${i}]`).addClass('notice');
			}
		}
		else
		{
			$('form.code-helper>p').removeClass('success error').text('');
			$('form.code-helper select, form.code-helper textarea').removeClass('notice');
		}
	},
	getCode : function()
	{
		this.is_json_ready = false;

		$.getJSON({
			url : '/code_helper/get_code',
			success : function(response)
			{
				code_helper.json = response.data;
				code_helper.is_json_ready = true;
			}
		});

		this.setCode();
	},
	setCode : function()
	{
		let set_interval = setInterval(function()
		{
			if (code_helper.is_json_ready)
			{
				clearInterval(set_interval);

				let order_data = [];

				for (let i in code_helper.json)
				{
					order_data.push([Number(i), code_helper.json[i].created]);
				}

				order_data.sort(function(a, b)
				{
					if (a[1] < b[1])
					{
						return 1;
					}

					if (a[1] > b[1])
					{
						return -1;
					}

					return 0;
				});

				code_helper.setTableData(order_data);
			}
		}, 200);
	},
	setTableData : function(order)
	{
		let tbody = '',
			json = this.json;

		$('tbody#js-code-helper').html(tbody);

		for (let o of order)
		{
			let i = o[0],
				viewed_by = [];

			for (let ii in json[i].viewed_by)
			{
				viewed_by.push(json[i].viewed_by[ii]);
			}

			viewed_by = viewed_by.join('\n');

			let options = `<i data-id="${i}" title="${this.strings.review}" class="far fa-eye"></i>`;

			if (json[i].right === 'write')
			{
				options = `<i data-id="${i}" title="${this.strings.edit}" class="far fa-edit"></i>`;
			}

			tbody += `
			<tr>
			<td colspan="2" data-id="${i}" class="view-code">${options}${json[i].body}</td>
			<td>${json[i].lang_name}</td>
			<td></td>
			<td>${json[i].created_by}</td>
			<td>${viewed_by}</td>
			</tr>
			`;
		}

		$('tbody#js-code-helper').html(tbody);
	},
	setFilteredCode : function()
	{
		let filter = {};

		$('tr.filter th>input, tr.filter th>select').each(function()
		{
			if ($(this).val().trim().length)
			{
				filter[$(this).attr('name')] = new RegExp(escapePattern($(this).val().trim()), 'im');
			}
		});

		if (Object.keys(filter).length && this.is_json_ready)
		{
			let json = this.json,
				order_data = [];

			for (let j in json)
			{
				let found = true;

				for (let i in filter)
				{
					let text = json[j][i];

					found = filter[i].test(text);

					if (!found)
					{
						break;
					}
				}

				if (found)
				{
					order_data.push([Number(j), json[j].created]);
				}
			}

			order_data.sort(function(a, b)
			{
				if (a[1] < b[1])
				{
					return 1;
				}

				if (a[1] > b[1])
				{
					return -1;
				}

				return 0;
			});

			this.setTableData(order_data);
		}
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if (code_helper.is_filtered_event)
				{
					code_helper.setFilteredCode();

					return false;
				}

				if (code_helper.is_textarea_focused)
				{
					$('form.code-helper textarea[name=body]').animate({'height': '+=30px'}, 0);
				}
			}
		});
	}()
};
$(code_helper.construct());

function escapePattern(string)
{
	const escape_characters = '^$?+*.[](){}|\\';

	let escape_string = '';

	for (let i in string)
	{
		if ([...escape_characters].includes(string[i]))
		{
			escape_string += '\\';
		}

		escape_string += string[i];
	}

	return escape_string;
}
</script>