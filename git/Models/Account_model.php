<?php
namespace App\Models;

use CodeIgniter\Model;

class Account_model extends Model
{
    const REGEX = [
        'USERNAME' => '/^[A-Za-zАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЬЮЯабвгдежзийклмнопрстуфхцчшщъьюя\.\- ]+$/',
        'PASSWORD' => '/^[A-Za-z0-9\.\-\_\#\!\?]+$/'
    ];
    const REGEX_DESCRIPTION = [
        'USERNAME' => 'може да съдържа букви на латиница, кирилица, интервал и символи за <b>.</b> и <b>-</b>',
        'PASSWORD' => 'може да съдържа латински букви, числа и символи за <b>.</b>, <b>-</b>, <b>_</b>, <b>#</b>, <b>!</b> и <b>?</b>'
    ];
    const LENGTH = [
        'MINIMUM_USERNAME' => 5,
        'MAXIMUM_USERNAME' => 60,
        'MINIMUM_PASSWORD' => 5,
        'MAXIMUM_PASSWORD' => 20
    ];
}