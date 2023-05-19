<?php
namespace App\Controllers;

class Home extends BaseController
{
    public function __construct()
    {
        parent::__contruct(false);
    }

    public function index()
    {
        if (isLogged())
        {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => SITE_TITLE .'Вход',
            'display_demo_users' => (ENVIRONMENT === 'development')
        ];

        return view('home/login', $data);
    }
}