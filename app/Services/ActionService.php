<?php

namespace App\Services;

use App\Helpers\Jwt;
use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Services\UserService;
use App\Models\User\CoinLogModel;
use App\Models\User\CreditLogModel;

use Illuminate\Support\Facades\Redis;
use QL\QueryList;

class ActionService
{
    public function __construct()
    {
        $this->user_redis = Redis::connection('user_redis');
        $this->userModel = new UserModel();
    }
}
