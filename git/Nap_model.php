<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 09/12/2020
# [MODIFIED] 25/01/2021
final class Nap_model extends J_Model
{
	public function getInquiry($start_date, $end_date, $client_name)
	{
		$where_clause = "WHERE doc_date BETWEEN '$start_date' AND '$end_date'";

		if (!empty($client_name))
		{
			$where_clause .= " AND LOWER(ename) LIKE LOWER('%$client_name%')";
		}

		$missing_invoice_query = '
		SELECT
			nap.*
		FROM
		(
			SELECT DISTINCT
				pu."Фактура ID" AS invoice,
				(CASE
					WHEN fcl."ДН" = \'BG121488372\' AND pu."Фактура ID" > 3000000 AND pu."Фактура ID" < 4000000 THEN \'BG831210862\'
					ELSE fcl."ДН"
				END) AS ein
			FROM public."Покупки" AS pu
			LEFT JOIN public."FКлиенти" AS fcl ON pu."FКлиенти ID" = fcl."Клиент ID"
			WHERE pu."FДата"::date >= \'2020-01-01\' AND pu."Фактура ID" IS NOT NULL AND fcl."ДН" IS NOT NULL
			UNION
			SELECT DISTINCT
				co."Фактура ID", fcl."ДН"
			FROM public."Разходи" AS co
			LEFT JOIN public."FКлиенти" AS fcl ON co."FКлиент ID" = fcl."Клиент ID"
			WHERE co."FДата"::date > \'2020-01-01\' AND co."Фактура ID" IS NOT NULL AND fcl."ДН" IS NOT NULL
			UNION
			SELECT DISTINCT
				inc."Фактура ID", fcl."ДН"
			FROM public."Приходи" AS inc
			LEFT JOIN public."FКлиенти" AS fcl ON inc."FКлиент ID" = fcl."Клиент ID"
			WHERE inc."FДата"::date > \'2020-01-01\' AND inc."Фактура ID" IS NOT NULL AND fcl."ДН" IS NOT NULL
		) AS inv
		';

		$wrong_date_query = '
		SELECT
			nap.*, inv.type_name, inv.type_id, inv."FДата" AS inv_date, COALESCE(inv.docdatenum, \'&bullet;\') AS docdatenum, inv.edate
		FROM
		(
			SELECT
				sub.type_name, STRING_AGG(sub.type_id::varchar, \'|\') AS type_id, sub."FДата", sub.invoice, sub.ein, sub.docdatenum, sub.edate
			FROM
			(
				SELECT
					invoices.*, plusmin.docdatenum::varchar, plusmin.edate::varchar
				FROM
				(SELECT
					\'P\' AS type_name, pu."Покупка ID" AS type_id, pu."FДата",
					pu."Фактура ID" AS invoice,
					(CASE
						WHEN fcl."ДН" = \'BG121488372\' AND pu."Фактура ID" > 3000000 AND pu."Фактура ID" < 4000000 THEN \'BG831210862\'
						ELSE fcl."ДН"
					END) AS ein
				FROM public."Покупки" AS pu
				LEFT JOIN public."FКлиенти" AS fcl ON pu."FКлиенти ID" = fcl."Клиент ID"
				WHERE pu."FДата"::date >= \'2020-01-01\' AND fcl."ДН" IS NOT NULL
				GROUP BY pu."Покупка ID", pu."Фактура ID", pu."FДата", fcl."ДН") AS invoices
				LEFT JOIN (SELECT DISTINCT nomer::double precision AS invoice, docdatenum::date, edate FROM plusmin.pokupki WHERE edate >= (\'' . $start_date . '\'::date - INTERVAL \'1 MONTH\') AND status = 1) AS plusmin USING(invoice)
			) AS sub
			GROUP BY sub.type_name, sub."FДата", sub.invoice, sub.ein, sub.docdatenum, sub.edate
			UNION ALL
			SELECT
				sub.type_name, STRING_AGG(sub.type_id::varchar, \'|\') AS type_id, sub."FДата", sub."Фактура ID", sub."ДН", sub.docdatenum, sub.edate
			FROM
			(
				SELECT
					\'C\' AS type_name, co."ID" AS type_id, co."FДата", co."Фактура ID", fcl."ДН", \'\' AS docdatenum, \'\' AS edate
				FROM public."Разходи" AS co
				LEFT JOIN public."FКлиенти" AS fcl ON co."FКлиент ID" = fcl."Клиент ID"
				WHERE co."FДата"::date > \'2020-01-01\' AND fcl."ДН" IS NOT NULL
			) AS sub
			GROUP BY sub.type_name, sub."FДата", sub."Фактура ID", sub."ДН", sub.docdatenum, sub.edate
		) AS inv
		';

		$wrong_sum_query = '
		SELECT
			nap.*, inv.type_name, inv.type_id, inv."FДата" AS inv_date, inv.total AS inv_total, inv.note
		FROM
		(
			SELECT
				sub.type_name, STRING_AGG(sub.type_id::varchar, \'|\') AS type_id, sub."FДата", ROUND(SUM(sub.total), 2) AS total, sub.invoice, sub.ein,
				ARRAY_AGG(sub.note)::varchar AS note
			FROM
			(
				SELECT
					\'P\' AS type_name, pu."Покупка ID" AS type_id, pu."FДата", SUM((st."сДДС"::integer::numeric * 0.2 + 1::numeric) * st."DM" * (sn."Статус ID" > 0)::integer::numeric) AS total,
					pu."Фактура ID" AS invoice,
					(CASE
						WHEN fcl."ДН" = \'BG121488372\' AND pu."Фактура ID" > 3000000 AND pu."Фактура ID" < 4000000 THEN \'BG831210862\'
						ELSE fcl."ДН"
					END) AS ein,
					JSON_BUILD_OBJECT(pu."Покупка ID", pu."Забележка")::varchar AS note
				FROM public."Покупки" AS pu
				JOIN public."Клиенти" AS cl USING("Клиент ID")
				LEFT JOIN public."Стоки" AS st USING("Покупка ID")
				LEFT JOIN public."SN" AS sn USING("Стока ID")
				LEFT JOIN public."FКлиенти" AS fcl ON cl."ИмеФактури ID" = fcl."Клиент ID"
				WHERE pu."FДата"::date >= \'2020-01-01\' AND fcl."ДН" IS NOT NULL
				GROUP BY pu."Покупка ID", pu."Фактура ID", pu."FДата", fcl."ДН"
			) AS sub
			GROUP BY sub.type_name, sub."FДата", sub.invoice, sub.ein
			UNION ALL
			SELECT
				sub.type_name, STRING_AGG(sub.type_id::varchar, \'|\') AS type_id, sub."FДата", ROUND(SUM(sub.total), 2) AS total, sub."Фактура ID", sub."ДН", \'\'
			FROM
			(
				SELECT
					\'C\' AS type_name, co."ID" AS type_id, co."FДата", ("Сума" * "ЛВ to _" + "DM" * "ЛВ to DM" + co."ЛВ") AS total, co."Фактура ID", fcl."ДН"
				FROM public."Разходи" AS co
				LEFT JOIN public."FКлиенти" AS fcl ON co."FКлиент ID" = fcl."Клиент ID"
				WHERE co."FДата"::date > \'2020-01-01\' AND fcl."ДН" IS NOT NULL
			) AS sub
			GROUP BY sub.type_name, sub."FДата", sub."Фактура ID", sub."ДН"
		) AS inv
		';

		$tax_base = "(tax_base_20::numeric + tax_base_9::numeric + tax_base_0::numeric + tax_base_free::numeric) AS all_tax_base";
		$tax_vat = "(vat_20::numeric + vat_9::numeric) AS all_tax_vat";

		$missing_invoice = "
		RIGHT JOIN (SELECT *,
		(CASE
			WHEN ein = 'BG204341093' AND doc_number < 10000 THEN EXTRACT(YEAR FROM doc_date)::varchar || doc_number::varchar
			ELSE doc_number::varchar
		END)::numeric AS _doc_number, $tax_base, $tax_vat FROM temp.nap_invoices $where_clause) AS nap ON inv.ein = nap.ein AND inv.invoice = nap._doc_number
		WHERE inv.invoice IS NULL
		";

		$wrong_date = "
		JOIN (SELECT *,
		(CASE
			WHEN ein = 'BG204341093' AND doc_number < 10000 THEN EXTRACT(YEAR FROM doc_date)::varchar || doc_number::varchar
			ELSE doc_number::varchar
		END)::numeric AS _doc_number FROM temp.nap_invoices $where_clause) AS nap ON inv.ein = nap.ein AND inv.invoice = nap._doc_number AND inv.\"FДата\" != nap.doc_date
		";

		$wrong_sum = "
		JOIN
		(
			SELECT
				plusmin.*, nap_period.*, nap_total.total
			FROM (SELECT doc_type, ein, (tax_base_20::numeric + tax_base_9::numeric + tax_base_0::numeric + tax_base_free::numeric + vat_20::numeric + vat_9::numeric) AS total,
			(CASE
				WHEN ein = 'BG204341093' AND doc_number < 10000 THEN EXTRACT(YEAR FROM doc_date)::varchar || doc_number::varchar
				ELSE doc_number::varchar
			END)::numeric AS _doc_number FROM temp.nap_invoices $where_clause) AS nap_total
			JOIN (SELECT *, $tax_base, $tax_vat,
			(CASE
				WHEN ein = 'BG204341093' AND doc_number < 10000 THEN EXTRACT(YEAR FROM doc_date)::varchar || doc_number::varchar
				ELSE doc_number::varchar
			END)::numeric AS doc_number,
			(CASE
				WHEN ein = 'BG204341093' AND doc_number < 10000 THEN EXTRACT(YEAR FROM doc_date)::varchar || doc_number::varchar
				ELSE doc_number::varchar
			END)::numeric AS _doc_number FROM temp.nap_invoices $where_clause) AS nap_period USING(ein, _doc_number, doc_type)
			LEFT JOIN
			(SELECT
				plusmin.nomer, plusmin.status,
				JSON_AGG(JSON_BUILD_OBJECT('name', plusmin.partner, 'payed', plusmin.payed, 'unpayed', plusmin.unpayed)) AS invoice_data
				FROM
				(
				SELECT
					nomer::double precision, partner, status,
					COALESCE(ROUND(SUM(plateno), 2), 0) AS payed,
					COALESCE(ROUND(SUM(neplateno), 2), 0) AS unpayed
				FROM
					plusmin.pokupki
				WHERE
					edate >= ('$start_date'::date - INTERVAL '1 MONTH')
				GROUP BY
					nomer, partner, status
				) AS plusmin
				GROUP BY
			plusmin.nomer, plusmin.status) AS plusmin ON nap_period._doc_number = plusmin.nomer
		) AS nap
		ON inv.ein = nap.ein AND inv.invoice = nap._doc_number AND ABS(inv.total - nap.total) > 0.01
		";

		$order_by = "ORDER BY nap.ein, nap.doc_date, SIGN(nap.tax_base::numeric) DESC";

		$missing_invoice_data = $this->db->query($missing_invoice_query . $missing_invoice . $order_by)->result_array();
		$wrong_date_data = $this->db->query($wrong_date_query . $wrong_date . $order_by)->result_array();
		$wrong_sum_data = $this->db->query($wrong_sum_query . $wrong_sum . $order_by)->result_array();

		foreach ($missing_invoice_data as $key => &$value)
		{
			$value['row'] = strval($key + 1);
		}

		foreach ($wrong_date_data as $key => &$value)
		{
			$value['row'] = strval($key + 1);
		}

		foreach ($wrong_sum_data as $key => &$value)
		{
			$value['row'] = strval($key + 1);
		}

		$inquiry = [
			'missing_invoices' => $missing_invoice_data,
			'wrong_date' => $wrong_date_data,
			'wrong_sum' => $wrong_sum_data
		];

		return $inquiry;
	}

	public function getClientName($client_name)
	{
		$query = "
		SELECT DISTINCT
			ename
		FROM
			temp.nap_invoices
		WHERE
			LOWER(ename) LIKE LOWER('%$client_name%')
		";

		$result = $this->db->query($query)->result_array();

		$clients = [];

		foreach ($result as $row)
		{
			$clients[$row['ename']] = $row['ename'];
		}

		return $clients;
	}

	public function setImport($data)
	{
		$counter = 0;
		$insert_values = [];

		$total = count($data) - 1;

		foreach ($data as $key => $value)
		{
			++$counter;

			$insert_values[] = "('{$value['ein']}', '{$value['ename']}', '{$value['tax_period']}', {$value['doc_type']}, {$value['doc_number']}, '{$value['doc_date']}'::date, '{$value['goods_type']}', '{$value['tax_base']}', '{$value['all_vat']}', '{$value['tax_base_20']}', '{$value['vat_20']}', '{$value['tax_base_9']}', '{$value['vat_9']}', '{$value['tax_base_0']}', '{$value['tax_base_free']}', '{$value['tax_base_163']}')";

			if ($counter === 250 || $key === $total)
			{
				$insert_values = implode(',' . PHP_EOL, $insert_values);

				$query = "
				INSERT INTO
					temp.nap_invoices(ein, ename, tax_period, doc_type, doc_number, doc_date, goods_type, tax_base, all_vat, tax_base_20, vat_20, tax_base_9, vat_9, tax_base_0, tax_base_free, tax_base_163)
				SELECT
					*
				FROM
				(
					VALUES
					$insert_values
				) AS nap (ein, ename, tax_period, doc_type, doc_number, doc_date, goods_type, tax_base, all_vat, tax_base_20, vat_20, tax_base_9, vat_9, tax_base_0, tax_base_free, tax_base_163)
				WHERE
					NOT EXISTS (SELECT 1 FROM temp.nap_invoices WHERE ein = nap.ein AND ename = nap.ename AND doc_number = nap.doc_number)
				";

				$this->db->query($query);

				$counter = 0;
				$insert_values = [];
			}
		}

		return true;
	}

	public function getNotes($start_date, $end_date)
	{
		$query = "
		SELECT
			id, note
		FROM
			temp.nap_invoices
		WHERE
			doc_date BETWEEN '$start_date' AND '$end_date'
		";

		$notes = [];

		$result = $this->db->query($query)->result_array();

		foreach ($result as $row)
		{
			$notes[$row['id']] = $row['note'];
		}

		return $notes;
	}

	public function setNote($id, $note)
	{
		$this->db->where('id', $id);

		$result = $this->db->update('temp.nap_invoices', ['note' => $note]);

		return $result;
	}

	public function unsetNote($id)
	{
		$this->db->where('id', $id);

		$result = $this->db->update('temp.nap_invoices', ['note' => null]);

		return $result;
	}
}
?>