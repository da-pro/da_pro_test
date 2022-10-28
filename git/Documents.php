<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 04/12/2018
# [MODIFIED] 07/07/2022
final class Documents extends J_Controller
{
	const FTP = [
		'URI' => 'docs.jarnet',
		'USERNAME' => '',
		'PASSWORD' => 'ew9Rlqtwus7RThew9Rlqtnew',
		'BROWSE_PATH' => '/Printer/',
		'INVOICE_PATH' => '/Documents/Scanirani Dokumenti/Base/fakturi/',
		'WARRANTY_PATH' => '/Documents/Scanirani Dokumenti/Base/warranty/',
		'COST_PATH' => '/Documents/Scanirani Dokumenti/Base/razhodi/',
		'PROFIT_PATH' => '/Documents/Scanirani Dokumenti/Base/prihodi/',
		'PAYROLL_PATH' => '/Documents/Scanirani Dokumenti/Base/vedomosti/',
		'OTHER_PURCHASE_PATH' => '/Documents/Scanirani Dokumenti/Base/buy/',
		'OTHER_SALE_PATH' => '/Documents/Scanirani Dokumenti/Base/sale/'
	];
	const ACTION_TYPE = [
		'SAVE_FILE' => 1,
		'FORCE_SAVE_FILE' => 2,
		'COPY_FILE' => 3,
		'FORCE_COPY_FILE' => 4,
		'DELETE_FILE' => 5
	];
	const SKIP_FILE = 'SKIP-';

	private $connection = null;
	private $allowed_precise_date_users = [350, 405];
	private $is_outside = false;

	public function __construct()
	{
		parent::__construct();

		$this->load->model('documents_model');

		$this->is_outside = (substr($_SERVER['REMOTE_ADDR'], 0, 6) !== '10.10.');
	}

	public function index()
	{
		$this->data['title'] = 'Сканирани Документи';

		$this->data['path']['documents'] = 'Сканирани Документи';
		$this->data['submenu']['documents/logs'] = 'Логове';

		$this->data['is_outside'] = $this->is_outside;
		$this->data['folders'] = $this->browse();

		$this->render();
	}

	public function get_folder()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$folder = trim($post['folder']);

		if (empty($folder))
		{
			$error[] = 'избери папка';
		}
		else
		{
			$folders = $this->browse();

			$data = [];
			$data_images = [];

			if (in_array($folder, $folders))
			{
				$data = $this->browse($folder);

				if (empty($data))
				{
					$error[] = "папка $folder няма файлове от тип 'jpg'";
				}
				else
				{
					$data_images = $this->documents_model->getDataImages($folder);
				}
			}
			else
			{
				$error[] = 'избраната папка не съществува';
			}
		}

		if (empty($error))
		{
			$response['data'] = $data;
			$response['data_images'] = $data_images;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function upload_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$scanned_folder = trim($post['scanned_folder']);
		$watermark = trim($post['watermark']);

		$folders = $this->browse();

		if (empty($scanned_folder))
		{
			$error[] = 'изберете папка';
		}
		else if (in_array($scanned_folder, $folders))
		{
			$upload_path = DEVELOPMENT ? './dompdf/' : './pdf/';

			$config['upload_path'] = $upload_path;
			$config['allowed_types'] = 'pdf|ai|jpg|jpeg';

			$this->load->library('upload', $config);

			if ($this->upload->do_upload('upload_file'))
			{
				$absolute_path = dirname(dirname(__DIR__)) . '/public_html/' . $upload_path;

				$file_name = $this->upload->data('file_name');

				chmod($absolute_path . $file_name, 0777);

				$is_pdf = true;

				if (exif_imagetype($file_name) === 2)
				{
					$files = [$file_name];

					$is_pdf = false;
				}
				else
				{
					$image = new Imagick();

					$image->readImage($upload_path . $file_name);

					$params = $image->identifyImage();

					$image = new Imagick();

					$image->setResolution($params['resolution']['x'] * 2, $params['resolution']['y'] * 2);
					$image->readImage($upload_path . $file_name);

					$file_prefix = $this->uid . '-' . date('Y-m-d-His');

					$image->writeImages($upload_path . $file_prefix . '.jpg', false);

					unlink($upload_path . $file_name);

					$files = [];

					chdir($upload_path);

					foreach (glob($file_prefix . '*.jpg') as $file)
					{
						chmod($file, 0777);

						$files[] = $file;
					}

					if (!empty($watermark))
					{
						foreach ($files as $file)
						{
							$image = new Imagick();

							$image->readImage($absolute_path . $file);

							$text = new ImagickDraw();

							$text->setFontSize(20);
							$text->setFillColor('#000000');

							$image->annotateImage($text, 1100, 50, 0, $watermark);
							$image->writeImages($absolute_path . $file, true);
						}
					}
				}

				if (!empty($files))
				{
					$this->set_connection();

					foreach ($files as $file)
					{
						if (ftp_put($this->connection, '/Printer/' . $scanned_folder . '/' . $file, $absolute_path . $file, FTP_BINARY))
						{
							unlink($absolute_path . $file);
						}
					}
				}
			}
			else
			{
				$error[] = substr(mb_strtolower(strip_tags($this->upload->display_errors())), 0, -1);
			}
		}
		else
		{
			$error[] = 'избраната папка не съществува';
		}

		if (empty($error))
		{
			if ($is_pdf)
			{
				$response['success'] = 'успешно преместване и създаване на ' . ((count($files) > 1) ? 'файлове' : 'файл');
			}
			else
			{
				$response['success'] = 'успешно качване на изображение';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function logs($is_active_employee = 1)
	{
		$this->data['title'] = 'Логове';

		$this->data['path']['documents'] = 'Логове';
		$this->data['submenu']['documents'] = 'Документи';

		$is_active_employee = in_array($is_active_employee, [-1, 0, 1]) ? intval($is_active_employee) : 1;

		switch ($is_active_employee)
		{
			case -1:
				$employee_type = 'всички';
				$other_employee_types = [
					0 => 'неактивни',
					1 => 'активни'
				];
			break;

			case 0:
				$employee_type = 'неактивни';
				$other_employee_types = [
					-1 => 'всички',
					1 => 'активни'
				];
			break;

			case 1:
				$employee_type = 'активни';
				$other_employee_types = [
					-1 => 'всички',
					0 => 'неактивни'
				];
			break;
		}

		$this->data['is_active_employee'] = $is_active_employee;
		$this->data['employee_type'] = $employee_type;
		$this->data['other_employee_types'] = $other_employee_types;
		$this->data['is_precise_date'] = $this->isPreciseDate();

		$this->render();
	}

	public function get_log($is_active_employee)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('logs');
		}

		$error = [];

		$post = $this->input->post();

		$data = [];

		if (array_key_exists('from_date', $post) && array_key_exists('to_date', $post))
		{
			$from_date = trim($post['from_date']);
			$to_date = trim($post['to_date']);

			$is_precise_date = $this->isPreciseDate();

			if (empty($from_date) && empty($to_date))
			{
				$error[] = 'въведи дати';
			}
			else
			{
				$date_input_format = $is_precise_date ? 'd.m.Y H:i:s' : 'd.m.Y';

				if (empty($from_date))
				{
					$error[] = 'въведи начална дата';
				}
				else
				{
					$date_object = date_create_from_format($date_input_format, $from_date);
					$check_date = date_format($date_object, $date_input_format);

					if ($check_date === $from_date)
					{
						$data['from_date'] = date_format($date_object, 'U');
					}
					else
					{
						$error[] = 'начална дата е невалидна';
					}
				}

				if (empty($to_date))
				{
					$data['to_date'] = time();
				}
				else
				{
					$date_object = date_create_from_format($date_input_format, $to_date);
					$check_date = date_format($date_object, $date_input_format);

					if ($check_date === $to_date)
					{
						$data['to_date'] = date_format($date_object, 'U');
					}
					else
					{
						$error[] = 'крайна дата е невалидна';
					}
				}
			}

			if (empty($error))
			{
				if ($data['from_date'] === $data['to_date'])
				{
					$data['to_date'] += 86399;
				}

				if ($data['from_date'] > $data['to_date'])
				{
					$error[] = 'невалиден интервал от време';
				}
			}
		}

		$is_active_employee = in_array($is_active_employee, [-1, 0, 1]) ? intval($is_active_employee) : 1;

		if (empty($error))
		{
			$request = $this->documents_model->getLogs($is_active_employee, $data);

			if (is_array($request) && !empty($request))
			{
				$response['data'] = $request;
			}
			else
			{
				$response['error'][] = 'няма данни за този интервал';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function upload_camera_invoice()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$post = $this->input->post();

		$purchase = intval($post['purchase']);
		$base64 = explode(';base64,', $post['base64']);

		$microtime = str_replace('.', '', strval(microtime(true)));
		$path = "/var/www/be.jarcomputers.com/public_html/images/temp_sync/{$this->uid}-{$microtime}.jpg";

		if (file_put_contents($path, base64_decode($base64[1])))
		{
			chmod($path, 0777);

			$cropped_path = dirname($path) . '/cropped-' . basename($path);

			$image = new Imagick($path);

			$image->cropImage(3200, 2160, 0, 0);

			$image->rotateImage(new ImagickPixel('#ffffff'), 90);

			$image->resizeImage((2160 * 0.5), (3200 * 0.5), Imagick::FILTER_CATROM, 1, false);

			$image->writeImage($cropped_path);

			chmod($cropped_path, 0777);

			unlink($path);

			$data = [
				'type' => 'purchase_id',
				'id' => $purchase
			];

			$request = $this->documents_model->getPurchaseDocumentsByID($data);

			if (is_array($request) && !empty($request))
			{
				$this->set_connection();

				$date_object = date_create_from_format('Y-m-d H:i:s', $request[0]['pidate']);
				$folder_name = date_format($date_object, 'ym');
				$browse = ftp_nlist($this->connection, self::FTP['INVOICE_PATH'] . $folder_name . '/' . sprintf('I-%010s-D-*.jpg', $purchase));

				$page = 0;

				if (is_array($browse) && count($browse) > 0)
				{
					sort($browse);

					$page = intval(end(explode('-', basename(end($browse), '.jpg')))) + 1;
				}

				$file_name = sprintf('I-%010s-D-%010s-%s-%02s.jpg', $purchase, $request[0]['piid'], date_format($date_object, 'ymd'), $page);

				if ($this->check_parent_folder(self::FTP['INVOICE_PATH'], $folder_name))
				{
					if (ftp_put($this->connection, self::FTP['INVOICE_PATH'] . $folder_name . '/' . $file_name, $cropped_path, FTP_BINARY))
					{
						unlink($cropped_path);

						$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['SAVE_FILE']);
						$this->documents_model->setScannedValue('I', $purchase);

						$response['path'] = 'http://docs.jarnet' . self::FTP['INVOICE_PATH'] . $folder_name . '/' . $file_name;
					}
					else
					{
						$response['error'] = 'не може да се премести файлът';
					}
				}
				else
				{
					$response['error'] = 'главна директория не може да бъде създадена';
				}

				ftp_close($this->connection);
			}
		}
		else
		{
			$response['error'] = 'не може да бъде създаден скрийншот';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_delivery_document($path)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$type = trim($post['type']);

		if (empty($id))
		{
			$error[] = 'въведи номер';
		}
		else
		{
			$data = [];

			if (in_array($type, ['purchase_id', 'invoice_id', 'warranty_id', 'serial_number']))
			{
				$data['type'] = $type;

				if ($type === 'serial_number')
				{
					if (strlen($id) >= 4)
					{
						$data['id'] = strval($id);
					}
					else
					{
						$error[] = 'сериен номер трябва да е по-дълъг';
					}
				}
				else
				{
					if (is_numeric($id))
					{
						if ($type === 'warranty_id')
						{
							if (strlen($id) < 4)
							{
								$error[] = 'номер на гаранционна карта трябва да е по-дълъг';
							}
						}

						$data['id'] = intval($id);
					}
					else
					{
						$error[] = 'това търсене изисква номер да е число';
					}
				}
			}
			else
			{
				$error[] = 'невалидно търсене';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getPurchaseDocumentsByID($data);

				if (is_array($request) && !empty($request))
				{
					$result = [];

					$this->set_connection();

					foreach ($request as $value)
					{
						$scanned_invoice = 0;
						$scanned_warranty = 0;

						if ($value['piid'] && $value['pidate'] && $path === 'invoice')
						{
							$date_object = date_create_from_format('Y-m-d H:i:s', $value['pidate']);
							$folder_name = date_format($date_object, 'ym');
							$browse = ftp_nlist($this->connection, self::FTP['INVOICE_PATH'] . $folder_name . '/' . sprintf('I-%010s-D-*.jpg', $value['pid']));

							$scanned_invoice = is_array($browse) ? count($browse) : 0;
						}

						if ($value['warr'] && $value['pwdate'] && $path === 'warranty')
						{
							$date_object = date_create_from_format('Y-m-d H:i:s', $value['pwdate']);
							$folder_name = date_format($date_object, 'ym');
							$browse = ftp_nlist($this->connection, self::FTP['WARRANTY_PATH'] . $folder_name . '/' . sprintf('W-%010s-D-*.jpg', $value['pid']));

							$scanned_warranty = is_array($browse) ? count($browse) : 0;
						}

						$result[] = [
							'purchase_id' => $value['pid'] ?: '',
							'invoice_id' => $value['piid'] ?: '',
							'invoice_date' => $value['pidate'] ? substr($value['pidate'], 0, 10) : '',
							'warranty_id' => $value['warr'] ?: '',
							'warranty_date' => $value['pwdate'] ? substr($value['pwdate'], 0, 10) : '',
							'provider_id' => $value['provider_id'] ?: '',
							'provider' => $value['pclient'] ?: '',
							'invoice_sum' => $value['psum'] ?: $value['credit_sum'] . ' (КИ)',
							'scanned_invoice' => $scanned_invoice,
							'scanned_warranty' => $scanned_warranty
						];
					}

					ftp_close($this->connection);
				}
				else
				{
					$error[] = 'няма такава стойност';
				}
			}
		}

		if (empty($error))
		{
			$response['data'] = $result;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_delivery_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['document_type', 'purchase_number', 'document_number', 'document_page', 'image_path'];

		$document = ($post['document_type'] === 'I') ? 'фактура' : 'гаранционна карта';

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = "номер на покупка, номер на {$document} и страница на документ са задължителни";
		}
		else
		{
			$document_type = trim($post['document_type']);
			$purchase_number = trim($post['purchase_number']);
			$document_number = trim($post['document_number']);
			$document_page = trim($post['document_page']);
			$image_path = trim($post['image_path']);
			$image_copy = boolval(intval($post['image_copy']));
			$image_rewrite = boolval(intval($post['image_rewrite']));

			if (in_array($document_type, ['I', 'W']))
			{
				if (!is_numeric($purchase_number))
				{
					$error[] = 'номер на покупка трябва да е число';
				}

				if (is_numeric($document_number))
				{
					$type = ($document_type === 'I') ? 'piid' : 'warr';
				}
				else
				{
					$error[] = "номер на {$document} трябва да е число";
				}
			}
			else
			{
				$error[] = 'грешен тип на документ';
			}

			if (is_numeric($document_page))
			{
				$document_page = intval($document_page);

				if ($document_page > 0 && $document_page < 100)
				{
					$document_page = --$document_page;

					$document_page = str_pad($document_page, 2, 0, STR_PAD_LEFT);
				}
				else
				{
					$error[] = 'страница на документ трябва да бъде между 1 и 99';
				}
			}
			else
			{
				$error[] = 'страница на документ трябва да е число';
			}

			if (!$this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				$error[] = 'грешно предаден файл';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getPurchaseByDocumentsID($purchase_number, $type, $document_number);

				if (empty($request))
				{
					$error[] = 'грешни данни за документ';
				}
				else
				{
					$date = ($document_type === 'I') ? $request[0]['pidate'] : $request[0]['pwdate'];

					if (empty($date))
					{
						$error[] = 'документ няма дата';
					}
				}
			}
		}

		if (empty($error))
		{
			$parent_folder = ($document_type === 'I') ? self::FTP['INVOICE_PATH'] : self::FTP['WARRANTY_PATH'];

			$date_object = date_create_from_format('Y-m-d', $date);
			$folder_name = date_format($date_object, 'ym');

			if ($this->check_parent_folder($parent_folder, $folder_name))
			{
				$new_file = sprintf('%s-%010s-D-%010s-%s-%s.jpg', $document_type, $purchase_number, $document_number, date_format($date_object, 'ymd'), $document_page);

				$destination_folder = $parent_folder . $folder_name;

				if (!$this->check_file_exist($destination_folder, $new_file) || $image_rewrite)
				{
					$destination_file = $destination_folder . '/' . $new_file;

					if ($image_copy)
					{
						if ($this->copy_file($image_path, $destination_file))
						{
							$action_type = ($image_rewrite) ? self::ACTION_TYPE['FORCE_COPY_FILE'] : self::ACTION_TYPE['COPY_FILE'];

							$this->documents_model->setLogScannedDocuments($action_type);
							$this->documents_model->setScannedValue($document_type, $purchase_number);

							$response['success'] = 'успешно преместване и запазване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
					else
					{
						if ($this->move_file($image_path, $destination_file))
						{
							$action_type = ($image_rewrite) ? self::ACTION_TYPE['FORCE_SAVE_FILE'] : self::ACTION_TYPE['SAVE_FILE'];

							$this->documents_model->setLogScannedDocuments($action_type);
							$this->documents_model->setScannedValue($document_type, $purchase_number);

							$response['success'] = 'успешно преместване и изтриване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
				}
				else
				{
					$button_text = $image_copy ? 'презапиши и запази документ' : 'презапиши и изтрий документ';
					$button_data = $image_copy ? '1' : '0';

					$button = '<input type="button" name="force" value="' . $button_text . '" data-image-copy="' . $button_data . '">';

					$response['special_case'] = "сканиран документ с тази страница вече съществува\n{$button} за да изтриеш съществуващия файл,\nили промени страница на документ ако сте сигурни";
				}
			}
			else
			{
				$response['error'][] = 'главна директория не може да бъде създадена';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_purchase()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$invoice_id = trim($post['invoice_id']);
		$warranty_id = trim($post['warranty_id']);
		$invoice_date = trim($post['invoice_date']);
		$warranty_date = trim($post['warranty_date']);
		$purchase_id = trim($post['purchase_id']);
		$document_type = trim($post['document_type']);

		$data = [];

		if (is_numeric($purchase_id))
		{
			$purchase_id = intval($purchase_id);
		}
		else
		{
			$error[] = 'номер на покупка трябва да е число';
		}

		if (in_array($document_type, ['I', 'W']))
		{
			if ($document_type === 'I')
			{
				$warranty_id = null;
				$warranty_date = null;
			}
			else
			{
				$invoice_id = null;
				$invoice_date = null;
			}
		}
		else
		{
			$error[] = 'грешен тип на документ';
		}

		if (!empty($invoice_id))
		{
			if (is_numeric($invoice_id))
			{
				$data['Фактура ID'] = intval($invoice_id);
			}
			else
			{
				$error[] = 'номер на фактура трябва да е число';
			}
		}

		if (!empty($invoice_date))
		{
			$date_format = date_create_from_format('Y-m-d', $invoice_date);
			$check_date = date_format($date_format, 'Y-m-d');

			if ($check_date === $invoice_date)
			{
				$data['FДата'] = $invoice_date . ' 00:00:00';
			}
			else
			{
				$error[] = 'дата на фактура е невалидна';
			}
		}

		if (!empty($warranty_id))
		{
			if (is_numeric($warranty_id))
			{
				$data['ГК'] = intval($warranty_id);
			}
			else
			{
				$error[] = 'номер на гаранционна карта трябва да е число';
			}
		}

		if (!empty($warranty_date))
		{
			$date_format = date_create_from_format('Y-m-d', $warranty_date);
			$check_date = date_format($date_format, 'Y-m-d');

			if ($check_date === $warranty_date)
			{
				$data['WДата'] = $warranty_date . ' 00:00:00';
			}
			else
			{
				$error[] = 'дата на гаранционна карта е невалидна';
			}
		}

		if (empty($error))
		{
			if (count($data) !== 2)
			{
				$error[] = 'попълнете всички полета';
			}
		}

		if (empty($error))
		{
			$request = $this->documents_model->updatePurchaseByID($purchase_id, $data);

			if ($request)
			{
				$response['success'] = 'успешно обновена покупка';
			}
			else
			{
				$response['error'][] = 'възникна грешка';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_cost_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$type = trim($post['type']);

		if (empty($id))
		{
			$error[] = 'въведи номер или име';
		}
		else
		{
			$data = [];

			if (in_array($type, ['cost_id', 'invoice_id', 'client']))
			{
				$data['type'] = $type;

				if ($type === 'client')
				{
					if (strlen($id) >= 3)
					{
						$data['id'] = strval($id);
					}
					else
					{
						$error[] = 'име на клиент трябва да е по-дълго';
					}
				}
				else
				{
					if (is_numeric($id))
					{
						$data['id'] = intval($id);
					}
					else
					{
						$error[] = 'това търсене изисква номер да е число';
					}
				}
			}
			else
			{
				$error[] = 'невалидно търсене';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getCostData($data);

				if (empty($request))
				{
					$error[] = 'няма такава стойност';
				}
				else
				{
					$result = [];

					$this->set_connection();

					foreach ($request as $value)
					{
						$date_object = date_create_from_format('Y-m-d', $value['invoice_date']);
						$folder_name = date_format($date_object, 'ym');
						$browse = ftp_nlist($this->connection, self::FTP['COST_PATH'] . $folder_name . '/' . sprintf('I-%010s-R-*.jpg', $value['cost_id']));

						$result[] = [
							'repeat_invoice_id' => $value['repeat_invoice_id'],
							'cost_id' => $value['cost_id'] ?: '',
							'invoice_id' => $value['invoice_id'] ?: '',
							'invoice_date' => $value['invoice_date'] ?: '',
							'client_id' => $value['client_id'] ?: '',
							'client' => $value['client'] ?: '',
							'invoice_sum' => $value['invoice_sum'] ?: '',
							'scanned_document' => (is_array($browse) && count($browse) > 0) ? $browse : 0
						];
					}

					ftp_close($this->connection);
				}
			}
		}

		if (empty($error))
		{
			$response['data'] = $result;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_cost_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['cost_number', 'invoice_number', 'document_page', 'image_path', 'image_copy'];

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = 'номер на разход, номер на фактура и страница на документ са задължителни';
		}
		else
		{
			$cost_number = trim($post['cost_number']);
			$invoice_number = trim($post['invoice_number']);
			$document_page = trim($post['document_page']);
			$image_path = trim($post['image_path']);
			$image_copy = boolval(intval($post['image_copy']));

			if (is_numeric($cost_number))
			{
				$cost_number = intval($cost_number);
			}
			else
			{
				$error[] = 'номер на разход трябва да е число';
			}

			if (is_numeric($invoice_number))
			{
				$invoice_number = intval($invoice_number);
			}
			else
			{
				$error[] = 'номер на фактура трябва да е число';
			}

			if (is_numeric($document_page))
			{
				$document_page = intval($document_page);

				if ($document_page > 0 && $document_page < 100)
				{
					$document_page = --$document_page;

					$document_page = str_pad($document_page, 2, 0, STR_PAD_LEFT);
				}
				else
				{
					$error[] = 'страница на документ трябва да бъде между 1 и 99';
				}
			}
			else
			{
				$error[] = 'страница на документ трябва да е число';
			}

			if (!$this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				$error[] = 'грешно предаден файл';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getCostDateByID($cost_number, $invoice_number);

				if (empty($request))
				{
					$error[] = 'фактура няма дата или разход и фактура не съвпадат';
				}
				else
				{
					$date = $request;
				}
			}
		}

		if (empty($error))
		{
			$parent_folder = self::FTP['COST_PATH'];

			$date_object = date_create_from_format('Y-m-d', $date);
			$folder_name = date_format($date_object, 'ym');

			if ($this->check_parent_folder($parent_folder, $folder_name))
			{
				$new_file = sprintf('I-%010s-R-%010s-%s-%s.jpg', $cost_number, $invoice_number, date_format($date_object, 'ymd'), $document_page);

				$destination_folder = $parent_folder . $folder_name;
				$destination_file = $destination_folder . '/' . $new_file;

				if (!$this->check_file_exist($destination_folder, $new_file))
				{
					if ($image_copy)
					{
						if ($this->copy_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['COPY_FILE']);
							$this->documents_model->setCostScannedInvoice($cost_number);

							$response['success'] = 'успешно преместване и запазване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
					else
					{
						if ($this->move_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['SAVE_FILE']);
							$this->documents_model->setCostScannedInvoice($cost_number);

							$response['success'] = 'успешно преместване и изтриване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
				}
				else
				{
					$response['error'][] = "сканиран документ с тази страница вече съществува\nможе да я промените за да продължите";
				}
			}
			else
			{
				$response['error'][] = 'главна директория не може да бъде създадена';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		if (array_key_exists('success', $response))
		{
			$this->set_connection();

			$browse = ftp_nlist($this->connection, self::FTP['COST_PATH'] . $folder_name . '/' . sprintf('I-%010s-R-*.jpg', $cost_number));

			ftp_close($this->connection);

			$response['documents'] = (is_array($browse) && count($browse) > 0) ? $browse : [];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_profit_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$type = trim($post['type']);

		if (empty($id))
		{
			$error[] = 'въведи номер или име';
		}
		else
		{
			$data = [];

			if (in_array($type, ['profit_id', 'invoice_id', 'client']))
			{
				$data['type'] = $type;

				if ($type === 'client')
				{
					if (strlen($id) >= 3)
					{
						$data['id'] = strval($id);
					}
					else
					{
						$error[] = 'име на клиент трябва да е по-дълго';
					}
				}
				else
				{
					if (is_numeric($id))
					{
						$data['id'] = intval($id);
					}
					else
					{
						$error[] = 'това търсене изисква номер да е число';
					}
				}
			}
			else
			{
				$error[] = 'невалидно търсене';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getProfitData($data);

				if (empty($request))
				{
					$error[] = 'няма такава стойност';
				}
				else
				{
					$result = [];

					$this->set_connection();

					foreach ($request as $value)
					{
						$date_object = date_create_from_format('Y-m-d', $value['invoice_date']);
						$folder_name = date_format($date_object, 'ym');
						$browse = ftp_nlist($this->connection, self::FTP['PROFIT_PATH'] . $folder_name . '/' . sprintf('P-%010s-I-*.jpg', $value['profit_id']));

						$result[] = [
							'repeat_invoice_id' => $value['repeat_invoice_id'],
							'profit_id' => $value['profit_id'] ?: '',
							'invoice_id' => $value['invoice_id'] ?: '',
							'invoice_date' => $value['invoice_date'] ?: '',
							'client_id' => $value['client_id'] ?: '',
							'client' => $value['client'] ?: '',
							'invoice_sum' => $value['invoice_sum'] ?: '',
							'scanned_document' => (is_array($browse) && count($browse) > 0) ? $browse : 0
						];
					}

					ftp_close($this->connection);
				}
			}
		}

		if (empty($error))
		{
			$response['data'] = $result;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_profit_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['profit_number', 'invoice_number', 'document_page', 'image_path', 'image_copy'];

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = 'номер на приход, номер на фактура и страница на документ са задължителни';
		}
		else
		{
			$profit_number = trim($post['profit_number']);
			$invoice_number = trim($post['invoice_number']);
			$document_page = trim($post['document_page']);
			$image_path = trim($post['image_path']);
			$image_copy = boolval(intval($post['image_copy']));

			if (is_numeric($profit_number))
			{
				$profit_number = intval($profit_number);
			}
			else
			{
				$error[] = 'номер на приход трябва да е число';
			}

			if (is_numeric($invoice_number))
			{
				$invoice_number = intval($invoice_number);
			}
			else
			{
				$error[] = 'номер на фактура трябва да е число';
			}

			if (is_numeric($document_page))
			{
				$document_page = intval($document_page);

				if ($document_page > 0 && $document_page < 100)
				{
					$document_page = --$document_page;

					$document_page = str_pad($document_page, 2, 0, STR_PAD_LEFT);
				}
				else
				{
					$error[] = 'страница на документ трябва да бъде между 1 и 99';
				}
			}
			else
			{
				$error[] = 'страница на документ трябва да е число';
			}

			if (!$this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				$error[] = 'грешно предаден файл';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getProfitDateByID($profit_number, $invoice_number);

				if (empty($request))
				{
					$error[] = 'фактура няма дата или приход и фактура не съвпадат';
				}
				else
				{
					$date = $request;
				}
			}
		}

		if (empty($error))
		{
			$parent_folder = self::FTP['PROFIT_PATH'];

			$date_object = date_create_from_format('Y-m-d', $date);
			$folder_name = date_format($date_object, 'ym');

			if ($this->check_parent_folder($parent_folder, $folder_name))
			{
				$new_file = sprintf('P-%010s-I-%010s-%s-%s.jpg', $profit_number, $invoice_number, date_format($date_object, 'ymd'), $document_page);

				$destination_folder = $parent_folder . $folder_name;
				$destination_file = $destination_folder . '/' . $new_file;

				if (!$this->check_file_exist($destination_folder, $new_file))
				{
					if ($image_copy)
					{
						if ($this->copy_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['COPY_FILE']);
							$this->documents_model->setProfitScannedInvoice($profit_number);

							$response['success'] = 'успешно преместване и запазване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
					else
					{
						if ($this->move_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['SAVE_FILE']);
							$this->documents_model->setProfitScannedInvoice($profit_number);

							$response['success'] = 'успешно преместване и изтриване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
				}
				else
				{
					$response['error'][] = "сканиран документ с тази страница вече съществува\nможе да я промените за да продължите";
				}
			}
			else
			{
				$response['error'][] = 'главна директория не може да бъде създадена';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		if (array_key_exists('success', $response))
		{
			$this->set_connection();

			$browse = ftp_nlist($this->connection, self::FTP['PROFIT_PATH'] . $folder_name . '/' . sprintf('P-%010s-I-*.jpg', $profit_number));

			ftp_close($this->connection);

			$response['documents'] = (is_array($browse) && count($browse) > 0) ? $browse : [];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_payroll_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$type = trim($post['type']);

		if (empty($id))
		{
			$error[] = 'въведи номер';
		}
		else
		{
			$data = [];

			if (in_array($type, ['payroll_id', 'invoice_id', 'client']))
			{
				$data['type'] = $type;

				if ($type === 'client')
				{
					if (strlen($id) >= 3)
					{
						$data['id'] = strval($id);
					}
					else
					{
						$error[] = 'име на клиент трябва да е по-дълго';
					}
				}
				else
				{
					if (is_numeric($id))
					{
						$data['id'] = intval($id);
					}
					else
					{
						$error[] = 'това търсене изисква номер да е число';
					}
				}
			}
			else
			{
				$error[] = 'невалидно търсене';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getPayrollData($data);

				if (empty($request))
				{
					$error[] = 'няма такава стойност';
				}
				else
				{
					$result = [];

					$this->set_connection();

					foreach ($request as $value)
					{
						$date_object = date_create_from_format('Y-m-d', $value['invoice_date']);
						$folder_name = date_format($date_object, 'ym');
						$browse = ftp_nlist($this->connection, self::FTP['PAYROLL_PATH'] . $folder_name . '/' . sprintf('V-%010s-I-*.jpg', $value['payroll_id']));

						$result[] = [
							'repeat_invoice_id' => $value['repeat_invoice_id'],
							'payroll_id' => $value['payroll_id'] ?: '',
							'invoice_id' => $value['invoice_id'] ?: '',
							'invoice_date' => $value['invoice_date'] ?: '',
							'client_id' => $value['client_id'] ?: '',
							'client' => $value['client'] ?: '',
							'from_sum' => $value['from_sum'] ?: '',
							'to_sum' => $value['to_sum'] ?: '',
							'currency' => $value['currency'],
							'scanned_document' => (is_array($browse) && count($browse) > 0) ? $browse : 0
						];
					}

					ftp_close($this->connection);
				}
			}
		}

		if (empty($error))
		{
			$response['data'] = $result;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_payroll_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['payroll_number', 'invoice_number', 'document_page', 'image_path', 'image_copy'];

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = 'номер на ред, номер на фактура и страница на документ са задължителни';
		}
		else
		{
			$payroll_number = trim($post['payroll_number']);
			$invoice_number = trim($post['invoice_number']);
			$document_page = trim($post['document_page']);
			$image_path = trim($post['image_path']);
			$image_copy = boolval(intval($post['image_copy']));

			if (is_numeric($payroll_number))
			{
				$payroll_number = intval($payroll_number);
			}
			else
			{
				$error[] = 'номер на ред трябва да е число';
			}

			if (is_numeric($invoice_number))
			{
				$invoice_number = intval($invoice_number);
			}
			else
			{
				$error[] = 'номер на фактура трябва да е число';
			}

			if (is_numeric($document_page))
			{
				$document_page = intval($document_page);

				if ($document_page > 0 && $document_page < 100)
				{
					$document_page = --$document_page;

					$document_page = str_pad($document_page, 2, 0, STR_PAD_LEFT);
				}
				else
				{
					$error[] = 'страница на документ трябва да бъде между 1 и 99';
				}
			}
			else
			{
				$error[] = 'страница на документ трябва да е число';
			}

			if (!$this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				$error[] = 'грешно предаден файл';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getPayrollDateByID($payroll_number, $invoice_number);

				if (empty($request))
				{
					$error[] = 'фактура няма дата или ред и фактура не съвпадат';
				}
				else
				{
					$date = $request;
				}
			}
		}

		if (empty($error))
		{
			$parent_folder = self::FTP['PAYROLL_PATH'];

			$date_object = date_create_from_format('Y-m-d', $date);
			$folder_name = date_format($date_object, 'ym');

			if ($this->check_parent_folder($parent_folder, $folder_name))
			{
				$new_file = sprintf('V-%010s-I-%010s-%s-%s.jpg', $payroll_number, $invoice_number, date_format($date_object, 'ymd'), $document_page);

				$destination_folder = $parent_folder . $folder_name;
				$destination_file = $destination_folder . '/' . $new_file;

				if (!$this->check_file_exist($destination_folder, $new_file))
				{
					if ($image_copy)
					{
						if ($this->copy_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['COPY_FILE']);
							$this->documents_model->setPayrollScannedInvoice($payroll_number);

							$response['success'] = 'успешно преместване и запазване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
					else
					{
						if ($this->move_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['SAVE_FILE']);
							$this->documents_model->setPayrollScannedInvoice($payroll_number);

							$response['success'] = 'успешно преместване и изтриване на този документ';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
				}
				else
				{
					$response['error'][] = "сканиран документ с тази страница вече съществува\nможе да я промените за да продължите";
				}
			}
			else
			{
				$response['error'][] = 'главна директория не може да бъде създадена';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		if (array_key_exists('success', $response))
		{
			$this->set_connection();

			$browse = ftp_nlist($this->connection, self::FTP['PAYROLL_PATH'] . $folder_name . '/' . sprintf('V-%010s-I-*.jpg', $payroll_number));

			ftp_close($this->connection);

			$response['documents'] = (is_array($browse) && count($browse) > 0) ? $browse : [];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_other_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$type = trim($post['type']);

		if (empty($id))
		{
			$error[] = 'въведи номер';
		}
		else
		{
			$data = [];

			if (in_array($type, ['purchase_id', 'sale_id']))
			{
				$data['type'] = $type;

				if (is_numeric($id))
				{
					$data['id'] = intval($id);
				}
				else
				{
					$error[] = 'номер трябва да е число';
				}
			}
			else
			{
				$error[] = 'невалидно търсене';
			}

			if (empty($error))
			{
				$request = $this->documents_model->getOtherData($data);

				if (empty($request))
				{
					$error[] = 'няма такава стойност';
				}
				else
				{
					if (empty($request['invoice_date']))
					{
						$error[] = 'няма дата на документ';
					}
					else
					{
						$this->set_connection();

						$date_object = date_create_from_format('Y-m-d', $request['invoice_date']);
						$folder_name = date_format($date_object, 'ym');

						switch ($type)
						{
							case 'purchase_id':
								$browse = ftp_nlist($this->connection, self::FTP['OTHER_PURCHASE_PATH'] . $folder_name . '/' . sprintf('B-%010s-D-*.jpg', $id));
							break;

							case 'sale_id':
								$browse = ftp_nlist($this->connection, self::FTP['OTHER_SALE_PATH'] . $folder_name . '/' . sprintf('S-%010s-D-*.jpg', $id));
							break;
						}

						$request['scanned_document'] = is_array($browse) ? count($browse) : 0;

						ftp_close($this->connection);
					}
				}
			}
		}

		if (empty($error))
		{
			$response['data'] = $request;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_other_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['other_number', 'invoice_number', 'document_page', 'image_path', 'image_copy'];

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = 'номер на покупка или продажба, номер на фактура и страница на документ са задължителни';
		}
		else
		{
			$other_number = trim($post['other_number']);
			$invoice_number = trim($post['invoice_number']);
			$document_page = trim($post['document_page']);
			$image_path = trim($post['image_path']);
			$image_copy = boolval(intval($post['image_copy']));

			$data = [];

			if (is_numeric($other_number))
			{
				$other_number = intval($other_number);

				$data['id'] = $other_number;
			}
			else
			{
				$error[] = 'номер на покупка или продажба трябва да е число';
			}

			if (is_numeric($invoice_number))
			{
				$invoice_number = intval($invoice_number);
			}
			else
			{
				$error[] = 'номер на фактура трябва да е число';
			}

			if (is_numeric($document_page))
			{
				$document_page = intval($document_page);

				if ($document_page > 0 && $document_page < 100)
				{
					$document_page = --$document_page;

					$document_page = str_pad($document_page, 2, 0, STR_PAD_LEFT);
				}
				else
				{
					$error[] = 'страница на документ трябва да бъде между 1 и 99';
				}
			}
			else
			{
				$error[] = 'страница на документ трябва да е число';
			}

			if (empty($error))
			{
				$data['type'] = 'purchase_id';

				$request_purchase = $this->documents_model->getOtherData($data);

				$data['type'] = 'sale_id';

				$request_sale = $this->documents_model->getOtherData($data);

				$request = array_merge($request_purchase, $request_sale);

				if (empty($request))
				{
					$error[] = 'няма такава стойност за покупка или продажба';
				}
				else
				{
					$data['type'] = (array_key_exists('provider', $request)) ? 'purchase_id' : 'sale_id';
					$image_type = $data['type'];

					if ($request['invoice_number'] != $invoice_number)
					{
						if ($image_type === 'sale_id')
						{
							if (empty($request['invoice_number']))
							{
								$invoice_number = str_repeat(0, 10);
							}
							else
							{
								$error[] = 'номер на продажба несъответства с номер на фактура';
							}
						}
						else
						{
							$error[] = 'номер на покупка несъответства с номер на фактура';
						}
					}

					if (empty($request['invoice_date']))
					{
						$error[] = 'документ по фактура няма дата';
					}
					else
					{
						$date = $request['invoice_date'];
					}
				}
			}

			if (!$this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				$error[] = 'грешно предаден файл';
			}
		}

		if (empty($error))
		{
			$parent_folder = ($image_type === 'purchase_id') ? self::FTP['OTHER_PURCHASE_PATH'] : self::FTP['OTHER_SALE_PATH'];
			$file_prefix = ($image_type === 'purchase_id') ? 'B' : 'S';

			$date_object = date_create_from_format('Y-m-d', $date);
			$folder_name = date_format($date_object, 'ym');

			if ($this->check_parent_folder($parent_folder, $folder_name))
			{
				$new_file = sprintf($file_prefix . '-%010s-D-%010s-%s-%s.jpg', $other_number, $invoice_number, date_format($date_object, 'ymd'), $document_page);

				$destination_folder = $parent_folder . $folder_name;
				$destination_file = $destination_folder . '/' . $new_file;

				if (!$this->check_file_exist($destination_folder, $new_file))
				{
					if ($image_copy)
					{
						if ($this->copy_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['COPY_FILE']);
							$this->documents_model->setOtherScannedDocument($data);

							$response['success'] = 'успешно преместване и запазване на този документ';
							$response['type'] = ($image_type === 'purchase_id') ? 'purchase' : 'sale';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
					else
					{
						if ($this->move_file($image_path, $destination_file))
						{
							$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['SAVE_FILE']);
							$this->documents_model->setOtherScannedDocument($data);

							$response['success'] = 'успешно преместване и изтриване на този документ';
							$response['type'] = ($image_type === 'purchase_id') ? 'purchase' : 'sale';
						}
						else
						{
							$response['error'][] = 'файлът не може да бъде качен';
						}
					}
				}
				else
				{
					$response['error'][] = "сканиран документ с тази страница вече съществува\nможе да я промените за да продължите";
				}
			}
			else
			{
				$response['error'][] = 'главна директория не може да бъде създадена';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_image_document_data()
	{
		$post = $this->input->post();

		$path = trim($post['path']);

		$result = shell_exec(FCPATH . '../s.sh ' . $path);

		$response['message'] = $result;

		$break_line = strpos($result, '=== D E B U G =================================');

		if ($break_line > 0)
		{
			$json = json_decode(substr($result, 0, $break_line), true);

			switch ($json['doc_type_id'])
			{
				case 3:
					$purchase = count($json['documents_id']);

					if ($purchase == 1)
					{
						$response['action']['purchase']['id'] = $json['documents_id'][0];
					}
					else
					{
						if ($json['invoice_number'] > 0)
						{
							$response['action']['purchase']['invoice_number'] = $json['invoice_number'];
						}
					}
				break;

				case 1:
					$cost = count($json['documents_id']);

					if ($cost == 1)
					{
						$response['action']['cost']['id'] = $json['documents_id'][0];
					}
					else
					{
						if ($json['invoice_number'] > 0)
						{
							$response['action']['cost']['invoice_number'] = $json['invoice_number'];
						}
						else
						{
							$response['action']['cost']['client'] = $json['supplier_name'];
						}
					}
				break;

				default:
				$response['error'] = 'няма открити данни';
			}
		}
		else
		{
			$response['error'] = 'няма открити данни';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function gallery($purchase_id = null)
	{
		if (is_numeric($purchase_id))
		{
			$purchase_id = intval($purchase_id);
		}
		else
		{
			redirect('documents');
		}

		$this->data['title'] = 'Галерия сканирани документи за покупка: ' . $purchase_id;

		$data = [
			'type' => 'purchase_id',
			'id' => $purchase_id
		];

		$result = $this->documents_model->getPurchaseDocumentsByID($data);

		$this->data['purchase_error'] = false;

		if (empty($result))
		{
			$this->data['purchase_error'] = true;
		}
		else
		{
			$this->set_connection();

			$date_object = date_create_from_format('Y-m-d H:i:s', $result[0]['pidate']);
			$folder_name = date_format($date_object, 'ym');
			$invoice_files = ftp_nlist($this->connection, self::FTP['INVOICE_PATH'] . $folder_name . '/' . sprintf('I-%010s-D-*.jpg', $purchase_id)) ?: [];

			$date_object = date_create_from_format('Y-m-d H:i:s', $result[0]['pwdate']);
			$folder_name = date_format($date_object, 'ym');
			$warranty_files = ftp_nlist($this->connection, self::FTP['WARRANTY_PATH'] . $folder_name . '/' . sprintf('W-%010s-D-*.jpg', $purchase_id)) ?: [];

			ftp_close($this->connection);

			$this->data['files'] = [
				'invoice' => $invoice_files,
				'warranty' => $warranty_files
			];
		}

		$this->load->view('documents/gallery', $this->data);
	}

	public function other_gallery($type, $id)
	{
		if (in_array($type, ['purchase', 'sale']))
		{
			$error_message = '';
			$browse_directory = [];
			$type_name = ($type === 'purchase') ? 'покупка' : 'продажба';

			if (is_numeric($id))
			{
				$data = [];

				$data['id'] = $id;

				switch ($type)
				{
					case 'purchase':
						$this->data['title'] = 'Други документи за покупка &numero; ' . $id;

						$data['type'] = 'purchase_id';

						$parent_folder = self::FTP['OTHER_PURCHASE_PATH'];
						$file_prefix = 'B';
					break;

					case 'sale':
						$this->data['title'] = 'Други документи за продажба &numero; ' . $id;

						$data['type'] = 'sale_id';

						$parent_folder = self::FTP['OTHER_SALE_PATH'];
						$file_prefix = 'S';
					break;
				}

				$request = $this->documents_model->getOtherData($data);

				if (empty($request))
				{
					$error_message = 'няма такава стойност за ' . $type_name;
				}
				else
				{
					if (empty($request['invoice_date']))
					{
						$error_message = $type_name . ' няма дата';
					}
					else
					{
						$date_object = date_create_from_format('Y-m-d', $request['invoice_date']);
						$folder_name = date_format($date_object, 'ym');

						$file = $file_prefix . '-' . str_pad($id, 10, 0, STR_PAD_LEFT);

						$this->set_connection();

						$browse_directory = ftp_nlist($this->connection, $parent_folder . $folder_name . '/' . $file . '*.jpg');

						ftp_close($this->connection);

						if (empty($browse_directory))
						{
							$error_message = 'няма други документи за тази ' . $type_name;
						}
					}
				}
			}
			else
			{
				$error_message = 'грешен номер на ' . $type_name;
			}

			$this->data['error_message'] = $error_message;
			$this->data['browse_directory'] = $browse_directory;
			$this->data['type_name'] = $type_name;
			$this->data['is_outside'] = $this->is_outside;

			$this->load->view('documents/other_gallery', $this->data);
		}
		else
		{
			redirect('');
		}
	}

	public function delete_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$image_path = trim($post['image_path']);

		if (empty($image_path))
		{
			$error[] = 'не сте подали файл';
		}
		else
		{
			if ($this->check_file_exist(dirname($image_path), basename($image_path)))
			{
				if ($this->delete_file($image_path))
				{
					$this->documents_model->setLogScannedDocuments(self::ACTION_TYPE['DELETE_FILE']);
				}
				else
				{
					$error[] = 'файлът не може да бъде изтрит';
				}
			}
			else
			{
				$error[] = 'файлът не съществува';
			}
		}

		if (empty($error))
		{
			$response['success'] = 'успешно изтриване на файл';
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function sort_scanned_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$required_fields = ['old_sort_id', 'new_sort_id', 'purchase_id', 'type'];

		if ($this->checkEmptyField($post, $required_fields))
		{
			$error[] = 'грешно подадени данни';
		}
		else
		{
			$old_sort_id = $post['old_sort_id'];
			$new_sort_id = $post['new_sort_id'];
			$purchase_id = intval($post['purchase_id']);
			$type = $post['type'];

			$data = [
				'type' => 'purchase_id',
				'id' => $purchase_id
			];

			$request = $this->documents_model->getPurchaseDocumentsByID($data);

			if (empty($request))
			{
				$error[] = 'няма данни за покупка';
			}
			else
			{
				$this->set_connection();

				switch ($type)
				{
					case 'invoice':
						$date_object = date_create_from_format('Y-m-d H:i:s', $request[0]['pidate']);
						$folder_name = date_format($date_object, 'ym');
						$files = ftp_nlist($this->connection, self::FTP['INVOICE_PATH'] . $folder_name . '/' . sprintf('I-%010s-D-*.jpg', $purchase_id)) ?: [];
					break;

					case 'warranty':
						$date_object = date_create_from_format('Y-m-d H:i:s', $request[0]['pwdate']);
						$folder_name = date_format($date_object, 'ym');
						$files = ftp_nlist($this->connection, self::FTP['WARRANTY_PATH'] . $folder_name . '/' . sprintf('W-%010s-D-*.jpg', $purchase_id)) ?: [];
					break;
				}

				if (empty($files))
				{
					$error[] = 'няма файлове от този тип';
				}
				else
				{
					$old = intval(substr($old_sort_id, 1));
					$new = intval(substr($new_sort_id, 1));

					if ($old > $new)
					{
						$full_range = range($new, $old);
					}
					else if ($old < $new)
					{
						$full_range = range($old, $new);
					}
					else
					{
						$error[] = 'не е извършено сортиране';
					}

					sort($files);

					$paging = [];

					foreach ($files as $value)
					{
						$page = end(explode('-', basename($value, '.jpg')));

						$paging["_{$page}"] = $value;
					}

					$switch = [];

					$switch[$paging[$old_sort_id]] = self::FTP['BROWSE_PATH'] . basename($paging[$new_sort_id]);

					$exisiting_range = [];

					foreach ($full_range as $presumed_page)
					{
						$page = '_' . str_pad($presumed_page, 2, '0', STR_PAD_LEFT);

						if (in_array($page, array_keys($paging)))
						{
							$exisiting_range[$presumed_page] = $page;
						}
					}

					foreach ($exisiting_range as $page => $page_key)
					{
						if ($old > $new)
						{
							if ($page < $old)
							{
								$switch[$paging[$page_key]] = self::FTP['BROWSE_PATH'] . basename($paging[$exisiting_range[$page + 1]]);
							}
						}
						else
						{
							if ($page > $old)
							{
								$switch[$paging[$page_key]] = self::FTP['BROWSE_PATH'] . basename($paging[$exisiting_range[$page - 1]]);
							}
						}
					}

					foreach ($switch as $source => $target)
					{
						$this->move_file($source, $target);
					}

					foreach ($switch as $source => $target)
					{
						$this->move_file($target, dirname($source) . '/' . basename($target));
					}

					ftp_close($this->connection);
				}
			}
		}

		if (empty($error))
		{
			$response['success'] = null;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function rotate_scanned_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$image_path = trim($post['image_path']);

		if (empty($image_path))
		{
			$error[] = 'не сте подали файл';
		}
		else
		{
			$url = 'http://docs.jarnet/' . $image_path;

			$save_path = DEVELOPMENT ? '/var/www/html/git/livebe/be/public_html/dompdf/' : '/var/www/be.jarcomputers.com/public_html/pdf/';

			$file_name = $save_path . basename($url);
			$rotate_file_name = $save_path . 'ROTATE-' . basename($url);

			copy($url, $file_name);

			chmod($file_name, 0777);

			$image = new Imagick($file_name);

			$image->rotateImage(new ImagickPixel('#ffffff'), 90);

			$image->writeImage($rotate_file_name);

			chmod($rotate_file_name, 0777);

			unlink($file_name);

			$this->set_connection();

			if (ftp_put($this->connection, $image_path, $rotate_file_name, FTP_BINARY))
			{
				ftp_close($this->connection);

				unlink($rotate_file_name);
			}
			else
			{
				$error[] = 'неуспешно преместване на файл';
			}
		}

		if (empty($error))
		{
			$response['success'] = null;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function delete_scanned_document()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$image_path = trim($post['image_path']);

		if (empty($image_path))
		{
			$error[] = 'не сте подали файл';
		}
		else
		{
			$destination_file = dirname($image_path) . '/' . self::SKIP_FILE . time() . '-' . basename($image_path);

			if ($this->move_file($image_path, $destination_file))
			{
				$folder = basename(dirname(dirname($image_path)));
				$date_folder = basename(dirname($image_path));
				$document_match = substr(basename($image_path), 0, 14);
				$id = ltrim(preg_replace('/[^0-9]/', '', $document_match), '0');

				switch ($folder)
				{
					case 'fakturi':
						$files = $this->browse_match_file(self::FTP['INVOICE_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Покупки" SET scanned_invoice = 0 WHERE "Покупка ID" = ' . $id);
						}
					break;

					case 'warranty':
						$files = $this->browse_match_file(self::FTP['WARRANTY_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Покупки" SET scanned_warranty = 0 WHERE "Покупка ID" = ' . $id);
						}
					break;

					case 'razhodi':
						$files = $this->browse_match_file(self::FTP['COST_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Разходи" SET scanned_invoice = 0 WHERE "ID" = ' . $id);
						}
					break;

					case 'prihodi':
						$files = $this->browse_match_file(self::FTP['PROFIT_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Приходи" SET scanned_invoice = 0 WHERE "ID" = ' . $id);
						}
					break;

					case 'vedomosti':
						$files = $this->browse_match_file(self::FTP['PAYROLL_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Обмяна валута" SET scanned_invoice = 0 WHERE "ID" = ' . $id);
						}
					break;

					case 'buy':
						$files = $this->browse_match_file(self::FTP['OTHER_PURCHASE_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Покупки" SET scanned_purchase = 0 WHERE "Покупка ID" = ' . $id);
						}
					break;

					case 'sale':
						$files = $this->browse_match_file(self::FTP['OTHER_SALE_PATH'] . $date_folder, $document_match);

						if (empty($files))
						{
							$this->db->query('UPDATE public."Продажби" SET scanned_sale = 0 WHERE "Продажба ID" = ' . $id);
						}
					break;
				}
			}
			else
			{
				$error[] = 'файлът не може да бъде изтрит';
			}
		}

		if (empty($error))
		{
			$response['success'] = null;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function download_scanned_document()
	{
		$escaped_string = str_replace(' ', '%20', $_GET['path']);

		$url = 'http://docs.jarnet/' . $escaped_string;

		$file = basename($escaped_string);

		header('content-type:application/octet-stream');
		header('content-transfer-encoding:binary');
		header('content-disposition:attachment;filename=' . $file);

		readfile($url);
	}

	public function print_scanned_document()
	{
		$this->data['image'] = 'http://docs.jarnet/' . $_GET['path'];

		$this->load->view('documents/print', $this->data);
	}

	public function image()
	{
		if (preg_match('/Personal/', $_GET['path']))
		{
			$escaped_string = str_replace(' ', '%20', $_GET['path']);

			$url = 'http://docs.jarnet/' . $escaped_string;

			$file = basename($escaped_string);
			$extension = explode('.', $file);
			$extension = $extension[count($extension) - 1];

			switch ($extension)
			{
				case 'jpg':
					header('content-type:image/jpeg');
				break;

				case 'pdf':
					header('content-type:application/pdf');
				break;

				default:
					header('content-type:application/octet-stream');
					header('content-transfer-encoding:binary');
					header('content-disposition:attachment;filename=' . $file);

					readfile($url);
			}

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);

			$data = curl_exec($curl);
			curl_close($curl);

			echo $data;
		}
		else
		{
			header('content-type:image/jpeg');

			$access = '';

			if (!isset($_GET['hotlink']))
			{
				$access = 'ftp://' . self::FTP['USERNAME'] . ':' . self::FTP['PASSWORD'] . '@' . self::FTP['URI'];
			}

			echo file_get_contents($access . $_GET['path']);
		}
	}

	private function set_connection()
	{
		$this->connection = ftp_connect(self::FTP['URI']);

		@ftp_login($this->connection, self::FTP['USERNAME'], self::FTP['PASSWORD']);

		ftp_pasv($this->connection, true);
	}

	private function browse($directory = null)
	{
		$this->set_connection();

		$browse = (is_null($directory)) ? self::FTP['BROWSE_PATH'] : self::FTP['BROWSE_PATH'] . $directory;

		$path = ftp_nlist($this->connection, $browse);

		$path_info = ftp_rawlist($this->connection, $browse);

		if (is_null($directory))
		{
			ftp_close($this->connection);

			$folders = [];

			foreach ($path_info as $key => $value)
			{
				if ($value[0] === 'd')
				{
					$folders[] = basename($path[$key]);
				}
			}

			return $folders;
		}
		else
		{
			$files = [];
			$staging = [];

			$new_page = 0;

			foreach ($path_info as $key => $value)
			{
				if ($value[0] === '-')
				{
					$file_size = ftp_size($this->connection, $path[$key]);

					if ($file_size < 1024)
					{
						continue;
					}

					$file = basename($path[$key]);

					$file_extension = pathinfo($file, PATHINFO_EXTENSION);

					if ($file_extension === 'jpg' && !boolval(preg_match('/^' . self::SKIP_FILE . '/', $file)))
					{
						$new_format = preg_match('/image\d{4}-\d{2}-\d{2}-\d{6}-/', $file);

						if ($new_format)
						{
							$page = end(explode('-', basename($file, '.jpg')));

							$unix_time = date_format(date_create_from_format('Y-m-d-His', substr($file, 5, 17)), 'U');

							$staging[date_format(date_create_from_format('U', $unix_time), 'Y-m-d')][$unix_time][$page] = $path[$key];
						}
						else
						{
							++$new_page;

							$unix_time = ftp_mdtm($this->connection, $path[$key]);

							$staging[date_format(date_create_from_format('U', $unix_time), 'Y-m-d')][$unix_time][$new_page] = $path[$key];
						}
					}
				}
			}

			ftp_close($this->connection);

			foreach ($staging as &$value)
			{
				ksort($value);
			}

			foreach ($staging as &$value)
			{
				foreach ($value as &$array)
				{
					ksort($array);
				}
			}

			foreach ($staging as $date => $unix_array)
			{
				foreach ($unix_array as $values)
				{
					foreach ($values as $image)
					{
						$files[$date][] = $image;
					}
				}
			}

			ksort($files);

			return $files;
		}
	}

	private function browse_match_file($directory, $match_file)
	{
		$this->set_connection();

		$path = ftp_nlist($this->connection, $directory);

		ftp_close($this->connection);

		$files = [];

		foreach ($path as $value)
		{
			$file = basename($value);

			$file_extension = pathinfo($file, PATHINFO_EXTENSION);

			if ($file_extension === 'jpg' && boolval(preg_match('/^' . $match_file . '/', $file)))
			{
				$files[] = $value;
			}
		}

		return $files;
	}

	private function check_parent_folder($parent_folder, $child_folder)
	{
		$this->set_connection();

		if (is_dir('ftp://' . self::FTP['USERNAME'] . ':' . self::FTP['PASSWORD'] . '@' . self::FTP['URI'] . $parent_folder . $child_folder))
		{
			return true;
		}
		else
		{
			ftp_chdir($this->connection, $parent_folder);

			if (ftp_mkdir($this->connection, $child_folder))
			{
				return true;
			}
		}

		return false;
	}

	private function check_file_exist($directory, $file)
	{
		$this->set_connection();

		$browse_directory = ftp_nlist($this->connection, $directory);

		ftp_close($this->connection);

		$files = [];

		foreach ($browse_directory as $value)
		{
			$files[] = basename($value);
		}

		if (in_array($file, $files))
		{
			return true;
		}

		return false;
	}

	private function move_file($old_file, $new_file)
	{
		$this->set_connection();

		if (ftp_rename($this->connection, $old_file, $new_file))
		{
			return true;
		}

		return false;
	}

	private function delete_file($image_path)
	{
		$this->set_connection();

		if (ftp_delete($this->connection, $image_path))
		{
			return true;
		}

		return false;
	}

	private function copy_file($from_source, $to_source)
	{
		$proceed = true;

		if ($this->check_file_exist(dirname($to_source), basename($to_source)))
		{
			$proceed = $this->delete_file($to_source);
		}

		if ($proceed)
		{
			$base_uri = 'ftp://' . self::FTP['USERNAME'] . ':' . self::FTP['PASSWORD'] . '@' . self::FTP['URI'];

			if (copy($base_uri . $from_source, $base_uri . $to_source))
			{
				return true;
			}
		}

		return false;
	}

	private function isPreciseDate()
	{
		return in_array($this->uid, $this->allowed_precise_date_users);
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

	private function checkEmptyField($post, $required_fields)
	{
		$is_empty = false;

		foreach ($required_fields as $value)
		{
			if ($post[$value] === '')
			{
				$is_empty = true;

				break;
			}
		}

		return $is_empty;
	}

	public function invoice_en($invoice_id, $reload = 0)
	{
		if (!is_numeric($invoice_id))
		{
			show_404();
		}

		$this->load->model('spravcho_model');

		$data = $this->spravcho_model->getTemplate(5, intval($invoice_id))['data'];

		$data['details'] = $this->spravcho_model->getDetailsFromInvoice(intval($invoice_id));

		$query = '
		SELECT translated
		FROM public."FФактури"
		WHERE "Номер документ" = ' . $invoice_id;

		$json = $this->db->query($query)->row_array()['translated'];

		$data['json'] = '{}';

		if (!empty($json) && !$reload)
		{
			$data['json'] = $json;
		}

		$this->data['data'] = $data;

		$this->render('main1c');
	}

	public function get_invoice_number()
	{
		if ($this->input->is_ajax_request())
		{
			$post = $this->input->post();

			$type = trim($post['type']);
			$id = intval($post['id']);

			$this->load->model('spravcho_model');

			$request = $this->spravcho_model->getInvoiceNumberByType($type, $id);

			if (empty($request))
			{
				$response['error'] = 'този номер няма фактура';
			}
			else
			{
				$response['number'] = $request;
			}

			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
	}

	public function set_invoice_en()
	{
		if ($this->input->is_ajax_request())
		{
			$post = $this->input->post();

			$query = '
			UPDATE public."FФактури"
			SET translated = \'' . str_replace("'", "''", $post['invoice_en']) . '\'
			WHERE "Номер документ" = ' . json_decode($post['invoice_en'], true)['id'];

			$this->db->query($query);

			$_SESSION['invoice_en'] = json_decode($post['invoice_en'], true);

			if (empty($_SESSION['invoice_en']))
			{
				$response['error'] = 'грешка';
			}
			else
			{
				$response['success'] = true;
			}

			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
	}

	public function print_invoice_en()
	{
		if (isset($_SESSION['invoice_en']) && !empty($_SESSION['invoice_en']))
		{
			$data = $_SESSION['invoice_en'];

			require_once(dirname(__DIR__) . '/third_party/tcpdf/tcpdf.php');

			// create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			// set document information
			$pdf->SetTitle('Invoice');

			$pdf->setPrintHeader(false);
			//$pdf->setPrintFooter(false);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, 12, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(6);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set font
			$pdf->SetFont('freesans', '', 10);

			$pdf->AddPage();

			$print = '<span style="font-family:pdfatimes; font-size:45px; font-weight:normal; text-align:center;">JAR Computers</span><p style="font-family:helvetica; font-size:10px; font-weight:normal; text-align:center;">Sofia, 83 Gyueshevo st., Business centre Serdika, tel: (+359 2) 822 1000</p><p style="font-family:helvetica; font-size:11px; font-weight:bold; line-height:0; text-align:center;">www.jarcomputers.com</p>';

			if (empty($data['credit_id']))
			{
				$invoice_info = 'Invoice No. ' . $data['id'] . ' / ' . $data['id_date'] . '&nbsp;&nbsp;&nbsp;ORIGINAL';
			}
			else
			{
				$invoice_info = 'Credit Note No. ' . $data['credit_id'] . ' / ' . $data['credit_date'] . '&nbsp;&nbsp;&nbsp;ORIGINAL<br>to Invoice No. ' . $data['id'] . ' / ' . $data['id_date'];
			}

			$print .= '<p style="font-size:11px; font-weight:normal; text-align:center;">' . $invoice_info . '</p><p></p>';

			$client = '';

			if ($data['client'])
			{
				$client .= '<tr><td>Client: <b>' . $data['client'] . '</b></td></tr>';
			}

			if ($data['client_address'])
			{
				$client .= '<tr><td>Address: <b>' . $data['client_address'] . '</b></td></tr>';
			}

			if ($data['client_registration_number'])
			{
				$client .= '<tr><td>Company Registration No.: <b>' . $data['client_registration_number'] . '</b></td></tr>';
			}

			if ($data['client_vat_number'])
			{
				$client .= '<tr><td>VAT-No.: <b>' . $data['client_vat_number'] . '</b></td></tr>';
			}

			if ($data['client_mol'])
			{
				$client .= '<tr><td>Responsible for the operation: <b>' . $data['client_mol'] . '</b></td></tr>';
			}

			$supplier = '';

			if ($data['supplier'])
			{
				$supplier .= '<tr><td>Supplier: <b>' . $data['supplier'] . '</b></td></tr>';
			}

			if ($data['supplier_address'])
			{
				$supplier .= '<tr><td>Address: <b>' . $data['supplier_address'] . '</b></td></tr>';
			}

			if ($data['company_id'])
			{
				$supplier .= '<tr><td>Company ID: <b>' . $data['company_id'] . '</b></td></tr>';
			}

			if ($data['supplier_vat_number'])
			{
				$supplier .= '<tr><td>VAT-No.: <b>' . $data['supplier_vat_number'] . '</b></td></tr>';
			}

			if ($data['bank_account'])
			{
				$supplier .= '<tr><td>Bank Account: <b>' . $data['bank_account'] . '</b></td></tr>';
			}

			if ($data['iban'])
			{
				$supplier .= '<tr><td>IBAN: <b>' . $data['iban'] . '</b></td></tr>';
			}

			if ($data['swift_code'])
			{
				$supplier .= '<tr><td>SWIFT Code: <b>' . $data['swift_code'] . '</b></td></tr>';
			}

			if ($data['supplier_mol'])
			{
				$supplier .= '<tr><td>Responsible for the operation: <b>' . $data['supplier_mol'] . '</b></td></tr>';
			}

			$print .= '<table cellspacing="0" cellpadding="0" border="0"><tr>
			<td width="50%"><table cellspacing="0" cellpadding="0" border="0">' . $client . '</table></td>
			<td width="50%"><table cellspacing="0" cellpadding="0" border="0">' . $supplier . '</table></td>
			</tr></table><p></p>';

			$currency_symbol = 'лв.';

			switch ($data['currency'])
			{
				case 'eur':
					$currency_symbol = '&#8364;';
				break;

				case 'gbp':
					$currency_symbol = '&#163;';
				break;

				case 'usd':
					$currency_symbol = '&#36;';
				break;
			}

			$tbody = '';
			$total = 0;

			foreach ($data['details'] as $value)
			{
				$quantity = intval($value['quantity']);
				$price = number_format($value['price'], 2, '.', '');
				$total_price = number_format($value['total'], 2, '.', '');

				$total += $total_price;

				$tbody .= '<tr>
				<td align="center">' . $value['module'] . '</td>
				<td>' . $value['name'] . '</td>
				<td align="right"><b>' . $quantity . '</b></td>
				<td align="right"><b>' . $price . '</b></td>
				<td align="right"><b>' . number_format($total_price, 2, '.', '') . '</b></td>
				</tr>';
			}

			$print .= '<table cellspacing="0" cellpadding="1" border="0.1">
			<tr>
			<th width="19%" align="center">
			<div style="font-size:5px;">&nbsp;</div>
			<b>Module</b>
			</th>
			<th width="55%" align="center">
			<div style="font-size:5px;">&nbsp;</div>
			<b>Model</b>
			</th>
			<th width="5%" align="center">
			<div style="font-size:5px;">&nbsp;</div>
			<b>Qty</b>
			</th>
			<th width="10%" align="center">
			<b>Price/unit</b> ' . $currency_symbol . '</th>
			<th width="11%" align="center">
			<b>Total Price</b> ' . $currency_symbol . '
			</th>
			</tr>
			' . $tbody . '
			<tr>
			<td colspan="4" align="right"><b>Total Price</b> ' . $currency_symbol . ' <b>(VAT excl)</b></td>
			<td align="right"><b>' . number_format($total, 2, '.', '') . '</b></td>
			</tr>
			<tr>
			<td colspan="4" align="right"><b>VAT ' . number_format($data['vat_percent'], 2, '.', '') . '% on ' . number_format($total, 2, '.', '') . '</b> ' . $currency_symbol . '</td>
			<td align="right"><b>' . number_format($total * (floatval($data['vat_percent']) / 100), 2, '.', '') . '</b></td>
			</tr>
			<tr>
			<td colspan="4" align="right"><b>Total</b> ' . $currency_symbol . '</td>
			<td align="right"><b>' . number_format($total * (1 + (floatval($data['vat_percent']) / 100)), 2, '.', '') . '</b></td>
			</tr>
			</table>';

			$print .= '<p style="font-size:9px;">' . $data['note'] . '</p><p></p>';
			$print .= '<table cellspacing="0" cellpadding="1" border="0">
			<tr>
			<th width="50%" align="left"><b>Consignee:</b></th>
			<th width="50%" align="left"><b>Compiled:</b> ' . $_SESSION['uid'] . '<br>' . str_repeat('&nbsp;', 20) . transliterate($_SESSION['name']) . '</th>
			</tr>
			</table>';

			$pdf->writeHTML($print, true, false, false, false, '');

			//Close and output PDF document
			$pdf->Output($data['id'] . '.pdf', 'I');
		}
		else
		{
			exit('click preview invoice');
		}
	}

	public function shortage_surplus($type, $id)
	{
		if (!in_array($type, ['sale', 'purchase']) || !is_numeric($id))
		{
			show_404();
		}

		$this->load->model('spravcho_model');

		$id = intval($id);

		switch ($type)
		{
			case 'sale':
				$data = $this->spravcho_model->_getSale($id, 1);

				$client_id = 'cid';

				$button = "преглед на липси за продажба &numero; $id";
				$button_range = 'преглед на липси по OK продажби за период';
			break;

			case 'purchase':
				$data = $this->spravcho_model->_getPurchase($id);

				$client_id = 'Доставчик ID';

				$button = "преглед на излишъци за покупка &numero; $id";
				$button_range = 'преглед на излишъци по OK покупки за период';
			break;
		}

		$message = '';

		if (empty($data))
		{
			$message = 'Няма ' . (($type === 'sale') ? 'продажба' : 'покупка') . ' с този номер';
		}
		else
		{
			if ($data['data']['data'][$client_id] != 39)
			{
				$message = 'Тази ' . (($type === 'sale') ? 'продажба' : 'покупка') . ' не е по Липси';
			}
		}

		if (empty($message))
		{
			$this->data['button'] = $button;
			$this->data['button_range'] = $button_range;
			$this->data['type'] = $type;
			$this->data['id'] = $id;

			$this->data['data'] = $data['data'];

			$this->render('main1c');
		}
		else
		{
			exit($message);
		}
	}

	public function set_shortage_surplus()
	{
		if ($this->input->is_ajax_request())
		{
			$post = $this->input->post();

			$_SESSION['shortage_surplus'] = json_decode($post['shortage_surplus'], true);

			$reason = [];

			$reason[] = $_SESSION['shortage_surplus']['order_reason'] ?: null;
			$reason[] = $_SESSION['shortage_surplus']['reason_inventory'] ? 'Инвентаризация' : null;
			$reason[] = $_SESSION['shortage_surplus']['reason_theft'] ? 'Кражба' : null;
			$reason[] = $_SESSION['shortage_surplus']['reason_blaze'] ? 'Пожар' : null;
			$reason[] = $_SESSION['shortage_surplus']['reason_flood'] ? 'Наводнение' : null;

			$_SESSION['shortage_surplus']['reason'] = implode(' / ', array_diff($reason, [null]));

			$query = '
			SELECT st."Име", po."PositionName"
			FROM public."Съдружници" AS st
			JOIN public."Position" AS po USING ("Position ID")
			WHERE "Position ID" IN (102, 310)
			ORDER BY "Position ID" ASC
			';

			$result = $this->db->query($query)->result_array();

			$_SESSION['shortage_surplus']['members'] = $result;

			if (empty($_SESSION['shortage_surplus']))
			{
				$response['error'] = 'грешка';
			}
			else
			{
				$response['success'] = true;
			}

			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
	}

	public function set_shortage_surplus_range()
	{
		if ($this->input->is_ajax_request())
		{
			$error = [];

			$post = $this->input->post();

			$type = trim($post['type']);
			$begin_date = trim($post['begin_date']);
			$end_date = trim($post['end_date']);

			if (empty($begin_date))
			{
				$error[] = 'въведи начална дата';
			}
			else
			{
				$date_object = date_create_from_format('d.m.Y', $begin_date);
				$check_date = date_format($date_object, 'd.m.Y');

				if ($check_date == $begin_date)
				{
					$begin_date = date_format($date_object, 'Y-m-d');
					$begin_integer = date_format($date_object, 'U');
				}
				else
				{
					$error[] = 'невалидна начална дата';
				}
			}

			if (empty($end_date))
			{
				$error[] = 'въведи крайна дата';
			}
			else
			{
				$date_object = date_create_from_format('d.m.Y', $end_date);
				$check_date = date_format($date_object, 'd.m.Y');

				if ($check_date == $end_date)
				{
					$end_date = date_format($date_object, 'Y-m-d');
					$end_integer = date_format($date_object, 'U');
				}
				else
				{
					$error[] = 'невалидна крайна дата';
				}
			}

			if (empty($error))
			{
				if ($begin_integer > $end_integer)
				{
					$error[] = 'невалиден интервал от време';
				}
			}

			$path = dirname(dirname(__DIR__)) . '/public_html/' . (DEVELOPMENT ? 'dompdf/' : 'pdf/');

			if (empty($error))
			{
				$between = "BETWEEN '" . $begin_date . "' AND '" . $end_date . "'";

				switch ($type)
				{
					case 'sale':
						$query = '
						SELECT "Продажба ID", okdate::date
						FROM public."Продажби"
						WHERE "Клиент ID" = 39 AND "OK" IS TRUE AND "Статус ID" NOT IN (164, 165) AND "Дата"::date ' . $between;

						$result = $this->db->query($query)->result_array();

						$files = [];

						foreach ($result as $row)
						{
							$date_object = date_create_from_format('Y-m-d', $row['okdate']);
							$date = date_format($date_object, 'd.m.Y');

							$request = $this->print_shortage_surplus('s', intval($row['Продажба ID']), $date);

							if (is_numeric($request))
							{
								$files[] = "{$path}{$request}.pdf";
							}
						}

						if (empty($files))
						{
							$response['error'][] = 'Няма Липси с Продукти за този период';
						}
						else
						{
							$this->load->library('PDFMerger');

							foreach ($files as $file)
							{
								$this->pdfmerger->addPDF($file, 'all');
							}

							$merged_file = "{$path}Липси_по_Продажби_(" . count($files) . ")_за_период_{$begin_date}_до_{$end_date}.pdf";

							$this->pdfmerger->merge('file', $merged_file);

							chmod($merged_file, 0777);

							foreach ($files as $file)
							{
								unlink($file);
							}

							$response['success'] = (DEVELOPMENT ? 'dompdf/' : 'pdf/') . end(explode('/', $merged_file));
						}
					break;

					case 'purchase':
						$query = '
						SELECT "Покупка ID", "Дата"::date
						FROM public."Покупки"
						WHERE "Клиент ID" = 39 AND "OK" IS TRUE AND "Дата"::date ' . $between;

						$result = $this->db->query($query)->result_array();

						$files = [];

						foreach ($result as $row)
						{
							$date_object = date_create_from_format('Y-m-d', $row['Дата']);
							$date = date_format($date_object, 'd.m.Y');

							$request = $this->print_shortage_surplus('p', intval($row['Покупка ID']), $date);

							if (is_numeric($request))
							{
								$files[] = "{$path}{$request}.pdf";
							}
						}

						if (empty($files))
						{
							$response['error'][] = 'Няма Излишъци с Продукти за този период';
						}
						else
						{
							$this->load->library('PDFMerger');

							foreach ($files as $file)
							{
								$this->pdfmerger->addPDF($file, 'all');
							}

							$merged_file = "{$path}Излишъци_по_Покупки_(" . count($files) . ")_за_период_{$begin_date}_до_{$end_date}.pdf";

							$this->pdfmerger->merge('file', $merged_file);

							chmod($merged_file, 0777);

							foreach ($files as $file)
							{
								unlink($file);
							}

							$response['success'] = (DEVELOPMENT ? 'dompdf/' : 'pdf/') . end(explode('/', $merged_file));
						}
					break;
				}
			}
			else
			{
				$response['error'] = $error;
			}

			$this->output->set_content_type('application/json')->set_output(json_encode($response));
		}
	}

	public function print_pdf()
	{
		$post = $this->input->post();

		$path = DEVELOPMENT ? '/var/www/html/git/livebe/be/public_html/' : '/var/www/be.jarcomputers.com/public_html/';

		$file_name = '"' . $path . $post['path'] . '"';

		$result = exec(FCPATH . '../p.sh ' . $file_name);

		if (strlen($result) == 0 || $result == 0)
		{
			$response['success'] = 'успешно принтиране';
		}
		else
		{
			$response['error'] = "грешка на принтиране ($result)";
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function print_shortage_surplus($type = null, $id = null, $date = null)
	{
		switch ($type)
		{
			case 's':
				$_SESSION['shortage_surplus'] = [
					'type' => 'sale',
					'id' => $id,
					'company' => 'ЖАР ЕООД',
					'mol' => 'ЖАР Сердика',
					'mol_order' => "№ {$id} / {$date}",
					'type_assets' => '',
					'order_reason' => '',
					'reason_inventory' => 1,
					'reason_theft' => 0,
					'reason_blaze' => 0,
					'reason_flood' => 0,
					'type_value' => '1',
					'shortage_surplus_action' => 1,
					'reason' => 'Инвентаризация',
					'members' => [
						0 => [
							'Име' => 'Борислав Николаев Димитров',
							'PositionName' => 'Търговски директор'
						],
						1 => [
							'Име' => 'Павел Митков Бъчваров',
							'PositionName' => 'Мениджър склад'
						]
					]
				];
			break;

			case 'p':
				$_SESSION['shortage_surplus'] = [
					'type' => 'purchase',
					'id' => $id,
					'company' => 'ЖАР ЕООД',
					'mol' => 'ЖАР Сердика',
					'mol_order' => "№ $id / {$date}",
					'type_assets' => '',
					'order_reason' => '',
					'reason_inventory' => 1,
					'reason_theft' => 0,
					'reason_blaze' => 0,
					'reason_flood' => 0,
					'type_value' => '0',
					'shortage_surplus_action' => 'ЖАР Сердика',
					'reason' => 'Инвентаризация',
					'members' => [
						0 => [
							'Име' => 'Борислав Николаев Димитров',
							'PositionName' => 'Търговски директор'
						],
						1 => [
							'Име' => 'Павел Митков Бъчваров',
							'PositionName' => 'Мениджър склад'
						]
					]
				];
			break;
		}

		if (isset($_SESSION['shortage_surplus']) && !empty($_SESSION['shortage_surplus']))
		{
			$data = $_SESSION['shortage_surplus'];

			require_once(dirname(__DIR__) . '/libraries/tcpdf/tcpdf.php');

			// create new PDF document
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			// set document information
			$pdf->SetTitle('Протокол за Липси / Излишъци');

			$pdf->setPrintHeader(false);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, 12, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(6);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

			// set font
			$pdf->SetFont('freesans', '', 10);

			$pdf->AddPage();

			$is_shortage = ($data['type'] === 'sale') ? '&#8864;' : '&#9633;';
			$is_surplus = ($data['type'] === 'purchase') ? '&#8864;' : '&#9633;';

			$print = '<table cellspacing="0" cellpadding="2" border="1">
			<tr>
			<td width="50%" style="font-size:12px;line-height:15px;text-align:center;"><b>ПРОТОКОЛ ЗА ЛИПСИ / ИЗЛИШЪЦИ</b></td>
			<td width="15%" style="line-height:15px;"><b>Организация</b></td>
			<td width="35%" style="line-height:15px;">' . $data['company'] . '</td>
			</tr>
			<tr>
			<td width="15%"><b>на Обект/МОЛ</b></td>
			<td width="35%">' . $data['mol'] . '</td>
			<td width="15%"><b>по Заповед</b></td>
			<td width="35%">' . $data['mol_order'] . '</td>
			</tr>
			<tr>
			<td width="15%"><b>за вид активи</b></td>
			<td width="85%">' . $data['type_assets'] . '</td>
			</tr>
			<tr>
			<td width="15%"><b>в резултат на</b></td>
			<td width="85%"><i>' . $data['reason'] . '</i></td>
			</tr>
			<tr>
			<td width="50%" style="line-height:15px;text-align:center;"><b>СЕ УСТАНОВИХА СЛЕДНИТЕ</b></td>
			<td width="25%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $is_shortage . '</span> <b>ЛИПСИ</b></td>
			<td width="25%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $is_surplus . '</span> <b>ИЗЛИШЪЦИ</b></td>
			</tr>
			</table>';

			$this->load->model('spravcho_model');

			switch ($data['type'])
			{
				case 'sale':
					$price = 'Продажна ЛВ';
					$products = $this->db->query("SELECT * FROM vwsales_stock_kasa WHERE pid = {$data['id']} ORDER BY item_id ASC")->result_array();

					if (empty($products))
					{
						return 'Няма продукти';
					}

					$stock_id = [];

					foreach ($products as $value)
					{
						if ($value['sid'] > 0)
						{
							$stock_id[] = $value['sid'];
						}
					}

					if (empty($stock_id))
					{
						return 'Няма изписана стока';
					}

					$stock_id = implode(', ', $stock_id);

					$sn = $this->spravcho_model->getSNByStockID($stock_id, intval($products[0]['pid']));

					foreach ($products as $key => $value)
					{
						$products[$key]['sn_ids'] = $sn[$value['sid']];
					}
				break;

				case 'purchase':
					$price = 'Закупна ЛВ';
					$products = $this->spravcho_model->_getPurchase($data['id'])['data']['products'];

					if (empty($products))
					{
						return 'Няма продукти';
					}

					$stock_id = [];

					foreach ($products as $value)
					{
						if ($value['sid'] > 0)
						{
							$stock_id[] = $value['sid'];
						}
					}

					if (empty($stock_id))
					{
						return 'Няма изписана стока';
					}

					$stock_id = implode(', ', $stock_id);

					$sn = $this->spravcho_model->getSNByStockID($stock_id);

					foreach ($products as $key => $value)
					{
						$products[$key]['sn_ids'] = $sn[$value['sid']];
					}
				break;
			}

			$tbody = '';
			$total_sum = 0;
			$total_value = 0;
			$counter = 0;

			foreach ($products as $value)
			{
				if (empty($value['sn_ids']))
				{
					continue;
				}

				++$counter;

				$amount = intval($value['sncount']);

				$sum = round($value[$price], 2) * $amount;

				$total_sum += $sum;
				$total_value += $sum;

				$tbody .= '<tr>
				<td align="right" style="font-size:9px;">' . $counter . '</td>
				<td style="font-size:9px;">' . $value['plid'] . '</td>
				<td style="font-size:9px;">' . $value['Описание'] . ' (' . $value['sn_ids'] . ')</td>
				<td style="font-size:9px;text-align:right;">' . number_format($value[$price], 2, '.', '') . '</td>
				<td style="font-size:9px;text-align:right;">' . $amount . '</td>
				<td style="font-size:9px;text-align:right;">' . number_format($sum, 2, '.', '') . '</td>
				<td style="font-size:9px;text-align:right;">' . number_format($sum, 2, '.', '') . '</td>
				</tr>';
			}

			if ($total_sum === 0)
			{
				return 'Няма продукти';
			}

			$print .= '<br><br><table cellspacing="0" cellpadding="1" border="1">
			<tr>
			<th width="2.5%" align="center">
			<div style="font-size:10px;">&nbsp;</div>
			<span style="font-family:dajavusans;font-size:9px;">&#8470;</span>
			</th>
			<th width="14.5%" align="center">
			<div style="font-size:10px;">&nbsp;</div>
			<span style="font-family:dajavusans;font-size:9px;">инв. &#8470;/код</span>
			</th>
			<th width="51%" align="center">
			<div style="font-size:5px;">&nbsp;</div>
			<span style="font-family:dajavusans;font-size:9px;">наименование на актива/запаса<br>(серийни номера)</span>
			</th>
			<th width="8%" align="center">
			<span style="font-family:dajavusans;font-size:9px;">отчетна<br>стойност<br>за 1 брой</span>
			</th>
			<th width="8.5%" align="center">
			<span style="font-family:dajavusans;font-size:9px;">колич.<br>липс./изл.<br>в брой</span>
			</th>
			<th width="7%" align="center">
			<div style="font-size:10px;">&nbsp;</div>
			<span style="font-family:dajavusans;font-size:9px;">сума</b>
			</th>
			<th width="8.5%" align="center">
			<div style="font-size:5px;">&nbsp;</div>
			<span style="font-family:dajavusans;font-size:9px;">справедл.<br>стойност</span>
			</th>
			</tr>
			' . $tbody . '
			<tr>
			<td colspan="5" align="right"><b>Общо</b></td>
			<td align="right"><b style="font-size:9px;">' . number_format($total_sum, 2, '.', '') . '</b></td>
			<td align="right"><b style="font-size:9px;">' . number_format($total_value, 2, '.', '') . '</b></td>
			</tr>
			</table>';

			$report_value = $data['type_value'] ? '&#8864;' : '&#9633;';
			$honest_value = $data['type_value'] ? '&#9633;' : '&#8864;';

			if ($data['type'] === 'sale')
			{
				$as_cost = $data['shortage_surplus_action'] ? '&#8864;' : '&#9633;';
				$as_mol = $data['shortage_surplus_action'] ? '&#9633;' : '&#8864;';

				$print .= '<br><br><table cellspacing="0" cellpadding="2" border="1">
				<tr>
				<td width="40%" style="line-height:15px;text-align:right;">Установените Липси ще бъдат за сметка на</td>
				<td width="30%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $as_cost . '</span> Отписването им на разход</td>
				<td width="30%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $as_mol . '</span> Вземане от подотчетно лице</td>
				</tr>
				<tr>
				<td width="40%" style="line-height:15px;text-align:right;">По тяхната</td>
				<td width="30%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $report_value . '</span> Отчетна Стойност</td>
				<td width="30%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $honest_value . '</span> Справедлива Стойност</td>
				</tr>
				</table>';
			}
			else
			{
				$print .= '<br><br><table cellspacing="0" cellpadding="2" border="1">
				<tr>
				<td width="65%" style="line-height:15px;text-align:right;">Установените Излишъци ще бъдат заприходени в Обект и/или на МОЛ</td>
				<td width="35%" style="line-height:15px;"><b>' . $data['shortage_surplus_action'] . '</b></td>
				</tr>
				<tr>
				<td width="50%" style="line-height:15px;text-align:right;">По тяхната</td>
				<td width="25%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $report_value . '</span> Отчетна Стойност</td>
				<td width="25%"> <span style="font-family:freemono;font-size:14px;line-height:1;">' . $honest_value . '</span> Справедлива Стойност</td>
				</tr>
				</table>';
			}

			$members = '';

			foreach ($data['members'] as $value)
			{
				$members .= '<tr>
				<td>' . $value['Име'] . '</td>
				<td>' . $value['PositionName'] . '</td>
				<td></td>
				<td></td>
				</tr>';
			}

			$print .= '<br><br><table cellspacing="0" cellpadding="2" border="1">
			<tr>
			<th width="100%" style="text-align:center;"><b>ЧЛЕНОВЕ НА КОМИСИЯ</b></th>
			</tr>
			<tr>
			<th width="50%" style="text-align:center;"><b>Име</b></th>
			<th width="25%" style="text-align:center;"><b>Длъжност</b></th>
			<th width="15%" style="text-align:center;"><b>Дата</b></th>
			<th width="10%" style="text-align:center;"><b>Подпис</b></th>
			</tr>
			' . $members . '
			</table>';

			$pdf->writeHTML($print, true, false, false, false, '');

			if (is_null($type))
			{
				// output PDF document
				$pdf->Output($data['id'] . '.pdf', 'I');
			}
			else
			{
				$file_name = dirname(dirname(__DIR__)) . '/public_html/';
				$file_name .= (DEVELOPMENT ? 'dompdf/' : 'pdf/') . "{$data['id']}.pdf";

				if (file_exists($file_name))
				{
					unlink($file_name);
				}

				// save PDF document
				$pdf->Output($file_name, 'F');

				chmod($file_name, 0777);

				return $data['id'];
			}
		}
		else
		{
			exit('натисни бутон за преглед на липси или излишъци');
		}
	}

	public function payment_order($action, $id)
	{
		$id = trim($id);

		if (is_numeric($id) && $id == intval($id))
		{
			$query = '
			SELECT
				sub.*,
				(SELECT SUM("Сума") FROM public."Banks" WHERE "Референция" = sub."Референция" AND tr_code = 51) AS tax,
				(SELECT bic FROM public.bank_codes WHERE code = (SELECT SUBSTRING(sub.iban FROM 5 for 8))) AS bic,
				(SELECT ename FROM public.bank_codes WHERE code = (SELECT SUBSTRING(sub.iban FROM 5 for 8))) AS branch
			FROM
				(
					SELECT ba.*, ca.iban AS self_iban, cu.ename AS currency_code
					FROM public."Banks" AS ba
					JOIN public."Каса Сметки" AS ca USING ("Сметка ID")
					JOIN unity.currency AS cu ON ca.currency_id = cu.id
					WHERE "Bank ID" = ' . intval($id) . ' AND tr_code != 51
				) AS sub
			';

			$row = $this->db->query($query)->row_array();

			if (empty($row))
			{
				exit('Няма валиден превод с този номер!');
			}
		}
		else
		{
			exit('Превод трябва да бъде число!');
		}

		$date = substr($row['Дата'], 0, 10);
		$urg = str_replace('-', '', $date) . substr($row['Референция'], 3);
		$format_date = substr($date, 8, 2) . '.' . substr($date, 5, 2) . '.' . substr($date, 0, 4);

		require_once(dirname(__DIR__) . '/libraries/tcpdf/tcpdf.php');

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetTitle('ПРЕВОДНО НАРЕЖДАНЕ ЗА КРЕДИТЕН ПРЕВОД');

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 12, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(6);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set font
		$pdf->SetFont('freesans', '', 10);

		$pdf->AddPage();

		$print = '<table width="100%" cellspacing="0" cellpadding="2" border="0">
		<tr>
		<td width="50%"><img src="/img/dsk_logo.png" width="90"></td>
		<td width="50%" style="font-size:0.8em; line-height:1.1; text-align:center; border-bottom:1px solid #008b8b;"><span></span><br>' . $urg . '</td>
		</tr>
		<tr>
		<td width="9%" style="font-size:0.9em; line-height:2;">До клон</td>
		<td width="36%" style="font-size:0.9em; line-height:2; text-align:center; border-bottom:1px solid #008b8b;">Банка ДСК "Централно управление"</td>
		<td width="5%"></td>
		<td width="50%" style="font-size:0.9em; line-height:1.2; text-align:center; border-bottom:1px solid #008b8b;"><span style="color:#008b8b;">уникален регистрационен номер</span><br>' . $format_date . '</td>
		</tr>
		<tr>
		<td width="9%" style="font-size:0.9em; line-height:2;">Адрес</td>
		<td width="36%" style="font-size:0.9em; line-height:2; text-align:center; border-bottom:1px solid #008b8b;"></td>
		<td width="5%"></td>
		<td width="50%" style="font-size:0.9em; line-height:1.1; text-align:center; border-bottom:1px solid #008b8b;"><span style="color:#008b8b;">дата на представяне</span><br>подписано с електронен подпис</td>
		</tr>
		<tr>
		<td width="50%"></td>
		<td width="50%" style="font-size:0.9em; line-height:1.1; text-align:center;"><span style="color:#008b8b;">подпис на наредителя</span></td>
		</tr>
		</table>
		<br>
		<br>
		<table width="100%" cellspacing="0" cellpadding="2" border="0.1">
		<tr>
		<td width="100%" style="font-size:0.8em; line-height:1.2;">Платете на - <span style="color:#008b8b;">име на получателя / Beneficiary name</span><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:4px;">&nbsp;' . $row['Коренспондент'] . '</span></td>
		</tr>
		<tr>
		<td width="64%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">IBAN на получателя / Beneficiary\'s IBAN<br><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:6px;">&nbsp;' . $row['iban'] . '</span></td>
		<td width="4%"></td>
		<td width="24%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">BIC на банката на получателя / Beneficiary bank\'s BIC<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; text-align:center; letter-spacing:5px;">' . $row['bic'] . '</span></td>
		<td width="8%" style="background-color:#e0ffff;"></td>
		</tr>
		<tr>
		<td width="100%" style="font-size:0.8em; line-height:1.2;">При банка - <span style="color:#008b8b;">име на банката на получателя / Beneficiary bank</span><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:5px;">&nbsp;' . $row['branch'] . '</span></td>
		</tr>
		<tr>
		<td width="47%" style="font-family:dajavusans; font-size:9px; font-weight:bold; line-height:1.8; text-align:center;">ПРЕВОДНО НАРЕЖДАНЕ ЗА КРЕДИТЕН ПРЕВОД<br><span style="color:#008b8b;">PAYMENT ORDER FOR CREDIT TRANSFER</span></td>
		<td width="17%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">Вид валута / Currency<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; text-align:center; letter-spacing:5px;">' . $row['currency_code'] . '</span></td>
		<td width="36%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">Сума / Amount<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; text-align:right; letter-spacing:4px;">' . number_format(abs($row['Сума']), 2, ',', '') . '&nbsp;</span></td>
		</tr>
		<tr>
		<td width="100%" style="font-size:0.8em; line-height:1.2;">Основание за превод - <span style="color:#008b8b;">информация за получателя / Details of payment - information for the beneficiary</span><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:4px;">&nbsp;' . mb_strtolower($row['Описание'], 'UTF-8') . '</span></td>
		</tr>
		<tr>
		<td width="100%" style="font-size:0.8em; line-height:1.2;"><span style="color:#008b8b;">Още пояснения / Additional Details</span><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:4px;">&nbsp;' . mb_strtolower($row['Пояснения'], 'UTF-8') . '</span></td>
		</tr>
		<tr>
		<td width="100%" style="font-size:0.8em; line-height:1.2;">Наредител - <span style="color:#008b8b;">име / Ordering customer</span><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:5px;">&nbsp;ЖАР ЕООД</span></td>
		</tr>
		<tr>
		<td width="64%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">IBAN на наредителя /Ordering customer\'s IBAN<br><br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:6px;">&nbsp;' . $row['self_iban'] . '</span></td>
		<td width="11%"></td>
		<td width="25%" style="background-color:#e0ffff; font-size:0.8em; line-height:1.2;">BIC на банката на наредителя / Ordering bank\'s BIC<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; text-align:center; letter-spacing:5px;">STSABGSF</span></td>
		</tr>
		<tr>
		<td width="28%" style="font-size:0.8em; line-height:1.2;">Платежна система / Payment system<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:5px;">&nbsp;Бисера</span></td>
		<td width="15%" style="font-size:0.8em; line-height:1.2;">*Такси / *Charges<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:5px;">&nbsp;2</span></td>
		<td width="5%"></td>
		<td width="24%" style="font-size:0.8em; line-height:1.2;">Размер на такса / Tax amount<br><br><span style="width:100%; font-family:dajavusans; font-size:13px; line-height:1.5; letter-spacing:4px;">&nbsp;' . number_format(abs($row['tax']), 2, ',', '') . ' <span style="text-align:right;">BGN</span></span></td>
		<td width="28%" style="font-size:0.8em; line-height:1.2;">Дата за изпълнение / Execution date<br><br><span style="font-family:dajavusans; font-size:13px; line-height:1.5; text-align:center; letter-spacing:5px;">' . $format_date . '</span></td>
		</tr>
		</table>
		<p style="font-size:0.8em; line-height:0.5;">*Такси: 1 - за сметка на наредителя; 2 - споделени (стандарт за местни преводи); 3 - за сметка на получателя</p>
		<p style="color:#008b8b; font-size:0.8em; line-height:0.5;">*Charges: 1 - to be borne by the ordering customer; 2 - shared (standard for local payments); 3 - to be borne by the beneficiary</p>
		<p style="font-size:1em; line-height:1; text-align:center;">Попълва се при преводи между местни и чуждестранни лица в страната, на стойност равна или<br>надвишаваща сумата по чл.2, ал.1, т.1 от Наредба 27 на БНБ за статистиката на платежния баланс</p>
		<table width="100%" cellspacing="0" cellpadding="2" border="0.1">
		<tr>
		<td width="50%" style="font-size:0.8em;">
		<table>
		<tr>
		<td style="width:60%;">Данни за наредителя</td>
		<td style="width:40%;">
		<span style="font-family:freemono; font-size:14px; line-height:0.8;">&#9633;</span><span style="font-size:9px; line-height:1;">&nbsp;местно лице</span>
		<br>
		<span style="font-family:freemono; font-size:14px; line-height:0.8;">&#9633;</span><span style="font-size:9px; line-height:1;">&nbsp;чуждестранно лице</span>
		</td>
		</tr>
		</table>
		</td>
		<td width="50%" style="font-size:0.8em;">
		<table>
		<tr>
		<td style="width:60%;">Данни за получателя</td>
		<td style="width:40%;">
		<span style="font-family:freemono; font-size:14px; line-height:0.8;">&#9633;</span><span style="font-size:9px; line-height:1;">&nbsp;местно лице</span>
		<br>
		<span style="font-family:freemono; font-size:14px; line-height:0.8;">&#9633;</span><span style="font-size:9px; line-height:1;">&nbsp;чуждестранно лице</span>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		<tr>
		<td width="50%" style="font-size:0.8em;">ЕГН/БУЛСТАТ на наредителя<br></td>
		<td width="50%" style="font-size:0.8em;">ЕГН/БУЛСТАТ на получателя<br></td>
		</tr>
		<tr>
		<td width="50%" style="font-size:0.8em;">Държава на наредителя<br></td>
		<td width="50%" style="font-size:0.8em;">Държава на получателя<br></td>
		</tr>
		<tr>
		<td width="50%" style="font-size:0.8em;">Адрес на наредителя<br></td>
		<td width="50%" style="font-size:0.8em;">Адрес на получателя<br></td>
		</tr>
		<tr>
		<td width="80%" style="font-size:0.8em;">Описание на икономическата същност на превода<br><br></td>
		<td width="20%" style="font-size:0.8em;">Код на операцията<br><br></td>
		</tr>
		<tr>
		<td width="80%" style="font-size:0.8em;">При превод на средства във връзка с вече предоставени от или на чуждестранно лице финансови кредити<br><br></td>
		<td width="20%" style="font-size:0.8em;">Номер на БНБ<br><br></td>
		</tr>
		</table>
		<p style="font-size:1em; line-height:1; text-align:center;">Известно ми е, че за посочването на неверни данни нося отговорност по чл. 313 от Наказателния кодекс</p>
		<p style="font-size:0.8em; line-height:0.6; text-align:right;">Статус: Прието за изпълнение</p>
		';

		$pdf->writeHTML($print, true, false, false, false, '');

		switch ($action)
		{
			case 0:
				$file_action = 'I';
			break;

			case 1:
				$file_action = 'D';
			break;

			case 2:
				$file_action = 'F';
			break;
		}

		$file_name = 'payment_order_' . $id . '.pdf';

		if ($action == 2)
		{
			$path = DEVELOPMENT ? 'dompdf/' : 'pdf/';

			$file_name = dirname(dirname(__DIR__)) . '/public_html/' . $path . $file_name;

			if (file_exists($file_name))
			{
				unlink($file_name);
			}

			$pdf->Output($file_name, $file_action);

			chmod($file_name, 0777);

			return $file_name;
		}
		else
		{
			//Close and output PDF document
			$pdf->Output($file_name, $file_action);
		}
	}

	public function config_sn()
	{
		$this->render();
	}

	public function get_config_sn()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents/config_sn');
		}

		$error = [];

		$post = $this->input->post();

		$grid = intval(trim($post['grid']));
		$series = json_decode(trim($post['series']), true);

		$serial_numbers = [];
		$allowed_sn = 200;

		if (in_array($grid, [10, 14]))
		{
			switch ($grid)
			{
				case 10:
					$allowed_sn = 200;
				break;

				case 14:
					$allowed_sn = 280;
				break;
			}
		}
		else
		{
			$error[] = 'грешен размер на схема';
		}

		foreach ($series as $value)
		{
			if (!empty($value['from']) && !empty($value['to']))
			{
				$sn = [];

				$from = intval(preg_replace('/[^0-9]/', '', $value['from']));
				$to = intval(preg_replace('/[^0-9]/', '', $value['to']));

				if (empty($from) || empty($to))
				{
					$error[] = 'сериен номер трябва да съдържа числа';

					continue;
				}

				if ($from < $to)
				{
					if (($to - $from) > $allowed_sn)
					{
						$error[] = "повече от $allowed_sn серийни номера за една серия";
					}
					else
					{
						$sn = range($from, $to);
					}
				}
				else if ($from > $to)
				{
					if (($from - $to) > $allowed_sn)
					{
						$error[] = "повече от $allowed_sn серийни номера за една серия";
					}
					else
					{
						$sn = range($to, $from);
					}
				}
				else
				{
					$sn = [$from];
				}

				$serial_numbers = array_merge($serial_numbers, $sn);
			}
		}

		if (empty($serial_numbers))
		{
			$error[] = 'невалидна или празна поредица от серийни номера';
		}
		else
		{
			$serial_numbers = array_unique($serial_numbers);

			sort($serial_numbers);

			foreach ($serial_numbers as &$value)
			{
				$value = "'jar$value'";
			}
		}

		if (count($serial_numbers) > $allowed_sn)
		{
			$error[] = "повече от $allowed_sn серийни номера за всички поредици";
		}

		if (empty($error))
		{
			$_SESSION['print_config_sn'] = [
				'grid' => $grid,
				'data' => []
			];

			$query = '
			SELECT "Сериен номер"
			FROM public."SN"
			WHERE LOWER("Сериен номер") IN (' . implode(', ', $serial_numbers) . ')
			ORDER BY "Сериен номер" ASC
			';

			$result = $this->db->query($query)->result_array();

			$counter = 0;
			$page = 1;
			$grid *= 2;

			foreach ($result as $value)
			{
				if (($counter + $grid) % $grid === 0)
				{
					++$page;
				}

				++$counter;

				$_SESSION['print_config_sn']['data'][$page][] = $value['Сериен номер'];
			}

			if (!empty($result))
			{
				$response['success'] = null;
			}
			else
			{
				$response['error'][] = 'няма серийни номера от тази поредица';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function print_config_sn()
	{
		if (isset($_SESSION['print_config_sn']) && !empty($_SESSION['print_config_sn']['data']))
		{
			$this->data['grid'] = $_SESSION['print_config_sn']['grid'];
			$this->data['data'] = $_SESSION['print_config_sn']['data'];

			$this->render('clear');
		}
		else
		{
			redirect('documents/config_sn');
		}
	}

	public function send_pdf_payment_order()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('documents');
		}

		$error = [];

		$post = $this->input->post();

		$id = trim($post['id']);
		$send_e_mail = trim($post['send_e_mail']);

		if (is_numeric($id))
		{
			$id = intval($id);
		}
		else
		{
			$error[] = 'грешно предаден превод';
		}

		if (empty($send_e_mail))
		{
			$error[] = 'въведи е-поща';
		}

		if (empty($error))
		{
			$file_name = $this->payment_order(2, $id);

			$this->load->model('safe_model');

			$request = $this->safe_model->getPaymentOrderDataByBankID($id);

			if (!empty($request['sender_e_mail']))
			{
				$send_e_mail = $send_e_mail . ', ' . $request['sender_e_mail'];
			}

			$message = "
ПРЕВОДНО НАРЕЖДАНЕ от JAR Computers
PAYMENT ORDER from JAR Computers\n\n
Клиент / Client: {$request['jar_client']}\n
От Дата / From Date: {$request['from_date']}\n
Сума (лв.) / Sum (BGN): {$request['total']}
			";

			$this->load->library('email');

			$config['mailtype'] = 'smtp';

			$this->email->initialize($config);
			$this->email->clear(true);

			$this->email->from('office@jarcomputers.com', 'JAR Computers');
			$this->email->to($send_e_mail);
			$this->email->subject('ПРЕВОДНО НАРЕЖДАНЕ от JAR Computers / PAYMENT ORDER from JAR Computers');
			$this->email->message(trim($message));
			$this->email->attach($file_name);

			if ($this->email->send())
			{
				unlink($file_name);

				$response['success'] = 'Успешно изпратен e-mail към: ' . $send_e_mail;
			}
			else
			{
				$response['error'][] = 'Грешка при изпращане на e-mail към: ' . $send_e_mail;
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
}
?>