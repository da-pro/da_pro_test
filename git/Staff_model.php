<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 20/08/2019
# [MODIFIED] 02/12/2019
final class Staff_model extends J_Model
{
	public function getStaff()
	{
		$query = '
		SELECT
			st.*,
			COALESCE(st.email, \'\') AS email,
			COALESCE(st."МобиленТелефон", \'\') AS "МобиленТелефон",
			COALESCE(st."Отпуск", 0) AS "Отпуск",
			COALESCE(po."PositionName", \'\') AS first_position,
			cl."Име" AS jar_client,
			st."Active"::int
		FROM
			public."Съдружници" AS st
		LEFT JOIN
			public."Position" AS po ON st."Position ID" = po."Position ID"
		LEFT JOIN
			public."Клиенти" AS cl ON st."Клиент ID" = cl."Клиент ID"
		ORDER BY
			st."Съдружник ID" ASC
		';

		$result = $this->db->query($query)->result_array();

		$staff = [];

		foreach ($result as $row)
		{
			$id = $row['Съдружник ID'];

			unset($row['Съдружник ID']);

			$row['image'] = '/img/users/' . slug($row['Име']) . '.bmp';
			$row['gender'] = preg_match('/а{1}$/', $row['Име']) ? 'female' : 'male';

			$staff[$id] = $row;
		}

		return $staff;
	}

	public function getPosition()
	{
		$query = '
		SELECT
			po1."Position ID" AS id, po1."PositionName" AS name,
			po2."PositionName" AS parent_name
		FROM
			public."Position" po1
		JOIN
			public."Position" po2 ON (100 * (po1."Position ID" / 100)) = po2."Position ID"
		ORDER BY
			po2."PositionName" ASC, po1."PositionName" ASC
		';

		$result = $this->db->query($query)->result_array();

		$position = [];

		foreach ($result as $row)
		{
			if ($row['name'] === $row['parent_name'])
			{
				$position[$row['parent_name']] = [];
			}
		}

		foreach ($result as $row)
		{
			if ($row['name'] !== $row['parent_name'])
			{
				$position[$row['parent_name']][$row['id']] = $row['name'];
			}
		}

		return $position;
	}

	public function getHRSource()
	{
		$query = '
		SELECT
			id, source_name
		FROM
			public.hr_attract_sources
		ORDER BY
			source_name ASC
		';

		$result = $this->db->query($query)->result_array();

		$source = [];

		foreach ($result as $row)
		{
			$source[$row['id']] = $row['source_name'];
		}

		return $source;
	}

	public function getSalaryType()
	{
		$query = '
		SELECT
			*
		FROM
			public.salary_type
		ORDER BY
			id ASC
		';

		$result = $this->db->query($query)->result_array();

		$salary_type = [];

		foreach ($result as $row)
		{
			$salary_type[$row['id']] = $row['ename'];
		}

		return $salary_type;
	}

	public function getEmployeeBankStatus()
	{
		$query = '
		SELECT
			*
		FROM
			public.user_bank_status
		ORDER BY
			id ASC
		';

		$result = $this->db->query($query)->result_array();

		$status = [];

		foreach ($result as $row)
		{
			$status[$row['id']] = $row['sname'];
		}

		return $status;
	}

	public function setEmployee($id, $data)
	{
		$this->db->set('"Position ID"', $data['Position ID'], false);
		$this->db->set('"Position2 ID"', $data['Position2 ID'], false);
		$this->db->set('"Клиент ID"', $data['Клиент ID'], false);
		$this->db->set('last_change', 'NOW()::timestamp(0)', false);

		unset($data['Position ID'], $data['Position2 ID'], $data['Клиент ID']);

		$employee_id = $id;

		if (is_null($id))
		{
			$query = 'SELECT (MAX("Съдружник ID") + 1)::smallint AS id FROM public."Съдружници" WHERE "Съдружник ID" < 1000';

			$result = $this->db->query($query)->row_array();

			$employee_id = intval($result['id']);
		}

		if (is_null($id))
		{
			$this->db->set('"Съдружник ID"', $employee_id, false);

			$result = $this->db->insert('public."Съдружници"', $data);
		}
		else
		{
			$this->db->where('"Съдружник ID" = ', $employee_id, false);

			$result = $this->db->update('public."Съдружници"', $data);
		}

		if ($result)
		{
			return $employee_id;
		}
	}

	public function unsetEmployee($id)
	{
		$this->db->set('"Active"', 'FALSE', false);
		$this->db->set('"Position ID"', 'NULL', false);

		$this->db->where('"Съдружник ID" = ', $id, false);

		$result = $this->db->update('public."Съдружници"');

		return $result;
	}

	public function getCardSum()
	{
		$query = '
		SELECT
			user_id, SUM(jsum) AS card
		FROM
			public.user_bank_transaction
		WHERE
			status_id != 0
		GROUP BY
			user_id
		';

		$result = $this->db->query($query)->result_array();

		$card = [];

		foreach ($result as $row)
		{
			$card[$row['user_id']] = $row['card'];
		}

		return $card;
	}

	public function getSalaries()
	{
		$query = '
		SELECT
			st."Съдружник ID",
			COALESCE(st."Клиент ID", 0) AS client_id,
			st."Име",
			st."Сума",
			st."Card",
			st.salary,
			st.salary_type_id,
			po."PositionName",
			s_t.ename AS salary_type,
			ROUND(ob.obligation, 2) AS obligation,
			ROUND(pa.payment, 2) AS payment
		FROM
			public."Съдружници" AS st
		JOIN
			public."Position" AS po USING("Position ID")
		LEFT JOIN
			public.salary_type AS s_t ON st.salary_type_id = s_t.id
		LEFT JOIN
			(SELECT "Клиент ID", SUM(COALESCE("Долари" * "PayRate"::numeric, 0)) AS obligation FROM public."Продажби" WHERE "OK" IS FALSE GROUP BY "Клиент ID") AS ob USING("Клиент ID")
		LEFT JOIN
			(SELECT sa."Клиент ID", SUM(ps."ЛВ" + (ps."DM" * ps."ЛВ to DM") + (ps."Сума" * ps."ЛВ to _")) AS payment FROM public."Продажби" AS sa JOIN "Плащания при продажби" AS ps USING("Продажба ID") WHERE ps."Дата" = CURRENT_DATE GROUP BY sa."Клиент ID") AS pa USING("Клиент ID")
		WHERE
			st."Active" IS TRUE AND st."Position ID" IS NOT NULL
		ORDER BY
			st."Съдружник ID" ASC
		';

		$result = $this->db->query($query)->result_array();

		$salary = [];

		foreach ($result as $row)
		{
			switch ($row['salary_type_id'])
			{
				case 1:
					$active_tab = 'advance';
				break;

				case 2:
					$active_tab = 'iban';
				break;

				default:
					$active_tab = 'iban';

					if (date('d') >= 15 && date('d') <= 25)
					{
						$active_tab = 'advance';
					}
			}

			$salary[$row['Съдружник ID']] = [
				'name' => $row['Име'],
				'position' => $row['PositionName'],
				'suma' => number_format($row['Сума'], 2, '.', ''),
				'card' => number_format($row['Card'], 2, '.', ''),
				'salary' => number_format($row['salary'], 2, '.', ''),
				'obligation' => [
					'client_id' => (is_null($row['obligation']) && is_null($row['payment'])) ? 0 : $row['client_id'],
					'total' => $row['obligation'] ?: 0,
					'payed' => $row['payment'] ?: '&nbsp;&bullet;'
				],
				'salary_type_id' => $row['salary_type_id'],
				'salary_type' => ($row['salary_type'] ? $row['salary_type'] : ''),
				'hanging_cost' => false,
				'active_tab' => 'js-' . $active_tab,
				'image' => '/img/users/' . slug($row['Име']) . '.bmp',
				'iban' => [],
				'advance' => []
			];
		}

		$query = '
		SELECT
			ubt.*,
			st."Име"
		FROM
			public.user_bank_transaction AS ubt
		JOIN
			public."Съдружници" AS st ON ubt.creator_id = st."Съдружник ID"
		ORDER BY
			id ASC
		';

		$result = $this->db->query($query)->result_array();

		foreach ($result as $row)
		{
			if (array_key_exists($row['user_id'], $salary))
			{
				$salary[$row['user_id']]['iban'][$row['id']] = [
					'iban' => $row['iban_r'],
					'bic' => $row['bic_r'],
					'suma' => number_format($row['jsum'], 2, '.', ''),
					'descr1' => htmlentities($row['rem_i']),
					'descr2' => htmlentities($row['rem_ii']),
					'creator' => $row['Име'],
					'created' => substr($row['created'], 0, 19),
					'activate_date' => ($row['activate_date'] ? substr($row['activate_date'], 0, 10) : null),
					'expire_date' => ($row['expire_date'] ? substr($row['expire_date'], 0, 10) : null),
					'status_id' => $row['status_id'],
					'note' => htmlentities($row['note'])
				];
			}
		}

		$day = intval(date('d'));
		$month = intval(date('m'));
		$year = intval(date('Y'));

		if ($day <= 5)
		{
			if ($month === 1)
			{
				$month = 12;
				$year -= 1;
			}
			else
			{
				$month -= 1;
			}
		}

		$query = '
		SELECT
			*
		FROM
			public."Заплати"
		WHERE
			"ID" IN (
				SELECT
					UNNEST(STRING_TO_ARRAY(STRING_AGG(ARRAY_TO_STRING(sub2.ids[0:6], \',\'), \',\'), \',\'))::int
				FROM
				(
					SELECT
						ARRAY_AGG(sub1."ID") AS ids
					FROM
					(
						SELECT
							un.*
						FROM
						(
							SELECT
								sa."ID", sa."Съдружник ID"
							FROM
								public."Заплати" AS sa
							JOIN
								public."Съдружници" AS st USING("Съдружник ID")
							JOIN
								public."Разходи" AS co ON sa."Разход ID" = co."ID"
							WHERE
								st."Active" IS TRUE AND st."Position ID" IS NOT NULL
							UNION
							SELECT
								sa."ID", sa."Съдружник ID"
							FROM
								public."Заплати" AS sa
							JOIN
								public."Съдружници" AS st USING("Съдружник ID")
							WHERE
								st."Active" IS TRUE AND st."Position ID" IS NOT NULL AND sa."Разход ID" = 0
						) AS un
						ORDER BY
							un."ID" DESC
					) AS sub1
					GROUP BY
						sub1."Съдружник ID"
				) AS sub2
			)
		ORDER BY
			"Съдружник ID" ASC,
			"ID" DESC
		';

		$result = $this->db->query($query)->result_array();

		foreach ($result as $row)
		{
			if (array_key_exists($row['Съдружник ID'], $salary))
			{
				if (intval($row['Разход ID']) === 0 && substr($row['Месец'], 0, 7) === $year . '-' . $month && $row['bank'] === 'f')
				{
					$salary[$row['Съдружник ID']]['hanging_cost'] = true;
				}

				$salary[$row['Съдружник ID']]['advance'][] = [
					'id' => $row['ID'],
					'suma' => $row['Сума'],
					'cost' => intval($row['Разход ID']),
					'month' => $row['Месец'] ? DateTime::createFromFormat('m', substr($row['Месец'], 5, 2))->format('M') . ' ' . substr($row['Месец'], 2, 2) : '',
					'date' => substr($row['Дата'], 0, 10),
					'note' => ($row['Забележка'] ? htmlentities($row['Забележка']) : ''),
					'creator' => $row['Съдружник ID2'],
					'is_bank' => ($row['bank'] === 't')
				];
			}
		}

		$remainder = DateTime::createFromFormat('m', $month)->format('M') . ' ' . substr($year, 2);

		$request = [
			'salary' => $salary,
			'status' => $this->getEmployeeBankStatus(),
			'remainder' => $remainder
		];

		return $request;
	}

	public function getAdvancesByID($id)
	{
		$query = '
		SELECT
			un.*
		FROM
		(
			SELECT
				sa.*
			FROM
				public."Заплати" AS sa
			JOIN
				public."Съдружници" AS st USING("Съдружник ID")
			JOIN
				public."Разходи" AS co ON sa."Разход ID" = co."ID"
			WHERE
				st."Active" IS TRUE AND st."Position ID" IS NOT NULL
			UNION
			SELECT
				sa.*
			FROM
				public."Заплати" AS sa
			JOIN
				public."Съдружници" AS st USING("Съдружник ID")
			WHERE
				st."Active" IS TRUE AND st."Position ID" IS NOT NULL AND sa."Разход ID" = 0
		) AS un
		WHERE
			un."Съдружник ID" = ' . $id . '
		ORDER BY
			un."ID" DESC
		';

		$result = $this->db->query($query)->result_array();

		$advance = [];

		foreach ($result as $row)
		{
			$advance[] = [
				'id' => $row['ID'],
				'suma' => $row['Сума'],
				'cost' => intval($row['Разход ID']),
				'month' => $row['Месец'] ? DateTime::createFromFormat('m', substr($row['Месец'], 5, 2))->format('M') . ' ' . substr($row['Месец'], 2, 2) : '',
				'date' => substr($row['Дата'], 0, 10),
				'note' => ($row['Забележка'] ? htmlentities($row['Забележка']) : ''),
				'creator' => $row['Съдружник ID2'],
				'is_bank' => ($row['bank'] === 't')
			];
		}

		return $advance;
	}

	public function setSuma($employee_id, $suma)
	{
		$query = '
		UPDATE
			public."Съдружници"
		SET
			"Сума" = ' . $suma . '
		WHERE
			"Съдружник ID" = ' . $employee_id;

		$result = $this->db->query($query);

		return $result;
	}

	public function setCard($employee_id, $card)
	{
		$query = '
		UPDATE
			public."Съдружници"
		SET
			"Card" = ' . $card . '
		WHERE
			"Съдружник ID" = ' . $employee_id;

		$result = $this->db->query($query);

		return $result;
	}

	public function setCorrection($employee_id, $salary)
	{
		$query = '
		UPDATE
			public."Съдружници"
		SET
			salary = ' . $salary . '
		WHERE
			"Съдружник ID" = ' . $employee_id;

		$result = $this->db->query($query);

		return $result;
	}

	public function setSalaryTypeID($employee_id, $salary_type_id)
	{
		$query = '
		UPDATE
			public."Съдружници"
		SET
			salary_type_id = ' . $salary_type_id . '
		WHERE
			"Съдружник ID" = ' . $employee_id;

		$result = $this->db->query($query);

		return $result;
	}

	public function setIBAN($id, $data)
	{
		if (is_null($id))
		{
			$data['creator_id'] = intval($this->session->uid);
			$this->db->set('created', 'NOW()::timestamp(3)', false);

			$result = $this->db->insert('public.user_bank_transaction', $data);

			$insert_id = $this->db->insert_id();
		}
		else
		{
			$result = $this->db->update('public.user_bank_transaction', $data, ['id' => $id]);
		}

		$iban_id = (is_null($id)) ? $insert_id : $id;

		if ($result)
		{
			return $iban_id;
		}
	}

	public function unsetIBAN($id)
	{
		$this->db->where('id', $id);

		return $this->db->delete('public.user_bank_transaction');
	}

	public function setAdvance($id, $data)
	{
		$this->db->set('"Съдружник ID"', $data['Съдружник ID'], false);
		$this->db->set('"Съдружник ID2"', intval($this->session->uid), false);

		unset($data['Съдружник ID']);

		if (is_null($id))
		{
			$result = $this->db->insert('public."Заплати"', $data);
		}
		else
		{
			$this->db->where('"ID"', $id, false);

			$result = $this->db->update('public."Заплати"', $data);
		}

		return $result;
	}

	public function unsetAdvance($id)
	{
		$this->db->where('"ID"', $id, false);

		return $this->db->delete('public."Заплати"');
	}

	public function setAdvanceMonth($id, $month)
	{
		$query = '
		UPDATE
			public."Заплати"
		SET
			"Месец" = \'' . $month . '\'
		WHERE
			"ID" = ' . $id;

		$result = $this->db->query($query);

		return $result;
	}

	public function setCost($id)
	{
		$query = '
		INSERT INTO
			public."Разходи" ("Съдружник ID", "ЛВ", "Описание", "Дата", "Описание разход ID", "Сума", "DM", "ЛВ to _", "ЛВ to DM", "ДДС", "Chas", "User ID", "Сметка ID", "Фактура ID")
		SELECT
			"Съдружник ID",
			"Сума" AS "ЛВ",
			\'Заплата за месец \' || (CASE EXTRACT(MONTH FROM (SELECT "Месец" FROM public."Заплати" WHERE "ID" = ' . $id . '))
				WHEN 1 THEN \'януари\'
				WHEN 2 THEN \'февруари\'
				WHEN 3 THEN \'март\'
				WHEN 4 THEN \'април\'
				WHEN 5 THEN \'май\'
				WHEN 6 THEN \'юни\'
				WHEN 7 THEN \'юли\'
				WHEN 8 THEN \'август\'
				WHEN 9 THEN \'септември\'
				WHEN 10 THEN \'октомври\'
				WHEN 11 THEN \'ноември\'
				WHEN 12 THEN \'декември\'
			END) AS "Описание",
			NOW()::date::timestamp AS "Дата",
			9 AS "Описание разход ID",
			0 AS "Сума",
			0 AS "DM",
			(SELECT "ЛВ to _" FROM public."Подразбиращи се курсове" LIMIT 1) AS "ЛВ to _",
			(SELECT "ЛВ to DM" FROM public."Подразбиращи се курсове" LIMIT 1) AS "ЛВ to DM",
			FALSE AS "ДДС",
			NOW()::timestamp(0) AS "Chas",
			' . intval($this->session->uid) . ' AS "User ID",
			1 AS "Сметка ID",
			0 AS "Фактура ID"
		FROM
			public."Заплати"
		WHERE
			"ID" = ' . $id;

		$result = $this->db->query($query);

		if ($result)
		{
			$insert_id = $this->db->insert_id();

			$query = '
			UPDATE
				public."Заплати"
			SET
				"Разход ID" = ' . $insert_id . '
			WHERE
				"ID" = ' . $id;

			$result = $this->db->query($query);

			return $result;
		}

		return $result;
	}

	public function setBankTransaction($string)
	{
		$data = explode(PHP_EOL, trim($string));

		$iban = [];

		foreach ($data as $value)
		{
			$chunks = explode("\t", trim($value));

			$iban[] = trim($chunks[0]);
		}

		$month = intval(date('m'));
		$year = intval(date('Y'));

		if ($month === 1)
		{
			$month = 12;
			$year -= 1;
		}
		else
		{
			$month -= 1;
		}

		$salary_month = $year . '-' . str_pad($month, '0', 2, STR_PAD_LEFT) . '-' . date('d') . ' 00:00:00';

		$query = '
		INSERT INTO
			public."Заплати" ("Месец", "Дата", "Съдружник ID", "Сума", "Забележка", "Съдружник ID2", bank)
		SELECT
			\'' . $salary_month . '\' AS "Месец",
			NOW()::timestamp(0) AS "Дата",
			user_id AS "Съдружник ID",
			jsum AS "Сума",
			iban_r AS "Забележка",
			' . $this->session->uid . ' AS "Съдружник ID2",
			TRUE AS bank
		FROM
			public.user_bank_transaction
		WHERE
			status_id != 0 AND iban_r IN (\'' . implode("', '", $iban) . '\')
		';

		$this->db->query($query);
	}
}
?>