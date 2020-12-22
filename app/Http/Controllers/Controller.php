<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $payload;
    public $request;


    public function __construct()
    {
        $this->request = request();
        $this->setPayload();
        //$this->payload = $this->getPayload();

    }

    public function setPayload()
    {
        $t = $this->request->getContent();
        $t = json_decode($t,true);
        $t = is_array($t)?$t:[];
        $this->payload = $t;
    }





}
