<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 11/03/2022
# [MODIFIED] 07/07/2022
final class Documents_model extends J_Model
{
	public function getDataImages($folder)
	{
		$query = "
		SELECT file_path, invoice_number, document_id, ARRAY_LENGTH(document_id, 1) AS id_count, '' AS client, 'P' AS document_type
		FROM sys.tesseract_file
		WHERE file_path LIKE '%/Printer/{$folder}/%' AND invoice_number != 0 AND document_type_id = 3
		UNION
		SELECT te.file_path, te.invoice_number, te.document_id, ARRAY_LENGTH(te.document_id, 1), COALESCE(cl.\"Име\", ''), 'C'
		FROM sys.tesseract_file AS te
		LEFT JOIN public.\"Клиенти\" AS cl ON te.supplier_id = cl.\"Клиент ID\"
		WHERE te.file_path LIKE '%/Printer/{$folder}/%' AND te.invoice_number != 0 AND te.document_type_id = 1
		";

		$result = $this->db->query($query)->result_array();

		$data_images = [];

		foreach ($result as $row)
		{
			$data_images[str_replace('/var/jardir', '', $row['file_path'])] = [
				'type' => $row['document_type'],
				'id' => ($row['id_count'] == 1 ? str_replace(['{', '}'], '', $row['document_id']) : ''),
				'invoice' => $row['invoice_number'],
				'client' => $row['client']
			];
		}

		return $data_images;
	}

	public function getLogs($is_active_employee, $interval = [])
	{
		$where_active = '';

		if ($is_active_employee === 1)
		{
			$where_active = 'WHERE s."Active" IS TRUE AND s."Position ID" IS NOT NULL';
		}

		if ($is_active_employee === 0)
		{
			$where_active = 'WHERE s."Active" IS FALSE OR s."Position ID" IS NULL';
		}

		$where_interval = '';

		if (!empty($interval))
		{
			$where_interval = '
			WHERE
				date_posted BETWEEN ' . $interval['from_date'] . ' AND ' . $interval['to_date'];
		}

		$query = '
		SELECT
			s."Име",
			CASE
				WHEN s."Active" IS FALSE OR s."Position ID" IS NULL THEN 0
				ELSE 1
			END AS active,
			l_s_d.*
		FROM
			public."Съдружници" AS s
		JOIN
			(
				SELECT
					user_id, action_type, COUNT(action_type) AS count_action_type
				FROM
					public.log_scanned_documents
				' . $where_interval . '
				GROUP BY
					user_id, action_type
			) AS l_s_d ON s."Съдружник ID" = l_s_d.user_id
		' . $where_active . '
		ORDER BY
			s."Име" ASC
		';

		$result = $this->db->query($query)->result_array();

		$logs = [];

		foreach ($result as $row)
		{
			if (array_key_exists($row['user_id'], $logs))
			{
				$logs[$row['user_id']]['types'][$row['action_type']] = $row['count_action_type'];
				$logs[$row['user_id']]['total'] += $row['count_action_type'];
			}
			else
			{
				$logs[$row['user_id']] = [
					'name' => $row['Име'],
					'active' => intval($row['active']),
					'types' => [
						$row['action_type'] => $row['count_action_type']
					],
					'total' => $row['count_action_type']
				];
			}
		}

		return $logs;
	}

	public function getPurchaseDocumentsByID($data)
	{
		switch ($data['type'])
		{
			case 'purchase_id':
				$where_clause = 'pu."Покупка ID" = ' . $data['id'];
			break;

			case 'invoice_id':
				$where_clause = 'pu."Фактура ID" = ' . $data['id'];
			break;

			case 'warranty_id':
				$where_clause = 'pu."ГК"::varchar LIKE \'%' . $data['id'] . '%\'';
			break;

			case 'serial_number':
				return $this->getPurchaseDocumentsBySerialNumber($data['id']);
			break;
		}

		$query = '
		SELECT
			pu."Покупка ID" AS pid, pu."Клиент ID" AS provider_id,
			pu."Фактура ID" AS piid, COALESCE(pu."FДата", NOW()::timestamp(0)) AS pidate,
			pu."ГК" AS warr, COALESCE(pu."WДата", NOW()::timestamp(0)) AS pwdate,
			pu.parent_id,
			ROUND(SUM((COALESCE(st."сДДС", FALSE)::integer * 0.2 + 1) * st."DM" * (COALESCE(sn."Статус ID", 0) > 0)::integer), 2) AS psum,
			-ROUND(SUM(COALESCE(SUBSTRING(SUBSTRING(pu."Забележка" FROM 0 FOR POSITION(\'лв\' IN pu."Забележка") + 2) FROM \'\| (.+)лв\')::numeric, 0)), 2) AS credit_sum,
			cl."Име" AS pclient
		FROM public."Покупки" AS pu
		JOIN public."Клиенти" AS cl USING ("Клиент ID")
		LEFT JOIN public."Стоки" AS st USING ("Покупка ID")
		LEFT JOIN public."SN" AS sn USING ("Стока ID")
		WHERE ' . $where_clause . '
		GROUP BY pu."Покупка ID", pu."Клиент ID", pu."Фактура ID", pu."FДата", pu."ГК", pu."WДата", pu.parent_id, cl."Име"
		ORDER BY pu."Покупка ID" DESC
		';

		return $this->db->query($query)->result_array();
	}

	public function getPurchaseDocumentsBySerialNumber($serial_numner)
	{
		$query = '
		SELECT DISTINCT
			pu."Покупка ID" AS pid, pu."Клиент ID" AS provider_id, pu."Фактура ID" AS piid, pu."ГК" AS warr, pu."FДата" AS pidate, pu."WДата" AS pwdate,
			cl."Име" AS pclient
		FROM
			public."Покупки" AS pu
		JOIN
			public."Клиенти" AS cl ON pu."Клиент ID" = cl."Клиент ID"
		JOIN
			public."Стоки" AS st ON pu."Покупка ID" = st."Покупка ID"
		JOIN
			public."SN" AS sn ON st."Стока ID" = sn."Стока ID"
		WHERE
			LOWER(sn."Сериен номер") LIKE LOWER(\'%' . str_replace("'", "''", $serial_numner) . '%\') AND pu."Дата" > (NOW()::timestamp(0)::date - INTERVAL \'6 MONTH\')::date
		ORDER BY
			pu."Покупка ID" DESC
		';

		$result = $this->db->query($query)->result_array();

		return $result;
	}

	public function setLogScannedDocuments($action_type)
	{
		$data = [
			'user_id' => $this->session->uid,
			'action_type' => $action_type,
			'date_posted' => time()
		];

		$this->db->insert('public.log_scanned_documents', $data);
	}

	public function setScannedValue($document_type, $purchase_number)
	{
		$set_clause = ($document_type === 'I') ? 'scanned_invoice' : 'scanned_warranty';

		$query = '
		UPDATE
			public."Покупки"
		SET
			' . $set_clause . ' = 1
		WHERE
			"Покупка ID" = ' . $purchase_number;

		$this->db->query($query);
	}

	public function getPurchaseByDocumentsID($pid, $type, $type_id)
	{
		$this->db->where('pid', $pid);
		$this->db->where($type, $type_id);
		$this->db->order_by('pdate', 'DESC');

		return $this->db->get('vw_documents_delivery')->result_array();
	}

	public function getCostData($data)
	{
		switch ($data['type'])
		{
			case 'cost_id':
				$where_clause = 'co."ID" = ' . $data['id'];
			break;

			case 'invoice_id':
				$where_clause = 'co."Фактура ID" = ' . $data['id'];
			break;

			case 'client':
				$where_clause = 'LOWER(cl."Име") LIKE LOWER(\'%' . str_replace("'", "''", $data['id']) . '%\')';
			break;
		}

		$query = '
		SELECT
			query.*,
			(
				SELECT
					COUNT("ID") AS repeat_invoice_id
				FROM
					public."Разходи"
				WHERE
					"Фактура ID" = query.invoice_id AND "Клиент ID" = query.client_id
			)
		FROM
			(
				SELECT
					co."ID" AS cost_id, co."Фактура ID" AS invoice_id, co."FДата"::date AS invoice_date, co."Клиент ID" AS client_id,
					ROUND(co."ЛВ" + co."DM" * co."ЛВ to DM" + co."Сума" * co."ЛВ to _", 2) AS invoice_sum,
					COALESCE(
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = co."FКлиент ID"),
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = cl."ИмеФактури ID"),
						cl."Име"
					) AS client
				FROM
					public."Разходи" AS co
				LEFT JOIN
					public."Клиенти" AS cl USING ("Клиент ID")
				WHERE
					' . $where_clause . '
				ORDER BY
					co."FДата" DESC
			) AS query
		ORDER BY
			query.cost_id DESC
		';

		$result = $this->db->query($query)->result_array();

		return $result;
	}

	public function getCostDateByID($cost_number, $invoice_number)
	{
		$query = '
		SELECT
			"FДата"::date
		FROM
			public."Разходи"
		WHERE
			"ID" = ' . $cost_number . ' AND "Фактура ID" = ' . $invoice_number;

		$row = $this->db->query($query)->row_array();

		return $row['FДата'];
	}

	public function setCostScannedInvoice($cost_number)
	{
		$query = '
		UPDATE
			public."Разходи"
		SET
			scanned_invoice = 1
		WHERE
			"ID" = ' . $cost_number;

		$this->db->query($query);
	}

	public function getProfitData($data)
	{
		switch ($data['type'])
		{
			case 'profit_id':
				$where_clause = 'pr."ID" = ' . $data['id'];
			break;

			case 'invoice_id':
				$where_clause = 'pr."Фактура ID" = ' . $data['id'];
			break;

			case 'client':
				$where_clause = 'LOWER(cl."Име") LIKE LOWER(\'%' . str_replace("'", "''", $data['id']) . '%\')';
			break;
		}

		$query = '
		SELECT
			query.*,
			(
				SELECT
					COUNT("ID") AS repeat_invoice_id
				FROM
					public."Приходи"
				WHERE
					"Фактура ID" = query.invoice_id AND "Клиент ID" = query.client_id
			)
		FROM
			(
				SELECT
					pr."ID" AS profit_id, pr."Фактура ID" AS invoice_id, pr."FДата"::date AS invoice_date, pr."Клиент ID" AS client_id,
					ROUND(pr."ЛВ" + pr."DM" * pr."ЛВ to DM" + pr."Сума" * pr."ЛВ to _", 2) AS invoice_sum,
					COALESCE(
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = pr."FКлиент ID"),
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = cl."ИмеФактури ID"),
						cl."Име"
					) AS client
				FROM
					public."Приходи" AS pr
				LEFT JOIN
					public."Клиенти" AS cl USING ("Клиент ID")
				WHERE
					' . $where_clause . '
				ORDER BY
					pr."FДата" DESC
			) AS query
		ORDER BY
			query.profit_id DESC
		';

		$result = $this->db->query($query)->result_array();

		return $result;
	}

	public function getProfitDateByID($profit_number, $invoice_number)
	{
		$query = '
		SELECT
			"FДата"::date
		FROM
			public."Приходи"
		WHERE
			"ID" = ' . $profit_number . ' AND "Фактура ID" = ' . $invoice_number;

		$row = $this->db->query($query)->row_array();

		return $row['FДата'];
	}

	public function setProfitScannedInvoice($profit_number)
	{
		$query = '
		UPDATE
			public."Приходи"
		SET
			scanned_invoice = 1
		WHERE
			"ID" = ' . $profit_number;

		$this->db->query($query);
	}

	public function getPayrollData($data)
	{
		switch ($data['type'])
		{
			case 'payroll_id':
				$where_clause = 'ch."ID" = ' . $data['id'];
			break;

			case 'invoice_id':
				$where_clause = 'ch."Фактура ID" = ' . $data['id'];
			break;

			case 'client':
				$where_clause = 'LOWER(cl."Име") LIKE LOWER(\'%' . str_replace("'", "''", $data['id']) . '%\')';
			break;
		}

		$query = '
		SELECT
			query.*,
			(
				SELECT
					COUNT("ID") AS repeat_invoice_id
				FROM
					public."Обмяна валута"
				WHERE
					"Фактура ID" = query.invoice_id AND "Клиент ID" = query.client_id
			)
		FROM
			(
				SELECT
					ch."ID" AS payroll_id, ch."Фактура ID" AS invoice_id, ch."FДата"::date AS invoice_date, ch."Клиент ID" AS client_id,
					ROUND(ch."От ЛВ" + ch."От DM" + ch."От _", 2) AS from_sum,
					ROUND(ch."В ЛВ" + ch."В DM" + ch."В _", 2) AS to_sum,
					CASE
						WHEN ch."От ЛВ" > 0 AND ch."В ЛВ" > 0 THEN \'BGN\'
						WHEN ch."От DM" > 0 AND ch."В DM" > 0 THEN \'EUR\'
						WHEN ch."От _" > 0 AND ch."В _" > 0 THEN \'USD\'
						ELSE \'MIX\'
					END AS currency,
					COALESCE(
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = ch."FКлиент ID"),
						(SELECT "Име" FROM public."FКлиенти" WHERE "Клиент ID" = cl."ИмеФактури ID"),
						cl."Име"
					) AS client
				FROM
					public."Обмяна валута" AS ch
				LEFT JOIN
					public."Клиенти" AS cl USING ("Клиент ID")
				WHERE
					' . $where_clause . '
			) AS query
		ORDER BY
			query.payroll_id DESC
		';

		$result = $this->db->query($query)->result_array();

		return $result;
	}

	public function getPayrollDateByID($payroll_number, $invoice_number)
	{
		$query = '
		SELECT
			"FДата"::date
		FROM
			public."Обмяна валута"
		WHERE
			"ID" = ' . $payroll_number . ' AND "Фактура ID" = ' . $invoice_number;

		$row = $this->db->query($query)->row_array();

		return $row['FДата'];
	}

	public function setPayrollScannedInvoice($payroll_number)
	{
		$query = '
		UPDATE
			public."Обмяна валута"
		SET
			scanned_invoice = 1
		WHERE
			"ID" = ' . $payroll_number;

		$this->db->query($query);
	}

	public function getOtherData($data)
	{
		$request = [];

		switch ($data['type'])
		{
			case 'purchase_id':
				$result = $this->getPurchaseDocumentsByID($data);

				if ($result)
				{
					$request = [
						'other_number' => $data['id'],
						'invoice_number' => $result[0]['piid'] ?: '',
						'invoice_date' => $result[0]['pidate'] ? substr($result[0]['pidate'], 0, 10) : '',
						'provider_id' => $result[0]['provider_id'] ?: '',
						'provider' => $result[0]['pclient'] ?: '',
						'invoice_sum' => $result[0]['psum'] ?: $result[0]['credit_sum'] . ' (КИ)'
					];
				}
			break;

			case 'sale_id':
				$this->load->model('spravcho_model');

				$result = $this->spravcho_model->_getSale($data['id'], 1);

				if ($result)
				{
					$request = [
						'other_number' => $data['id'],
						'invoice_number' => $result['data']['data']['Фактура ID'] ?: str_repeat(0, 10),
						'invoice_date' => substr($result['data']['data']['Дата'], 0, 10),
						'client_id' => $result['data']['data']['cid'] ?: '',
						'client' => $result['data']['data']['Клиент'] ?: '',
						'invoice_sum' => $result['data']['data']['Фактурна сума'] ?: ''
					];
				}
			break;
		}

		return $request;
	}

	public function setOtherScannedDocument($data)
	{
		switch ($data['type'])
		{
			case 'purchase_id':
				$query = '
				UPDATE
					public."Покупки"
				SET
					scanned_purchase = 1
				WHERE
					"Покупка ID" = ' . $data['id'];
			break;

			case 'sale_id':
				$query = '
				UPDATE
					public."Продажби"
				SET
					scanned_sale = 1
				WHERE
					"Продажба ID" = ' . $data['id'];
			break;
		}

		$this->db->query($query);
	}

	public function getDataByPurchasesAndInvoice($multiple_purchases, $document_invoice)
	{
		$query = '
		SELECT DISTINCT
			"FДата"
		FROM
			public."Покупки"
		WHERE
			"Покупка ID" IN (' . implode(', ', $multiple_purchases) . ') AND "Фактура ID" = ' . $document_invoice;

		$result = $this->db->query($query)->row_array();

		return $result['FДата'];
	}

	public function updatePurchaseByID($purchase_id, $data)
	{
		$set = [];

		foreach ($data as $key => $value)
		{
			$value = (is_numeric($value)) ? $value : "'" . $value . "'";

			$set[] = '"' . $key . '" = ' . $value;
		}

		$query = '
		UPDATE
			public."Покупки"
		SET
			' . implode(',', $set) . '
		WHERE
			"Покупка ID" = ' . $purchase_id;

		$result = $this->db->query($query);

		return $result;
	}
}
?>