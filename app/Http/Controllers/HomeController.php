<?php

namespace App\Http\Controllers;

use App\Services\TeamCollectService;
use Illuminate\Http\Request;
use QL\QueryList;

class HomeController extends Controller
{
    public function index(){
        $ql = QueryList::get('https://www.wanplus.com/lol/team/5147');
       // $res['describe'] = $ql->find('.main-content  .lemma-summary')->text();//百度百科抓取 战队简介
        $res['logo'] = $ql->find('#sharePic')->src;//战队logo
        $res['logo'] =str_replace('_mid','',$res['logo'] );
        $infos= $ql->find('#shareTitle')->text();
        $infos=trim($infos,'【');
        $infos=trim($infos,'】');
        $arr=explode('，',$infos);
        if($arr){
            $res['title']=$arr[0]??'';//战队名称
            $res['aka']=trim($arr[1])??'';//别名
            if($res['aka']){
                $res['aka']=str_replace('别名:','',$res['aka']);
            }
            $res['country']=trim($arr[2])??'';//国家
            if($res['country']){
                $res['country']=str_replace('别名:','',$res['country']);
            }
        }
        //战绩
        $res['military_exploits']=$ql->find('.team_tbb dt:eq(0)')->text();
        $res['military_exploits']= preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags( $res['military_exploits']));
        //现役队员
        $cur_imgs = $ql->find('.team_box  ul:eq(0) img')->attrs('src')->all();//队员图片
        $cur_position= $ql->find('.team_box  ul:eq(0) li>a strong')->texts()->all();//队员名称
        $cur_name = $ql->find('.team_box  ul:eq(0) li>a span')->texts()->all();//队员名称
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
                ];
            }
        }
        //历史队员
        $old_imgs = $ql->find('.team_box  ul:eq(1) img')->attrs('src')->all();//队员图片
        $old_position= $ql->find('.team_box  ul:eq(1) li>a strong')->texts()->all();//队员名称
        $old_name = $ql->find('.team_box  ul:eq(1) li>a span')->texts()->all();//队员名称
        if($old_name){
            foreach ($old_name as $key=>$val){
                $position=$old_position[$key]??'';

                $res['old_team_members'][$key]=[
                    'name'=>$val,//队员名称
                    'main_img'=>(isset($old_imgs[$key]) && $old_imgs[$key]) ? str_replace('_mid','',$old_imgs[$key]) :'',//队员主图,
                    //'position'=>$position,//位置
                ];
            }
        }

        return $res;
    }

}
