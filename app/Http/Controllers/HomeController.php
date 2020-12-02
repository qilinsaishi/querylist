<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QL\QueryList;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function index(){
        $client=new Client(['verify' =>false]);
        $headers=[
            'user-agent'=>' Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36'
        ];
        $response = $client->request('GET', 'https://www.wanplus.com//lol/player/1246',['headers'=>$headers]);
        $headerData=$response->getHeaders();
        $arrToken=$arr1=$arrStorage=$arrSid=$arrCsrf=$arrGameType=[];
        $cookieStr='';

        if(isset($headerData['Set-Cookie']) && $headerData['Set-Cookie']){
            foreach ($headerData['Set-Cookie'] as $val){
                if(strpos($val,'wanplus_token') !==false){
                    $arrToken=explode('expires',$val);
                    $cookieStr.=$arrToken[0] ??'';
                }
                if(strpos($val,'wanplus_storage') !==false){
                    $arrStorage=explode('expires',$val);
                    $cookieStr.=$arrStorage[0] ??'';
                }
                if(strpos($val,'wanplus_sid') !==false){
                    $arrCsrf=explode('expires',$val);
                    $cookieStr.=$arrCsrf[0] ??'';
                }
                if(strpos($val,'wanplus_csrf') !==false){
                    $arrCsrf=explode('Path',$val);
                    $cookieStr.=$arrCsrf[0] ??'';
                }
                if(strpos($val,'gameType') !==false){
                    $arrGameType=explode('expires',$val);
                    $cookieStr.=$arrGameType[0] ??'';
                }
            }
        }

      //  $cookieStr=' wanplus_token=536b0fa2abf0b093601c5b95b746410f;wanplus_storage=lf4m67eka3o;wanplus_sid=401ec9c3cc8eda979a17978fd167da61;wanplus_csrf=_csrf_tk_1787628412;gameType=2; ';

        $url = "https://www.wanplus.com/ajax/statelist/player";
        $headers = [
            'x-requested-with'  => 'XMLHttpRequest',
            'x-csrf-token' => '1854737276',
            'cookie'=>$cookieStr

        ];
        $client = new Client(['verify' =>false]);
        $param = [
            'playerid'=>'1246',
            'gametype'=>'2',
            'eid'=>-1,
            '_gtk'=>'1292691799'
        ];

        $response = $client->request('POST', $url,['form_params'=>$param,'headers'=>$headers]);

        $data = json_decode($response->getBody(), true);dd($data);

        exit;

       /* $url = "https://www.wanplus.com/lol/player/15263";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ch);


        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = $response;
        $body = substr($response, $header_size);

        curl_close($ch);


        print($headers);exit;*/

/*$test_url='https://www.wanplus.com/lol/player/25177';
        $data=curl_get($test_url);dd($data);*/

       $url = "https://www.wanplus.com/ajax/statelist/player";
        $headers = [
            'x-requested-with'  => 'XMLHttpRequest',
            'x-csrf-token' => '1368290349',
            'origin'=>'https://www.wanplus.com',
            'cookie'=>'wanplus_token=265cf4c19f0b1e5e1720f9774d969388; wanplus_storage=lf4m67eka3o; gameType=2; wanplus_sid=9fb2db2244783e9483fc52d5a5f4012c;'

        ];
        $client = new Client(['verify' =>false]);
        $param = [
            'playerid'=>'25177',
            'gametype'=>'2',
            'eid'=>'1003',
            '_gtk'=>'1368290349'
        ];

        $response = $client->request('POST', $url,['form_params'=>$param,'headers'=>$headers]);

        $data = json_decode($response->getBody(), true);dd($data);
       dd($url);
       /* $refeerer='http://lol.qq.com/biz/hero/summoner.js';
        $data=curl_get($refeerer);dd($data);*/

        $ql = QueryList::get('https://www.wanplus.com/lol/player/15263');
        dd($ql);
        $infos= $ql->find('#shareTitle')->text();
        $infos=trim($infos,'【');
        $infos=trim($infos,'】');
        $arr=explode('，',$infos);
        if($arr){
            $res['country']=trim($arr[1])??'';//国家
            if($res['country']){
                $res['country']=str_replace('国家：','',$res['country']);
            }
        }
        $playerid=$ql->find('#recent #id')->attr('value');//id
        $gametype=$ql->find('#recent #gametype')->attr('value');

        $eid=$ql->find("select[name='matchName']")->find("option:selected")->attr("data-eid");
        dd($eid);
        $play_url='https://www.wanplus.com/ajax/statelist/player';
        $postdata=[
            'playerid'=>'15263',
            'gametype'=>'2',
            '_gtk'=>'1368290349'
        ];
        $header=[
            'Accept:application/json',
            'x-requested-with:XMLHttpRequest',
            'x-csrf-token:1368290349'
        ];
        $list=[];
        $list=curl_post( $play_url='', $postdata='' );
        dd($list);
        //$ql = QueryList::get('https://www.wanplus.com/lol/player/15263');
     //   $res['military_exploits']=$ql->find('.team_tbb dt:eq(0)')->text();//胜/平/负(历史总战绩)
     //   dd($res['military_exploits']);
        //历史事件
        $history_times = $ql->find('.team-history  li .history-time')->texts()->all();//队员名称
        $history_teams = $ql->find('.team-history  li span')->texts()->all();//队员名称
        $historys=[];

        foreach ($history_times as $k=>$val){
            $temps=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags( $val));
            $history_time=preg_replace('# #', '', $temps);
            $historys[$k]['history_time']=$history_time ?? '';
            $historys[$k]['history_team']=$history_teams[$k] ?? '';
            //$array=explode('- ',$temps);dd(trim($array[1]));
        }


        //战绩
        $res['military_exploits']=$ql->find('.team_tbb tr:eq(0) dt')->text();//胜/平/负(历史总战绩)
        dd( $res['military_exploits']);
        dd( $res['military_exploits']);
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

        return $res;
    }

    public function kplInfo(){
        $iSubType='330';//330=>活动,329=>赛事，

        $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page=100&pagesize=15&_='.msectime();

        $refeerer='https://pvp.qq.com/web201605/searchResult.shtml';
        $data=curl_get($url,$refeerer);dd($data);

        $resultTotal=$data['data']['resultTotal'] ?? '';
        $resultNum=$data['data']['resultNum'] ?? '';

        //$data=curl_get('');
        $page=getLastPage($resultTotal,$resultNum);
        for ($i=0;$i<=$page;$i++){
            $m=$i+1;
            $url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page='.$m.'&num=16&target=24&source=web_pc&_='.msectime();
            //echo $url.'<br/>';
            $data[$i]=$url;
        }
        dd($data);
    }


}
