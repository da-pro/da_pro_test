<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 24/02/2021
# [MODIFIED] 17/12/2021
final class Guru_model extends J_Model
{
	public function getClientData()
	{
		$query = '
		SELECT
			g_a.id,
			g_a.client_data,
			COALESCE(g_a.client_data::json->\'businessName\'->>\'local\', g_a.client_data::json->\'name\'->>\'local\', \'&mdash;\') AS firm_name,
			g_a.created,
			COALESCE(g_a.updated::varchar, \'&mdash;\') AS updated,
			(CASE
				WHEN g_a.updated IS NOT NULL THEN g_a.updated
				ELSE g_a.created
			END) AS sort_date,
			cl."Име" AS client_name
		FROM
			public.guru_api AS g_a
		JOIN
			public."Клиенти" AS cl ON g_a.jar_client_id = cl."Клиент ID"
		ORDER BY
			sort_date DESC
		';

		$result = $this->db->query($query)->result_array();

		return $result;
	}

	public function getClientName($client)
	{
		$query = '
		(
			SELECT
				cl."Клиент ID", cl."Име",
				fcl."БУЛСТАТ"
			FROM
				public."Клиенти" AS cl
			JOIN
				public."FКлиенти" AS fcl ON cl."ИмеФактури ID" = fcl."Клиент ID"
			WHERE
				LOWER(cl."Име") LIKE LOWER(\'%' . $client . '%\')
			ORDER BY
				cl."Име" ASC
			LIMIT 10
		)
		UNION
		(
			SELECT
				cl."Клиент ID", cl."Име",
				fcl."БУЛСТАТ"
			FROM
				public."Клиенти" AS cl
			JOIN
				public."FКлиенти" AS fcl ON cl."ИмеФактури ID" = fcl."Клиент ID"
			WHERE
				fcl."БУЛСТАТ" LIKE \'%' . $client . '%\'
			ORDER BY
				cl."Име" ASC
			LIMIT 10
		)
		';

		$result = $this->db->query($query)->result_array();

		$clients = [];

		foreach ($result as $row)
		{
			$clients[$row['Клиент ID']] = "<b>{$row['БУЛСТАТ']}</b> {$row['Име']}";
		}

		return $clients;
	}

	public function getClientEIKByClientID($clients)
	{
		$query = '
		SELECT
			cl."Клиент ID",
			fcl."БУЛСТАТ"
		FROM
			public."Клиенти" AS cl
		LEFT JOIN
			(SELECT id, jar_client_id FROM public.guru_api WHERE jar_client_id IN (' . implode(', ', $clients) . ')) AS gu ON cl."Клиент ID" = gu.jar_client_id
		JOIN
			public."FКлиенти" AS fcl ON cl."ИмеФактури ID" = fcl."Клиент ID"
		WHERE
			cl."Клиент ID" IN (' . implode(', ', $clients) . ') AND gu.id IS NULL
		';

		$result = $this->db->query($query)->result_array();

		$clients_eik = [];

		foreach ($result as $row)
		{
			$clients_eik[$row['БУЛСТАТ']] = $row['Клиент ID'];
		}

		return $clients_eik;
	}

	public function setTableData($data, $id = null)
	{
		if (is_null($id))
		{
			$this->db->set('created', 'NOW()::timestamp(0)', false);

			$result = $this->db->insert('public.guru_api', $data);

			$id = $this->db->insert_id();
		}
		else
		{
			$this->db->set('updated', 'NOW()::timestamp(0)', false);

			$result = $this->db->update('public.guru_api', $data, ['id' => $id]);
		}

		if ($result)
		{
			return $id;
		}

		return false;
	}

	public function getTableData($id)
	{
		$query = "SELECT COALESCE(updated, created) AS last_update, client_data, DATE_PART('day', NOW()::timestamp(0) - COALESCE(updated, created)) AS days_from_update FROM public.guru_api WHERE id = $id";

		$client_data = $this->db->query($query)->row_array();

		return $client_data;
	}

	public function getClientGURUData($id)
	{
		$query = "SELECT guru_client_id, guru_client_locale FROM public.guru_api WHERE id = $id";

		$client_data = $this->db->query($query)->row_array();

		return $client_data;
	}

	public function getGURUTableIDByClientID($id)
	{
		$query = "
		SELECT
			COALESCE(id::varchar, '') AS guru_id
		FROM
			public.guru_api
		WHERE
			jar_client_id = $id
		ORDER BY
			created DESC
		LIMIT 1
		";

		$guru_id = $this->db->query($query)->row_array()['guru_id'];

		return $guru_id;
	}

	private function formatDate($format, $value, $new_format)
	{
		$date_format = date_create_from_format($format, $value);
		$unix_time = date_format($date_format, 'U');
		$new_value = date($new_format, $unix_time);

		return $new_value;
	}

	public function getSavedClientData($id)
	{
		$table_data = $this->getTableData($id);

		$data = json_decode($table_data['client_data'], true);

		$client_data = [];
		$client_data['Последно обновяване'] = '<span class="custom">' . $this->formatDate('Y-m-d H:i:s', $table_data['last_update'], 'd.m.Y H:i:s') . '</span><a href="/guru/client/' . $id . '" title="Отвори JSON" target="_blank"><i class="fas fa-link"></i></a><input type="button" value="обнови" onclick="setGURUClientUpdate(' . $id . ');">';

		return $this->setDefaultResponse($data, $client_data);
	}

	public function setDefaultResponse($data, $client_data)
	{
		$client_data['Име'] = $data['businessName']['local'] ?: $data['name']['local'];
		$client_data['Дата на установяване'] = $this->formatDate('Y-m-d', $data['establishmentDate'], 'd.m.Y');
		$client_data['Основна дейност'] = $data['mainActivity']['bg'];

		$activities = '';

		foreach ($data['activityScope'] as $value)
		{
			$activity = str_replace(['b', 'h', 'k', 'm', 't'], ['в', 'н', 'к', 'м', 'т'], $value);

			$activities .= '<tr><td>' . $activity . '</td></tr>';
		}

		if (!empty($activities))
		{
			$client_data['Всички дейности'] = '<div class="toggle"><h1 data-toggle="1">покажи</h1><br><div><table>' . $activities . '</table></div></div>';
		}

		if (!empty($data['headquarterAddress']['rawAddress']))
		{
			$client_data['Адрес'] = '<div class="toggle"><h1 data-toggle="1">покажи</h1><br><div>' . $data['headquarterAddress']['rawAddress'] . '</div></div>';
		}

		$phones = [];

		foreach ($data['phoneNumbers'] as $value)
		{
			$phones[] = $value['number'];
		}

		if (!empty($phones))
		{
			$client_data['Телефони'] = '<div class="toggle"><h1 data-toggle="1">покажи</h1><br><div>' . implode(PHP_EOL, $phones) . '</div></div>';
		}

		$emails = [];

		foreach ($data['emailAddresses'] as $value)
		{
			$emails[] = $value['address'];
		}

		if (!empty($emails))
		{
			$client_data['Е-поща'] = '<div class="toggle"><h1 data-toggle="1">покажи</h1><br><div>' . implode(PHP_EOL, $emails) . '</div></div>';
		}

		if (!empty($data['webAddresses']['0']['address']))
		{
			$web_address = $data['webAddresses']['0']['address'];

			if (!preg_match('#^http(s)?://#', $web_address))
			{
				$web_address = "http://$web_address";
			}

			$client_data['Web Адрес'] = '<a href="' . $web_address . '" target="_blank">' . $data['webAddresses']['0']['address'] . '</a>';
		}

		$owners = [];

		foreach ($data['owners'] as $value)
		{
			$more_info = '';

			if (!empty($value['entity']['id']) && $value['entity']['type'] !== 'person')
			{
				$more_info = '<i title="Информация от GURU API" class="fas fa-link" onclick="getGURUClientData(\'' . strtolower($value['entity']['country']) . '\',\'' . $value['entity']['id'] . '\',\'' . htmlentities($value['entity']['name']['local']) . '\')"></i>';
			}

			$owners[] = "{$value['entity']['name']['local']} <b>{$value['entity']['personalNo']}</b>$more_info";
		}

		$client_data['Собственици'] = implode(PHP_EOL, $owners);

		$representatives = [];

		foreach ($data['representatives'] as $value)
		{
			$more_info = '';

			if (!empty($value['entity']['id']) && $value['entity']['type'] !== 'person')
			{
				$more_info = '<i title="Информация от GURU API" class="fas fa-link" onclick="getGURUClientData(\'' . strtolower($value['entity']['country']) . '\',\'' . $value['entity']['id'] . '\',\'' . htmlentities($value['entity']['name']['local']) . '\')"></i>';
			}

			$representatives[] = "{$value['entity']['name']['local']} <b>{$value['entity']['personalNo']}</b>$more_info";
		}

		$client_data['Представители'] = implode(PHP_EOL, $representatives);

		$overview = [];

		$employees = $data['financialOverviews']['employees'];
		$capital = $data['financialOverviews']['capital'];
		$assets = $data['financialOverviews']['assets'];
		$revenue = $data['financialOverviews']['revenue'];
		$net_result = $data['financialOverviews']['netResult'];

		foreach ($employees as $key => $value)
		{
			$overview[$key]['employees'] = $value['value'];
		}

		foreach ($capital as $key => $value)
		{
			$total = number_format($value['value'], 0, '', ' ');

			$overview[$key]['capital'] = $total;
		}

		foreach ($assets as $key => $value)
		{
			$total = number_format($value['value'], 0, '', ' ');

			$overview[$key]['assets'] = $total;
		}

		foreach ($revenue as $key => $value)
		{
			$total = number_format($value['value'], 0, '', ' ');

			$overview[$key]['revenue'] = $total;
		}

		foreach ($net_result as $key => $value)
		{
			$total = number_format($value['value'], 0, '', ' ');

			$overview[$key]['netResult'] = $total;
		}

		krsort($overview);

		$table = '<tr><th>Година</th><th>Служители</th><th>Капитал</th><th>Активи</th><th>Приходи</th><th>Нетен Резултат</th></tr>';

		foreach ($overview as $key => $value)
		{
			$table .= "<tr><td>$key</td><td>{$value['employees']}</td><td>{$value['capital']}</td><td>{$value['assets']}</td><td>{$value['revenue']}</td><td>{$value['netResult']}</td></tr>";
		}

		$client_data['preview'] = '<table class="overview preview"><caption>Преглед</caption>' . $table . '</table>';

		$raw_report = [];

		foreach ($data['financialFullReports'] as $key => $value)
		{
			$raw_report[$key] = [
				'period' => $value['I0001'],
				'Вид на отчета' => $value['I0002']['bg'],
				'Формат на отчета' => $value['I0003']['bg'],
				'Размер на предприятието' => $value['I0004']['bg'],
				'Брой на персонала' => $value['I0005'],
				'Общо дълготрайни материални активи' => number_format($value['P0012'], 0, '', ' '),
				'Отсрочени данъци' => number_format($value['P0025'], 0, '', ' '),
				'Общо нетекущи (дълготрайни) активи' => number_format($value['C0002'], 0, '', ' '),
				'Коефициент на мобилизация на капитала в дълготрайни активи' => "{$value['R0012']}%",
				'Дълготрайни активи/Общо пасиви' => "{$value['R0013']}%",
				'Общо материални запаси' => number_format($value['P0029'], 0, '', ' '),
				'Общо краткосрочни вземания' => number_format($value['C0007'], 0, '', ' '),
				'Общо краткосрочни инвестиции' => number_format($value['P0034'], 0, '', ' '),
				'Общо парични средства' => number_format($value['P0036'], 0, '', ' '),
				'Общо текущи (краткотрайни) активи' => number_format($value['C0008'], 0, '', ' '),
				'Коефициент на текуща ликвидност' => $value['R0007'],
				'Разходи за бъдещи периоди' => number_format($value['P0039'], 0, '', ' '),
				'Сума на актива' => number_format($value['P0040'], 0, '', ' '),
				'Общо премии и резерви' => number_format($value['P0046'], 0, '', ' '),
				'Общо натрупана печалба (загуба) от предходни години и текущата година' => number_format($value['P0049'], 0, '', ' '),
				'Общо капитал' => number_format($value['P0050'], 0, '', ' '),
				'Предоставени аванси и дълготрайни материални активи в процес на изграждане' => "{$value['R0011']}%",
				'Общо задължения' => number_format($value['P0077'], 0, '', ' '),
				'Финансирания и приходи за бъдещи периоди' => number_format($value['P0078'], 0, '', ' '),
				'Сума на пасива' => number_format($value['P0079'], 0, '', ' '),
				'Общо приходи от оперативна дейност' => number_format($value['P0086'], 0, '', ' '),
				'Общо разходи за оперативна дейност' => number_format($value['P0095'], 0, '', ' '),
				'Резултат от оперативна дейност' => number_format($value['C0015'], 0, '', ' '),
				'Брутен марж' => "{$value['R0001']}%",
				'Общо финансови разходи' => number_format($value['P0102'], 0, '', ' '),
				'Печалба преди облагане с данъци и финансови приходи и разходи' => number_format($value['C0020'], 0, '', ' '),
				'Разходи за данъци от печалбата' => number_format($value['P0105'], 0, '', ' '),
				'Марж на печалбата' => "{$value['R0004']}%"
			];
		}

		krsort($raw_report);

		$report = [];
		$counter = 0;
		$period = 1;

		foreach (array_keys($raw_report) as $value)
		{
			++$counter;

			$report[$period][$value] = $raw_report[$value];

			if ($counter === 5)
			{
				++$period;

				$counter = 0;
			}
		}

		$client_data['report'] = $report;

		return $client_data;
	}
}
?>