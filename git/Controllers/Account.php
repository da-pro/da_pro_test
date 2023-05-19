<?php
namespace App\Controllers;

use App\Models\Account_model;

class Account extends BaseController
{
    private $account_model = null;

    public function __construct()
    {
        parent::__contruct();

        $this->account_model = new Account_model;
    }

    public function dashboard()
    {
        $bottle_model = model(Bottle_model::class);

        $data = [
            'title' => SITE_TITLE . 'Начало',
            'purchase_orders' => $bottle_model->getPurchaseOrders()
        ];

        return view('account/index', $data);
    }

    public function change_password()
    {
        if (!$this->request->isAJAX())
        {
            return redirect()->to('');
        }

        $error = [];

        $post = $this->request->getPost();

        $old_password = $post['old_password'];
        $new_password = $post['new_password'];
        $confirm_new_password = $post['confirm_new_password'];

        if (empty($old_password))
        {
            $error['old_password'] = 'въведи стара парола';
        }

        if (empty($new_password))
        {
            $error['new_password'] = 'въведи нова парола';
        }

        if (empty($confirm_new_password))
        {
            $error['confirm_new_password'] = 'въведи потвърдена нова парола';
        }

        if (empty($error))
        {
            if (!preg_match(Account_model::REGEX['PASSWORD'], $old_password))
            {
                $error['old_password'] = 'стара парола '. Account_model::REGEX_DESCRIPTION['PASSWORD'];
            }
            else
            {
                if (strlen($old_password) < Account_model::LENGTH['MINIMUM_PASSWORD'] || strlen($old_password) > Account_model::LENGTH['MAXIMUM_PASSWORD'])
                {
                    $error['old_password'] = 'стара парола трябва да е между '. Account_model::LENGTH['MINIMUM_PASSWORD'] .' и '. Account_model::LENGTH['MAXIMUM_PASSWORD'] .' символа';
                }
            }

            if ($new_password !== $confirm_new_password)
            {
                $error['new_password|confirm_new_password'] = 'новите пароли не съвпадат';
            }
            else
            {
                if (!preg_match(Account_model::REGEX['PASSWORD'], $new_password))
                {
                    $error['new_password|confirm_new_password'] = 'нова парола '. Account_model::REGEX_DESCRIPTION['PASSWORD'];
                }
                else
                {
                    if (strlen($new_password) < Account_model::LENGTH['MINIMUM_PASSWORD'] || strlen($new_password) > Account_model::LENGTH['MAXIMUM_PASSWORD'])
                    {
                        $error['new_password|confirm_new_password'] = 'нова парола трябва да е между '. Account_model::LENGTH['MINIMUM_PASSWORD'] .' и '. Account_model::LENGTH['MAXIMUM_PASSWORD'] .' символа';
                    }
                }
            }
        }

        if (empty($error))
        {
            $db = db_connect();

            $user_id = getUserID();

            $query = "SELECT val, created FROM public.winconfig WHERE id = {$user_id}";

            $row = $db->query($query)->getRowArray();

            $unique = md5($row['created']);

            if (!password_verify($unique . $old_password, $row['val']))
            {
                $error['old_password'] = 'грешна стара парола';
            }
            else
            {
                if ($old_password === $new_password)
                {
                    $error['old_password|new_password|confirm_new_password'] = 'стара и нова парола съвпадат';
                }
                else
                {
                    $password = password_hash($unique . $new_password, PASSWORD_BCRYPT);

                    $query = "
                    UPDATE public.winconfig
                    SET val = '{$password}'
                    WHERE id = {$user_id}
                    ";

                    $result = $db->query($query);

                    if (!$result)
                    {
                        $error[] = 'грешка при смяна на парола';
                    }
                }
            }
        }

        $response = [];

        if (empty($error))
        {
            $response['success'] = 'успешно сменена парола';
        }
        else
        {
            $response['error'] = $error;
        }

        return $this->response->setJSON($response);
    }

    public function logout()
    {
        if (!empty($_SESSION))
        {
            $session_keys = array_keys($_SESSION);

            foreach ($session_keys as $value)
            {
                unset($_SESSION[$value]);
            }
        }

        setLocation();
    }
}