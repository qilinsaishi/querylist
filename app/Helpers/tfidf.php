<?php
Class tfidf{

    var $analysis;
    var $word_limit = 20;
    public function __construct(){
        $this->analysis = scws_new();
        $this->analysis->set_charset('utf8');
    }

    public function get_tfidf($txt){
        $word_ret = array();
        $this->analysis->send_text($txt);
        $total = 0;
        while($result = $this->analysis->get_result()){
            $total++;
            foreach($result as $v){
                if(mb_strlen($v['word'],"UTF-8")<=1) continue;
                if(preg_match("/[^\w\s]+/u",$v['word'])){
                    continue;
                }
                if( array_key_exists($v['word'] , $word_ret) ){
                    $v["cnt"]=intval($word_ret[$v["word"]]["cnt"])+1;
                }
                else{
                    $v["cnt"] = 1;
                }
                $word_ret[$v["word"]] = $v;
            }
        }
        $sort = array();
        foreach ($word_ret as $key => $row) {
            $sort[$key]  = round(($row['cnt']/$total) * $row['idf'],2);
        }
        unset($word_ret);
        arsort($sort);
        return $sort;
    }

    public function combine_word($arr1,$arr2){
        $word_array = array();
        foreach($arr1 as $k=>$v){
            $word_array[$k] = array($v,0);
        }
        foreach($arr2 as $k=>$v){
            if( !array_key_exists($k , $word_array) ){
                $word_array[$k] = array(0,$v);
            }else{
                $word_array[$k][1] = $v;
            }
        }
        return $word_array;
    }

    public function sim_value($arr1,$arr2) {
        $words = $this->combine_word($arr1,$arr2);
        unset($arr1);unset($arr2);
        $c = 0;$s1 = $s2 = 0;
        foreach($words as $row){
            $c += floatval($row[0]) * floatval($row[1]);
            $s1  += pow(floatval($row[0]),2);
            $s2  += pow(floatval($row[1]),2);
        }
        $f = $c / (sqrt($s1) * sqrt($s2));
        return $f;
    }
    public function close_analysis(){
        $this->analysis->close();
    }

}
//$txt1 = '';
//$txt2 = '';
//$ContentSim = new ContentSim ( );

//$txt_ret1 = $ContentSim->get_tfidf($text1);
//$txt_ret2 = $ContentSim->get_tfidf($text2);


//$result = $ContentSim->sim_value($txt_ret1,$txt_ret2);

?>
