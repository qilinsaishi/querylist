<?php

namespace App\Collect\hero\dota2;

class gamedota2
{
    protected $data_map =
        [
        ];
    public $hero_type = [
        'int'=>'智力',
        'agi'=>'敏捷',
        'str'=>'力量',

    ];
    public function collect($arr)
    {
        print_r($arr);exit;
    }
    public function process($arr)
    {
        var_dump($arr);
    }
}
