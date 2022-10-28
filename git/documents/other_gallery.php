<!doctype html>
<html>
<head>
<title><?= $title ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="/js/jquery/jquery-3.4.1.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
</head>
<body>
<style>
html, body, div, input, img, h1, h2 {margin:0; padding:0; border:0; vertical-align:baseline;}
html, body {height:100%; background:#708090;}
h1 {color:#ffffff; font:small-caps bold 1.6em/50px 'Open Sans', sans-serif; text-align:center; cursor:default;}
h2 {margin:0; position:absolute; right:0; left:0; color:#ffffff; font:small-caps normal 1.3em/50px 'Open Sans', sans-serif; text-align:center; cursor:default;}
h1, h2 {border-bottom:1px solid #d1d1d1; box-sizing:border-box;}
div.other-documents {position:relative; background:#7d8c9b; border-right:1px solid #d1d1d1;}
div.other-documents>div {width:100%; padding:0 10px; margin:10px 0 0; float:left; box-sizing:border-box; display:block;}
div[class^='_']>div>img {width:100%; border:5px solid #ffffff; box-sizing:border-box; display:block; cursor:pointer;}
div[class^='_']>div:nth-of-type(1)>img {margin:31px 0 0;}
div[class^='_']>div>img:hover, div[class^='_']>div>img.active {border-color:#6ecaff;}
div.document>img {width:90%; margin:0 auto; display:block; cursor:pointer;}
div.document>input[type=button] {width:150px; height:35px; margin:10px auto; color:#ffffff; background:#ff6666; font:small-caps bold 1.1em/30px 'Open Sans', sans-serif; border-radius:5px; box-shadow:0 -2px 0 #ff0000 inset; display:none;}
div.document>input[type=button]:hover, div.document>input[type=button]:focus {background:#ff0000; outline:none; cursor:pointer;}
div.document>input[type=button]::-moz-focus-inner {border:0;}
div.table {width:100%; height:100%; display:table;}
div.row {width:inherit; height:100%; display:table-row;}
._1, ._2, ._3, ._4, ._5, ._6 {width:100%; height:100%; padding:0 5px; float:left; box-sizing:border-box; display:table-cell;}
@media only screen and (min-width:768px)
{
._1_, ._2_, ._3_, ._4_ {padding:0;}
._1_ {width:25%;}
._2_ {width:50%;}
._3_ {width:75%;}
._4_ {width:100%;}
}
@media only screen and (min-width:1200px)
{
._1 {width:16.66%;}
._2 {width:33.33%;}
._3 {width:50%;}
._4 {width:66.66%;}
._5 {width:83.33%;}
._6 {width:100%;}
}
</style>
<?php if ($error_message): ?>
<h1><?= $error_message ?></h1>
<?php else: ?>
<div class="table">
	<div class="row">
		<div class="_1 _2_ other-documents">
			<h2><?= $type_name ?></h2>&nbsp;
<?php foreach ($browse_directory as $value): ?>
			<div>
				<img src="<?= $is_outside ? '/documents/image?path=' : 'http://docs.jarnet' ?><?= $value ?>?v=<?= time() ?>" data-src="<?= $value ?>">
			</div>
<?php endforeach; ?>
		</div>
		<div class="_5 _2_ document">
			<h1>избери документ</h1>
			<input type="button" value="изтриване">
			<img title="Отвори в нов прозорец">
		</div>
	</div>
</div>
<?php endif; ?>
<script>
var gallery = {
	string : {
		other_documents : 'други документи ',
		confirm_delete_document : 'Потвърди Изтриване на Документ'
	},
	document_source : '',
	init : function()
	{
		$('div.other-documents>div>img').on('click', function()
		{
			gallery.document_source = $(this).data('src');

			let selector = $(this).parent(),
				total_ducuments = selector.parent().find('img').length,
				number_of_document = selector.index();

			$('div.other-documents>div>img').removeClass('active');
			$(this).addClass('active');

			let message = gallery.string.other_documents+number_of_document+'/'+total_ducuments;

			$('div.document>h1').text(message);
			$('div.document>input[type=button]').css('display', 'block');
			$('div.document>img').attr('src', $(this).attr('src'));

			$('html').animate({'scrollTop' : 0}, 200);
		});

		$('div.document>input[type=button]').on('click', function()
		{
			if (confirm(gallery.string.confirm_delete_document))
			{
				let object = {
					image_path : gallery.document_source
				};

				$.post({
					url : '/documents/delete_scanned_document',
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
							alert(response.error);
						}
					}
				});
			}
		});

		$('div.document>img').on('click', function()
		{
			window.open($(this).attr('src'));
		});
	}
};
$(gallery.init());
</script>
</body>
</html>