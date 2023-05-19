<div data-tab="bottles" data-object="bottle" class="active">
	<div class="header">
		<input type="button" name="get_excel" value="изтегли excel">
		<input type="button" name="clear_filtering" value="изчисти филтрите">
		<section></section>
	</div>
	<table>
		<caption><span class="js-heading"></span> (<span class="js-count"></span>)</caption>
		<thead>
			<tr>
				<th rowspan="2" data-sort="id">#<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc active">&#9660;</span></th>
				<th rowspan="2" data-sort="purchase_order" class="w-140">Поръчка<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th colspan="3" class="highlight">Палет</th>
				<th colspan="3">Кашон</th>
				<th colspan="3" class="highlight">Бутилка</th>
				<th rowspan="2" data-sort="created">Дата<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
			</tr>
			<tr class="custom-sorting">
				<th data-sort="palette_id" class="highlight">номер<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="palette_c" class="highlight w-80">текущ<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="palette" class="highlight w-190">баркод<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="box_id">номер<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="box_c" class="w-80">текущ<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="box" class="w-190">баркод<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="bottle_type" class="highlight w-80">размер<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="banderol_id" class="highlight">текуща<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="banderol" class="highlight">бандерол<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
			</tr>
			<tr class="filter">
				<th><input type="text" name="id"></th>
				<th>
					<select name="purchase_order">
						<option value="" selected>избери</option>
<?php foreach ($purchase_orders as $id => $order): ?>
						<option value="<?= $id ?>"><?= $order ?></option>
<?php endforeach; ?>
					</select>
					<input type="text" name="purchase_order">
				</th>
				<th><input type="text" name="palette_id"></th>
				<th><input type="text" name="palette_c"></th>
				<th><input type="text" name="palette"></th>
				<th><input type="text" name="box_id"></th>
				<th><input type="text" name="box_c"></th>
				<th><input type="text" name="box"></th>
				<th>
					<select name="bottle_type">
						<option value="" selected>избери</option>
						<option value="750 ml">750 ml</option>
						<option value="1000 ml">1000 ml</option>
					</select>
				</th>
				<th><input type="text" name="banderol_id"></th>
				<th><input type="text" name="banderol"></th>
				<th><input type="text" name="created"></th>
			</tr>
		</thead>
		<tbody id="js-bottle"></tbody>
	</table>
</div>
