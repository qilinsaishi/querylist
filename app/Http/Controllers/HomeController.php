<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use Illuminate\Http\Request;
use QL\QueryList;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function index()
    {
        $url = 'https://pvp.qq.com/web201605/js/summoner.json';

        $client = new ClientServices();
        $data = $client->curlGet($url);dd($data);


        $url = 'https://www.wanplus.com/lol/player/14504';//队员链接
        $ql = QueryList::get($url);
        $infos = $ql->find('.f15')->texts();//胜/平/负(历史总战绩)
        $country = $aka = $title = '';
        if (!empty($infos->all())) {
            foreach ($infos->all() as $val) {
                if (strpos($val, '名称') !== false) {
                    $title = str_replace('名称：', '', $val);
                }
                if (strpos($val, '别名') !== false) {
                    $aka = str_replace('别名：', '', $val);
                }
                if (strpos($val, '地区') !== false) {
                    $country = str_replace('地区：', '', $val);
                }

            }
        }
        $res['country'] = $country;
        $res['aka'] = $aka;
        $res['title'] = $title;

        $playerid = $ql->find('#recent #id')->attr('value');//id
        $gametype = $ql->find('#recent #gametype')->attr('value');

        //曾役战队
        $history_times = $ql->find('.team-history  li .history-time')->texts()->all();//队员名称
        $history_teams = $ql->find('.team-history  li span')->texts()->all();//队员名称
        $historys = [];

        foreach ($history_times as $k => $val) {
            $temps = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($val));
            $history_time = preg_replace('# #', '', $temps);
            $historys[$k]['history_time'] = $history_time ?? '';
            $historys[$k]['history_team'] = $history_teams[$k] ?? '';

        }
        $res['historys'] = $historys;
        $param = [
            'playerid' => $playerid,//队员id
            'gametype' => $gametype,//游戏类型
            'eid' => -1
        ];
        //该队员相关赛事$playData['eventList'],该队员相关的胜平负以及常用英雄$playData['stateList']
        $AjaxModel=new AjaxRequest();
        $playData = $AjaxModel->getMemberMatch($url, $param);
        $res['playData'] = $playData;//赛事相关信息
        return $res;
    }

    //资讯
    public function kplInfo()
    {
        $iSubType = '330';//330=>活动,329=>赛事，

        $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=100&pagesize=15&_=' . msectime();

        $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';
        $data = curl_get($url, $refeerer);
        dd($data);
        $resultTotal = $data['data']['resultTotal'] ?? '';
        $resultNum = $data['data']['resultNum'] ?? '';

        //$data=curl_get('');
        $page = getLastPage($resultTotal, $resultNum);
        for ($i = 0; $i <= $page; $i++) {
            $m = $i + 1;
            $url = 'https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=' . $m . '&num=16&target=24&source=web_pc&_=' . msectime();
            //echo $url.'<br/>';
            $data[$i] = $url;
        }
        dd($data);
    }






}
