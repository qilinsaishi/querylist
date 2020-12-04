<?php

namespace App\Collect\hero\kpl;

use App\Libs\ClientServices;
use QL\QueryList;

class pvp_qq
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $res=[];
        $url = $arr['detail']['url'] ?? '';
        //$res = $this->getData($url);//curl获取json数据
        $res['cname']=$arr['detail']['cname'] ?? '';
        $res['title']=$arr['detail']['title'] ?? '';
        $res['hero_type"']=$arr['detail']['hero_type"'] ?? '';
        $res['hero_type2"']=$arr['detail']['hero_type2"'] ?? '';
        $res['logo"']=$arr['detail']['logo"'] ?? '';
        if (!empty($res)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => $url,
                'title' => $arr['detail']['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
                'update_time' => date("Y-m-d H:i:s")
            ];

            return $cdata;
        }
    }
    public function process($arr)
    {
        /*var typeMap = {
                    3: '坦克',
                    1: '战士',
                    2: '法师',
                    4: '刺客',
                    5: '射手',
                    6: '辅助',
                    10: '限免',
                    11: '新手'
                }*/

        var_dump($arr);
    }
    public function unicodeDecode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];
    }
    public function strToUtf8($str){
        $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        if($encode == 'UTF-8'){
            return $str;
        }else{
            return mb_convert_encoding($str, 'UTF-8', $encode);
        }
    }
    public function getData($url){
        $ql = QueryList::get($url);
        $skinImg = $ql->find('.pic-pf-list ')->attr('data-imgname');dd($this->unicodeDecode($skinImg));
        $t1 = $this->strToUtf8($skinImg);
        $arr=explode('|',$t1);
       $t2 = iconv("UTF-8", "ISO-8859-1", $t[0]);dd($t2);
        /*echo "origin:".$skinImg."\n";
        echo "decoded:".$this->strToUtf8($skinImg);
        $skinImg = str_replace("[","",$skinImg);
        $skinImg = str_replace("]","",$skinImg);
        $skinImg = str_replace('"',"",$skinImg);
        //echo mb_detect_encoding($skinImg);
        $t = explode("|",$skinImg);
        echo "decode:".iconv("UTF-8", "ISO-8859-1", $t[0]);die();


        die();*/
        //echo urldecode($skinImg);
     /*   echo $skinImg;
        print_R($t);*/
        //print_R
        //echo "unidocede:".$this->unicodeDecode($t[0]);

        //echo "t1:".$t1."\n";
        //echo "t2:".$t2."\n";
        //die();
        if($skinImg){
            foreach ($skinImg as $key=>$val){
                $arr=explode('|',$val);
                //dd($arr[0]);
            }
        }
        dd($skinImg);
        $data=[];

        return $data;
    }
}
