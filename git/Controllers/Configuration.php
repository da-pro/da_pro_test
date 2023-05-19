<?php
namespace App\Controllers;

use App\Models\Configuration_model;

final class Configuration extends BaseController
{
	private $configuration_model = null;

	public function __construct()
	{
		parent::__contruct();

		$this->configuration_model = new Configuration_model;
	}

	public function data()
	{
		if (!$this->request->isAJAX())
		{
			return redirect()->to('');
		}

		$response['data'] = [];

		$request = $this->configuration_model->getConfigurations();

		if (is_array($request) && !empty($request))
		{
			$response['data'] = $request;
		}

		return $this->response->setJSON($response);
	}

	public function get($id)
	{
		$data = [
			'ename' => '',
			'val' => '',
			'note' => ''
		];

		if ($id > 0)
		{
			$id = intval($id);

			$data = $this->configuration_model->getConfiguration($id);
		}
		else
		{
			$id = 0;
		}

		$data['id'] = $id;

		echo view('configuration/form', $data);
	}

	public function set()
	{
		if (!$this->request->isAJAX())
		{
			return redirect()->to('');
		}

		$error = [];

		$post = $this->request->getPost();

		$id = intval($post['id']);
		$ename = trim($post['ename']);
		$val = trim($post['val']);
		$note = trim($post['note']);

		$data = [];

		if ($id === 0)
		{
			$id = null;
		}

		if (empty($ename))
		{
			$error['ename'] = 'въведи име';
		}
		else
		{
			$data['ename'] = $ename;
		}

		if (empty($val))
		{
			$error['val'] = 'въведи стойност';
		}
		else
		{
			$data['val'] = $val;
		}

		$data['note'] = $note;

		$response = [];

		if (empty($error))
		{
			$request = $this->configuration_model->setConfiguration($data, $id);

			if (is_integer($request))
			{
				$response['success'] = 'успешно '. (is_null($id) ? 'създадена' : 'обновена') .' конфигурационна стойност';
			}
			else
			{
				$response['error'][] = $request;
			}
		}
		else
		{
			$response['error'] = $error;
		}

		return $this->response->setJSON($response);
	}

	public function remove($id)
	{
		if (!$this->request->isAJAX())
		{
			return redirect()->to('');
		}

		$id = intval($id);

		$response = [];

		if ($id > 0)
		{
			$request = $this->configuration_model->unsetConfiguration($id);

			if ($request)
			{
				$response['success'] = 'успешно изтрит запис';
			}
			else
			{
				$response['error'][] = 'възникна грешка';
			}
		}
		else
		{
			$response['error'][] = 'невалидна стойност';
		}

		return $this->response->setJSON($response);
	}
}