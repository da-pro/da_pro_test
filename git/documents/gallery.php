<!doctype html>
<html>
<head>
	<title><?= $title ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="/js/jquery/jquery-3.4.1.js"></script>
	<script src="/js/jquery/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
</head>
<body>
<style>
html, body, div, img, h1, h2, span {margin:0; padding:0; border:0;}
html, body {width:100%; height:100%; background:#708090;}
h1, h2 {width:100%; height:40px; color:#ffffff; text-align:center; text-transform:uppercase; border-bottom:1px solid #d1d1d1; cursor:default;}
h1 {font:bold 18px/40px 'Open Sans', sans-serif;}
h2 {font:normal 18px/40px 'Open Sans', sans-serif;}
div.table {width:100%; height:100%; display:table;}
div.row {height:100%; display:table-row;}
div.column {width:300px; height:100%; float:left; background:#7d8c9b; display:block;}
div.document {width:calc(100% - 600px); height:100%; float:left; border-right:1px solid #d1d1d1; border-left:1px solid #d1d1d1; box-sizing:border-box; display:block;}
div.containment {margin:0;}
div.container {width:100%; padding:0 10px; position:relative; margin:0 0 10px; float:left; box-sizing:border-box; display:block;}
div.containment>div.container:first-child {margin:10px 0;}
div.container>div.image {width:250px; border:5px solid #ffffff; box-sizing:border-box; display:block;}
div.container>div.image.active {border-color:#ff3333;}
div.invoice div.image {float:left;}
div.warranty div.image {float:right;}
div.image>img {width:100%; height:100%; float:left;}
div.container>span {width:30px; height:16.66%; position:absolute; color:#ffffff; border:0; border-bottom:1px solid #7d8c9b; box-sizing:border-box; display:block;}
div.container>span:nth-of-type(1) {top:0; background:#66a3ff url('/img/svg/sort.svg') no-repeat 50% 50%; background-size:16px 16px;}
div.container>span:nth-of-type(2) {top:16.66%; background:#66a3ff url('/img/svg/arrows-rotate.svg') no-repeat 50% 50%; background-size:16px 16px;}
div.container>span:nth-of-type(3) {top:33.33%; background:#66a3ff url('/img/svg/trash.svg') no-repeat 50% 50%; background-size:16px 16px;}
div.container>span:nth-of-type(4) {top:50%; background:#66a3ff url('/img/svg/download.svg') no-repeat 50% 50%; background-size:16px 16px;}
div.container>span:nth-of-type(5) {top:66.66%; background:#66a3ff url('/img/svg/print.svg') no-repeat 50% 50%; background-size:16px 16px;}
div.container>span:nth-of-type(6) {top:83.33%; background:#66a3ff url('/img/svg/eye.svg') no-repeat 50% 50%; background-size:16px 16px; border-bottom:0;}
div.invoice div.container>span {right:10px;}
div.warranty div.container>span {left:10px;}
div.container>span:nth-of-type(3):hover {background-color:#ff3333;}
span>span.button {width:100%; height:100%; display:flex; align-items:center; cursor:pointer;}
span>span.message {width:230px; height:100%; position:absolute; top:0; color:#ffffff; background:#66a3ff; font:20px 'Open Sans', sans-serif; display:none; align-items:center; justify-content:center; cursor:pointer;}
div.container>span:nth-of-type(3)>span.message:hover, div.container>span:nth-of-type(3):hover>span.message {background:#ff3333;}
div.invoice span>span.message {padding:0 0 0 20px; right:30px;}
div.warranty span>span.message {padding:0 20px 0 0; left:30px;}
div.invoice span>span.button:hover+span.message, div.invoice span>span.message:hover, div.warranty span>span.button:hover+span.message, div.warranty span>span.message:hover {display:flex;}
div.document>img {width:calc(100% - 20px); margin:10px auto; display:block;}
div.background {width:100%; height:100%; position:fixed; top:0; left:0; background:rgba(0, 0, 0, 0.5); display:none; cursor:default; z-index:10001;}
div.background>div.loading {width:50px; height:50px; margin:auto; position:absolute; top:0; right:0; bottom:0; left:0; background:transparent; border:2px solid #ffffff; border-top:2px solid #ff6666!important; border-radius:100%; box-sizing:border-box; animation:rotate 1s linear infinite;}
@keyframes rotate {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
</style>
<?php if ($purchase_error): ?>
<h1>няма покупка с този номер</h1>
<?php
else:
$uri = (substr($_SERVER['REMOTE_ADDR'], 0, 6) !== '10.10.') ? '/documents/image?path=' : 'http://docs.jarnet';
?>
<div class="background">
	<div class="loading"></div>
</div>
<div class="table">
	<div class="row">
		<div class="column invoice">
			<h2>фактури</h2>
			<div class="containment">
				<?php
				sort($files['invoice']);

				foreach ($files['invoice'] as $value):
				?>
				<div data-image-path="<?= $value ?>" data-sorder="_<?= preg_replace('/[^0-9]/', '', end(explode('-', $value))) ?>" class="container">
					<div class="image">
						<img src="<?=  $uri . $value ?>?v=<?= time() ?>" data-src="<?= $value ?>">
					</div>
					<span class="js-sort"><span class="button"></span><span class="message">Сортиране</span></span>
					<span class="js-rotate"><span class="button"></span><span class="message">Завъртане</span></span>
					<span class="js-delete"><span class="button"></span><span class="message">Изтриване</span></span>
					<span class="js-download"><span class="button"></span><span class="message">Сваляне</span></span>
					<span class="js-print"><span class="button"></span><span class="message">Принтирай</span></span>
					<span class="js-preview"><span class="button"></span><span class="message">Преглед</span></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="document">
			<h1><?= (count($files['invoice']) || count($files['warranty'])) ? 'избери документ' : 'тази покупка няма документи' ?></h1>
			<img>
		</div>
		<div class="column warranty">
			<h2>гаранционни карти</h2>
			<div class="containment">
			<?php
			sort($files['warranty']);

			foreach ($files['warranty'] as $value):
			?>
			<div data-image-path="<?= $value ?>" data-sorder="_<?= preg_replace('/[^0-9]/', '', end(explode('-', $value))) ?>" class="container">
				<div class="image">
					<img src="<?=  $uri . $value ?>?v=<?= time() ?>" data-src="<?= $value ?>">
				</div>
				<span class="js-sort"><span class="button"></span><span class="message">Сортиране</span></span>
				<span class="js-rotate"><span class="button"></span><span class="message">Завъртане</span></span>
				<span class="js-delete"><span class="button"></span><span class="message">Изтриване</span></span>
				<span class="js-download"><span class="button"></span><span class="message">Сваляне</span></span>
				<span class="js-print"><span class="button"></span><span class="message">Принтирай</span></span>
				<span class="js-preview"><span class="button"></span><span class="message">Преглед</span></span>
			</div>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
<script>
var gallery = {
	strings : {
		invoice : 'фактура ',
		warranty : 'гаранционна карта ',
		confirm_rotate_document : 'Потвърди Завъртане на Документ на 90° по часовниковата стрелка',
		confirm_delete_document : 'Потвърди Изтриване на Документ'
	},
	document_source : '',
	document_path : '',
	purchase_id : <?= $this->uri->segment(3) ?>,
	file_name : '<?= $_GET['path'] ?: '' ?>',
	init : function()
	{
		if (this.file_name.length)
		{
			$('div.document>img').attr('src', this.file_name);
		}
		
		let sort_array = [],
			position = 0;

		$('div.invoice>div.containment').sortable({
			containment : 'div.invoice>div.containment',
			tolerance : 'pointer',
			handle : 'span.js-sort',
			delay : 10,
			start : function()
			{
				$('div.invoice>div.containment div.container').removeClass('js-sortable');

				let ui = arguments[1];

				ui.item.addClass('js-sortable');

				sort_array = [];
				position = 0;

				sort_array[0] = ui.item.data('sorder');

				$('div.invoice>div.containment div.container').each(function()
				{
					++position;

					if ($(this).hasClass('js-sortable'))
					{
						return false;
					}
				});
			},
			stop : function()
			{
				let event = arguments[0],
					counter = 0;

				for (let i in event.target.children)
				{
					if (typeof event.target.children[i] === 'object')
					{
						++counter;

						if (event.target.children[i].className.includes('js-sortable'))
						{
							if (position === counter)
							{
								return;
							}

							if (position > counter)
							{
								sort_array[1] = event.target.children[counter].dataset.sorder;
							}

							if (position < counter)
							{
								sort_array[1] = event.target.children[counter - 2].dataset.sorder;
							}

							break;
						}
					}
				}

				if (sort_array[0] != sort_array[1])
				{
					$('div.background').show();

					let object = {
						old_sort_id : sort_array[0],
						new_sort_id : sort_array[1],
						purchase_id : gallery.purchase_id,
						type : 'invoice'
					};

					$.post({
						url : '/documents/sort_scanned_document',
						dataType : 'json',
						data : object,
						success : function(response)
						{
							if (response.hasOwnProperty('success'))
							{
								window.location.reload(true);
							}

							if (response.hasOwnProperty('error'))
							{
								$('div.background').hide();

								alert(response.error);
							}
						}
					});
				}
			}
		});

		$('div.warranty>div.containment').sortable({
			containment : 'div.warranty>div.containment',
			tolerance : 'pointer',
			handle : 'span.js-sort',
			delay : 10,
			start : function()
			{
				$('div.warranty>div.containment div.container').removeClass('js-sortable');

				let ui = arguments[1];

				ui.item.addClass('js-sortable');

				sort_array = [];
				position = 0;

				sort_array[0] = ui.item.data('sorder');

				$('div.warranty>div.containment div.container').each(function()
				{
					++position;

					if ($(this).hasClass('js-sortable'))
					{
						return false;
					}
				});
			},
			stop : function()
			{
				let event = arguments[0],
					counter = 0;

				for (let i in event.target.children)
				{
					if (typeof event.target.children[i] === 'object')
					{
						++counter;

						if (event.target.children[i].className.includes('js-sortable'))
						{
							if (position === counter)
							{
								return;
							}

							if (position > counter)
							{
								sort_array[1] = event.target.children[counter].dataset.sorder;
							}

							if (position < counter)
							{
								sort_array[1] = event.target.children[counter - 2].dataset.sorder;
							}

							break;
						}
					}
				}

				if (sort_array[0] != sort_array[1])
				{
					$('div.background').show();

					let object = {
						old_sort_id : sort_array[0],
						new_sort_id : sort_array[1],
						purchase_id : gallery.purchase_id,
						type : 'warranty'
					};

					$.post({
						url : '/documents/sort_scanned_document',
						dataType : 'json',
						data : object,
						success : function(response)
						{
							if (response.hasOwnProperty('success'))
							{
								window.location.reload(true);
							}

							if (response.hasOwnProperty('error'))
							{
								$('div.background').hide();

								alert(response.error);
							}
						}
					});
				}
			}
		});

		$('div.container>span').on('click', function()
		{
			let parent = $(this).parent(),
				type = parent.parent().parent().attr('class').split(' ')[1];

			gallery.document_path = parent.data('image-path');

			switch ($(this).attr('class'))
			{
				case 'js-rotate':
					gallery.setRotation();
				break;

				case 'js-delete':
					gallery.setDelete();
				break;

				case 'js-download':
					window.location.href = `/documents/download_scanned_document?path=${gallery.document_path}`;
				break;
			
				case 'js-print':
					window.open(`/documents/print_scanned_document?path=${gallery.document_path}`);
				break;

				case 'js-preview':
					let number_of_document = parent.index() + 1,
						total_number_of_documents = $(`div.${type} div.container`).length;

					$('div.image').removeClass('active');
					parent.find('div.image').addClass('active');

					gallery.document_source = parent.find('div.image>img').attr('src');

					$('div.document>h1').text(gallery.strings[type]+number_of_document+'/'+total_number_of_documents);

					$('div.document>img').attr('src', gallery.document_source);

					$('html').animate({'scrollTop' : 0}, 200);
				break;
			}
		});

		$('div.document>img').on('click', function()
		{
			window.open($(this).attr('src'));
		});
	},
	setRotation : function()
	{
		if (confirm(this.strings.confirm_rotate_document))
		{
			$('div.background').show();

			let object = {
				image_path : this.document_path
			};

			$.post({
				url : '/documents/rotate_scanned_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.location.reload(true);
					}

					if (response.hasOwnProperty('error'))
					{
						$('div.background').hide();

						alert(response.error);
					}
				}
			});
		}
	},
	setDelete : function()
	{
		if (confirm(this.strings.confirm_delete_document))
		{
			let object = {
				image_path : this.document_path
			};

			$.post({
				url : '/documents/delete_scanned_document',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						window.location.href = `/documents/gallery/${gallery.purchase_id}`;
					}

					if (response.hasOwnProperty('error'))
					{
						alert(response.error);
					}
				}
			});
		}
	}
};
$(gallery.init());
</script>
</body>
</html>