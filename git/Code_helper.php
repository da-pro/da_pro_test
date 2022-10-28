<?php
# [AUTHOR] darin prodanov
# [EMAIL] d.prodanov@jarcomputers.com
# [CREATED] 12/01/2022
# [MODIFIED] 15/06/2022
final class Code_helper extends J_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!$this->user_model->checkRight(10))
		{
			show_404();
		}

		$this->load->model('code_helper_model', 'code_helper');
	}

	public function index()
	{
		$this->data['title'] = 'Помощни Кодове';
		$this->data['path'][] = 'Помощни Кодове';

		$this->data['user_id'] = $this->uid;
		$this->data['language'] = $this->code_helper->getLanguage();
		$this->data['staff'] = $this->code_helper->getStaff();

		$this->render();
	}

	public function form($id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('code_helper');
		}

		if (is_numeric($id) && $id > 0)
		{
			$id = intval($id);

			$code = $this->code_helper->getCodeByID($id);
		}

		if (empty($code))
		{
			$id = 0;

			$code = [
				'body' => '',
				'code' => '',
				'lang' => 'plaintext',
				'right' => 'write',
				'created_by' => $this->session->name,
				'viewed_by' => []
			];
		}

		$this->data['id'] = $id;
		$this->data['code'] = $code;
		$this->data['language'] = $this->code_helper->getLanguage();

		$this->render('clear_all');
	}

	public function get_code()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('code_helper');
		}

		$response['data'] = [];

		$request = $this->code_helper->getCode();

		if (is_array($request) && !empty($request))
		{
			$response['data'] = $request;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_code()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('code_helper');
		}

		$post = $this->input->post();

		$form = ['id', 'body', 'code', 'lang', 'viewed_by'];

		$error = [];

		foreach ($form as $value)
		{
			$$value = trim($post[$value]);
		}

		$id = intval($id);

		if ($id === 0)
		{
			$id = null;
		}

		if (empty($body))
		{
			$error['body'] = 'въведи заглавие';
		}

		if (empty($code))
		{
			$error['code'] = 'въведи код';
		}

		if (empty($lang))
		{
			$error['lang'] = 'избери език';
		}
		else
		{
			$language = $this->code_helper->getLanguage();

			if (!in_array($lang, array_keys($language)))
			{
				$error['lang'] = 'грешен език';
			}
		}

		$viewed_by = json_decode($viewed_by, true);

		if (in_array($this->uid, $viewed_by))
		{
			unset($viewed_by[array_search($this->uid, $viewed_by)]);
		}

		if (empty($viewed_by))
		{
			$viewed_by = '{}';
		}
		else
		{
			$viewed_by = '{' . implode(',', $viewed_by) . '}';
		}

		if (empty($error))
		{
			$data = [
				'body' => $body,
				'code' => $code,
				'lang' => $lang,
				'viewed_by' => $viewed_by
			];

			$request = $this->code_helper->setCode($id, $data);

			if (is_integer($request))
			{
				$response['success']['message'] = is_null($id) ? 'успешно добавен код' : 'успешно обновен код';
				$response['success']['id'] = $request;
			}
			else
			{
				$response['error'] = 'възникна грешка';
			}
		}
		else
		{
			$response['error'] = $error;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function unset_code($id)
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('code_helper');
		}

		if (is_numeric($id) && ($id == intval($id)))
		{
			$request = $this->code_helper->unsetCode(intval($id));

			if ($request)
			{
				$response['success'] = 'успешно изтрит код';
			}
			else
			{
				$response['error'] = 'възникна грешка';
			}
		}
		else
		{
			$response['error'] = 'грешно предаден код';
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function set_inquiry()
	{
		if (!$this->input->is_ajax_request())
		{
			redirect('code_helper');
		}

		$post = $this->input->post();

		$error = [];

		$body = trim($post['body']);
		$code = trim($post['code']);
		$is_create_inquiry = boolval($post['is_create_inquiry']);

		if (empty($body))
		{
			$error['body'] = 'въведи заглавие';
		}
		else
		{
			$body = str_replace("'", "''", $body);
		}

		if (empty($code))
		{
			$error['code'] = 'въведи код';
		}
		else
		{
			$code = str_replace("'", "''", $code);
		}

		if (empty($error))
		{
			$request = $this->code_helper->setInquiry($body, $code, $is_create_inquiry);

			if ($request)
			{
				$response['success'] = null;
				$response['id'] = $request;
			}
			else
			{
				$response['error'] = 'възникна грешка';
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