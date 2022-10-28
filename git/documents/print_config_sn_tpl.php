<style>
@font-face {font-family:'3OF9'; src:url('/css/3OF9.TTF'), url('/css/3OF9.eot');}
html, body, table, thead, tbody, tfoot, tr, td, span {margin:0; padding:0; border:0; vertical-align:baseline;}
table {width:100%; border-collapse:separate; border-spacing:0; cursor:default;}
table tr td {width:25%; height:60px;}
table tr.filler td {height:55px;}
table tr td span {width:100%; color:#000000; text-align:center!important; box-sizing:border-box; display:block;}
table tr td span.sn {font:1.3em/30px arial, sans-serif!important;}
table tr td span.barcode {font:2.4em/30px '3OF9'!important;}
table tr td span.sn._1 {padding:0 0 0 16px!important; text-align:left!important;}
table tr td span.barcode._1 {padding:0 0 0 16px!important; text-align:left!important;}
table tr td span.sn._2 {padding:0 2px 0 0!important;}
table tr td span.barcode._2 {padding:0 2px 0 0!important;}
table tr td span.sn._3 {padding:0 0 0 0!important;}
table tr td span.barcode._3 {padding:0 0 0 0!important;}
table tr td span.sn._4 {padding:0 16px 0 0!important; text-align:right!important;}
table tr td span.barcode._4 {padding:0 16px 0 0!important; text-align:right!important;}
table tr td span.first-row {margin-top:4px!important;}
table tr:first-child td span.sn {line-height:40px!important;}
table tr:first-child td span.barcode {line-height:20px!important;}
<?php if ($grid === 14): ?>
table tr.filler td {height:20px;}
<?php endif; ?>
@media print {
table {page-break-after:always;}
}
</style>
<?php foreach ($data as $page): ?>
<table>
<?php
for ($i = 0; $i < count($page); $i += 2):
$margin_top = ($i === 0) ? ' first-row' : '';
?>
	<tr>
		<td>
			<span class="sn _1<?= $margin_top ?>">SN: <?= $page[$i] ?></span>
			<span class="barcode _1">*<?= $page[$i] ?>*</span>
		</td>
		<td>
			<span class="sn _2<?= $margin_top ?>">SN: <?= $page[$i] ?></span>
			<span class="barcode _2">*<?= $page[$i] ?>*</span>
		</td>
<?php if (isset($page[$i + 1])): ?>
		<td>
			<span class="sn _3<?= $margin_top ?>">SN: <?= $page[$i + 1] ?></span>
			<span class="barcode _3">*<?= $page[$i + 1] ?>*</span>
		</td>
		<td>
			<span class="sn _4<?= $margin_top ?>">SN: <?= $page[$i + 1] ?></span>
			<span class="barcode _4">*<?= $page[$i + 1] ?>*</span>
		</td>
<?php endif; ?>
	</tr>
<?php if (count($page) - $i > 2): ?>
	<tr class="filler">
		<td colspan="4"></td>
	</tr>
<?php
endif;
endfor;
?>
</table>
<?php endforeach; ?>