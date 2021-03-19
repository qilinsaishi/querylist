<?php

namespace App\Libs;

use GuzzleHttp\Client;
use QL\QueryList;

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
//curlGet
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
        $client = new ClientServices();
        $data = $client->curlPost($play_url, $param,$headers);

        //$data=siz
        if ($data['ret'] == 0) {
            $list = $data['data'] ?? [];
        } else {
            return $data['msg'];
        }

        return $list;
    }
    /**
     * @param $url // 通过url获取头文件信息
     * @param $play_url //ajax请求链接
     * @param array $param //包含playerid:队员id，赛事id:eid,游戏类型：gametype,
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMatchList($url, $param = [])
    {
        $list = [];
        $play_url = 'http://www.wanplus.com/ajax/schedule/list';
        $paramHeaderData = $this->getHeaderInfo($play_url);//

        $headers = [
            'x-requested-with' => 'XMLHttpRequest',
            'x-csrf-token' => $paramHeaderData['token'] ?? '',
            'cookie' => $paramHeaderData['cookieStr'] ?? ''
        ];
        $client = new ClientServices();
        $data = $client->curlPost($url, $param,$headers);

        //$data=siz
        if ($data['ret'] == 0) {
            $list = $data['data'] ?? [];
        } else {
            return $data['msg'];
        }

        return $list;
    }
    /**
     * @param $url // 通过url获取头文件信息
     * @param $play_url //ajax请求链接
     * @param array $param //包含playerid:队员id，赛事id:eid,游戏类型：gametype,
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAjaxPost($url, $param = [],$headers=[])
    {
        $list = [];
        /* $paramHeaderData = $this->getHeaderInfo($head_url);//

         $headers = [
             'x-requested-with' => 'XMLHttpRequest',
             'x-csrf-token' => $paramHeaderData['token'] ?? '',
             'cookie' => $paramHeaderData['cookieStr'] ?? ''
         ];*/
        $client = new ClientServices();
        $data = $client->curlPost($url, $param,$headers);
       // $data = $client->curlPost($url, $param);

        //$data=siz
        if ($data['ret'] == 0) {
            $list = $data['data'] ?? [];
        } else {
            return $data['msg'];
        }

        return $list;
    }

    /**
     * @param $url // 通过url获取头文件信息
     * @param $play_url //ajax请求链接
     * @param array $param //包含playerid:队员id，赛事id:eid,游戏类型：gametype,
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHistoryMatch($url, $param = [])
    {
        $list = [];
        $play_url = 'https://www.wanplus.com/ajax/statelist/player';
        $paramHeaderData = $this->getHeaderInfo($play_url);//

        $headers = [
            'x-requested-with' => 'XMLHttpRequest',
            'x-csrf-token' => $paramHeaderData['token'] ?? '',
            'cookie' => $paramHeaderData['cookieStr'] ?? ''
        ];
        $client = new ClientServices();
        $data = $client->curlGet($url, $param,$headers);

        //$data=siz
        if ($data['ret'] == 0) {
            $list = $data['data'] ?? [];
        } else {
            return $data['msg'];
        }

        return $list;
    }
    /**
     * @param $url // 通过url获取头文件信息
     * @param $play_url //ajax请求链接
     * @param array $param //包含playerid:队员id，赛事id:eid,游戏类型：gametype,
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function ajaxGetData($url, $param = [])
    {
        $list = [];
        $play_url = 'https://www.wanplus.com/ajax/statelist/player';
        $paramHeaderData = $this->getHeaderInfo($play_url);//

        $headers = [
            'x-requested-with' => 'XMLHttpRequest',
            'x-csrf-token' => $paramHeaderData['token'] ?? '',
            'cookie' => $paramHeaderData['cookieStr'] ?? ''
        ];
        $client = new ClientServices();
        $data = $client->curlGet($url, $param,$headers);

        try{
            if ($data['ret'] == 0) {
                $list = $data['data'] ?? [];
            } else {
                return $data['msg'] ?? '';
            }
        }catch (\Exception $e){
            print_r($e->getMessage());
        }


        return $list;
    }


    /**
     * 采集来着www.wanplus.com战队的信息
     * @param string $url
     * @return array
     */
    public function getCollectWanplusTeam($url='')
    {
        $res=[];
        //判断url是否有效
        $headers=get_headers($url,1);
        if(!preg_match('/200/',$headers[0])){
            return  [];
        }
        if ($url && strlen($url)>=6) {
            $ql = QueryList::get($url);
            $res['logo'] = $ql->find('#sharePic')->src;//战队logo
            $res['logo'] =str_replace('_mid','',$res['logo'] );
            $infos=$ql->find('.f15')->texts()->all();//胜/平/负(历史总战绩)
            $country=$aka=$title='';
            if(!empty($infos)){
                foreach ($infos as $val){
                    if(strpos($val,'名称')!==false) {
                        $title=str_replace('名称：','',$val);
                    }
                    if(strpos($val,'别名')!==false) {
                        $aka=str_replace('别名：','',$val);
                    }
                    if(strpos($val,'地区')!==false) {
                        $country=str_replace('地区：','',$val);
                    }

                }
            }
            $res['country']=$country;
            $res['aka']=$aka;
            $res['title']=$title;
            //战绩
            $res['military_exploits']=$ql->find('.team_tbb dt:eq(0)')->text();//胜/平/负(历史总战绩)
            $res['military_exploits']= preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags( $res['military_exploits']));
            //现役队员
            $cur_imgs = $ql->find('.team_box  ul:eq(0) img')->attrs('src')->all();//队员图片
            $cur_position= $ql->find('.team_box  ul:eq(0) li>a strong')->texts()->all();//队员名称
            $cur_name = $ql->find('.team_box  ul:eq(0) li>a span')->texts()->all();//队员名称
            $cur_link = $ql->find('.team_box  ul:eq(0) li>a ')->attrs('href')->all();//队员名称
            if($cur_name){
                foreach ($cur_name as $key=>$val){
                    $position=$cur_position[$key]??'';
                    if($position){
                        $position=str_replace('位置:','',$cur_position[$key]);
                    }
                    $res['cur_team_members'][$key]=[
                        'name'=>$val,//队员名称
                        'main_img'=>(isset($cur_imgs[$key]) && $cur_imgs[$key]) ? str_replace('_mid','',$cur_imgs[$key]) :'',//队员主图
                        'position'=>$position,//位置
                        'link_url'=>'https://www.wanplus.com/'.$cur_link[$key] ??''
                    ];
                }
            }
            //历史队员
            $old_imgs = $ql->find('.team_box  ul:eq(1) img')->attrs('src')->all();//队员图片
            $old_position= $ql->find('.team_box  ul:eq(1) li>a strong')->texts()->all();//队员名称
            $old_name = $ql->find('.team_box  ul:eq(1) li>a span')->texts()->all();//队员名称
            $old_link = $ql->find('.team_box  ul:eq(1) li>a ')->attrs('href')->all();//队员名称
            if($old_name){
                foreach ($old_name as $key=>$val){
                    $position=$old_position[$key]??'';

                    $res['old_team_members'][$key]=[
                        'name'=>$val,//队员名称
                        'main_img'=>(isset($old_imgs[$key]) && $old_imgs[$key]) ? str_replace('_mid','',$old_imgs[$key]) :'',//队员主图,
                        'link_url'=>'https://www.wanplus.com'.$old_link[$key] ?? ''
                        //'position'=>$position,//位置
                    ];
                }
            }

        }
        return $res;
    }


}
