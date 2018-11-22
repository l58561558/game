<?php
namespace app\admin\model;
class Nba extends base {
	
    public function getMacthlist($url='')
    {	
        $data = array();
    	$game_data = array();
    	$macthlist = array();
    	$microtime = microtime();
    	//封装比赛数据
		$gamedata = array();
    	$api = "preBet_jclqNewMixAllAjax.html?cache=".$microtime."&betDate=";
    	$url = "http://caipiao.163.com/order/".$api;
    	//获取比赛列表数组  
    	$data = http_request($url);
    	$data = json_decode($data,true);
    	//获取比赛列表
    	$macthlist = $data['matchList'];
        ksort($macthlist);
    	if(!empty($macthlist)){
            $array = array('win_road','win_home','let_score_win_road','let_score_win_home','total_small','total_big');
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
            foreach ($macthlist as $key => $value) {
                if($macthlist[$key]['leagueName'] == "NBA"){
                    $nba_game['road_team']     = $macthlist[$key]['guestName'];
                    $nba_game['home_team']     = $macthlist[$key]['hostName'];
                    $nba_game['game_name']     = $macthlist[$key]['leagueName'];
                    $nba_game['end_time']      = $macthlist[$key]['buyEndTime']/1000;
                    $nba_game['game_no']       = substr($key,9,3);
                    $nba_game['week']          = $weekday[substr($key,8,1)];
                    $nba_game['let_score']     = !isset($macthlist[$key]['spTabMix'][1][2])?0:$macthlist[$key]['spTabMix'][1][2]; // 让分
                    $nba_game['total_score']   = !isset($macthlist[$key]['spTabMix'][2][2])?0:$macthlist[$key]['spTabMix'][2][2]; // 总分
                    $nba_game['add_time']      = time(); // 生成时间
                    $nba_game_id = db('nba_game')->insertGetId($nba_game);
                    if($nba_game_id > 0){
                        $arr['win_road']           = !isset($macthlist[$key]['spTabMix'][0][0])?0:$macthlist[$key]['spTabMix'][0][0];
                        $arr['win_home']           = !isset($macthlist[$key]['spTabMix'][0][1])?0:$macthlist[$key]['spTabMix'][0][1];
                        $arr['let_score_win_road'] = !isset($macthlist[$key]['spTabMix'][1][0])?0:$macthlist[$key]['spTabMix'][1][0];
                        $arr['let_score_win_home'] = !isset($macthlist[$key]['spTabMix'][1][1])?0:$macthlist[$key]['spTabMix'][1][1];
                        
                        $arr['total_small']        = !isset($macthlist[$key]['spTabMix'][2][0])?0:$macthlist[$key]['spTabMix'][2][0];
                        $arr['total_big']          = !isset($macthlist[$key]['spTabMix'][2][1])?0:$macthlist[$key]['spTabMix'][2][1];
                        
                        $arr['differ_road_5']      = !isset($macthlist[$key]['spTabMix'][3][0])?0:$macthlist[$key]['spTabMix'][3][0];
                        $arr['differ_road_10']     = !isset($macthlist[$key]['spTabMix'][3][1])?0:$macthlist[$key]['spTabMix'][3][1];
                        $arr['differ_road_15']     = !isset($macthlist[$key]['spTabMix'][3][2])?0:$macthlist[$key]['spTabMix'][3][2];
                        $arr['differ_road_20']     = !isset($macthlist[$key]['spTabMix'][3][3])?0:$macthlist[$key]['spTabMix'][3][3];
                        $arr['differ_road_25']     = !isset($macthlist[$key]['spTabMix'][3][4])?0:$macthlist[$key]['spTabMix'][3][4];
                        $arr['differ_road_26']     = !isset($macthlist[$key]['spTabMix'][3][5])?0:$macthlist[$key]['spTabMix'][3][5];
                        $arr['differ_home_5']      = !isset($macthlist[$key]['spTabMix'][3][6])?0:$macthlist[$key]['spTabMix'][3][6];
                        $arr['differ_home_10']     = !isset($macthlist[$key]['spTabMix'][3][7])?0:$macthlist[$key]['spTabMix'][3][7];
                        $arr['differ_home_15']     = !isset($macthlist[$key]['spTabMix'][3][8])?0:$macthlist[$key]['spTabMix'][3][8];
                        $arr['differ_home_20']     = !isset($macthlist[$key]['spTabMix'][3][9])?0:$macthlist[$key]['spTabMix'][3][9];
                        $arr['differ_home_25']     = !isset($macthlist[$key]['spTabMix'][3][10])?0:$macthlist[$key]['spTabMix'][3][10];
                        $arr['differ_home_26']     = !isset($macthlist[$key]['spTabMix'][3][11])?0:$macthlist[$key]['spTabMix'][3][11];
                        foreach ($arr as $k => $v) {
                            $cate_arr['game_id'] = $nba_game_id;
                            $cate_arr['cate_name'] = db('nba_code')->where('code="'.$k.'"')->value('code_name');
                            $cate_arr['cate_code'] = $k;
                            $cate_arr['cate_odds'] = $v==0?0:(in_array($k, $array)?floor($v*1.085*100)/100:$v);
                            $nba_game_cate[] = $cate_arr;
                        }
                        $res = db('nba_game_cate')->insertAll($nba_game_cate);
                        unset($nba_game_cate);
                    }
                }
            }
            return $res==0?0:$res;
        }else{
            return 0;
        }
    }
}