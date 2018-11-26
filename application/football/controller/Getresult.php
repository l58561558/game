<?php
namespace app\football\controller;

use app\home\controller\Base; 
class Getresult extends Base {


    /**
     * curl的get请求
     * @param  string $url 请求的url
     * @return mixed  $result 返回请求结果
     */
    public function getCurl($url){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_HEADER,0);
        curl_setopt($curl,CURLOPT_NOBODY,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_URL,$url);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    /**
     * 获取足球比赛分数数组
     * @return [array] $game_score
     */
    public function getScore()
    {   
        $game_score = array();
        //获取昨天日期
        $gamedate = date("Y-m-d",strtotime("-1 day"));
        //$gamedate = "2018-11-25";
        $url = "http://live.caipiao.163.com/jcbf/?date=".$gamedate;    
        $html = $this->getCurl($url);
        //将html编码替换为utf-8
        $coding = mb_detect_encoding($html);
        if ($coding != "UTF-8" || !mb_check_encoding($html, "UTF-8")){$html = mb_convert_encoding($html, 'utf-8', 'GBK,UTF-8,ASCII');}
        //匹配<dl id="gameList"></dl>     
        //$pattern = '|<dl id ="gameList">(.*)<\/dl>|isU';
        $pattern = '|<dl[^>]*>(.*)<\/dl>|isU';
        preg_match_all($pattern, $html, $dl);
        //dump($dl);
        //获取dl标签下的内容
        $games = $dl[1][0];
        $doc = new \DOMDocument();
        //利用dom解析Html字符串（utf-8编码）
        $doc->loadHtml('<?xml encoding="UTF-8">'.$games);
        $dd = $doc->getElementsByTagName('dd');
        for($i=0; $i<$dd->length; $i++){
            $game_no = $dd->item($i)->getElementsByTagName('em')->item(0)->getElementsByTagName('span')->item(0)->textContent;
            $status  = $dd->item($i)->getAttribute('statusdesc');
            if($status == "已结束"){
                $game_score['game_date'] = $gamedate;
                $game_score[$game_no]['status'] = $status;
                //dump($game_no);
                $halfscore = $dd->item($i)->getAttribute('halfscore');
                $score = $dd->item($i)->getAttribute('score');
                if($halfscore != '-' && $score != '-'){
                    $game_score[$game_no]['halfscore'] = $halfscore;
                    $game_score[$game_no]['score'] = $score;
                }else{
                   continue;
                }                
            }else{
                continue;
            }           
        }
        dump($game_score);
        // return $game_score;
    }

}