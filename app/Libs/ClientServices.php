<?php

namespace App\Libs;

use GuzzleHttp\Client;

class ClientServices
{

    /**
     * CurlGet方法
     * @param $url
     * @param array $form_params
     * @param array $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function curlGet($url,$form_params=[],$headers=[])
    {
        $client = new Client(['verify' => false]);
        $params=[];
        $data=[];
        //是否有参数
        if(!empty($form_params)){
            $params['form_params']=$form_params;
        }
        //是否有请求头
        if(!empty($headers)){
            $params['headers']=$headers;
        }
        if($params){
            $response = $client->request('GET', $url,$params);
        }else{
            $response = $client->request('GET', $url);
        }

        $data = json_decode($response->getBody(), true);
        return  $data;
    }

    /**
     * Curl Post方法
     * @param $url
     * @param array $param
     * @param array $headers
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function curlPost($url, $param = [],$headers=[])
    {
        $client = new Client(['verify' => false]);
        $params=[];
        $data=[];
        //是否有参数
        if(!empty($form_params)){
            $params['form_params']=$form_params;
        }
        //是否有请求头
        if(!empty($headers)){
            $params['headers']=$headers;
        }
        $response = $client->request('POST', $url, ['form_params' => $param, 'headers' => $headers]);
        $data = json_decode($response->getBody(), true);
        return $data;
    }


}
