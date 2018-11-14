<?php
namespace app\dxh\model;
use think\Model;

class Rule extends Model {
    //自定义初始化
    protected static function init(){
        //TODO:自定义的初始化
    }

    public function rule($kjid)
    {
        $da   = (int)db('touz')->where('kjid='.$kjid.' and Tzjg=1')->sum('Tzje');
        $he   = (int)db('touz')->where('kjid='.$kjid.' and Tzjg=2')->sum('Tzje');
        $xiao = (int)db('touz')->where('kjid='.$kjid.' and Tzjg=3')->sum('Tzje');

        // if($list['Kjjg']>0){
        //     $list['Kjjg'] = db('code_info')->where('code_info_id=22 and dxhzy='.$list['Kjjg'])->value('code_name');
        // }
        
        $arr = array($da,$he,$xiao);

        $val = array_count_values($arr);
        rsort($val);

        $sum = array();
        $sum[] = $da;
        $sum[] = $he;
        $sum[] = $xiao;

        if($val[0] > 1){

            if($val[0] == 3){
                $rand = mt_rand(0,2);

                $zjje = $sum[$rand]; // 中奖金额
                $Kjjg = $rand+1; // 开奖结果                
            }
            if($val[0] == 2){
                $key = array_keys($sum, min($sum));
                $n = array_rand($key);
                $rand = $key[$n];

                $zjje = $sum[$rand]; // 中奖金额
                $Kjjg = $rand+1; // 开奖结果 
            }
        }else{
            $zjje = min($sum); // 中奖金额
            $key = array_search($zjje , $sum);
            $Kjjg = $key+1; // 开奖结果 
        }    	

        $res = array();
        $res['zjje'] = $zjje;
        $res['Kjjg'] = $Kjjg;
        
        return $res;
    }
}