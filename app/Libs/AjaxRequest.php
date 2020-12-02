<?php

namespace App\Libs;

use GuzzleHttp\Client;

class AjaxRequest
{

    /**
     * @param $url
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHeaderInfo($url)
    {
        $client = new Client(['verify' => false]);
        $headers = [
            'user-agent' => ' Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36'
        ];
        $response = $client->request('GET', $url, ['headers' => $headers]);
        $headerData = $response->getHeaders();//获取请求头信息
        $cookieStr = '';
        $token = '';
        if (isset($headerData['Set-Cookie']) && $headerData['Set-Cookie']) {
            foreach ($headerData['Set-Cookie'] as $val) {
                if (strpos($val, 'wanplus_token') !== false) {
                    $arrToken = explode('expires', $val);
                    $wanplus_token = str_replace('wanplus_token=', '', $arrToken[0] ?? '');
                    $wanplus_token = str_replace(';', '', $wanplus_token);
                    $token = getToken(trim($wanplus_token));//获取token
                    $cookieStr .= $arrToken[0] ?? '';
                }
                if (strpos($val, 'wanplus_storage') !== false) {
                    $arrStorage = explode('expires', $val);
                    $cookieStr .= $arrStorage[0] ?? '';
                }
                if (strpos($val, 'wanplus_sid') !== false) {
                    $arrCsrf = explode('expires', $val);
                    $cookieStr .= $arrCsrf[0] ?? '';
                }
                if (strpos($val, 'wanplus_csrf') !== false) {
                    $arrCsrf = explode('Path', $val);
                    $cookieStr .= $arrCsrf[0] ?? '';
                }
                if (strpos($val, 'gameType') !== false) {
                    $arrGameType = explode('expires', $val);
                    $cookieStr .= $arrGameType[0] ?? '';
                }
            }
        }
        $data = [
            'token' => $token,
            'cookieStr' => $cookieStr//获取cookie
        ];
        return $data;
    }
    /**
     * @param $url // 通过url获取头文件信息
     * @param $play_url //ajax请求链接
     * @param array $param //包含playerid:队员id，赛事id:eid,游戏类型：gametype,
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMemberMatch($url, $param = [])
    {
        $list = [];
        $play_url = 'https://www.wanplus.com/ajax/statelist/player';
        $paramHeaderData = $this->getHeaderInfo($url);//
        $headers = [
            'x-requested-with' => 'XMLHttpRequest',
            'x-csrf-token' => $paramHeaderData['token'] ?? '',
            'cookie' => $paramHeaderData['cookieStr'] ?? ''
        ];
        $client = new Client(['verify' => false]);
        $response = $client->request('POST', $play_url, ['form_params' => $param, 'headers' => $headers]);

        $data = json_decode($response->getBody(), true);
        //$data=siz
        if ($data['ret'] == 0) {
            $list = $data['data'] ?? [];
        } else {
            return $data['msg'];
        }

        return $list;
    }


}
