<script src="/js/jquery/jquery-3.4.1.js"></script>
<script src="/js/jquery/jquery-ui.js"></script>
<link rel="stylesheet" href="/js/jquery/jquery-ui.css">
<script src="/js/jquery.cropzoom.js"></script>
<link rel="stylesheet" href="/css/jquery.cropzoom.css">
<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
<style>
section, div, form, span, label, p, h1, strong, b, img {margin:0; padding:0; border:0;}
div.product-images {width:100%; height:100%; position:fixed; top:0; left:0; background:#ffffff; display:none; overflow:hidden; z-index:10000;}
div.product-images>div.top-content {width:100%; height:50px; position:relative; border-bottom:1px solid #f0f0f0;}
div.product-images>div.top-content>h1 {margin:0 0 0 10px; font:19px/50px 'Open Sans', sans-serif; text-align:left; white-space:nowrap;}
div.product-images>div.top-content>h1::after {width:50px; height:50px; position:absolute; top:0; right:0; background:#ffffff; display:block; content:'';}
div.product-images>div.top-content>h1>strong {color:#242424; font-weight:normal; cursor:default;}
div.product-images>div.top-content>h1>strong.old-product {color:#ff3333;}
div.product-images>div.top-content>h1>strong>b {color:#242424; font-weight:bold;}
div.product-images>div.top-content>h1>span {height:30px; margin:10px 10px 10px 0; padding:0 35px 0 10px; position:relative; float:left; color:#ffffff!important; border-radius:3px; border:0!important; display:none; user-select:none; cursor:default;}
div.product-images>div.top-content>h1>span>span.message {height:30px; margin:0!important; padding:0!important; color:#ffffff!important; background:none!important; font:bold 14px/30px 'Open Sans', sans-serif!important; display:block;}
div.product-images>div.top-content>h1>span>span.close {width:20px; height:20px; margin:0; padding:0; position:absolute; top:5px; right:5px; float:none; background:#ffffff; font-size:18px; text-align:center!important; line-height:20px; border-radius:3px;}
div.product-images>div.top-content>h1>span.success {background:#66bb66; display:block;}
div.product-images>div.top-content>h1>span.error {background:#ff6666; display:block;}
div.product-images>div.top-content>h1>span.success>span.close {color:#66bb66;}
div.product-images>div.top-content>h1>span.success>span.close:hover {color:#ffffff; background:#009900; cursor:pointer;}
div.product-images>div.top-content>h1>span.error>span.close {color:#ff6666;}
div.product-images>div.top-content>h1>span.error>span.close:hover {color:#ffffff; background:#ff3333; cursor:pointer;}
div.product-images>div.top-content>input[name=close] {width:30px; height:30px; padding:0; position:absolute; top:10px; right:10px; color:#ff6666; background:#ffffff; font:bold 28px/20px 'Open Sans', sans-serif; text-align:center; border:2px solid #ff6666; border-radius:3px; box-sizing:border-box;}
div.product-images>div.top-content>input[name=close]:hover {color:#ffffff; background:#ff6666; outline:none; cursor:pointer;}
div.product-images>section {width:100%; height:calc(100% - 50px);}
div.product-images>section>div[class$='content'] {width:50%; height:100%; padding:10px; position:relative; float:left; box-sizing:border-box; overflow-y:auto;}
div.product-images>section>div.left-content {border-right:1px solid #f0f0f0;}
div.left-content>div.view-gallery, div.right-content>div.web-search, div.right-content>div.crop-image {width:100%; display:block;}
div.settings>h1, div.active-image>h1, div.other-image>h1, div.short-name>h1, div.long-name>h1, div.part-number>h1, div.crop-image>h1 {height:22px; margin:0 0 10px; padding:0 0 0 10px; color:#ffffff; font:14px/20px 'Open Sans', sans-serif; text-align:left; border-top:1px solid #708090; border-radius:0 0 0 3px; cursor:default;}
div.settings>h1 {background:linear-gradient(to right, #708090 66px, #ffffff 66px);}
div.active-image>h1 {background:linear-gradient(to right, #708090 76px, #ffffff 76px);}
div.other-image>h1 {background:linear-gradient(to right, #708090 94px, #ffffff 94px);}
div.short-name>h1 {background:linear-gradient(to right, #708090 114px, #ffffff 114px); cursor:pointer;}
div.long-name>h1 {background:linear-gradient(to right, #708090 102px, #ffffff 102px); cursor:pointer;}
div.part-number>h1 {background:linear-gradient(to right, #708090 116px, #ffffff 116px); cursor:pointer;}
div.crop-image>h1 {background:linear-gradient(to right, #708090 84px, #ffffff 84px);}
div.settings>form>input[type=file] {display:none;}
div.settings input[type=button] {margin:0 5px 10px 0; padding:0 10px; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; text-align:center; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:inline-block;}
div.settings input[type=button]:hover {background:#cccccc; cursor:pointer;}
div.view-gallery section>p {margin:0 0 5px; padding:0 0 0 10px; color:#242424; background:#f0f0f0; font:small-caps 17px/1 sans-serif; cursor:default;}
div.view-gallery>div.active-image, div.view-gallery>div.other-image {clear:both;}
div.view-gallery>div.other-image>section>input[type=button] {width:auto; margin:-20px auto 10px; padding:0 10px; color:#ffffff; background:#ff6666; font:bold 14px/30px 'Open Sans', sans-serif; border:1px solid #ff3333; border-radius:3px; box-sizing:border-box; display:block;}
div.view-gallery>div.other-image>section>input[type=button]:hover {background:#ff3333; cursor:pointer;}
div.view-gallery div.images {width:25%; height:240px; float:left;}
div.view-gallery div.images>div.image {width:98%; height:98%; margin:0 2% 2% 0; position:relative; background:#ffffff; border:1px solid #ffffff; box-sizing:border-box;}
div.view-gallery div.images>div.backup {border-color:#ff3333;}
div.view-gallery div.images>div.inner {border-color:#3b5998;}
div.view-gallery div.images>div.outer {border-color:#d9d9d9;}
div.view-gallery div.images:hover>div.outer {border-color:#333333;}
div.view-gallery div.images>div.image>img {width:96%; height:140px; margin:calc(2% + 20px) auto 2%; color:#ff3333; font:14px 'Open Sans', sans-serif; display:block; word-wrap:break-word;}
div.view-gallery div.images>div.outer>img {max-width:140px;}
div.view-gallery div.images>div.inner>div.preview {width:352px; height:352px; position:absolute; background:#ffffff; border:1px solid #3b5998; box-sizing:border-box; z-index:1000;}
div.view-gallery div.images>div.inner>div.preview>img {display:block;}
section#default-image div.images>div.image {border-color:#66bb66;}
section#active-images div.images>div.image>img {cursor:grab;}
div.images>div.image span {position:absolute; font:12.8px/20px 'Open Sans', sans-serif; text-align:center;}
div.images>div.image span.remove {width:100%; top:0; color:#ffffff; background:#ff3333; cursor:pointer;}
div.images>div.image span.search {padding:0 0 0 2px; top:0; left:0; color:#3b5998; text-align:left;}
div.images>div.image span.remove-all {padding:0 2px 0 0; top:0; right:0; color:#3b5998; text-align:right;}
div.images>div.image span.remove-all:hover {text-decoration:underline; color:#ff3333; cursor:pointer;}
div.images>div.image span.default-image {width:100%; bottom:40px; left:0; color:#ffffff; background:#66bb66; text-align:center; cursor:default;}
div.images>div.image span.set-default {width:50%; bottom:40px; left:0;}
div.images>div.image span.set-active {width:50%; right:0; bottom:40px;}
div.images>div.image span.crop {width:100%; left:0; bottom:40px; color:#ffffff; background:#3b5998; cursor:pointer;}
div.images>div.image span.image-info {width:100%; bottom:20px; color:#242424;}
div.images>div.image span.image-info b {font-weight:normal; cursor:default;}
div.images>div.image span.image-info>a {margin:0 0 0 10px; padding:0 4px; color:#3b5998; background:#ffffff; text-decoration:none;}
div.images>div.image span.image-info>a.highlight {color:#ffffff; background:#ff3333;}
div.images>div.image span.image-info>a:hover {color:#242424; background:#f0f0f0; cursor:pointer;}
div.images>div.image span.edit {width:50%; padding:0 0 0 2px; bottom:0; left:0; color:#3b5998; text-align:left;}
div.images>div.image span.download {width:50%; padding:0 2px 0 0; right:0; bottom:0; color:#3b5998; text-align:right;}
div.images>div.image span.search:hover, div.images>div.image span.edit:hover, div.images>div.image span.download:hover {text-decoration:underline; cursor:pointer;}
div.images>div.image span.edit-backup {width:100%; bottom:0; left:0; color:#ffffff; background:#66bb66; cursor:pointer;}
span.set-default>label.checkbox {padding:0 0 0 24px; text-align:left;}
span.set-active>label.checkbox {padding:0 24px 0 0; text-align:right;}
div.images>div.image label.checkbox {width:100%; height:20px; position:relative; color:#242424; font:1em/20px 'Open Sans', sans-serif; box-sizing:border-box; display:block;}
div.images>div.image label.checkbox:hover {cursor:pointer;}
div.images>div.image label.checkbox>input[type=checkbox] {display:none;}
div.images>div.image label.checkbox>input[type=checkbox]+span {width:16px; height:16px; position:absolute; top:2px; background:#ffffff; border:2px solid #777777; box-sizing:border-box;}
div.images>div.image label.checkbox>input[type=checkbox]+span::before {width:8px; height:8px; position:absolute; top:2px; left:2px; background:#777777; display:none; content:'';}
div.images>div.image label.checkbox>input[type=checkbox]:checked+span::before {display:block;}
span.set-default>label.checkbox>input[type=checkbox]+span {left:2px;}
span.set-active>label.checkbox>input[type=checkbox]+span {right:2px;}
div.images>div.image span.set-default>label.checkbox:hover {color:#66bb66;}
div.images>div.image span.set-default>label.checkbox:hover>input[type=checkbox]+span {border-color:#66bb66!important;}
div.images>div.image span.set-default>label.checkbox:hover>input[type=checkbox]+span::before {background:#66bb66!important;}
div.images>div.image span.set-active>label.checkbox:hover {color:#3b5998;}
div.images>div.image span.set-active>label.checkbox:hover>input[type=checkbox]+span {border-color:#3b5998!important;}
div.images>div.image span.set-active>label.checkbox:hover>input[type=checkbox]+span::before {background:#3b5998!important;}
div.short-name section, div.long-name section {width:100%;}
div.short-name span, div.long-name span {margin:0 5px 5px 0; padding:0 2px; color:#000000; font:12.8px/20px 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; display:inline-block;}
div.short-name span.active, div.short-name span:hover, div.long-name span.active, div.long-name span:hover {background:#cccccc; cursor:pointer;}
div.web-search>form.web-search {width:100%; margin:0 auto; background:#ffffff; cursor:default;}
div.web-search>form.web-search input[type=text] {width:100%; height:30px; margin:0 0 10px; padding:0 5px; color:#242424; background:#cceeff; font:16px/30px 'Open Sans', sans-serif; border:1px solid #99ddff; border-radius:3px; box-sizing:border-box;}
div.web-search>form.web-search input[type=text]:focus {background:#ffffff; outline:none;}
div.web-search>form.web-search input[type=button] {width:32.6%; margin:0 1.1% 10px 0; padding:0; float:left; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; text-align:center; border:1px solid #cccccc!important; border-radius:3px; box-sizing:border-box!important;}
div.web-search>form.web-search input[name=search_icecat] {margin:0 0 10px 0;}
div.web-search>form.web-search input[type=button]:hover, div.web-search>form.web-search input[type=button]:focus {background:#cccccc; outline:none; cursor:pointer;}
form.web-search input[type=button]::-moz-focus-inner {border:0;}
div.web-search p {width:100%; margin:0 0 10px; color:#ffffff; background:#ff6666; font:16px/24px 'Open Sans', sans-serif; text-align:center; clear:both; white-space:pre-line;}
div.web-search>section div.column {width:20%; float:left;}
div.web-search>section div.column div {position:relative; float:left;}
div.column div>img {width:100%; float:left; border:2px solid #ffffff; box-sizing:border-box;}
div.column div>img:hover {border-color:#3399ff;}
div.column div>span {width:calc(100% - 4px); position:absolute; left:2px; text-align:center; display:none;}
div.column div>span.download {bottom:22px; color:#ffffff; background:#3399ff; font:bold 16px/26px 'Open Sans', sans-serif; text-transform:uppercase; cursor:pointer;}
div.column div>span.image-dimension {bottom:2px; color:#ffffff; background:rgba(0, 0, 0, 0.6); font:bold 14.4px/20px 'Open Sans', sans-serif; cursor:default;}
div.crop-image>input[type=button] {margin:0 5px 0 0; padding:0 10px; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; text-align:center; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:inline-block;}
div.crop-image>input[name=cancel] {color:#ffffff; background:#ff6666; border-color:#ff3333;}
div.crop-image>input[type=button]:hover, div.crop-image>span>input[type=button]:hover {background:#cccccc; cursor:pointer;}
div.crop-image>input[name=cancel]:hover {background:#ff3333;}
div.crop-image>span {margin:0 5px 0 0; background:#cccccc; border:1px solid #cccccc; border-radius:3px; box-sizing:border-box; display:inline-block;}
div.crop-image>span>span {padding:0 10px; color:#333333; font:bold 14px/30px 'Open Sans', sans-serif; text-align:left; cursor:default;}
div.crop-image>span>input[type=button] {margin:0 0 0 1px; padding:0 10px; float:right; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; text-align:center; border:0; border-radius:0; box-sizing:border-box;}
div.crop-image>div.container {width:690px; height:690px; margin:10px auto 0; position:relative; background:#ffffff; display:block;}
div.crop-image>div.container>div {display:none;}
div.crop-image>div.container>div.top-left {width:50%; height:40px; position:absolute; top:0; left:0;}
div.crop-image>div.container>div.top-right {width:50%; height:40px; position:absolute; top:0; right:0;}
div.crop-image>div.container>div.bottom-left {width:50%; height:40px; position:absolute; bottom:0; left:0;}
div.crop-image>div.container>div.bottom-right {width:50%; height:40px; position:absolute; bottom:0; right:0;}
div.crop-image>div.container>div.right {width:40px; height:100%; position:absolute; top:0; right:0;}
div.crop-image>div.container>div.left {width:40px; height:100%; position:absolute; top:0; left:0;}
div.crop-image>div.container>div>input[type=button], div.crop-image>div.container>div>span {position:absolute; color:#333333; background:#f0f0f0; font:bold 14px/30px 'Open Sans', sans-serif; border:1px solid #cccccc; border-radius:3px; display:block;}
div.crop-image>div.container>div.top-left>input[type=button] {padding:0 10px; top:0; right:5px;}
div.crop-image>div.container>div.top-right>input[type=button] {padding:0 10px; top:0; left:5px;}
div.crop-image>div.container>div.bottom-left>input[type=button] {padding:0 10px; bottom:0; right:5px;}
div.crop-image>div.container>div.bottom-right>input[type=button] {padding:0 10px; bottom:0; left:5px;}
div.crop-image>div.container>div.right>span {height:95px; margin:auto; padding:10px 0; top:0; bottom:0; right:0; text-align:center; text-orientation:upright; writing-mode:vertical-lr;}
div.crop-image>div.container>div.left>span {height:75px; margin:auto; padding:10px 0; top:0; bottom:0; left:0; text-align:center; text-orientation:upright; writing-mode:vertical-lr;}
div.crop-image>div.container>div>input[type=button]:hover, div.crop-image>div.container>div>span:hover {background:#cccccc; cursor:pointer;}
div.crop-image>div.container>div#zoom-crop-container {width:600px; height:600px; position:absolute; top:40px; left:40px; background:#ffffff; border:1px solid #cccccc!important; display:block;}
div#zoom-crop-container.border-90 {box-shadow:0 0 0 29px #ffffff inset, 0 0 0 30px #cccccc inset!important;}
div#zoom-crop-container.border-80 {box-shadow:0 0 0 59px #ffffff inset, 0 0 0 60px #cccccc inset!important;}
div#zoom-crop-container.border-70 {box-shadow:0 0 0 89px #ffffff inset, 0 0 0 90px #cccccc inset!important;}
div#zoom-crop-container.border-50 {box-shadow:0 0 0 149px #ffffff inset, 0 0 0 150px #cccccc inset!important;}
div#zoom-crop-container_selector {display:none;}
span.ui-slider-handle {width:18px!important; height:18px!important; left:-7px!important;}
div.background {width:100%; height:100%; position:absolute; top:0; left:0; background:rgba(0, 0, 0, 0.6); display:none; cursor:default; z-index:10000;}
div.background>div.loading {width:50px; height:50px; margin:auto; position:absolute; top:0; right:0; bottom:0; left:0; background:transparent; border:3px solid #ffffff; border-top:3px solid #ff6666!important; border-radius:50%; box-sizing:border-box; animation:rotate 1s linear infinite;}
@keyframes rotate {from {transform:rotate(0deg);} to {transform:rotate(360deg);}}
div.product-images>div.foreground {width:100%; height:100%; position:fixed; background:rgba(0, 0, 0, 0.6); box-sizing:border-box; display:none; overflow-y:auto; cursor:default; z-index:10001;}
div.product-images>div.foreground>div.container {position:absolute;}
div.product-images>div.foreground>div.container>h1 {width:100%; position:absolute; top:0; color:#ffffff; background:#ff6666; font:14px/20px sans-serif; text-align:center;}
div.product-images>div.foreground>div.container>img {width:auto; height:auto; margin:auto; position:absolute; top:20px; right:0; left:0; display:block; cursor:pointer;}
div.product-images>div.foreground>div.sizes-types {padding:0 10px 10px; position:absolute; top:0; left:0;}
div.product-images>div.foreground>div.sizes-types>span.container {width:160px; margin:10px 0 0; background:#6699cc; text-align:center; border:5px solid #6699cc; border-radius:3px; display:block;}
div.product-images>div.foreground>div.sizes-types>span.container>span.size {width:100%; color:#ffffff; font:bold 18px/36px 'Open Sans', sans-serif; display:block;}
div.product-images>div.foreground>div.sizes-types>span.container>span.type {width:80px; color:#242424; font:bold 15px/30px 'Open Sans', sans-serif; display:inline-block;}
div.product-images>div.foreground>div.sizes-types>span.container>span.type:nth-of-type(2) {background:#f0f0f0;}
div.product-images>div.foreground>div.sizes-types>span.container>span.type:nth-of-type(3) {background:#cccccc;}
div.product-images>div.foreground>div.sizes-types>span.container>span.wide {width:160px;}
div.product-images>div.foreground>div.sizes-types>span.container>span.type:hover {color:#ffffff; background:#66bb66; cursor:pointer;}
div.product-images>div.foreground>div.sizes-types>span.container>span.active {color:#ffffff; background:#66bb66!important;}
div.display-none {display:none!important;}
@media only screen and (min-width:1680px)
{
div.view-gallery div.images {width:20%;}
}
</style>
<div class="product-images">
	<div class="background">
		<div class="loading"></div>
	</div>
	<div class="foreground">
		<div class="sizes-types"></div>
		<div class="container">
			<h1>&lt;Esc&gt; или кликни върху картинката за да затворите</h1>
			<img>
		</div>
	</div>
	<div class="top-content">
		<h1>
			<span>
				<span class="message"></span>
				<span class="close" title="Затвори">&times;</span>
			</span>
			<strong></strong>
		</h1>
		<input type="button" name="close" value="&times;" title="Затвори">
	</div>
	<section>
		<div class="left-content">
			<div class="background">
				<div class="loading"></div>
			</div>
			<div class="view-gallery">
				<div class="settings">
					<h1>Опции</h1>
					<form enctype="multipart/form-data">
						<input type="file" name="product_image[]" multiple>
						<input type="button" name="upload_image" value="качване" title="Повече от едно изображение">
						<input type="button" name="reload_images" value="презареди картинките">
						<input type="button" name="to_product" value="към продукта">
						<?php if (in_array($this->session->userdata('uid'), [405])): ?>
						<input type="button" name="stats" value="размери">
						<input type="button" name="previous_product" value="&laquo; предишен" data-previous-product="<?= $previous_product_id ?>">
						<input type="button" name="next_product" value="следващ &raquo;" data-next-product="<?= $next_product_id ?>">
						<?php endif; ?>
					</form>
				</div>
				<div class="active-image">
					<h1>Активни</h1>
					<section id="default-image"></section>
					<section id="active-images"></section>
				</div>
				<div class="other-image">
					<h1>Неактивни</h1>
					<section></section>
				</div>
			</div>
			<!-- END VIEW GALLERY -->
		</div>
		<!-- END LEFT CONTENT -->
		<div class="right-content">
			<div class="background">
				<div class="loading"></div>
			</div>
			<div class="web-search">
				<div class="short-name">
					<h1 title="Търси по Кратко Име">Кратко Име &#9733;</h1>
					<section></section>
				</div>
				<div class="long-name">
					<h1 title="Търси по Цяло Име">Цяло Име &#9733;</h1>
					<section></section>
				</div>
				<div class="part-number">
					<h1 title="Търси по Парт Номер">Парт Номер &#9733;</h1>
					<section></section>
				</div>
				<form class="web-search">
					<input type="text" name="web_search">
					<input type="button" name="search_bing" value="Bing (текст)">
					<input type="button" name="search_yandex" value="Yandex (текст / линк)">
					<input type="button" name="search_icecat" value="Icecat (автоматично)">
				</form>
				<p></p>
				<section>
					<div class="column js-1"></div>
					<div class="column js-2"></div>
					<div class="column js-3"></div>
					<div class="column js-4"></div>
					<div class="column js-5"></div>
				</section>
			</div>
			<!-- END WEB SEARCH -->
			<div class="crop-image display-none">
				<h1>Редакция</h1>
				<input type="button" name="crop_image" value="">
				<input type="button" name="cancel" value="отмени">
				<span>
					<span>позиция</span>
					<input type="button" name="align_horizontal" value="хоризонтална">
					<input type="button" name="align_vertical" value="вертикална">
					<input type="button" name="reset_position" value="изходна">
				</span>
				<span>
					<span>размер</span>
					<input type="button" name="border_50" value="50">
					<input type="button" name="border_70" value="70">
					<input type="button" name="border_80" value="80">
					<input type="button" name="border_90" value="90">
				</span>
				<div class="container">
					<div class="top-left">
						<input type="button" name="y-0" value="отгоре">
					</div>
					<div class="top-right">
						<input type="button" name="z-0" value="отпред">
					</div>
					<div class="right">
						<span name="x-100">дясно</span>
					</div>
					<div class="bottom-left">
						<input type="button" name="y-100" value="отдолу">
					</div>
					<div class="bottom-right">
						<input type="button" name="z-100" value="отзад">
					</div>
					<div class="left">
						<span name="x-0">ляво</span>
					</div>
					<div id="zoom-crop-container"></div>
				</div>
			</div>
			<!-- END CROP IMAGE -->
		</div>
		<!-- END RIGHT CONTENT -->
	</section>
</div>
<script>
var jQ = $.noConflict(true);
var product_images = {
	strings : {
		set_default : 'основна',
		set_active : 'активна',
		edit : 'редакция',
		edit_backup : 'редакция от BACKUP',
		download : 'сваляне',
		search : 'търсене',
		crop : 'отрязване',
		remove : 'изтриване',
		remove_all_inactive : 'изтриване на всички неактивни картинки',
		missing_images : 'няма картинки от този тип',
		missing_product_id : 'изберете вкаран продукт',
	},
	product_id : <?= $product_id ?: 0 ?>,
	crop : null,
	crop_image_id : 0,
	json : [],
	is_json_ready : false,
	is_window_opened : false,
	step_between_siblings : true,
	preview : {
		image_id : 0,
		image_size : [],
		image_type : '',
		scale_dimensions : [40, 87, 150, 250, 350, 680, 1000].reverse()
	},
	construct : function()
	{
		let that = this;

		jQ('body').on('click', 'input[type=button][name=view_gallery]', function()
		{
			that.init();
		});

		if (window.location.pathname.includes('/product_images/index/'))
		{
			that.init();
		}

		jQ('div.top-content>input[name=close]').on('click', function()
		{
			that.is_window_opened = false;

			that.setMessage('success error', '');

			jQ('div.product-images').fadeOut();
			jQ('body').css('overflow-y', 'scroll');
		});

		jQ('div.product-images>div.top-content>h1>span>span.close').on('click', function()
		{
			jQ(this).parent().removeClass('success error');
		});

		jQ('form>input[type=button][name=upload_image]').on('click', function()
		{
			jQ('form>input[type=file][name="product_image[]"]').trigger('click');
		});

		jQ('form>input[type=file][name="product_image[]"]').on('change', function()
		{
			that.setLeftLoader();

			let form_data = new FormData(),
				product_images = jQ('form>input[type=file][name="product_image[]"]').prop('files');

			for (let i = 0; i < product_images.length; i++)
			{
				form_data.append(`product_image_${i}`, product_images[i]);
			}

			form_data.append('product_id', that.product_id);

			jQ.post({
				url : '/product_images/upload_image',
				dataType : 'json',
				data : form_data,
				cache : false,
				contentType : false,
				processData : false,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				},
				error : function()
				{
					that.getProductImages('error', arguments[2]);
				}
			});
		});

		jQ('form>input[type=button][name=reload_images]').on('click', function()
		{
			that.getProductImages('success error', '');
		});

		jQ('form>input[type=button][name=to_product]').on('click', function()
		{
			window.open(`https://www.jarcomputers.com/p_${that.json.product_info.code}`, '_blank');
		});

		jQ('form>input[type=button][name=stats]').on('click', function()
		{
			window.open(`/product_images/stats/${that.product_id}`, '_blank');
		});

		jQ('form>input[type=button][name=previous_product]').on('click', function()
		{
			if (that.step_between_siblings)
			{
				window.location.href = `/product_images/index/${--that.product_id}`;
			}
			else
			{
				window.location.href = `/product_images/index/${jQ(this).data('previous-product')}`;
			}
		});

		jQ('form>input[type=button][name=next_product]').on('click', function()
		{
			if (that.step_between_siblings)
			{
				window.location.href = `/product_images/index/${++that.product_id}`;
			}
			else
			{
				window.location.href = `/product_images/index/${jQ(this).data('next-product')}`;
			}
		});

		jQ('body').on('click', 'span.search', function()
		{
			let url = that.json.product_images[jQ(this).parent().data('image-id')].file_name;

			jQ('div.right-content>div.web-search').removeClass('display-none');
			jQ('div.right-content>div.crop-image').addClass('display-none');

			jQ('input[name=web_search]').val(url);
			jQ('input[type=button][name=search_yandex]').trigger('click');
		});

		jQ('body').on('click', 'div.images>div.image span.remove-all, div.images>div.image span.remove', function()
		{
			that.setLeftLoader();
			that.setMessage('success error', '');

			let image_id = jQ(this).parent().data('image-id');

			if (image_id == that.crop_image_id)
			{
				jQ('input[name=cancel]', 'div.crop-image').trigger('click');
			}

			jQ.getJSON({
				url : `/product_images/remove_product_image/${image_id}`,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.setMessage('error', response.error);
					}
				}
			});
		});

		jQ('body').on('click', 'input[name=set_default]', function()
		{
			that.setLeftLoader();
			that.setMessage('success error', '');

			let image_id = jQ(this).data('image-id');

			jQ('div.view-gallery>div.active-image>section#active-images input[name=set_default]').prop('checked', false);
			jQ(this).prop('checked', true);

			jQ.getJSON({
				url : `/product_images/set_default_image/${image_id}`,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				}
			});
		});

		jQ('body').on('click', 'input[name=set_active]', function()
		{
			that.setLeftLoader();

			let image_id = jQ(this).data('image-id'),
				is_active = Number(jQ(this).prop('checked'));

			jQ.getJSON({
				url : `/product_images/toggle_active_image/${image_id}/${is_active}`,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				}
			});
		});

		jQ('body').on('click', 'div.images>div.image span.crop', function()
		{
			jQ('div.product-images>div.background').show();

			jQ.post({
				url : '/product_images/set_crop_image',
				dataType : 'json',
				data : {image_id : jQ(this).data('image-id')},
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				}
			});
		});

		jQ('body').on('click', 'div.images>div.image span.image-info>b', function()
		{
			let image_id = jQ(this).parent().parent().data('image-id');

			jQ.getJSON({
				url : `/product_images/get_all_image_sizes/${image_id}`,
				success : function(response)
				{
					if (response.data)
					{
						window.open(`/product_images/stats/${response.data}`, '_blank');
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				}
			});
		});

		jQ('body').on('click', 'div.images>div.image span.image-info>a', function()
		{
			that.preview.image_id = jQ(this).parent().parent().data('image-id');
			that.preview.image_size = [that.json.product_images[that.preview.image_id].image.width, that.json.product_images[that.preview.image_id].image.height];
			that.preview.image_type = 'jpg';

			that.setPreviewImage();
		});

		jQ('body').on('click', 'div.foreground>div.sizes-types>span.container>span.type', function()
		{
			that.preview.image_size = [jQ(this).data('size'), jQ(this).data('size')];
			that.preview.image_type = jQ(this).data('type');

			that.setPreviewImage();
		});

		jQ('div.foreground>div.container>img').on('click', function()
		{
			jQ('div.product-images>div.foreground').fadeOut();
		});

		jQ('body').on('click', 'div.images>div.image span.edit, div.images>div.image span.edit-backup', function()
		{
			that.crop_image_id = jQ(this).parent().data('image-id');

			that.setStagingImage();
		});

		jQ('body').on('click', 'div.image>span.download', function()
		{
			let url = jQ(this).parent().find('img').attr('src');

			window.location.href = `/product_images/download?url=${url}`;
		});

		jQ('body').on('click', 'section>input[name=remove_all_inactive]', function()
		{
			that.setLeftLoader();
			that.setMessage('success error', '');

			jQ('input[name=cancel]', 'div.crop-image').trigger('click');

			jQ.getJSON({
				url : `/product_images/remove_inactive_images_by_product/${that.product_id}`,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						that.getProductImages('error', response.error);
					}
				}
			});
		});

		jQ('body').on('mouseenter', 'section>div.images>div.image', function()
		{
			if (!jQ(this).find('img').attr('alt').includes('http'))
			{
				jQ(this).find('img').attr('src', '');
			}
		});

		jQ('body').on('mouseleave', 'section>div.images>div.image', function()
		{
			jQ(this).find('img').attr('src', jQ(this).find('img').data('src'));
		});

		jQ('body').on('mouseenter', 'section>div.images>div.image.inner>img', function()
		{
			let offset = jQ(this).parent().offset(),
				top = offset.top,
				left = offset.left,
				object = {};

			if (top < 402)
			{
				object.top = '-1px';
			}
			else
			{
				object.top = '-352px';
			}

			if (left > 450)
			{
				object.right = '-1px';
			}
			else
			{
				object.left = '-1px';
			}

			if (jQ(this).parent().find('div.preview').length)
			{
				jQ(this).parent().find('div.preview').css(object).show();
			}
			else
			{
				let img = new Image();

				img.src = jQ(this).data('src');

				let width = 350,
					height = 350;

				if (img.width > img.height)
				{
					height = parseInt((img.height * width) / img.width);
				}

				if (img.width < img.height)
				{
					width = parseInt((img.width * height) / img.height);
				}

				jQ(this).parent().append(`<div class="preview"><img src="${jQ(this).data('src')}" width="${width}" height="${height}"></div>`);

				jQ(this).parent().find('div.preview').css(object).show();
			}

			jQ(this).on('mouseleave', function()
			{
				jQ('div.preview').hide();
			});
		});

		jQ('body').on('mouseenter', 'div.preview', function()
		{
			jQ(this).show();

			jQ(this).on('mouseleave', function()
			{
				jQ('div.preview').hide();
			});
		});

		jQ('input[name=crop_image]', 'div.crop-image').on('click', function()
		{
			jQ('div.product-images>div.background').show();

			that.crop.send('/product_images/set_crop_image', 'POST', {image_id : that.crop_image_id}, function(response)
			{
				if (response.hasOwnProperty('success'))
				{
					that.getProductImages('success', response.success);
				}

				if (response.hasOwnProperty('error'))
				{
					that.getProductImages('error', response.error);
				}

				jQ('div.right-content>div.web-search').removeClass('display-none');
				jQ('div.right-content>div.crop-image').addClass('display-none');
			});
		});

		jQ('input[name=cancel]', 'div.crop-image').on('click', function()
		{
			that.crop_image_id = 0;

			jQ('div.right-content>div.web-search').removeClass('display-none');
			jQ('div.right-content>div.crop-image').addClass('display-none');
		});

		jQ('input[name=reset_position]', 'div.crop-image').on('click', function()
		{
			jQ('div#zoom-crop-container').removeClass();

			that.setStagingImage();
		});

		jQ('input[name=align_horizontal]', 'div.crop-image').on('click', function()
		{
			let outer_width = jQ('div#zoom-crop-container').width(),
				inner_width = jQ('div#zoom-crop-container img.ui-draggable').width(),
				image_data = jQ('div#zoom-crop-container').data('image'),
				position_left = (outer_width - inner_width) / 2;

			image_data.posX = position_left;

			jQ('div#zoom-crop-container').data('image', image_data);
			jQ('div#zoom-crop-container img.ui-draggable').css('left', `${position_left}px`);
		});

		jQ('input[name=align_vertical]', 'div.crop-image').on('click', function()
		{
			let outer_height = jQ('div#zoom-crop-container').height(),
				inner_height = jQ('div#zoom-crop-container img.ui-draggable').height(),
				image_data = jQ('div#zoom-crop-container').data('image'),
				position_top = (outer_height - inner_height) / 2;

			image_data.posY = position_top;

			jQ('div#zoom-crop-container').data('image', image_data);
			jQ('div#zoom-crop-container img.ui-draggable').css('top', `${position_top}px`);
		});

		jQ('input[name^=border_]', 'div.crop-image').on('click', function()
		{
			let img = new Image();

			img.src = jQ('div#zoom-crop-container img.ui-draggable').attr('src');

			let size = jQ(this).val(),
				image_container = 600 * (parseInt(size, 10) / 100),
				position_top = (600 - image_container) / 2,
				position_left = (600 - image_container) / 2,
				image_width = 0,
				image_height = 0,
				image_data = jQ('div#zoom-crop-container').data('image');

			if (img.width > img.height)
			{
				image_width = image_container;
				image_height = (image_container * img.height) / img.width;
			}
			else if (img.width < img.height)
			{
				image_height = image_container;
				image_width = (image_container * img.width) / img.height;
			}
			else
			{
				image_width = image_container;
				image_height = image_container;
			}

			image_data.w = image_width;
			image_data.h = image_height;
			image_data.posY = position_top;
			image_data.posX = position_left;
			image_data.scaleX = image_width / img.width;
			image_data.scaleY = image_height / img.height;

			jQ('div#zoom-crop-container img.ui-draggable').css({'width' : `${image_width}px`, 'height' : `${image_height}px`, 'top' : `${position_top}px`, 'left' : `${position_left}px`});

			jQ('div#zoom-crop-container').removeClass().addClass(`border-${size}`);
		});

		jQ('body').on('click', 'div.short-name h1, div.long-name h1', function()
		{
			let parent_class = jQ(this).parent().attr('class'),
				product_name = (parent_class === 'short-name') ? that.json.product_info.short_name : that.json.product_info.ename;

			jQ(`div[class$='name'] section span`).removeClass('active');
			jQ('input[name=web_search]').val(product_name);
		});

		jQ('body').on('click', 'div.part-number h1', function()
		{
			jQ(`div[class$='name'] section span`).removeClass('active');
			jQ('input[name=web_search]').val(that.json.product_info.producer_code);
		});

		jQ('body').on('click', 'div.short-name section span, div.long-name section span', function()
		{
			let current_class = jQ(this).parent().parent().attr('class'),
				reset_buttons = (current_class === 'short-name') ? 'long-name' : 'short-name';

			jQ(`div.${reset_buttons} section span`).removeClass('active');

			if (jQ(this).hasClass('active'))
			{
				jQ(this).removeClass('active');
			}
			else
			{
				jQ(this).addClass('active');
			}

			let web_search = [];

			jQ(`div.${current_class} section span`).each(function()
			{
				if (jQ(this).hasClass('active'))
				{
					web_search.push(jQ(this).text());
				}
			});

			jQ('input[name=web_search]').val(web_search.join(' '));
		});

		jQ('input[type=button][name^=search]').on('click', function()
		{
			that.resetWebSearch();

			let object = {
				keywords : jQ('input[name=web_search]').val()
			};

			let url = '';

			switch (jQ(this).attr('name'))
			{
				case 'search_bing':
					url = 'get_bing_search';
				break;

				case 'search_yandex':
					url = 'get_yandex_search';
				break;

				case 'search_icecat':
					url = 'get_icecat_search';
					object.keywords = that.product_id;
				break;
			}

			jQ.post({
				url : `/product_images/${url}`,
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						let counter = 0,
							image_counter = 0,
							no_size_images = {},
							images = {};

						for (let i in response.data)
						{
							++counter;
							++image_counter;

							if (counter === 6)
							{
								counter = 1;
							}

							if (!images.hasOwnProperty(counter))
							{
								images[counter] = [];
							}

							let image = '';

							if (response.data[i].size === '')
							{
								image = `
								<div>
								<img src="${response.data[i].thumb}" title="${response.data[i].title}">
								<span data-url="${response.data[i].image}" class="download">${that.strings.download}</span>
								<span class="image-dimension" id="js-size-${image_counter}"></span>
								</div>
								`;

								no_size_images[image_counter] = response.data[i].image;
							}
							else
							{
								image = `
								<div>
								<img src="${response.data[i].thumb}" title="${response.data[i].title}">
								<span data-url="${response.data[i].image}" class="download">${that.strings.download}</span>
								<span class="image-dimension">${response.data[i].size}</span>
								</div>
								`;
							}

							images[counter].push(image);
						}

						for (let i in images)
						{
							jQ(`div.column.js-${i}`).html(images[i].join(''));
						}

						that.getImageSize(no_size_images);

						setTimeout(function()
						{
							jQ('div.column div>span.image-dimension').css('display', 'block');
						}, 800);
					}

					if (response.hasOwnProperty('error'))
					{
						jQ('div.web-search p').html(response.error);
					}
				}
			});
		});

		jQ('body').on('mouseenter', 'div.column div', function()
		{
			jQ(this).find('span.download').css('display', 'block');
		});

		jQ('body').on('mouseleave', 'div.column div', function()
		{
			jQ(this).find('span.download').hide();
		});

		jQ('body').on('click', 'div.column div>span.download', function()
		{
			that.setRightLoader();

			jQ('div.web-search p').text('');

			let div = jQ(this).parent(),
				object = {
					product_id : that.product_id,
					url : jQ(this).data('url')
				};

			jQ.post({
				url : '/product_images/save_image_from_url',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						that.getProductImages('success', response.success);
					}

					if (response.hasOwnProperty('error'))
					{
						jQ('div.web-search p').text(response.error);
						that.removeLoader();
					}

					div.remove();
				}
			});
		});
	},
	setMessage : function(css_class, message)
	{
		if (message.length)
		{
			jQ('div.product-images>div.top-content>h1>span').addClass(css_class).find('span.message').text(message);
		}
		else
		{
			jQ('div.product-images>div.top-content>h1>span').removeClass(css_class).find('span.message').text(message);
		}
	},
	init : function()
	{
		if (this.product_id <= 0)
		{
			alert(this.strings.missing_product_id);

			return;
		}

		this.is_window_opened = true;

		jQ('body').css('overflow-y', 'hidden');
		jQ('div.product-images, div.product-images>div.background').show();

		this.getProductData();

		let set_interval = setInterval(function()
		{
			if (product_images.is_json_ready)
			{
				clearInterval(set_interval);

				product_images.setProductImages();
				product_images.setWebSearch();
				product_images.removeLoader();
			}
		}, 100);
	},
	getProductData : function()
	{
		this.is_json_ready = false;

		jQ.getJSON({
			url : `/product_images/get_data/${this.product_id}`,
			success : function(response)
			{
				product_images.json = response.data;
				product_images.is_json_ready = true;
			}
		});
	},
	setProductImages : function()
	{
		this.setDefaultImage('');
		this.setActiveImages('');
		this.setOtherImages('');

		let product_name = this.json.product_info.ename,
			product_code = this.json.product_info.code;

		jQ('div.product-images>div.top-content>h1>strong').html(`${product_name} (<b>${product_code}</b>)`).attr('title', `${product_name}\n\n\n${product_code}`);

		if (!this.json.product_info.id)
		{
			jQ('div.product-images>div.top-content>h1>strong').addClass('old-product');
		}

		let products = this.json.product_images,
			staging = [];

		for (let i in products)
		{
			staging.push([Number(i), Number(products[i].sorder)]);
		}

		staging.sort(function(a, b)
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

		let active_images = '',
			other_images = '',
			file_version = new Date().getTime(),
			remove_all_inactive_counter = 0;

		for (let s of staging)
		{
			let id = s[0],
				object = products[id],
				top_link = '',
				alt = '',
				set_image_status = '',
				size = '',
				set_image_options = '';

			if (!!Number(object.approved))
			{
				top_link = `<span class="remove">${this.strings.remove}</span>`;

				if (object.image.border === 'outer')
				{
					top_link = `
					<span class="search">${this.strings.search}</span>
					<span class="remove-all">${this.strings.remove}</span>
					`;
				}
			}
			else
			{
				top_link = `
				<span class="search">${this.strings.search}</span>
				<span class="remove-all">${this.strings.remove}</span>
				`;

				if (object.image.border === 'inner')
				{
					top_link = `<span class="remove">${this.strings.remove}</span>`;
				}
			}

			if (object.image.hasOwnProperty('error'))
			{
				top_link = `
				<span class="search">${this.strings.search}</span>
				<span class="remove-all">${this.strings.remove}</span>
				`;

				alt = object.image.error;

				if (object.image.hasOwnProperty('backup'))
				{
					set_image_options = `<span class="edit-backup">${this.strings.edit_backup}</span>`;
				}
			}
			else
			{
				alt = object.file_name;

				if (Number(object.status_id) === 2)
				{
					top_link = `<span class="search">${this.strings.search}</span>`;
					set_image_status = `<span class="default-image">${this.strings.set_default}</span>`;
				}

				if (Number(object.status_id) === 1)
				{
					set_image_status = `
					<span class="set-default">
					<label class="checkbox"><input type="checkbox" name="set_default" data-image-id="${id}"><span></span>${this.strings.set_default}</label>
					</span>
					<span class="set-active">
					<label class="checkbox"><input type="checkbox" name="set_active" data-image-id="${id}" checked><span></span>${this.strings.set_active}</label>
					</span>
					`;
				}

				if (Number(object.status_id) === 0)
				{
					if (object.image.hasOwnProperty('status'))
					{
						set_image_status = `
						<span class="set-default">
						<label class="checkbox"><input type="checkbox" name="set_default" data-image-id="${id}"><span></span>${this.strings.set_default}</label>
						</span>
						<span class="set-active">
						<label class="checkbox"><input type="checkbox" name="set_active" data-image-id="${id}"><span></span>${this.strings.set_active}</label>
						</span>
						`;
					}

					if (object.image.hasOwnProperty('crop'))
					{
						set_image_status = `<span data-image-id="${id}" class="crop">${this.strings.crop} (${object.image.crop} x ${object.image.crop})</span>`;
					}
				}

				let highlight = '';

				if ((![680, 1000].includes(object.image.width) || ![680, 1000].includes(object.image.height) || object.image.width != object.image.height) && object.status_id != 0)
				{
					highlight = ' class="highlight"';
				}

				size = `
				<span class="image-info">
				<b>${object.image.size}</b>
				<a title="${object.file_name}"${highlight}>${object.image.width} x ${object.image.height}</a>
				</span>
				`;
				set_image_options = `<span class="edit">${this.strings.edit}</span><span class="download">${this.strings.download}</span>`;
			}

			switch (Number(object.status_id))
			{
				case 2:
					let default_image = `
					<div class="images">
					<div data-image-id="${id}" class="image ${object.image.border}">
					${top_link}
					<img src="${object.file_name}?v=${file_version}" alt="${alt}" data-src="${object.file_name}?v=${file_version}">
					${set_image_status}
					${size}
					${set_image_options}
					</div>
					</div>
					`;

					this.setDefaultImage(default_image);
				break;

				case 1:
					active_images += `
					<div class="images" data-sorder="${object.sorder}">
					<div data-image-id="${id}" class="image ${object.image.border}">
					${top_link}
					<img src="${object.file_name}?v=${file_version}" alt="${alt}" data-src="${object.file_name}?v=${file_version}">
					${set_image_status}
					${size}
					${set_image_options}
					</div>
					</div>
					`;
				break;

				case 0:
					++remove_all_inactive_counter;

					other_images += `
					<div class="images">
					<div data-image-id="${id}" class="image ${object.image.border}">
					${top_link}
					<img src="${object.file_name}?v=${file_version}" alt="${alt}" data-src="${object.file_name}?v=${file_version}">
					${set_image_status}
					${size}
					${set_image_options}
					</div>
					</div>
					`;
				break;
			}
		}

		if (remove_all_inactive_counter > 1)
		{
			other_images = `
			<input type="button" name="remove_all_inactive" value="${this.strings.remove_all_inactive}">
			${other_images}
			`;
		}

		other_images = other_images.length ? other_images : `<p>${this.strings.missing_images}</p>`;

		this.setActiveImages(active_images);
		this.setOtherImages(other_images);
	},
	setDefaultImage : function(content)
	{
		jQ('div.view-gallery>div.active-image>section#default-image').html(content);
	},
	setActiveImages : function(content)
	{
		jQ('div.view-gallery>div.active-image>section#active-images').html(content);

		setTimeout(function()
		{
			let sort_array = [],
				position = 0;

			jQ('div.view-gallery>div.active-image>section#active-images').sortable({
				containment : 'div.view-gallery>div.active-image',
				tolerance : 'pointer',
				delay : 10,
				start : function()
				{
					jQ('div.view-gallery>div.active-image>section#active-images>div.images').removeClass('js-sortable');

					let ui = arguments[1];

					ui.item.addClass('js-sortable');

					sort_array = [];
					position = 0;

					sort_array[0] = ui.item.data('sorder');

					jQ('div.view-gallery>div.active-image>section#active-images>div.images').each(function()
					{
						++position;

						if (jQ(this).hasClass('js-sortable'))
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
						product_images.setLeftLoader();

						let object = {
							old_sort_id : sort_array[0],
							new_sort_id : sort_array[1],
							product_id : product_images.product_id
						};

						jQ.post({
							url : '/product_images/sort_product_image',
							dataType : 'json',
							data : object,
							success : function(response)
							{
								if (response.hasOwnProperty('success'))
								{
									product_images.getProductImages('success', response.success);
								}

								if (response.hasOwnProperty('error'))
								{
									product_images.setMessage('error', response.error);
								}

								product_images.removeLoader();
							}
						});
					}
				}
			}).disableSelection();
		}, 200);
	},
	setOtherImages : function(content)
	{
		jQ('div.view-gallery>div.other-image>section').html(content);
	},
	setWebSearch : function()
	{
		let short_name = this.json.product_info.short_name,
			full_name = this.json.product_info.ename;

		jQ('input[name=web_search]').val(short_name);

		if (short_name.length)
		{
			jQ('div.short-name section').html(this.getSearchWords(short_name));
		}

		if (full_name.length)
		{
			jQ('div.long-name section').html(this.getSearchWords(full_name));
		}

		this.resetWebSearch();
	},
	resetWebSearch : function()
	{
		jQ('div.web-search p').text('');
		jQ('div.web-search section div.column').html('');
	},
	getSearchWords : function(string)
	{
		let sanitised = string.replace(/,/g, ''),
			keywords = sanitised.split(' '),
			words = '';

		for (let i of keywords)
		{
			if (i.length)
			{
				words += `<span>${i}</span>`;
			}
		}

		return words;
	},
	getProductImages : function()
	{
		this.setLeftLoader();
		this.getProductData();

		let is_set_message = false,
			css_class = '',
			message = '';

		if (arguments[0] && arguments[1])
		{
			is_set_message = true;
			css_class = arguments[0];
			message = arguments[1];
		}

		let set_interval = setInterval(function()
		{
			if (product_images.is_json_ready)
			{
				clearInterval(set_interval);

				product_images.setProductImages();
				product_images.removeLoader();

				if (is_set_message)
				{
					product_images.setMessage(css_class, message);
				}

				if (typeof getProductImages === 'function')
				{
					getProductImages(product_images.product_id);
				}
			}
		}, 200);
	},
	getWebSearch : function()
	{
		this.setRightLoader();
		this.getProductData();

		let set_interval = setInterval(function()
		{
			if (product_images.is_json_ready)
			{
				clearInterval(set_interval);

				product_images.setWebSearch();
				product_images.removeLoader();
			}
		}, 200);
	},
	setStagingImage : function()
	{
		this.setRightLoader();

		jQ('div.right-content>div.web-search').addClass('display-none');
		jQ('div.right-content>div.crop-image').removeClass('display-none');
		jQ('input[name=crop_image]', 'div.crop-image').val(`${this.strings.crop} (1000 x 1000)`);

		jQ.getJSON({
			url : `/product_images/set_staging_image/${this.crop_image_id}`,
			success : function(response)
			{
				if (response.hasOwnProperty('file'))
				{
					if (response.width < 1000 && response.height < 1000)
					{
						jQ('input[name=crop_image]', 'div.crop-image').val(`${product_images.strings.crop} (680 x 680)`);
					}

					product_images.crop = jQ('div#zoom-crop-container').cropzoom({
						width : 600,
						height : 600,
						bgColor : 'white',
						enableRotation : true,
						enableZoom : true,
						zoomSteps : 1,
						rotationSteps : 5,
						image : {
							source : response.file,
							width : response.width,
							height : response.height,
							minZoom : 20,
							maxZoom : 200
						}
					});
				}

				if (response.hasOwnProperty('error'))
				{
					product_images.getProductImages();

					jQ('div.right-content>div.web-search').removeClass('display-none');
					jQ('div.right-content>div.crop-image').addClass('display-none');
					alert(response.error);
				}

				product_images.removeLoader();
			},
			error : function(response)
			{
				jQ('div.right-content>div.web-search').removeClass('display-none');
				jQ('div.right-content>div.crop-image').addClass('display-none');
				alert(response.statusText);

				product_images.removeLoader();
			}
		});
	},
	setLeftLoader : function()
	{
		jQ('div.left-content').animate({scrollTop : 0}, 100);
		jQ('div.left-content>div.background').show();

		this.setMessage('success error', '');
	},
	setRightLoader : function()
	{
		jQ('div.right-content').animate({scrollTop : 0}, 100);
		jQ('div.right-content>div.background').show();

		this.setMessage('success error', '');
	},
	removeLoader : function()
	{
		jQ('div.background').fadeOut();
	},
	getImageSize : function(no_size_images)
	{
		if (Object.values(no_size_images).length)
		{
			for (let i in no_size_images)
			{
				let img = new Image();

				img.src = no_size_images[i];

				img.onload = function()
				{
					jQ(`span#js-size-${i}`).text(`${img.width} x ${img.height}`);
				};
			}
		}
	},
	setPreviewImage : function()
	{
		jQ('div.foreground>div.sizes-types').html('');

		let image_object = this.json.product_images[this.preview.image_id],
			url = image_object.file_name;

		if (!!Number(image_object.approved))
		{
			let sizes_types = '',
				scale = this.preview.scale_dimensions,
				is_webp_added = !!Number(image_object.descriptive_name.length);

			if (this.preview.image_type === 'webp')
			{
				url = image_object.descriptive_name;
			}

			url = url.replace(/([0-9]+x[0-9]+)/g, `${this.preview.image_size[0]}x${this.preview.image_size[1]}`);

			for (let i of scale)
			{
				let current_class = this.preview.image_size.includes(i) ? ' active' : '',
					image_type = `<span data-size="${i}" data-type="jpg" class="type wide${current_class}">JPG</span>`;

				if (is_webp_added)
				{
					if (this.preview.image_type === 'webp')
					{
						image_type = `<span data-size="${i}" data-type="jpg" class="type">JPG</span><span data-size="${i}" data-type="webp" class="type${current_class}">WEBP</span>`;
					}
					else
					{
						image_type = `<span data-size="${i}" data-type="jpg" class="type${current_class}">JPG</span><span data-size="${i}" data-type="webp" class="type">WEBP</span>`;
					}
				}

				sizes_types += `
				<span class="container">
				<span class="size">${i}x${i}</span>
				${image_type}
				</span>
				`;
			}

			if (this.preview.image_size[0] < 680)
			{
				this.preview.image_size = [430, 370];

				jQ('div.product-images>div.foreground>div.container>img').css({'top' : 0, 'bottom' : 0});
			}
			else
			{
				jQ('div.product-images>div.foreground>div.container>img').css({'top' : '20px'});
			}

			jQ('div.foreground>div.sizes-types').html(sizes_types);
		}

		let file_version = new Date().getTime(),
			width_size = Number(this.preview.image_size[0]),
			height_size = Number(this.preview.image_size[1]) + 20,
			top = (Number(jQ('div.product-images').height()) > height_size) ? (Number(jQ('div.product-images').height()) - height_size) / 2 : 0,
			left = (Number(jQ('div.product-images').width()) > width_size) ? (Number(jQ('div.product-images').width()) - width_size) / 2 : 0;

		jQ('div.foreground>div.container').css({'width' : `${width_size}px`, 'height' : `${height_size}px`, 'top' : `${top}px`, 'left' : `${left}px`});
		jQ('div.foreground>div.container>img').attr('src', `${url}?v=${file_version}`);
		jQ('div.product-images>div.foreground').fadeIn();
	},
	keydown : function()
	{
		jQ(document).on('keydown', function(event)
		{
			product_images.step_between_siblings = !event.shiftKey;

			if (event.which === 13 && product_images.is_window_opened)
			{
				return false;
			}

			if (event.which === 27 && product_images.is_window_opened)
			{
				if (jQ('div.product-images>div.foreground').css('display') === 'block')
				{
					jQ('div.product-images>div.foreground').fadeOut();

					return false;
				}

				jQ('div.top-content>input[name=close]').trigger('click');
			}

			if (event.which === 37 && product_images.is_window_opened)
			{
				jQ('form>input[type=button][name=previous_product]').trigger('click');
			}

			if (event.which === 39 && product_images.is_window_opened)
			{
				jQ('form>input[type=button][name=next_product]').trigger('click');
			}
		});
	}()
};
jQ(product_images.construct());
</script>