<?php
namespace App\Controllers;

use App\Models\Account_model;

class Login extends BaseController
{
    public function index()
    {
        if (!$this->request->isAJAX())
        {
            return redirect()->to('');
        }

        $error = [];

        $post = $this->request->getPost();

        $username = trim($post['username']);
        $password = $post['password'];

        if (empty($username))
        {
            $error['username'] = 'въведи потребител';
        }

        if (empty($password))
        {
            $error['password'] = 'въведи парола';
        }

        if (empty($error))
        {
            if (!preg_match(Account_model::REGEX['USERNAME'], $username))
            {
                $error['username'] = 'потребител '. Account_model::REGEX_DESCRIPTION['USERNAME'];
            }
            else
            {
                if (mb_strlen($username) < Account_model::LENGTH['MINIMUM_USERNAME'] || mb_strlen($username) > Account_model::LENGTH['MAXIMUM_USERNAME'])
                {
                    $error['username'] = 'потребител трябва да е между '. Account_model::LENGTH['MINIMUM_USERNAME'] .' и '. Account_model::LENGTH['MAXIMUM_USERNAME'] .' символа';
                }
            }

            if (!preg_match(Account_model::REGEX['PASSWORD'], $password))
            {
                $error['password'] = 'парола '. Account_model::REGEX_DESCRIPTION['PASSWORD'];
            }
            else
            {
                if (strlen($password) < Account_model::LENGTH['MINIMUM_PASSWORD'] || strlen($password) > Account_model::LENGTH['MAXIMUM_PASSWORD'])
                {
                    $error['password'] = 'парола трябва да е между '. Account_model::LENGTH['MINIMUM_PASSWORD'] .' и '. Account_model::LENGTH['MAXIMUM_PASSWORD'] .' символа';
                }
            }
        }

        if (empty($error))
        {
            $query = "
            SELECT id, ename, val, created
            FROM public.winconfig
            WHERE id IN (-1, 0) AND LOWER(ename) = LOWER('$username')
            ";

            $db = db_connect();

            $row = $db->query($query)->getRowArray();

            if (empty($row))
            {
                $error['username'] = 'грешен потребител';
            }
            else
            {
                $unique = md5($row['created']);

                if (!password_verify($unique . $password, $row['val']))
                {
                    $error['password'] = 'грешна парола';
                }
                else
                {
                    $_SESSION['authenticate'] = [
                        'username' => $row['ename'],
                        'id' => intval($row['id']),
                        'session_expire' => time() + SESSION_EXPIRE
                    ];
                }
            }
        }

        $response = [];

        if (empty($error))
        {
            $response['success'] = null;
        }
        else
        {
            $response['error'] = $error;
        }

        return $this->response->setJSON($response);
    }

    public function init()
    {
        if (ENVIRONMENT === 'development')
        {
            $employee = [
                [
                    'insert' => true,
                    'id' => -1,
                    'ename' => 'Админ',
                    'val' => password_hash(md5('2023-04-10 14:12:05') . 'config_pass_1', PASSWORD_BCRYPT),
                    'note' => 'Администратор',
                    'created' => '2023-04-10 14:12:05'
                ],
                [
                    'insert' => true,
                    'id' => 0,
                    'ename' => 'Технолог',
                    'val' => password_hash(md5('2023-04-11 11:38:54') . 'wine_pass_23', PASSWORD_BCRYPT),
                    'note' => 'Потребител',
                    'created' => '2023-04-11 11:38:54'
                ]
            ];

            $values = [];

            foreach ($employee as $value)
            {
                if ($value['insert'])
                {
                    $values[] = "({$value['id']}, '{$value['ename']}', '{$value['val']}', '{$value['note']}', '{$value['created']}')";
                }
            }

            if (!empty($values))
            {
                $values = implode(',' . PHP_EOL, $values);

                $query = "
                INSERT INTO
                public.winconfig (id, ename, val, note, created)
                VALUES
                $values
                ";

                $db = db_connect();

                $db->query($query);

                $db->close();
            }
        }
        else
        {
            return redirect()->to('');
        }
    }
}