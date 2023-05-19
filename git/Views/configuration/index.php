<div data-tab="configurations" data-object="configuration">
	<div class="header">
		<input type="button" name="new_configuration" value="нова стойност">
		<input type="button" name="clear_filtering" value="изчисти филтрите">
		<p></p>
	</div>
	<table class="default">
		<thead>
			<tr>
				<th data-sort="ename">Име<span title="Възходящо" class="asc active">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="val">Стойност<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="note">Забележка<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th data-sort="created">Дата<span title="Възходящо" class="asc">&#9650;</span><span title="Низходящо" class="desc">&#9660;</span></th>
				<th>Опции</th>
			</tr>
			<tr class="filter">
				<th><input type="text" name="ename"></th>
				<th><input type="text" name="val"></th>
				<th><input type="text" name="note"></th>
				<th><input type="text" name="created"></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="js-configuration"></tbody>
	</table>
</div>
