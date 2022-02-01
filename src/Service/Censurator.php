<?php

namespace App\Service;

class Censurator
{
    private $censorList = [
        'toto',
        'titi',
        'tata'
    ];

    public function purify($string)
    {
        $string = str_replace($this->censorList, '****', $string);
        return $string;
    }
}