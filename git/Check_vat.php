<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 31/10/2019
# [MODIFIED] 12/05/2020
final class Check_vat extends J_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('check_vat_model');

		$this->data['path']['check_vat'] = 'Проверка ДДС';
	}

	public function index($vat_number = null)
	{
		$this->data['title'] = 'Проверка ДДС';

		//$this->data['download_link'] = $this->user_model->checkRight(37);
		$this->data['download_link'] = $this->user_model->checkRight(0);
		$this->data['vat_number'] = boolval(preg_match('/^([A-Z]{2})?[0-9]+$/i', $vat_number)) ? $vat_number : '';
		$this->data['created'] = $this->check_vat_model->getDateCreated();

		$time_format = date_create_from_format('Y-m-d', $this->data['created']);

		$unix_time = date_format($time_format, 'U');

		$this->data['old_date'] = ((time() - $unix_time) > (3600 * 24 * 3));

		$this->render();
	}

	public function nap_link()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('check_vat');
		}

		$response = [
			'link_href' => '',
			'link_text' => 'Няма линк'
		];

		$inbox = imap_open('{0.0.0.0:993/imap/ssl/novalidate-cert}INBOX', '', '');

		$week_behind = date('Y-m-d', time() - (60 * 60 * 24 * 7));

		$emails = imap_search($inbox, 'SINCE "'. $week_behind .'"');

		rsort($emails);

		$data = [];

		if (is_array($emails))
		{
			foreach ($emails as $uid)
			{
				$data[$uid] = [
					'body' => imap_qprint(imap_body($inbox, $uid)),
					'meta' => (array) imap_fetch_overview($inbox, $uid)[0]
				];
			}
		}

		if (!empty($data))
		{
			foreach ($data as $value)
			{
				$strip_message = explode(PHP_EOL, $value['body']);

				if (is_array($strip_message))
				{
					foreach ($strip_message as $line)
					{
						if (preg_match('/bulletindwn/', $line))
						{
							$response['link_href'] = trim($line);
							$response['link_text'] = 'Свалете ZIP(csv) файл от '. date('d.m.Y', $value['meta']['udate']);

							break 2;
						}
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function upload()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('check_vat');
		}

		$upload_path = DEVELOPMENT ? './dompdf/' : './pdf/';

		$config['upload_path'] = $upload_path;
		$config['file_name'] = 'firm_dds_nap.zip';
		$config['allowed_types'] = 'zip';
		$config['max_size'] = 0;
		$config['remove_spaces'] = true;

		$zip_file_name = $upload_path . $config['file_name'];

		if (file_exists($zip_file_name))
		{
			unlink($zip_file_name);
		}

		$this->load->library('upload', $config);
		$this->upload->initialize($config);

		$response = [];

		if ($this->upload->do_upload('upload_zip'))
		{
			$data = $this->upload->data();

			chmod($zip_file_name, 0777);
		}
		else
		{
			$response['error'] = trim(mb_strtolower(strip_tags($this->upload->display_errors())), '.');
		}

		if (empty($response))
		{
			$zip = new ZipArchive;

			$is_opened = $zip->open($zip_file_name);

			if ($is_opened)
			{
				if ($zip->numFiles > 1)
				{
					$response['error'] = 'архива има повече от един файл';
				}
				else
				{
					$csv_file_name = $upload_path . $zip->getNameIndex(0);

					if (file_exists($csv_file_name))
					{
						unlink($csv_file_name);
					}

					$zip->extractTo($upload_path);

					chmod($csv_file_name, 0777);

					if (substr($csv_file_name, -3) != 'csv')
					{
						$response['error'] = 'архива не съдържа CSV файл';
					}
				}

				$zip->close();

				if (empty($response))
				{
					$counter = 0;
					$raw_data = [];
					$clear_insert = 20000;

					if (($handle = fopen($csv_file_name, 'r')) === false)
					{
						$response['error'] = 'не може да бъде прочетен CSV файл';
					}
					else
					{
						$this->db->query('TRUNCATE TABLE temp.vat_registred');

						while (($row = fgetcsv($handle, 2000, ',')) !== false)
						{
							++$counter;

							if ($counter <= 2)
							{
								continue;
							}

							$row[0] = str_replace("'", "''", $row[0]);
							$row[2] = str_replace("'", "''", $row[2]);

							$row = "('". implode("','", $row) . "')";

							$raw_data[] = array_map('self::convert', (array) $row)[0];

							if ($counter % $clear_insert === 0)
							{
								$query = "
								INSERT INTO temp.vat_registred (firm, vat_id, addr, oblast, obstina, naseleno_miasto, dr96, dd96, dr96a1, dd96a1, dr96a9, dd96a9, dr97, dd97, dr97a, dd97a, dr97b, dd97b, dr98, dd98, dr99, dd99, dr99a3, dd99a3, dr99a7, dd99a7, dr100a1, dd100a1, dr100a2, dd100a2, dr100a3, dd100a3, dr, dd, dr151a, dp151a, dd151a, dr156a1, dd156a1, dr156a16, dd156a16)
								VALUES
								" . implode(','. PHP_EOL, $raw_data);

								$this->db->query($query);

								$raw_data = [];
							}
						}

						fclose($handle);

						$query = "
						INSERT INTO temp.vat_registred (firm, vat_id, addr, oblast, obstina, naseleno_miasto, dr96, dd96, dr96a1, dd96a1, dr96a9, dd96a9, dr97, dd97, dr97a, dd97a, dr97b, dd97b, dr98, dd98, dr99, dd99, dr99a3, dd99a3, dr99a7, dd99a7, dr100a1, dd100a1, dr100a2, dd100a2, dr100a3, dd100a3, dr, dd, dr151a, dp151a, dd151a, dr156a1, dd156a1, dr156a16, dd156a16)
						VALUES
						" . implode(','. PHP_EOL, $raw_data);

						$this->db->query($query);

						$this->db->query('TRUNCATE TABLE unity.vat_registred');

						$query = "
						INSERT INTO
						unity.vat_registred
						SELECT * FROM temp.vw_temp2unity_vat_registred
						";

						$this->db->query($query);

						$query = "
						UPDATE unity.vat_registred
						SET created = NOW()::date
						";

						$this->db->query($query);

						$response['success'] = 'Вкарани записи - '. $counter - 2;
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	private static function convert($value)
	{
		return iconv('Windows-1251', 'UTF-8', $value);
	}

	public function get_data()
	{
		if ($this->input->is_ajax_request())
		{
			$post = $this->input->post();

			$form = ['firm', 'vat', 'date'];

			if ($this->checkInputField($post, $form))
			{
				$this->output->set_content_type('application/json')->set_output(json_encode(['error' => [0 => 'грешна форма']]));

				return;
			}

			$error = [];

			$firm = trim($post['firm']);
			$vat = trim($post['vat']);
			$date = trim($post['date']);

			$is_empty_firm = false;
			$is_empty_vat = false;

			if (empty($firm))
			{
				$is_empty_firm = true;

				$firm = null;
			}
			else
			{
				if (mb_strlen($firm) < 2)
				{
					$error['firm'] = 'въведи поне 2 символа за име на фирма';
				}
				else
				{
					$firm = str_replace("'", "''", $firm);
				}
			}

			if (empty($vat))
			{
				$is_empty_vat = true;

				$vat = null;
			}
			else
			{
				if (boolval(preg_match('/^([A-Z]{2})?[0-9]+$/i', $vat)))
				{
					$vat = strtoupper($vat);

					if (strlen($vat) <= 3)
					{
						$error['vat'] = 'въведи повече от 3 символа за номер на ДДС';
					}
				}
				else
				{
					$error['vat'] = 'невалиден номер на ДДС';
				}
			}

			if ($is_empty_firm && $is_empty_vat)
			{
				$error['firm|vat'] = 'въведи име на фирма и/или номер на ДДС';
			}

			if (empty($date))
			{
				$date = date('Y-m-d');
			}
			else
			{
				$date_format = date_create_from_format('Y-m-d', $date);

				$unix_time = date_format($date_format, 'U');

				$check_date = date('Y-m-d', $unix_time);

				if ($check_date !== $date)
				{
					$error['date'] = 'грешна дата';
				}
			}

			if (empty($error))
			{
				$vies_data = 'Търсене само по номер на ДДС';

				if (!$is_empty_vat)
				{
					$this->load->library('vat', ['no' => $vat, 'is_error_string' => null]);

					$vies = $this->vat->check_vat();

					if (is_string($vies))
					{
						switch ($vies)
						{
							case 'INVALID_INPUT':
								$vies_data = 'The provided CountryCode is invalid or the VAT number is empty';
							break;

							case 'GLOBAL_MAX_CONCURRENT_REQ':
								$vies_data = 'Your Request for VAT validation has not been processed; the maximum number of concurrent requests has been reached. Please re-submit your request later or contact';
							break;

							case 'MS_MAX_CONCURRENT_REQ':
								$vies_data = 'Your Request for VAT validation has not been processed; the maximum number of concurrent requests for this Member State has been reached';
							break;

							case 'SERVICE_UNAVAILABLE':
								$vies_data = 'An error was encountered either at the network level or the Web application level, try again later';
							break;

							case 'MS_UNAVAILABLE':
								$vies_data = 'The application at the Member State is not replying or not available. Please refer to the Technical Information page to check the status of the requested Member State, try again later';
							break;

							case 'TIMEOUT':
								$vies_data = 'The application did not receive a reply within the allocated time period, try again later';
							break;
						}
					}

					if (is_object($vies))
					{
						$vies_data = [
							'name' => $vies->name,
							'vat' => $vies->countryCode . '' . $vies->vatNumber,
							'address' => $vies->address,
							'is_valid' => boolval(intval($vies->valid)) ? 'да' : 'не'
						];
					}
				}

				$vat = preg_replace('/[^0-9]/', '', $vat);

				$data = [
					'firm' => $firm,
					'vat' => $vat,
					'date' => $date
				];

				$base_data = $this->check_vat_model->getData($data);

				foreach ($base_data as &$value)
				{
					foreach ($value as $key => $date)
					{
						$value['date_created'] = '';

						if (substr($key, 0, 2) === 'dr' && $date !== '5000-01-01')
						{
							$value['date_created'] = $date;

							break;
						}
					}
				}

				$response['success'] = [
					'vies' => $vies_data,
					'base' => $base_data
				];
			}
			else
			{
				$response['error'] = $error;
			}

			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
		else
		{
			redirect('check_vat');
		}
	}

	private function checkInputField($post, $required_fields)
	{
		$is_invalid = false;

		foreach ($required_fields as $value)
		{
			if (!array_key_exists($value, $post))
			{
				$is_invalid = true;

				break;
			}
		}

		return $is_invalid;
	}
}
?>