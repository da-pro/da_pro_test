<?php
namespace App\Controllers;

use App\Models\Bottle_model;

final class Bottle extends BaseController
{
	private $bottle_model = null;

	public function __construct()
	{
		parent::__contruct();

		$this->bottle_model = new Bottle_model;
	}

	public function data()
	{
		if (!$this->request->isAJAX())
		{
			return redirect()->to('');
		}

		$post = $this->request->getPost();

		$filtering = json_decode($post['filtering'], true);
		$sorting = json_decode($post['sorting'], true);
		$page = isset($post['page']) ? abs(intval($post['page'])) : 1;

		$response = [
			'data' => [],
			'rows' => 0
		];

		$request = $this->bottle_model->getBottles($filtering, $sorting, $page);

		if (is_array($request['data']) && !empty($request['data']))
		{
			$response = $request;
		}

		return $this->response->setJSON($response);
	}
}