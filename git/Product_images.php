<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 25/03/2021
# [MODIFIED] 02/08/2022
final class Product_images extends J_Controller
{
	const ROOT = DEVELOPMENT ? '/var/www/html/git/livebe/be/public_html/' : '/var/www/be.jarcomputers.com/public_html/';
	const PRODUCT_FOLDER = 'images/products/';
	const PRODUCT_PATH = self::ROOT . self::PRODUCT_FOLDER;
	const PICTURES_PATH = '/var/www/pictures/';
	const BACKUP_PATH = 'backup/pictures/';
	const PICTURES_BACKUP_PATH = self::PICTURES_PATH . self::BACKUP_PATH;
	const SYNC_PATH = self::ROOT . 'images/sync/';
	const TEMP_PATH = self::ROOT . 'images/temp_sync/';
	const COMPRESS_PATH = self::ROOT . 'images/in_sync/';
	const URL_BE = DEVELOPMENT ? 'http://gitbe.jarcomputers.com/' : 'https://be.jarcomputers.com/';
	const URL_I = 'http://i.jarcomputers.com/';
	const URL_P = 'https://p.jarcomputers.com/';
	const IMAGE_DIMENSIONS = [680, 1000];
	const SCALE_DIMENSIONS = [40, 87, 150, 250, 350, 680, 1000];
	const SHORT_PIXEL_BIG_QUOTA = '';

	private $sftp;
	private $is_compressed = true;

	public function __construct($load_default = true)
	{
		if ($load_default)
		{
			parent::__construct();

			$this->load->model('product_images_model', 'product_images');
		}
	}

	public function index($product_id)
	{
		$product_id = intval($product_id);

		if ($this->product_images->getProductInfoByProductID($product_id) && $this->user_model->checkRight(10))
		{
			$data['product_id'] = $product_id;

			$siblings = $this->product_images->getClosestProductsByCriteria($product_id);

			$data['previous_product_id'] = $siblings['previous_product_id'];
			$data['next_product_id'] = $siblings['next_product_id'];

			$this->load->view('pricing/product_images_tpl', $data);
		}
		else
		{
			redirect('');
		}
	}

	public function get_data($product_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$product_id = intval($product_id);

		$product_info = (array) $this->product_images->getProductInfoByProductID($product_id);
		$product_images = $this->product_images->getData($product_id);

		foreach ($product_images as &$product_image)
		{
			$product_image['file_name'] = str_replace(['http://i.', 'http://be.'], ['https://p.', 'https://be.'], $product_image['file_name']);
			$product_image['descriptive_name'] = str_replace('http://i.', 'https://p.', $product_image['descriptive_name']);
			$image_info['image'] = $this->getImageInfo($product_image['file_name']);

			if (array_key_exists('error', $image_info['image']))
			{
				$file_name = $this->getBaseImageName($product_image['file_name']);

				$i_path = self::URL_I . self::BACKUP_PATH . $file_name;
				$p_path = self::URL_P . self::BACKUP_PATH . $file_name;

				if (is_array(getimagesize($i_path)) || is_array(getimagesize($p_path)))
				{
					$image_info['image']['backup'] = null;
				}
			}
			else
			{
				if ($image_info['image']['width'] === $image_info['image']['height'] && in_array($image_info['image']['width'], self::IMAGE_DIMENSIONS) && $image_info['image']['border'] === 'outer')
				{
					$image_info['image']['status'] = null;
				}

				if ($image_info['image']['width'] === $image_info['image']['height'] && $image_info['image']['border'] === 'inner')
				{
					$dimension = ($image_info['image']['width'] >= 1000) ? 1000 : 680;

					$image_info['image']['crop'] = $dimension;
				}
			}

			$product_image = array_merge($product_image, $image_info);
		}

		$response['data'] = [
			'product_info' => $product_info,
			'product_images' => $product_images
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function upload_image()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$product_id = $post['product_id'];

		if (is_numeric($product_id) && $product_id > 0)
		{
			$product_id = intval($product_id);

			$total_files = count($_FILES);
			$total_upload = 0;

			$config['upload_path'] = self::PRODUCT_PATH;
			$config['allowed_types'] = 'jpg|png';
			$config['max_size'] = 10 * 1024;

			foreach (array_keys($_FILES) as $upload_file_name)
			{
				$image_data = $this->product_images->getUploadImageName($product_id);

				$config['file_name'] = $image_data['file_name'];

				$this->load->library('upload', $config);
				$this->upload->initialize($config);

				$response = [];

				if ($this->upload->do_upload($upload_file_name))
				{
					$upload_data = $this->upload->data();

					$file_name = self::PRODUCT_PATH . $config['file_name'];

					$contents = file_get_contents($file_name);

					if (mb_substr($contents, 1, 3) === 'PNG')
					{
						$temp_path = self::PRODUCT_PATH . $this->getBaseImageName($config['file_name'], true) . '.png';

						file_put_contents($temp_path, $contents);

						$conversion = $this->convertPNG($temp_path);

						if (is_string($conversion))
						{
							$response['error'] = $conversion;
						}
					}

					if (empty($response['error']))
					{
						if (is_numeric($upload_data['image_width']) && is_numeric($upload_data['image_height']))
						{
							$data = [
								'product_id' => $product_id,
								'file_name' => self::URL_BE . self::PRODUCT_FOLDER . $image_data['file_name'],
								'sorder' => $image_data['sorder'],
								'status_id' => 0,
								'note' => "{$upload_data['image_width']}x{$upload_data['image_height']}",
								'size' => ceil($upload_data['file_size']),
								'file_name_md5' => md5($image_data['file_name'])
							];

							$request = $this->product_images->setProductImage($data);

							if ($request)
							{
								++$total_upload;
							}
							else
							{
								unlink($file_name);
							}
						}
					}
				}
			}

			if ($total_upload !== 0)
			{
				if ($total_files === 1)
				{
					$response['success'] = 'успешно качено изображение';
				}
				else
				{
					$response['success'] = "качени $total_upload от $total_files изображения";
				}
			}
			else
			{
				$response['error'] = "$total_files невалидни изображения";
			}
		}
		else
		{
			$response['error'] = 'грешен номер на продукт';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_default_image($image_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$image_id = intval($image_id);

		$product_data = $this->product_images->getProductDataByImageID($image_id);

		$product_id = intval($product_data['product_id']);
		$file_name = $this->getBaseImageName($product_data['file_name'], true);
		$webp_file_name = empty($product_data['descriptive_name']) ? null : $this->getBaseImageName(basename($product_data['descriptive_name'], '.webp'));

		if (empty($file_name))
		{
			$response['error'] = 'няма картинка';
		}
		else if (preg_match('/be.jarcomputers.com/', $product_data['file_name']))
		{
			$response['error'] = 'картинката не е обработена';
		}
		else
		{
			$product_info = $this->product_images->getProductInfoByProductID($product_id);

			if ($product_info['code'] === $file_name)
			{
				$this->product_images->resetDefaultProductImage($product_id);

				$data = [
					'status_id' => 2
				];

				$request = $this->product_images->setProductImage($data, $image_id);

				if ($request)
				{
					$response['success'] = 'успешно добавена основна картинка';
				}
				else
				{
					$response['error'] = 'възникна грешка';
				}
			}
			else
			{
				$all_images = $this->product_images->getData($product_id);

				$default_image_id = null;

				foreach ($all_images as $key => $value)
				{
					if ($product_info['code'] === $this->getBaseImageName($value['file_name'], true) && !preg_match('/be.jarcomputers.com/', $value['file_name']))
					{
						$default_image_id = intval($key);

						break;
					}
				}

				if (is_null($default_image_id))
				{
					$is_default_image_present = false;

					foreach ($all_images as $key => $value)
					{
						if ($product_info['code'] === $this->getBaseImageName($value['file_name'], true) && preg_match('/be.jarcomputers.com/', $value['file_name']))
						{
							$is_default_image_present = true;

							$override_data = [
								'id' => $key,
								'file_name' => $file_name,
								'temp_name' => "TEMP{$this->uid}{$file_name}"
							];

							if (file_exists(self::PRODUCT_PATH . $product_info['code'] . '.jpg'))
							{
								rename(self::PRODUCT_PATH . $product_info['code'] . '.jpg', self::PRODUCT_PATH . $override_data['temp_name'] . '.jpg');
							}

							break;
						}
					}

					if (file_exists(self::PRODUCT_PATH . $file_name . '.jpg'))
					{
						rename(self::PRODUCT_PATH . $file_name . '.jpg', self::PRODUCT_PATH . $product_info['code'] . '.jpg');
					}

					$source_images = $this->setResizedImagesPath($file_name, $webp_file_name);
					$destination_images = $this->setResizedImagesPath($product_info['code'], $this->product_images->getSymbolicFileName($product_id));

					$this->setSFTP();

					$I_SERVER = [];
					$P_SERVER = [];

					foreach ($source_images as $size => $path)
					{
						if (!copy($path['jpg'], $destination_images[$size]['jpg']))
						{
							$I_SERVER[$path['jpg']] = $destination_images[$size]['jpg'];
						}

						if (!$this->sftp->put($destination_images[$size]['jpg'], $path['jpg'], NET_SFTP_LOCAL_FILE))
						{
							$P_SERVER[$path['jpg']] = $destination_images[$size]['jpg'];
						}

						if (array_key_exists('webp', $path))
						{
							if (!copy($path['webp'], $destination_images[$size]['webp']))
							{
								$I_SERVER[$path['webp']] = $destination_images[$size]['webp'];
							}

							if (!$this->sftp->put($destination_images[$size]['webp'], $path['webp'], NET_SFTP_LOCAL_FILE))
							{
								$P_SERVER[$path['webp']] = $destination_images[$size]['webp'];
							}
						}
					}

					if (!empty($I_SERVER))
					{
						$I_SERVER = $this->repeatCopy($I_SERVER, true);
					}

					if (!empty($P_SERVER))
					{
						$P_SERVER = $this->repeatCopy($P_SERVER, false);
					}

					if (!empty($I_SERVER) || !empty($P_SERVER))
					{
						$response['error'] = 'не са създадени всички снимки';

						$this->sendMail($I_SERVER, $P_SERVER, $product_id);

						foreach ($destination_images as $image)
						{
							if (file_exists($image['jpg']))
							{
								unlink($image['jpg']);
							}

							if (array_key_exists('webp', $image))
							{
								if (file_exists($image['webp']))
								{
									unlink($image['webp']);
								}
							}
						}
					}
					else
					{
						foreach ($source_images as $image)
						{
							if (file_exists($image['jpg']))
							{
								unlink($image['jpg']);
							}

							if (array_key_exists('webp', $image))
							{
								if (file_exists($image['webp']))
								{
									unlink($image['webp']);
								}
							}
						}

						$this->product_images->resetDefaultProductImage($product_id);

						$size_folder = basename(dirname(dirname($product_data['file_name'])));
						$current_size = explode('x', $size_folder)[0];

						$data = [
							'status_id' => 2,
							'file_name' => str_replace(self::PICTURES_PATH, self::URL_I, $destination_images[$current_size]['jpg'])
						];

						if (!empty($product_data['descriptive_name']))
						{
							$data['descriptive_name'] = str_replace(self::PICTURES_PATH, self::URL_I, $destination_images[$current_size]['webp']);
						}

						$request = $this->product_images->setProductImage($data, $image_id);

						if ($request)
						{
							$response['success'] = 'успешно обновена основна картинка';
						}
						else
						{
							$response['error'] = 'възникна грешка';
						}

						if ($is_default_image_present)
						{
							if (file_exists(self::PRODUCT_PATH . $override_data['temp_name'] . '.jpg'))
							{
								rename(self::PRODUCT_PATH . $override_data['temp_name'] . '.jpg', self::PRODUCT_PATH . $override_data['file_name'] . '.jpg');
							}

							$data = [
								'file_name' => self::URL_BE . self::PRODUCT_FOLDER . $override_data['file_name'] . '.jpg'
							];

							$request = $this->product_images->setProductImage($data, $override_data['id']);
						}
					}
				}
				else
				{
					$request = $this->switchImagesNames($image_id, $default_image_id);

					if (is_string($request))
					{
						$response['error'] = $request;
					}
					else
					{
						$data = [
							'status_id' => 2,
							'file_name' => $request[$image_id]['jpg'],
							'descriptive_name' => $request[$image_id]['webp']
						];

						$this->product_images->setProductImage($data, $image_id);

						$data = [
							'status_id' => 1,
							'file_name' => $request[$default_image_id]['jpg'],
							'descriptive_name' => $request[$default_image_id]['webp']
						];

						$this->product_images->setProductImage($data, $default_image_id);

						$response['success'] = 'успешно сменена основна картинка';
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function toggle_active_image($image_id, $status_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$status_id = intval($status_id);

		if (in_array($status_id, [0, 1]))
		{
			$data = [
				'status_id' => $status_id
			];

			$request = $this->product_images->setProductImage($data, intval($image_id));

			if ($request)
			{
				$response['success'] = (boolval($status_id)) ? 'добавена активна картинка' : 'обновихте картинка като неактивна';
			}
			else
			{
				$response['error'] = 'възникна грешка';
			}
		}
		else
		{
			$response['error'] = 'невалиден статус';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function sort_product_image()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$old_sort_id = intval(trim($post['old_sort_id']));
		$new_sort_id = intval(trim($post['new_sort_id']));
		$product_id = intval(trim($post['product_id']));

		if ($old_sort_id === $new_sort_id)
		{
			$response['error'] = 'сортиране на една и съща картинка';
		}
		else
		{
			$request = $this->product_images->sortProductImage($old_sort_id, $new_sort_id, $product_id);

			if ($request)
			{
				$response['success'] = 'успешно сортирани картинки';
			}
			else
			{
				$response['error'] = 'възникна грешка';
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function remove_product_image($image_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$image_id = intval($image_id);

		$product_data = $this->product_images->getProductDataByImageID($image_id);

		$file_name = $product_data['file_name'];
		$webp_file_name = empty($product_data['descriptive_name']) ? null : $this->getBaseImageName(basename($product_data['descriptive_name'], '.webp'));

		if (empty($file_name))
		{
			$response['error'] = 'изображението не съществува';
		}
		else
		{
			$base_product_image = $this->getBaseImageName($file_name, true);

			if (file_exists(self::PRODUCT_PATH . $base_product_image . '.jpg'))
			{
				unlink(self::PRODUCT_PATH . $base_product_image . '.jpg');
			}

			if (!DEVELOPMENT)
			{
				$image_folders = $this->setResizedImagesPath($base_product_image, $webp_file_name);

				$this->setSFTP();

				foreach ($image_folders as $image)
				{
					if (file_exists($image['jpg']))
					{
						unlink($image['jpg']);
					}

					if ($this->sftp->file_exists($image['jpg']))
					{
						$this->sftp->delete($image['jpg']);
					}

					if (array_key_exists('webp', $image))
					{
						if (file_exists($image['webp']))
						{
							unlink($image['webp']);
						}

						if ($this->sftp->file_exists($image['webp']))
						{
							$this->sftp->delete($image['webp']);
						}
					}
				}
			}

			$this->product_images->unsetProductImage($image_id);

			$response['success'] = 'успешно изтриване';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function remove_inactive_images_by_product($product_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$product_images = $this->product_images->getData(intval($product_id));

		$inactive_images = [];

		foreach ($product_images as $key => $value)
		{
			if ($value['status_id'] == 0)
			{
				$inactive_images[$key] = [
					'file_name' => $value['file_name'],
					'descriptive_name' => $value['descriptive_name']
				];
			}
		}

		if (empty($inactive_images))
		{
			$response['error'] = 'няма неактивни картинки';
		}
		else
		{
			$this->setSFTP();

			foreach ($inactive_images as $image_id => $files)
			{
				$base_product_image = $this->getBaseImageName($files['file_name'], true);
				$webp_file_name = empty($files['descriptive_name']) ? null : $this->getBaseImageName(basename($files['descriptive_name'], '.webp'));

				if (file_exists(self::PRODUCT_PATH . $base_product_image . '.jpg'))
				{
					unlink(self::PRODUCT_PATH . $base_product_image . '.jpg');
				}

				if (!DEVELOPMENT)
				{
					$image_folders = $this->setResizedImagesPath($base_product_image, $webp_file_name);

					foreach ($image_folders as $image)
					{
						if (file_exists($image['jpg']))
						{
							unlink($image['jpg']);
						}

						if ($this->sftp->file_exists($image['jpg']))
						{
							$this->sftp->delete($image['jpg']);
						}

						if (array_key_exists('webp', $image))
						{
							if (file_exists($image['webp']))
							{
								unlink($image['webp']);
							}

							if ($this->sftp->file_exists($image['webp']))
							{
								$this->sftp->delete($image['webp']);
							}
						}
					}
				}

				$this->product_images->unsetProductImage($image_id);
			}

			$response['success'] = 'успешно изтриване на неактивни картинки';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function remove_image_by_name($base)
	{
		$base_product_image = $this->getBaseImageName(trim($base), true);

		if (file_exists(self::PRODUCT_PATH . $base_product_image . '.jpg'))
		{
			unlink(self::PRODUCT_PATH . $base_product_image . '.jpg');
		}

		if (!DEVELOPMENT)
		{
			$this->setSFTP();

			$image_folders = $this->setResizedImagesPath($base_product_image);

			foreach ($image_folders as $image)
			{
				if (file_exists($image['jpg']))
				{
					unlink($image['jpg']);
				}

				if ($this->sftp->file_exists($image['jpg']))
				{
					$this->sftp->delete($image['jpg']);
				}
			}
		}
	}

	public function save_image_from_url()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$product_id = intval($post['product_id']);
		$url = trim($post['url']);

		$urls = json_decode($url, true);

		if (!is_array($urls))
		{
			$urls = [$url];
		}

		foreach ($urls as $url)
		{
			if (!preg_match('/^http/', $url))
			{
				continue;
			}

			sleep(1);

			$image_data = $this->product_images->getUploadImageName($product_id);

			$path = self::PRODUCT_PATH . $image_data['file_name'];
			$temp_path = self::TEMP_PATH . $this->getBaseImageName($image_data['file_name'], true);
			$timeout = 10;
			$url_query = parse_url($url, PHP_URL_QUERY);

			$url = str_replace(['%26', '%3D'], ['&', '='], str_replace($url_query, urlencode($url_query), $url));

			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => $url,
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_BINARYTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYHOST => (strpos($url, 'https') !== false) ? 0 : 1,
				CURLOPT_SSL_VERIFYPEER => (strpos($url, 'https') !== false) ? 0 : 1
			]);

			$image = curl_exec($curl);

			$response_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

			curl_close($curl);

			$data = [
				'product_id' => $product_id,
				'file_name' => self::URL_BE . self::PRODUCT_FOLDER . $image_data['file_name'],
				'sorder' => $image_data['sorder'],
				'status_id' => 0,
				'file_name_md5' => md5($image_data['file_name'])
			];

			if (empty($image))
			{
				$response['error'] = "картинката не може да бъде свалена в интервал от $timeout секунди";
			}
			else if ($response_type === 'image/jpeg')
			{
				if (substr($image, 0, 4) === 'RIFF' && substr($image, 8, 4) === 'WEBP')
				{
					$temp_path .= '.webp';

					file_put_contents($temp_path, $image);

					$conversion = $this->convertWEBP($temp_path);

					if (is_string($conversion))
					{
						$response['error'] = $conversion;
					}
				}
				else
				{
					file_put_contents($path, $image);
				}
			}
			else if ($response_type === 'image/png')
			{
				$temp_path .= '.png';

				file_put_contents($temp_path, $image);

				$conversion = $this->convertPNG($temp_path);

				if (is_string($conversion))
				{
					$response['error'] = $conversion;
				}
			}
			else if ($response_type === 'image/webp')
			{
				$temp_path .= '.webp';

				file_put_contents($temp_path, $image);

				$conversion = $this->convertWEBP($temp_path);

				if (is_string($conversion))
				{
					$response['error'] = $conversion;
				}
			}
			else
			{
				$response['error'] = 'картинката не е с разширение "JPG", "PNG" или "WEBP"';
			}

			if (empty($response['error']))
			{
				$dimensions = getimagesize($path);

				$data['note'] = "{$dimensions[0]}x{$dimensions[1]}";
				$data['size'] = ceil(filesize($path) / 1024);

				$request = $this->product_images->setProductImage($data);

				if ($request)
				{
					$response['success'] = 'успешно свалена картинка';
				}
				else
				{
					unlink($path);

					$response['error'] = 'възникна грешка';
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_staging_image($image_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$image_id = intval($image_id);

		$file_name = $this->product_images->getProductDataByImageID($image_id)['file_name'];

		if (empty($file_name))
		{
			$response['error'] = 'изображението не съществува';
		}
		else
		{
			$original_file = $this->getBaseImageName($file_name);
			$original_path = self::PRODUCT_PATH . $original_file;

			$is_proceed = true;

			if (!file_exists($original_path))
			{
				$i_path = self::URL_I . self::BACKUP_PATH . $original_file;
				$p_path = self::URL_P . self::BACKUP_PATH . $original_file;

				if (is_array(getimagesize($i_path)))
				{
					$file_link = $i_path;
				}
				else if (is_array(getimagesize($p_path)))
				{
					$file_link = $p_path;
				}
				else
				{
					$file_link = $file_name;
				}

				try
				{
					$image = new Imagick($file_link);

					if ($image->getImageLength() < 1024)
					{
						$is_proceed = false;

						$response['error'] = 'изображението е по малко от 1kB';
					}
					else
					{
						$image->writeImage($original_path);

						chmod($original_path, 0777);
					}
				}
				catch (Exception $exception)
				{
					$is_proceed = false;

					$response['error'] = 'изображението не съществува';
				}
			}

			if ($is_proceed)
			{
				$response['file'] = self::URL_BE . self::PRODUCT_FOLDER . $original_file . '?v=' . time();

				$dimensions = getimagesize($original_path);
				$response['width'] = $dimensions[0] ?: 0;
				$response['height'] = $dimensions[1] ?: 0;
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_crop_image()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		if (!array_key_exists('imageRotate', $post))
		{
			$post['imageRotate'] = 0;
			$post['viewPortW'] = 600;
			$post['viewPortH'] = 600;
			$post['imageW'] = 600;
			$post['imageH'] = 600;
			$post['imageX'] = 0;
			$post['imageY'] = 0;
		}

		$image_id = intval($post['image_id']);

		$product_data = $this->product_images->getProductDataByImageID($image_id);

		$product_id = $product_data['product_id'];
		$file_name = $product_data['file_name'];
		$webp_file_name = empty($product_data['descriptive_name']) ? null : $this->getBaseImageName(basename($product_data['descriptive_name'], '.webp'));

		if (preg_match('/be.jarcomputers.com/', $file_name))
		{
			$image_numbers = explode('_', $this->getBaseImageName($file_name, true));
			$image_number = '';

			if (count($image_numbers) === 2 && is_numeric($image_numbers[1]))
			{
				$image_number = "_{$image_numbers[1]}";
			}

			$webp_file_name = $this->product_images->getSymbolicFileName($product_id) . $image_number;
		}

		$response = [];

		if (empty($file_name))
		{
			$response['error'] = 'изображението не съществува';
		}
		else
		{
			$original_file = $this->getBaseImageName($file_name);
			$original_path = self::PRODUCT_PATH . $original_file;
			$backup_path = self::PICTURES_BACKUP_PATH . $original_file;
			$parent_folder = substr(md5(basename($original_file, '.jpg')), 0, 2);

			$prefix = $this->setUniquePrefix();
			$crop_file = $prefix . $original_file;

			# FILE FORMAT $crop_path : SYNC_PATH [UID_MICROTIME_CODE(_\d)?.jpg]
			$crop_path = self::SYNC_PATH . $crop_file;

			copy($original_path, $crop_path);

			chmod($crop_path, 0777);

			$new_size = 1000;

			$dimensions = getimagesize($crop_path);

			if ($dimensions[0] < $new_size && $dimensions[1] < $new_size)
			{
				$new_size = 680;
			}

			$new_width = $new_size;
			$new_height = $new_size;

			$x_ratio = $new_size / $post['viewPortW'];
			$y_ratio = $new_size / $post['viewPortH'];

			$zoom_width = intval($post['imageW'] * $x_ratio);
			$zoom_height = intval($post['imageH'] * $y_ratio);
			$rotate = intval($post['imageRotate']);
			$offset_x = -intval($post['imageX'] * $x_ratio);
			$offset_y = -intval($post['imageY'] * $y_ratio);

			$foreground = new Imagick($crop_path);
			$width = $foreground->getImageWidth();
			$height = $foreground->getImageHeight();

			if ($zoom_width != $width && $zoom_height != $height)
			{
				$foreground->resizeImage($zoom_width, $zoom_height, Imagick::FILTER_CATROM, 1, false);
			}

			if ($rotate > 0)
			{
				$foreground->rotateImage(new ImagickPixel('#ffffff'), $rotate);
			}

			$foreground->cropImage($new_width, $new_height, $offset_x, $offset_y);

			$destination_x = (-$offset_x > 0) ? -$offset_x : 0;
			$destination_y = (-$offset_y > 0) ? -$offset_y : 0;

			$background = new Imagick();
			$background->newImage($new_size, $new_size, '#ffffff');
			$background->setImageFormat('jpg');
			$background->compositeImage($foreground, $foreground->getImageCompose(), $destination_x, $destination_y);

			$background->writeImage($crop_path);

			# FILE FORMAT $resized_images : SYNC_PATH [UID_MICROTIME_CODE(_\d)?_SIZExSIZE.jpg]
			$resized_images = $this->setResizedImages($crop_path, self::SYNC_PATH);

			if (is_array($resized_images))
			{
				copy($resized_images[$new_size], $original_path);

				if (!copy($resized_images[$new_size], $backup_path))
				{
					$response['error'] = 'проблем със сървъра, свържи се с администратор';

					foreach ($resized_images as $image)
					{
						unlink($image);
					}
				}

				if (!DEVELOPMENT && empty($response))
				{
					$this->setSFTP();

					$this->sftp->put($backup_path, $resized_images[$new_size], NET_SFTP_LOCAL_FILE);

					# FILE FORMAT $staging_images : SYNC_PATH [CODE(_\d)?_SIZExSIZE.jpg] | SYNC_PATH [symbolic-name-code(_\d)?_SIZExSIZE.webp]
					$staging_images = $this->getCompressedImages($resized_images, $prefix, $image_id, $webp_file_name);

					if (is_array($staging_images))
					{
						$I_SERVER = [];
						$P_SERVER = [];

						foreach ($staging_images as $size => $image)
						{
							if ($size === $new_size)
							{
								$status_id = intval($product_data['status_id']);

								if ($status_id === 0)
								{
									$product_code = $this->product_images->getProductInfoByProductID($product_id)['code'];
									$all_product_images = $this->product_images->getData($product_id);
									$set_default_image = true;

									foreach ($all_product_images as $value)
									{
										if ($value['status_id'] == 2)
										{
											$set_default_image = false;

											break;
										}
									}

									$status_id = ($product_code === basename($original_file, '.jpg') && $set_default_image) ? 2 : 1;
								}

								$data = [
									'file_name' => self::URL_I . "{$size}x{$size}/{$parent_folder}/" . basename($image['jpg']),
									'status_id' => $status_id,
									'approved' => 1,
									'is_compressed' => $this->is_compressed ? 1 : -1,
									'descriptive_name' => null
								];

								if (array_key_exists('webp', $image))
								{
									$data['descriptive_name'] = self::URL_I . "{$size}x{$size}/{$parent_folder}/" . basename($image['webp']);
								}

								$this->product_images->setProductImage($data, $image_id);
							}

							$external_path = self::PICTURES_PATH . "{$size}x{$size}/{$parent_folder}/" . basename($image['jpg']);

							if (!copy($image['jpg'], $external_path))
							{
								$I_SERVER[$image['jpg']] = $external_path;
							}

							if (!$this->sftp->put($external_path, $image['jpg'], NET_SFTP_LOCAL_FILE))
							{
								$P_SERVER[$image['jpg']] = $external_path;
							}

							if (array_key_exists('webp', $image))
							{
								$external_path = self::PICTURES_PATH . "{$size}x{$size}/{$parent_folder}/" . basename($image['webp']);

								if (!copy($image['webp'], $external_path))
								{
									$I_SERVER[$image['webp']] = $external_path;
								}

								if (!$this->sftp->put($external_path, $image['webp'], NET_SFTP_LOCAL_FILE))
								{
									$P_SERVER[$image['webp']] = $external_path;
								}
							}
						}

						if (!empty($I_SERVER))
						{
							$I_SERVER = $this->repeatCopy($I_SERVER, true);
						}

						if (!empty($P_SERVER))
						{
							$P_SERVER = $this->repeatCopy($P_SERVER, false);
						}

						if (!empty($I_SERVER) || !empty($P_SERVER))
						{
							$response['error'] = 'не са копирани всички снимки';

							$this->sendMail($I_SERVER, $P_SERVER, $product_id);
						}

						foreach ($staging_images as $image)
						{
							if (file_exists($image['jpg']))
							{
								unlink($image['jpg']);
							}

							if (array_key_exists('webp', $image))
							{
								if (file_exists($image['webp']))
								{
									unlink($image['webp']);
								}
							}
						}
					}
					else
					{
						unlink($backup_path);

						$this->sftp->delete($backup_path);

						$response['error'] = $staging_images;
					}
				}
			}
			else
			{
				$response['error'] = 'грешка при създаване на новите размери';
			}

			if (empty($response['error']))
			{
				$response['success'] = 'успешно създаване на снимка';
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_all_image_sizes($image_id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$file_name = $this->product_images->getProductDataByImageID(intval($image_id))['file_name'];

		$response['data'] = [];

		if (empty($file_name))
		{
			$response['error'] = 'изображението не съществува';
		}
		else
		{
			if (preg_match('/be.jarcomputers.com/', $file_name))
			{
				$response['error'] = 'изображението няма други размери';
			}
			else
			{
				$response['data'] = $this->getBaseImageName($file_name, true);
			}
		}

		if ($this->uid != 405)
		{
			$response['error'] = 'недостъпни размери за изображение';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function download()
	{
		if (isset($_GET['url']))
		{
			$_GET['url'] = str_replace('https://p', 'http://i', $_GET['url']);

			header('content-type:image/jpeg');
			header('content-disposition:attachment;filename=' . parse_url(basename($_GET['url']), PHP_URL_PATH));

			readfile($_GET['url']);
		}
	}

	public function credits()
	{
		$this->data['title'] = 'ShortPixel';
		$this->data['path'][] = 'ShortPixel API Keys';

		require_once(dirname(self::ROOT) . '/application/libraries/shortpixel/lib/shortpixel-php-req.php');

		$api_keys = [self::SHORT_PIXEL_BIG_QUOTA];

		foreach ($api_keys as $key)
		{
			try
			{
				ShortPixel\setKey($key);

				$response = \ShortPixel\ShortPixel::getClient()->apiStatus($key);

				$this->data['shortpixel'][$key] = [
					'Общо' => intval($response[0]->APICallsQuotaOneTime),
					'Използвани' => intval($response[0]->APICallsMadeOneTime),
					'DateSubscription' => $response[0]->DateSubscription,
					'DomainCheck' => $response[0]->DomainCheck
				];
			}
			catch (Exception $exception)
			{
				echo "ShortPixel API KEY: $key" . $exception->getMessage();
			}
		}

		$this->render();
	}

	public function stats($input)
	{
		if ($this->user_model->checkRight(10))
		{
			$data['input'] = $input;

			$this->load->view('product_images/stats_tpl', $data);
		}
		else
		{
			redirect('');
		}
	}

	public function get_stats($input)
	{
		$product_files = [];

		if (is_numeric($input))
		{
			$product_images = (array) $this->product_images->getData(intval($input));

			foreach ($product_images as $value)
			{
				if (!preg_match('/be.jarcomputers.com/', $value['file_name']))
				{
					$webp_file_name = empty($value['descriptive_name']) ? null : $this->getBaseImageName(basename($value['descriptive_name'], '.webp'));

					$product_files[] = $this->setResizedImagesPath($this->getBaseImageName($value['file_name'], true), $webp_file_name);
				}
			}
		}
		else
		{
			$product_files[] = $this->setResizedImagesPath(trim($input));
		}

		$codes = [];

		foreach ($product_files as $value)
		{
			$codes[] = $this->getBaseImageName($value[0]['jpg'], true);
		}

		$base = [];
		$additional_codes = [];

		if (count($codes) > 1)
		{
			foreach ($codes as $value)
			{
				$parts = explode('_', $value);

				$base[] = str_replace('_' . end($parts), '', $value);
			}

			$base = array_unique($base);

			if (!in_array($base[0], $codes))
			{
				$additional_codes[] = $base[0];
			}

			$counter = 0;

			while (true)
			{
				++$counter;

				if (!in_array("{$base[0]}_{$counter}", $codes))
				{
					$additional_codes[] = "{$base[0]}_{$counter}";
				}

				if ($counter === 20)
				{
					break;
				}
			}
		}
		else
		{
			$parts = explode('_', $codes[0]);

			$base[] = str_replace('_' . end($parts), '', $codes[0]);

			if (!in_array($base[0], $codes))
			{
				$additional_codes[] = $base[0];
			}

			$counter = 0;

			while (true)
			{
				++$counter;

				if (!in_array("{$base[0]}_{$counter}", $codes))
				{
					$additional_codes[] = "{$base[0]}_{$counter}";
				}

				if ($counter === 20)
				{
					break;
				}
			}
		}

		$scale_type = ['BACKUP'];

		foreach (self::SCALE_DIMENSIONS as $size)
		{
			$scale_type[$size] = "{$size}x{$size}";
		}

		$properties = [
			'header' => [
				'path' => 'Път',
				'image_i' => 'Картинка',
				'image_permission_i' => 'Права',
				'image_owner_i' => 'Собственик',
				'image_group_i' => 'Група',
				'image_size_i' => 'Размер',
				'image_created_i' => 'Създадена',
				'image_changed_i' => 'Променена',
				'image_p' => 'Картинка',
				'image_permission_p' => 'Права',
				'image_size_p' => 'Размер',
				'image_created_p' => 'Създадена',
				'image_changed_p' => 'Променена'
			],
			'scale_type' => $scale_type,
			'additional_codes' => $additional_codes
		];

		$this->setSFTP();

		foreach ($product_files as $id => $value)
		{
			foreach ($value as $key => $file)
			{
				$webp_link_i = '';
				$webp_link_p = '';

				if (array_key_exists('webp', $file))
				{
					$webp_url_i = str_replace(self::PICTURES_PATH, self::URL_I, $file['webp']);
					$webp_link_i = '<a href="' . $webp_url_i . '" target="_blank"><img src="' . $webp_url_i . '?v' . time() . '"></a>';

					$webp_url_p = str_replace(self::PICTURES_PATH, self::URL_P, $file['webp']);
					$webp_link_p = '<a href="' . $webp_url_p . '" target="_blank"><img src="' . $webp_url_p . '?v' . time() . '"></a>';
				}

				$properties[$id]['path'][$key] = str_replace(self::PICTURES_PATH, '', $file['jpg']);

				$link_i = str_replace(self::PICTURES_PATH, self::URL_I, $file['jpg']);

				$properties[$id]['image_i'][$key] = '<a href="' . $link_i . '" target="_blank"><img src="' . $link_i . '?v' . time() . '"></a>' . $webp_link_i;

				$i_stat = stat($file['jpg']);

				$properties[$id]['image_permission_i'][$key] = substr(sprintf('%o', $i_stat['mode']), -4) ?: '*';
				$properties[$id]['image_owner_i'][$key] = $i_stat['uid'] ?: '*';
				$properties[$id]['image_group_i'][$key] = $i_stat['gid'] ?: '*';
				$properties[$id]['image_size_i'][$key] = $this->setSizeFormat($i_stat['size']);
				$properties[$id]['image_created_i'][$key] = date('d.m.Y H:i', $i_stat['mtime']);
				$properties[$id]['image_changed_i'][$key] = date('d.m.Y H:i', $i_stat['ctime']);

				$link_p = str_replace(self::PICTURES_PATH, self::URL_P, $file['jpg']);

				$properties[$id]['image_p'][$key] = '<a href="' . $link_p . '" target="_blank"><img src="' . $link_p . '?v' . time() . '"></a>' . $webp_link_p;

				$p_stat = $this->sftp->stat($file['jpg']);

				$properties[$id]['image_permission_p'][$key] = substr(sprintf('%o', $p_stat['permissions']), -4) ?: '*';
				$properties[$id]['image_size_p'][$key] = $this->setSizeFormat($p_stat['size']);
				$properties[$id]['image_created_p'][$key] = date('d.m.Y H:i', $p_stat['mtime']);
				$properties[$id]['image_changed_p'][$key] = date('d.m.Y H:i', $p_stat['atime']);
			}
		}

		$response['data'] = $properties;

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	private function getImageInfo($file_name)
	{
		$response = [];

		if (preg_match('/be.jarcomputers.com/', $file_name))
		{
			$response['border'] = 'inner';

			$file_name = self::PICTURES_BACKUP_PATH . $this->getBaseImageName($file_name);

			if (file_exists($file_name))
			{
				$response['border'] = 'backup';
			}
			else
			{
				$file_name = self::PRODUCT_PATH . $this->getBaseImageName($file_name);

				if (!file_exists($file_name))
				{
					$response['error'] = '
					изображението
					&#10&#13
					не съществува
					';

					return $response;
				}
			}
		}
		else
		{
			$response['border'] = 'outer';

			if (!DEVELOPMENT)
			{
				$file_name = str_replace(self::URL_P, self::PICTURES_PATH, $file_name);

				if (!file_exists($file_name))
				{
					$response['error'] = '
					изображението
					&#10&#13
					не съществува
					';

					return $response;
				}
			}
		}

		if (exif_imagetype($file_name) !== 2)
		{
			$response['error'] = '
			изображението
			&#10&#13
			не е JPG
			';

			return $response;
		}

		$dimensions = getimagesize($file_name);

		if (is_array($dimensions))
		{
			$response['width'] = $dimensions[0];
			$response['height'] = $dimensions[1];
		}
		else
		{
			try
			{
				$image = new Imagick($file_name);
			}
			catch (Exception $exception)
			{
				$response['error'] = '
				изображението
				&#10&#13
				е повредено
				';

				return $response;
			}

			$response['width'] = $image->getImageWidth();
			$response['height'] = $image->getImageHeight();
		}

		$response['size'] = $this->setSizeFormat((new Imagick($file_name))->getImageLength());

		return $response;
	}

	private function getBaseImageName($value, $remove_extension = false)
	{
		if ($remove_extension)
		{
			$file_name = basename($value, '.jpg');
		}
		else
		{
			$file_name = basename($value);
		}

		$product_image = preg_replace('/(_\d+x\d+)*/', '', $file_name);

		return $product_image;
	}

	private function setSizeFormat($value)
	{
		if (is_numeric($value))
		{
			$value = intval($value);
			$base = 1024;
			$size = ['B', 'kB', 'MB'];
			$counter = -1;

			while (true)
			{
				++$counter;

				if ($value >= $base)
				{
					$value /= $base;
				}
				else
				{
					break;
				}
			}

			$value = ($counter === 0) ? $value : number_format($value, 2, '.', '');

			return $value . $size[$counter];
		}
		else
		{
			return '*';
		}
	}

	private function setUniquePrefix()
	{
		return $this->uid . '_' . str_replace('.', '', microtime(true)) . '_';
	}

	private function setResizedImagesPath($base_jpg_image, $base_webp_image = null)
	{
		$parent_folder = substr(md5($base_jpg_image), 0, 2);

		$image_folders[0]['jpg'] = self::PICTURES_BACKUP_PATH . $base_jpg_image . '.jpg';

		foreach (self::SCALE_DIMENSIONS as $size)
		{
			$image_folders[$size]['jpg'] = self::PICTURES_PATH . "{$size}x{$size}/{$parent_folder}/{$base_jpg_image}_{$size}x{$size}.jpg";

			if (!is_null($base_webp_image))
			{
				$image_folders[$size]['webp'] = self::PICTURES_PATH . "{$size}x{$size}/{$parent_folder}/{$base_webp_image}_{$size}x{$size}.webp";
			}
		}

		return $image_folders;
	}

	private function setResizedImages($image, $sync_path)
	{
		$file_name = basename($image, '.jpg');
		$scaled_images = [];

		$dimensions = getimagesize($image);

		$skip = $dimensions[0];

		foreach (self::SCALE_DIMENSIONS as $size)
		{
			$path = $sync_path . "{$file_name}_{$size}x{$size}.jpg";

			$scaled_images[$size] = $path;

			if ($size === $skip)
			{
				$new_image = $path;

				continue;
			}

			$prime_image = new Imagick($image);
			$prime_image->resizeImage($size, $size, Imagick::FILTER_CATROM, 1, false);
			$prime_image->writeImage($path);

			chmod($path, 0777);
		}

		rename($image, $new_image);

		return $scaled_images;
	}

	private function getCompressedImages($images, $prefix, $image_id, $webp_file_name = null)
	{
		$staging_images = [];
		$sanitised_images = [];

		foreach ($images as $size => $image)
		{
			$file = str_replace($prefix, '', basename($image));

			$staging_images[$size]['jpg'] = self::SYNC_PATH . $file;

			if (!is_null($webp_file_name))
			{
				$jpeg_image = imagecreatefromjpeg($image);

				$webp_image = self::SYNC_PATH . "{$webp_file_name}_{$size}x{$size}.webp";

				if (imagewebp($jpeg_image, $webp_image, 78))
				{
					$staging_images[$size]['webp'] = $webp_image;
				}
				else
				{
					return "не може да се създаде картинка WEBP с размери {$size}x{$size}";
				}
			}

			if ($size >= 150)
			{
				$sanitised_images[$image] = $file;
			}
			else
			{
				rename($image, self::SYNC_PATH . $file);
			}
		}

		$this->is_compressed = true;

		require_once(dirname(self::ROOT) . '/application/libraries/shortpixel/lib/shortpixel-php-req.php');

		try
		{
			ShortPixel\setKey(self::SHORT_PIXEL_BIG_QUOTA);
			ShortPixel\fromFiles(array_keys($sanitised_images))->optimize(2)->toFiles(self::SYNC_PATH, array_values($sanitised_images));
		}
		catch (Exception $exception)
		{
			$this->is_compressed = false;

			$query = "SELECT unity.to_messages('{email}', '{d.prodanov@jarcomputers.com}', 'ShortPixel - {$image_id}', '" . str_replace("'", "''", $exception->getMessage()) . "', 6)";
			$this->db->query($query);
		}
		finally
		{
			$product_id = $this->product_images->getProductDataByImageID($image_id)['product_id'];

			foreach ($sanitised_images as $source => $target)
			{
				if (file_exists(self::SYNC_PATH . $target))
				{
					$dimensions = getimagesize(self::SYNC_PATH . $target);

					$file_dimension = explode('_', $target);
					$file_size = explode('x', end($file_dimension));

					if ($dimensions[0] != $file_size[0])
					{
						$this->sendMail([$dimensions[0], $file_size[0]], [], $product_id);

						copy($source, self::SYNC_PATH . $target);
					}
				}
				else
				{
					$this->sendMail([$target], [], $product_id);

					copy($source, self::SYNC_PATH . $target);
				}
			}

			foreach ($images as $image)
			{
				if (file_exists($image))
				{
					unlink($image);
				}
			}

			foreach ($staging_images as $image)
			{
				if (file_exists($image['jpg']))
				{
					chmod($image['jpg'], 0777);
				}

				if (array_key_exists('webp', $image))
				{
					if (file_exists($image['webp']))
					{
						chmod($image['webp'], 0777);
					}
				}
			}
		}

		return $staging_images;
	}

	private function switchImagesNames($old_image_id, $new_image_id)
	{
		$old_image_data = $this->product_images->getProductDataByImageID($old_image_id);

		$old_product_id = intval($old_image_data['product_id']);
		$old_file_name = $old_image_data['file_name'];

		if (empty($old_file_name))
		{
			return 'няма картинка';
		}

		if ($old_image_data['status_id'] == 2)
		{
			return 'забранена е смяна на основна картинка с друга';
		}

		$new_image_data = $this->product_images->getProductDataByImageID($new_image_id);

		$new_product_id = intval($new_image_data['product_id']);
		$new_file_name = $new_image_data['file_name'];

		if (empty($new_file_name))
		{
			return 'няма картинка';
		}

		if ($old_product_id !== $new_product_id)
		{
			return 'картинките са от различни продукти';
		}

		$old_webp_image = empty($old_image_data['descriptive_name']) ? null : $this->getBaseImageName(basename($old_image_data['descriptive_name'], '.webp'));
		$new_webp_image = empty($new_image_data['descriptive_name']) ? null : $this->getBaseImageName(basename($new_image_data['descriptive_name'], '.webp'));

		if (is_null($old_webp_image) && !is_null($new_webp_image))
		{
			$image_numbers = explode('_', $this->getBaseImageName($old_file_name, true));
			$image_number = '';

			if (count($image_numbers) === 2 && is_numeric($image_numbers[1]))
			{
				$image_number = "_{$image_numbers[1]}";
			}

			$old_webp_image = $this->product_images->getSymbolicFileName($old_product_id) . $image_number;
		}

		if (!is_null($old_webp_image) && is_null($new_webp_image))
		{
			$image_numbers = explode('_', $this->getBaseImageName($new_file_name, true));
			$image_number = '';

			if (count($image_numbers) === 2 && is_numeric($image_numbers[1]))
			{
				$image_number = "_{$image_numbers[1]}";
			}

			$new_webp_image = $this->product_images->getSymbolicFileName($new_product_id) . $image_number;
		}

		$old_images = $this->setResizedImagesPath($this->getBaseImageName($old_file_name, true), $old_webp_image);
		$new_images = $this->setResizedImagesPath($this->getBaseImageName($new_file_name, true), $new_webp_image);

		$temp_images = [];

		$prefix = $this->setUniquePrefix();

		foreach ($old_images as $size => $path)
		{
			$path_jpg = $path['jpg'];

			if (!file_exists($path_jpg))
			{
				continue;
			}

			$temp_image = self::TEMP_PATH . $prefix . basename($path_jpg);

			$temp_images[$size]['jpg'] = $temp_image;

			if (!copy($path_jpg, $temp_image))
			{
				return "картинката {$path_jpg} не може да бъде копирана";
			}

			if (array_key_exists('webp', $path))
			{
				$path_webp = $path['webp'];

				if (!file_exists($path_webp))
				{
					continue;
				}

				$temp_image = self::TEMP_PATH . $prefix . basename($path_webp);

				$temp_images[$size]['webp'] = $temp_image;

				if (!copy($path_webp, $temp_image))
				{
					return "картинката {$path_webp} не може да бъде копирана";
				}
			}
		}

		$this->setSFTP();

		$I_SERVER = [];
		$P_SERVER = [];

		foreach ($new_images as $size => $path)
		{
			$path_jpg = $path['jpg'];

			if (!file_exists($path_jpg))
			{
				continue;
			}

			if (!copy($path_jpg, $old_images[$size]['jpg']))
			{
				$I_SERVER[$path_jpg] = $old_images[$size]['jpg'];
			}

			if (!$this->sftp->put($old_images[$size]['jpg'], $path_jpg, NET_SFTP_LOCAL_FILE))
			{
				$P_SERVER[$path_jpg] = $old_images[$size]['jpg'];
			}

			if (array_key_exists('webp', $path))
			{
				$path_webp = $path['webp'];

				if ((file_exists($path_webp) && file_exists($old_images[$size]['webp'])) || !file_exists($old_images[$size]['webp']))
				{
					if (!copy($path_webp, $old_images[$size]['webp']))
					{
						$I_SERVER[$path_webp] = $old_images[$size]['webp'];
					}

					if (!$this->sftp->put($old_images[$size]['webp'], $path_webp, NET_SFTP_LOCAL_FILE))
					{
						$P_SERVER[$path_webp] = $old_images[$size]['webp'];
					}
				}
				else if (!file_exists($path_webp))
				{
					if (file_exists($old_images[$size]['webp']))
					{
						unlink($old_images[$size]['webp']);
					}

					if ($this->sftp->file_exists($old_images[$size]['webp']))
					{
						$this->sftp->delete($old_images[$size]['webp']);
					}
				}
			}
		}

		if (!empty($I_SERVER))
		{
			$I_SERVER = $this->repeatCopy($I_SERVER, true);
		}

		if (!empty($P_SERVER))
		{
			$P_SERVER = $this->repeatCopy($P_SERVER, false);
		}

		if (!empty($I_SERVER) || !empty($P_SERVER))
		{
			$this->sendMail($I_SERVER, $P_SERVER, $old_product_id);

			return 'не са сменени всички снимки';
		}

		foreach ($temp_images as $size => $path)
		{
			$path_jpg = $path['jpg'];

			if (!file_exists($path_jpg))
			{
				continue;
			}

			if (!copy($path_jpg, $new_images[$size]['jpg']))
			{
				$I_SERVER[$path_jpg] = $new_images[$size]['jpg'];
			}

			if (!$this->sftp->put($new_images[$size]['jpg'], $path_jpg, NET_SFTP_LOCAL_FILE))
			{
				$P_SERVER[$path_jpg] = $new_images[$size]['jpg'];
			}

			if (array_key_exists('webp', $path))
			{
				$path_webp = $path['webp'];

				if ((file_exists($path_webp) && file_exists($new_images[$size]['webp'])) || !file_exists($new_images[$size]['webp']))
				{
					if (!copy($path_webp, $new_images[$size]['webp']))
					{
						$I_SERVER[$path_webp] = $new_images[$size]['webp'];
					}

					if (!$this->sftp->put($new_images[$size]['webp'], $path_webp, NET_SFTP_LOCAL_FILE))
					{
						$P_SERVER[$path_webp] = $new_images[$size]['webp'];
					}
				}
				else if (!file_exists($path_webp))
				{
					if (file_exists($new_images[$size]['webp']))
					{
						unlink($new_images[$size]['webp']);
					}

					if ($this->sftp->file_exists($new_images[$size]['webp']))
					{
						$this->sftp->delete($new_images[$size]['webp']);
					}
				}
			}
		}

		if (!empty($I_SERVER))
		{
			$I_SERVER = $this->repeatCopy($I_SERVER, true);
		}

		if (!empty($P_SERVER))
		{
			$P_SERVER = $this->repeatCopy($P_SERVER, false);
		}

		if (!empty($I_SERVER) || !empty($P_SERVER))
		{
			$this->sendMail($I_SERVER, $P_SERVER, $old_product_id);

			return 'не са сменени всички снимки';
		}

		foreach ($temp_images as $image)
		{
			if (file_exists($image['jpg']))
			{
				unlink($image['jpg']);
			}

			if (array_key_exists('webp', $image))
			{
				if (file_exists($image['webp']))
				{
					unlink($image['webp']);
				}
			}
		}

		$old_product_image = self::PRODUCT_PATH . $this->getBaseImageName($old_file_name);
		$new_product_image = self::PRODUCT_PATH . $this->getBaseImageName($new_file_name);

		copy($old_images[0]['jpg'], $old_product_image);
		copy($new_images[0]['jpg'], $new_product_image);

		$old_size = basename(dirname(dirname($old_file_name)));
		$new_size = basename(dirname(dirname($new_file_name)));

		$old_webp = $old_image_data['descriptive_name'] ?: null;
		$new_webp = $new_image_data['descriptive_name'] ?: null;

		if (empty($old_image_data['descriptive_name']) && !empty($new_image_data['descriptive_name']))
		{
			$old_webp = str_replace(self::PICTURES_PATH, self::URL_I, $old_images[explode('x', $new_size)[0]]['webp']);
			$new_webp = null;
		}

		if (!empty($old_image_data['descriptive_name']) && empty($new_image_data['descriptive_name']))
		{
			$old_webp = null;
			$new_webp = str_replace(self::PICTURES_PATH, self::URL_I, $new_images[explode('x', $old_size)[0]]['webp']);
		}

		$result = [
			$old_image_id => [
				'jpg' => str_replace(['680x680', '1000x1000'], $old_size, $new_file_name),
				'webp' => $new_webp
			],
			$new_image_id => [
				'jpg' => str_replace(['680x680', '1000x1000'], $new_size, $old_file_name),
				'webp' => $old_webp
			]
		];

		return $result;
	}

	private function convertPNG($file)
	{
		$convert_image = self::PRODUCT_PATH . basename($file, '.png') . '.jpg';

		$image = imagecreatefrompng($file);

		unlink($file);

		$background = imagecreatetruecolor(imagesx($image), imagesy($image));

		imagefill($background, 0, 0, imagecolorallocate($background, 255, 255, 255));
		imagealphablending($background, true);
		imagecopy($background, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);

		if (imagejpeg($background, $convert_image, 100))
		{
			imagedestroy($background);

			return true;
		}
		else
		{
			return 'изображение "png" не може да бъде конвертирано';
		}
	}

	private function convertWEBP($file)
	{
		$convert_image = self::PRODUCT_PATH . basename($file, '.webp') . '.jpg';

		$image = imagecreatefromwebp($file);

		unlink($file);

		$background = imagecreatetruecolor(imagesx($image), imagesy($image));

		imagefill($background, 0, 0, imagecolorallocate($background, 255, 255, 255));
		imagealphablending($background, true);
		imagecopy($background, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);

		if (imagejpeg($background, $convert_image, 100))
		{
			imagedestroy($background);

			return true;
		}
		else
		{
			return 'изображение "webp" не може да бъде конвертирано';
		}
	}

	private function repeatCopy($server, $is_local)
	{
		$repeat = 0;

		while (true)
		{
			++$repeat;

			foreach ($server as $local_file => $external_file)
			{
				usleep(100000);

				if ($is_local)
				{
					if (copy($local_file, $external_file))
					{
						unset($server[$local_file]);
					}
				}
				else
				{
					if ($this->sftp->put($external_file, $local_file, NET_SFTP_LOCAL_FILE))
					{
						unset($server[$local_file]);
					}
				}
			}

			if ($repeat === 3 || empty($server))
			{
				break;
			}
		}

		return $server;
	}

	private function sendMail($I_SERVER, $P_SERVER, $product_id)
	{
		$this->load->model('staff_model', 'staff');

		$email = $this->staff->getStaff()[$this->uid]['email'];

		if (!empty($email) && $this->uid == 405)
		{
			$style = '
			<style>
			h1 {margin:0; padding:0.4em; color:#ffffff; background:#1A64A4;}
			h2 {margin:0; padding:0.4em; color:#ffffff; background:#ff3300; font-size:2em;}
			h1 a {color:#ffffff; font-size:2.2em; text-decoration:none;}
			b {padding:0.4em; font-size:1.4em; font-weight:bold;}
			</style>
			';

			$heading = '
			<h1>
			<a href="https://be.jarcomputers.com/pricing?product=' . $product_id . '">Приложко &raquo; Продукт</a>
			</h1>
			<h2>Несъздадени картинки!</h2>
			';

			$note = '';

			if (!empty($I_SERVER))
			{
				foreach ($I_SERVER as $I)
				{
					$note .= "<br><b>I &raquo; $I</b>";
				}
			}

			if (!empty($P_SERVER))
			{
				foreach ($P_SERVER as $P)
				{
					$note .= "<br><b>P &raquo; $P</b>";
				}
			}

			$message = $style . $heading . str_replace(self::PICTURES_PATH, '', $note);

			$query = "SELECT unity.to_messages('{email}', '{" . $email . "}', 'Приложко - Снимки на Продукт', '$message', 6)";

			$this->db->query($query);
		}
	}

	private function setSFTP()
	{
		chdir(dirname(self::ROOT) . '/application/libraries/phpseclib/');

		require_once('Net/SFTP.php');
		require_once('Crypt/RSA.php');

		$this->sftp = new Net_SFTP('p.jarnet', 2022);

		$private_key = '
-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----
		';

		$key = new Crypt_RSA();

		$key->loadKey(trim($private_key));

		if (!$this->sftp->login('picsync', $key))
		{
			$this->sftp = null;
		}
	}

	public function copySiblingImages($old_product, $new_product)
	{
		require_once(dirname(__DIR__) . '/models/Product_images_model.php');

		$model_product_images = new Product_images_model;

		$product_images = $model_product_images->getData($old_product);
		$old_product_info = (array) $model_product_images->getProductInfoByProductID($old_product);
		$new_product_info = (array) $model_product_images->getProductInfoByProductID($new_product);
		$descriptive_name = $model_product_images->getSymbolicFileName($new_product);

		$eligible_images = [];

		foreach ($product_images as $value)
		{
			if ($value['status_id'] != 0 && !preg_match('/be.jarcomputers.com/', $value['file_name']))
			{
				$old_base_name = $this->getBaseImageName($value['file_name'], true);
				$old_webp_file_name = empty($value['descriptive_name']) ? null : $this->getBaseImageName(basename($value['descriptive_name'], '.webp'));

				$value['source'] = $this->setResizedImagesPath($old_base_name, $old_webp_file_name);

				$suffix = str_replace($old_product_info['code'], '', $old_base_name);

				$value['target'] = $this->setResizedImagesPath($new_product_info['code'] . $suffix, $descriptive_name . $suffix);

				$size = explode('x', basename(dirname(dirname($value['file_name']))))[0];

				$value['data'] = [
					'product_id' => $new_product,
					'file_name' => str_replace(self::PICTURES_PATH, self::URL_I, $value['target'][$size]['jpg']),
					'sorder' => $value['sorder'],
					'status_id' => $value['status_id'],
					'created' => 'now()',
					'file_name_md5' => md5(basename($value['target'][0]['jpg'])),
					'approved' => 1,
					'is_compressed' => intval($value['is_compressed']),
					'description' => $value['description'] ?: null,
					'descriptive_name' => is_null($old_webp_file_name) ? null : str_replace(self::PICTURES_PATH, self::URL_I, $value['target'][$size]['webp'])
				];

				$eligible_images[] = $value;
			}
		}

		if (!DEVELOPMENT)
		{
			$this->setSFTP();

			foreach ($eligible_images as $value)
			{
				foreach ($value['source'] as $size => $source)
				{
					copy($source['jpg'], $value['target'][$size]['jpg']);

					$this->sftp->put($value['target'][$size]['jpg'], $source['jpg'], NET_SFTP_LOCAL_FILE);

					if (array_key_exists('webp', $source))
					{
						copy($source['webp'], $value['target'][$size]['webp']);

						$this->sftp->put($value['target'][$size]['webp'], $source['webp'], NET_SFTP_LOCAL_FILE);
					}
				}

				$model_product_images->setProductImage($value['data']);
			}
		}
	}

	public function compress($from_image_id, $to_image_id)
	{
		$from_image_id = is_numeric($from_image_id) ? abs(intval($from_image_id)) : false;
		$to_image_id = is_numeric($to_image_id) ? abs(intval($to_image_id)) : false;
		$image_id_range = 200000;

		$this->data['title'] = 'Компресиране';
		$this->data['from_image_id'] = 0;
		$this->data['to_image_id'] = 0;

		$error = [];

		if (!$from_image_id)
		{
			$error[] = 'първи параметър не е положително число';
		}

		if (!$to_image_id)
		{
			$error[] = 'втори параметър не е положително число';
		}

		if (empty($error))
		{
			if ($from_image_id < $to_image_id)
			{
				if ($to_image_id - $from_image_id > $image_id_range)
				{
					$error[] = "диапазон между първи и втори параметър е по-голям от {$image_id_range}";
				}
				else
				{
					$this->data['title'] = "Компресиране ({$from_image_id} - {$to_image_id})";
					$this->data['from_image_id'] = $from_image_id;
					$this->data['to_image_id'] = $to_image_id;
				}
			}
			else
			{
				$error[] = 'първи параметър трябва да е по-малък от втори';
			}
		}

		$this->data['error'] = $error;

		$this->render();
	}

	public function set_compress_image($from_image_id, $to_image_id)
	{
		$start_request = microtime(true);

		$image_data = $this->product_images->getImageToCompress($from_image_id, $to_image_id);

		$response = [];

		if (is_null($image_data))
		{
			$response['error'] = 'няма картинки за компресиране в този диапазон';
		}

		if (DEVELOPMENT)
		{
			$response['error'] = 'изисква https://be.jarcomputers.com';
		}

		if (empty($response))
		{
			$file_name = self::PICTURES_BACKUP_PATH . $this->getBaseImageName($image_data['file_name']);

			$data['is_compressed'] = -1;

			if (file_exists($file_name))
			{
				$dimensions = getimagesize($file_name);

				if ($dimensions[0] != $dimensions[1] && !in_array($dimensions[0], self::IMAGE_DIMENSIONS))
				{
					$response['error'] = "{$image_data['id']} BACKUP не е с правилен размер";
				}
				else
				{
					$parent_folder = basename(dirname($image_data['file_name']));

					$prefix = $this->setUniquePrefix();
					$compress_path = self::COMPRESS_PATH . $prefix . basename($file_name);

					copy($file_name, $compress_path);

					chmod($compress_path, 0777);

					# FILE FORMAT $resized_images : COMPRESS_PATH [UID_MICROTIME_CODE(_\d)?_SIZExSIZE.jpg]
					$resized_images = $this->setResizedImages($compress_path, self::COMPRESS_PATH);

					$staging_images = [];
					$sanitised_images = [];

					foreach ($resized_images as $size => $image)
					{
						$file = str_replace($prefix, '', basename($image));

						$staging_images[$size] = self::COMPRESS_PATH . $file;

						if ($size >= 150)
						{
							$sanitised_images[$image] = $file;
						}
						else
						{
							rename($image, self::COMPRESS_PATH . $file);
						}
					}

					$is_successful = true;

					require_once(dirname(self::ROOT) . '/application/libraries/shortpixel/lib/shortpixel-php-req.php');

					try
					{
						ShortPixel\setKey(self::SHORT_PIXEL_BIG_QUOTA);
						ShortPixel\fromFiles(array_keys($sanitised_images))->optimize(2)->toFiles(self::COMPRESS_PATH, array_values($sanitised_images));
					}
					catch (Exception $exception)
					{
						$is_successful = false;
					}

					foreach ($sanitised_images as $source => $target)
					{
						if (file_exists(self::COMPRESS_PATH . $target))
						{
							$dimensions = getimagesize(self::COMPRESS_PATH . $target);

							$file_dimension = explode('_', $target);
							$file_size = explode('x', end($file_dimension));

							if ($dimensions[0] != $file_size[0])
							{
								$is_successful = false;

								copy($source, self::COMPRESS_PATH . $target);
							}
						}
						else
						{
							$is_successful = false;

							copy($source, self::COMPRESS_PATH . $target);
						}
					}

					foreach ($resized_images as $image)
					{
						if (file_exists($image))
						{
							unlink($image);
						}
					}

					foreach ($staging_images as $image)
					{
						if (file_exists($image))
						{
							chmod($image, 0777);
						}
					}

					$this->setSFTP();

					$I_SERVER = [];
					$P_SERVER = [];

					foreach ($staging_images as $size => $image)
					{
						$external_path = self::PICTURES_PATH . "{$size}x{$size}/{$parent_folder}/" . basename($image);

						if (!copy($image, $external_path))
						{
							$I_SERVER[$image] = $external_path;
						}

						if (!$this->sftp->put($external_path, $image, NET_SFTP_LOCAL_FILE))
						{
							$P_SERVER[$image] = $external_path;
						}
					}

					if (!empty($I_SERVER))
					{
						$I_SERVER = $this->repeatCopy($I_SERVER, true);
					}

					if (!empty($P_SERVER))
					{
						$P_SERVER = $this->repeatCopy($P_SERVER, false);
					}

					if (!empty($I_SERVER) || !empty($P_SERVER))
					{
						$this->sendMail($I_SERVER, $P_SERVER, $image_data['product_id']);
					}
					else
					{
						if ($is_successful)
						{
							$data['is_compressed'] = 1;
						}
					}

					foreach ($staging_images as $image)
					{
						if (file_exists($image))
						{
							unlink($image);
						}
					}
				}
			}
			else
			{
				$response['error'] = "{$image_data['id']} BACKUP не съществува";
			}

			$this->product_images->setProductImage($data, $image_data['id']);

			$end_request = microtime(true);

			$compress_operation = number_format($end_request - $start_request, 2, '.', '');

			if (empty($response))
			{
				$response['success'] = [
					'image_id' => $image_data['id'],
					'product_id' => $image_data['product_id'],
					'time' => $compress_operation
				];
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_bing_search()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$error = [];

		$keywords = trim($post['keywords']);

		if (empty($keywords))
		{
			$error[] = 'въведи думи за търсене';
		}
		else if (boolval(preg_match('#^http(s)?://#', $keywords)))
		{
			$error[] = 'bing търси само по думи';
		}
		else
		{
			$url = 'https://www.bing.com/images/search?q=' . urlencode($keywords) . '&qft=filterui:imagesize-custom_1000_1000';

			$curl = curl_init();

			$curl_options = [
				CURLOPT_URL => $url,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_REFERER => $url,
				CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT']
			];

			curl_setopt_array($curl, $curl_options);

			$html_string = curl_exec($curl);

			curl_close($curl);

			require_once(dirname(__DIR__) . '/third_party/simple_html_dom/HtmlDocument.php');

			$html = new HtmlDocument();

			$html->load($html_string);

			$images = array_slice($html->find('li div.imgpt'), 0, 28);

			$html->clear();
			unset($html);

			$data = [];

			foreach ($images as $value)
			{
				$json = json_decode($value->nodes[0]->attr['m'], true);

				$data[] = [
					'title' => $json['t'],
					'image' => $json['murl'],
					'thumb' => $json['turl'],
					'size' => implode(' ', array_slice(explode(' ', $value->find('div.img_info span', 0)->plaintext), 0, 3))
				];
			}

			if (empty($data))
			{
				$error[] = 'това търсене не връща резултат';
			}
		}

		if (empty($error))
		{
			$response['data'] = $data;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_yandex_search()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$error = [];

		$keywords = trim($post['keywords']);

		if (empty($keywords))
		{
			$error[] = 'въведи думи за търсене или линк към картинка';
		}
		else
		{
			$url = 'https://yandex.com/images/search?';

			if (preg_match('#^http(s)?://#', $keywords))
			{
				$url .= 'source=collections&rpt=imageview&url=' . urlencode($keywords);
			}
			else
			{
				$url .= 'text=' . urlencode($keywords) . '&isize=large';
			}

			$query = parse_url($url, PHP_URL_QUERY);

			parse_str($query, $parse_str);

			$curl = curl_init();

			$curl_options = [
				CURLOPT_URL => $url,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_REFERER => $url,
				CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT']
			];

			curl_setopt_array($curl, $curl_options);

			$html_string = curl_exec($curl);

			curl_close($curl);

			require_once(dirname(__DIR__) . '/third_party/simple_html_dom/HtmlDocument.php');

			$html = new HtmlDocument();

			$html->load($html_string);

			if (array_key_exists('text', $parse_str))
			{
				$images = array_slice($html->find('div.serp-item'), 0, 28);

				$html->clear();
				unset($html);

				$data = [];

				foreach ($images as $value)
				{
					$json = json_decode($value->attr['data-bem'], true)['serp-item'];

					$data[] = [
						'title' => $json['snippet']['title'],
						'image' => $json['preview'][0]['url'],
						'thumb' => $json['thumb']['url'],
						'size' => $json['preview'][0]['w'] . ' x ' . $json['preview'][0]['h']
					];
				}
			}
			else
			{
				$images = array_slice($html->find('li.cbir-similar__thumb>a'), 0, 28);

				$html->clear();
				unset($html);

				$data = [];

				foreach ($images as $value)
				{
					parse_str($value->attr['href'], $parse_str);

					$data[] = [
						'title' => '',
						'image' => $parse_str['img_url'],
						'thumb' => $value->nodes[0]->attr['src'],
						'size' => ''
					];
				}
			}

			if (empty($data))
			{
				$error[] = 'това търсене не връща резултат';
			}
		}

		if (empty($error))
		{
			$response['data'] = $data;
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function get_icecat_search()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('');
		}

		$post = $this->input->post();

		$error = [];

		$product_id = intval($post['keywords']);

		if ($product_id > 0)
		{
			$product_info = $this->product_images->getProductInfoByProductID($product_id);

			$brand = $product_info['brand'];
			$product_code = $product_info['producer_code'];
			$ean = $product_info['ean'];

			$url = "https://live.icecat.biz/api/?UserName=zarojar&Language=en&Content=Gallery&Brand=$brand&ProductCode=$product_code";

			if (!empty($ean))
			{
				$url = "https://live.icecat.biz/api/?UserName=zarojar&Language=en&Content=Gallery&GTIN=$ean";
			}

			$curl = curl_init();

			curl_setopt_array($curl, [
				CURLOPT_URL => $url,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_HEADER => false,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => [
					'Authorization: Basic ' . base64_encode('zarojar:1c3c@t')
				]
			]);

			$request = curl_exec($curl);

			curl_close($curl);

			$request = json_decode($request, true);

			if (is_array($request) && array_key_exists('statusCode', $request))
			{
				$error[] = $request['message'] . PHP_EOL . '<a href="' . $url . '" target="_blank">Icecat Request</a>';
			}
			else
			{
				$data = [];

				$gallery = $request['data']['Gallery'];

				foreach ($gallery as $value)
				{
					if ($value['PicHeight'] >= 1000 || $value['PicWidth'] >= 1000)
					{
						$data[] = [
							'title' => '',
							'image' => $value['Pic'],
							'thumb' => $value['LowPic'],
							'size' => $value['PicWidth'] . ' x ' . $value['PicHeight']
						];
					}
				}
			}
		}
		else
		{
			$error[] = 'грешен номер на продукт';
		}

		if (empty($error))
		{
			if (empty($data))
			{
				$response['error'] = 'icecat няма картинки с достатъчно големи размери';
			}
			else
			{
				$response['data'] = $data;
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function debug()
	{

	}
}
?>