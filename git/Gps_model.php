<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 26/10/2020
# [MODIFIED] 15/02/2021
final class Gps_model extends J_Model
{
	public function getVehicles()
	{
		$query = '
		SELECT
			ve.id,
			COALESCE(ve.ename, \'\') AS ename,
			ve.brand,
			COALESCE(ve.model, \'\') AS model,
			ve.vehicle_number,
			COALESCE(ve.gps_gsm::varchar, \'\') AS gps_gsm,
			COALESCE(ve.note, \'\') AS note,
			ve.status_id,
			COALESCE(ve.driver_id::varchar, \'\') AS driver_id,
			CASE ve.status_id
				WHEN 1 THEN \'активен\'
				WHEN 0 THEN \'неактивен\'
			END AS status,
			COALESCE(st."Име", \'\') AS driver
		FROM
			public.vehicle AS ve
		LEFT JOIN
			public."Съдружници" AS st ON ve.driver_id = st."Съдружник ID"
		ORDER BY
			ve.id ASC, ve.status_id DESC
		';

		$vehicles = $this->db->query($query)->result_array();

		return $vehicles;
	}

	public function getDriverStaff()
	{
		$query = '
		SELECT
			"Име", "Съдружник ID", "Position ID"
		FROM
			public."Съдружници"
		WHERE
			"Active" = true AND "Position ID" NOT IN (501, 701, 702, 710, 1001)
			--501[Касиер]
			--701[Стажант счетоводител]
			--702[Счетоводител]
			--710[Главен счетоводител]
			--1001[Хигиенист]
		ORDER BY
			"Име" ASC
		';

		$result = $this->db->query($query)->result_array();

		$staff = [
			'Шофьори' => [],
			'Други Шофьори' => [],
			'Други Служители' => []
		];

		foreach ($result as $row)
		{
			$type = ($row['Position ID'] >= 600 && $row['Position ID'] <= 699) ? 'Шофьори' : 'Други Служители';

			if (in_array($row['Съдружник ID'], [2, 295]))
			{
				$type = 'Други Шофьори';
			}

			$staff[$type][$row['Съдружник ID']] = $row['Име'];
		}

		return $staff;
	}

	public function getVehicleByID($id)
	{
		$query = "SELECT * FROM public.vehicle WHERE id = $id";

		$result = $this->db->query($query)->row_array();

		return $result;
	}

	public function setVehicle($id, $data)
	{
		$is_date_disabled = $data['is_date_disabled'];

		unset($data['is_date_disabled']);

		if (is_null($id))
		{
			$result = $this->db->insert('public.vehicle', $data);

			$id = $this->db->insert_id();
		}
		else
		{
			if ($is_date_disabled)
			{
				$this->db->set('disabled', 'NOW()::timestamp(0)', false);
			}

			$this->db->where('id', $id);

			$result = $this->db->update('public.vehicle', $data);
		}

		if ($result)
		{
			return $id;
		}

		return false;
	}

	public function getLastDateOfMovement()
	{
		$query = "
		SELECT
			MAX(stop_time) AS last_date_of_movement
		FROM
			public.vehicle_gps
		LIMIT 1
		";

		$date = $this->db->query($query)->row_array()['last_date_of_movement'];

		$date_format = date_create_from_format('Y-m-d H:i:s', $date);
		$unix_time = date_format($date_format, 'U');
		$last_date_of_movement = date('d.m.Y H:i:s', $unix_time);

		return $last_date_of_movement;
	}

	public function getVehicleMovementType()
	{
		$query = 'SELECT * FROM public.vehicle_movement_type';

		$result = $this->db->query($query)->result_array();

		$movement_type = [];

		foreach ($result as $row)
		{
			$movement_type[$row['ename']] = $row['id'];
		}

		return $movement_type;
	}

	public function getVehicleData()
	{
		$query = '
		SELECT
			ve.id, ve.brand, ve.model, ve.driver_id, ve.vehicle_number, ve.status_id, ve.consumption_rate_urban, ve.consumption_rate_extra_urban,
			COALESCE(MAX(gps.kilometers), 0) AS kilometers
		FROM
			public.vehicle AS ve
		LEFT JOIN
			public.vehicle_gps AS gps ON ve.id = gps.vehicle_id
		GROUP BY
			ve.id
		ORDER BY
			ve.id ASC
		';

		$result = $this->db->query($query)->result_array();

		$vehicle_data = [];

		foreach ($result as $row)
		{
			$vehicle_data[$row['id']] = [
				'brand' => $row['brand'],
				'model' => $row['model'],
				'vehicle_number' => $row['vehicle_number'],
				'driver_id' => $row['driver_id'],
				'status_id' => $row['status_id'],
				'kilometers' => $row['kilometers'],
				'consumption_rate_urban' => floatval($row['consumption_rate_urban']),
				'consumption_rate_extra_urban' => floatval($row['consumption_rate_extra_urban'])
			];
		}

		return $vehicle_data;
	}

	public function setVehicleGPS($data)
	{
		$counter = 0;
		$insert_values = [];

		$total = count($data) - 1;

		foreach ($data as $key => $value)
		{
			++$counter;

			$insert_values[] = "({$value['vehicle_id']}, {$value['driver_id']}, '{$value['from_place']}', '{$value['to_place']}', {$value['movement_type_id']}, '{$value['start_time']}'::timestamp, '{$value['stop_time']}'::timestamp, ROUND({$value['distance']}, 2), ROUND({$value['consumption']}, 2), ROUND({$value['kilometers']}, 2), '{$value['stay']}'::interval, '{$value['in_movement']}'::interval)";

			if ($counter === 100 || $key === $total)
			{
				$insert_values = implode(',' . PHP_EOL, $insert_values);

				$query = "
				INSERT INTO
					public.vehicle_gps(vehicle_id, driver_id, from_place, to_place, movement_type_id, start_time, stop_time, distance, consumption, kilometers, stay, in_movement)
				SELECT
					*
				FROM
					(
						VALUES
						$insert_values
					) AS depot (vehicle_id, driver_id, from_place, to_place, movement_type_id, start_time, stop_time, distance, consumption, kilometers, stay, in_movement)
				WHERE
					NOT EXISTS (SELECT 1 FROM public.vehicle_gps WHERE vehicle_id = depot.vehicle_id AND start_time = depot.start_time AND stop_time = depot.stop_time)
				";

				$this->db->query($query);

				$counter = 0;
				$insert_values = [];
			}
		}

		return true;
	}

	public function getVehiclesGPS($start_date, $end_date, $vehicles, $custom_drivers)
	{
		if (!empty($custom_drivers))
		{
			$this->load->model('staff_model', 'staff');

			$staff = $this->staff->getStaff();
		}

		$query = '
		SELECT
			gps.vehicle_id, gps.driver_id, gps.from_place, gps.to_place, gps.start_time, gps.stop_time, gps.distance, gps.consumption, gps.kilometers, gps.stay, gps.in_movement, gps.start_time::date AS start_date, SUBSTRING(gps.start_time::varchar FROM 0 FOR 8) AS period,
			mt.ename AS movement_type,
			COALESCE(st."Име", \'Неизвестен шофьор\') AS driver
		FROM
			public.vehicle_gps AS gps
		JOIN
			public.vehicle_movement_type AS mt ON gps.movement_type_id = mt.id
		LEFT JOIN
			public."Съдружници" AS st ON gps.driver_id = st."Съдружник ID"
		WHERE
			gps.vehicle_id IN (' . implode(',', $vehicles) . ')
			AND gps.start_time::date >= \'' . $start_date . '\'
			AND gps.stop_time::date <= \'' . $end_date . '\'
		ORDER BY
			gps.vehicle_id ASC, gps.start_time ASC
		';

		$result = $this->db->query($query)->result_array();

		$gps = [];
		$total = [];

		foreach ($result as $row)
		{
			$driver = $row['driver'];

			if (!empty($custom_drivers))
			{
				if (array_key_exists($custom_drivers[$row['vehicle_id']], $staff))
				{
					$driver = $staff[$custom_drivers[$row['vehicle_id']]]['Име'];
				}
			}

			$gps[$row['period']][$row['vehicle_id']][$row['start_date']][] = [
				'from_place' => $row['from_place'],
				'to_place' => $row['to_place'],
				'start_time' => $row['start_time'],
				'stop_time' => $row['stop_time'],
				'distance' => $row['distance'],
				'consumption' => $row['consumption'],
				'kilometers' => $row['kilometers'],
				'stay' => $row['stay'],
				'in_movement' => $row['in_movement'],
				'movement_type' => $row['movement_type'],
				'driver' => $driver
			];

			$total[$row['vehicle_id']][$row['start_date']]['distance_inner'] += (($row['movement_type'] == 'Градско') ? $row['distance'] : 0);
			$total[$row['vehicle_id']][$row['start_date']]['consumption_inner'] += (($row['movement_type'] == 'Градско') ? $row['consumption'] : 0);
			$total[$row['vehicle_id']][$row['start_date']]['distance_outer'] += (($row['movement_type'] != 'Градско') ? $row['distance'] : 0);
			$total[$row['vehicle_id']][$row['start_date']]['consumption_outer'] += (($row['movement_type'] != 'Градско') ? $row['consumption'] : 0);

			if (!in_array($driver, $total[$row['vehicle_id']][$row['start_date']]['drivers']))
			{
				$total[$row['vehicle_id']][$row['start_date']]['drivers'][] = $driver;
				$total[$row['vehicle_id']][$row['start_date']]['drivers_signature'][] = $driver . str_repeat('&nbsp;', 3) . str_repeat('.', 12);
			}
		}

		return [
			'gps' => $gps,
			'total' => $total
		];
	}
}
?>