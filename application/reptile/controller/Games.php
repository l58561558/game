<?php
namespace app\reptile\controller;

use think\Log;
use app\reptile\controller\Base;
use app\reptile\model\Soccer;
use app\reptile\model\Crawler;

class Games extends Base {
    
    function __construct(){
        Log::init([
            'type' =>  'File',
            'path' =>  LOG_PATH,
        ]);
    }

    public function setGames()
    {
    	$soccer = new Soccer();
    	$game_data = $soccer->getGameData();
    	foreach ($game_data as $key => $value) {
    		$game_list = $game_data[$key];
    		foreach ($game_list as $k => $val) {
    			$game_id = db('fb_game')
    						->where('week','=',$game_list[$k]['week'])
    						->where('game_no','=',$game_list[$k]['game_no'])
    						->where('end_time','=',$game_list[$k]['end_time'])
    						->value('id');
    			if(!empty($game_id) && $game_id >0){
    				$soccer->updateData($val);
    			}else{
    				$soccer->insertData($val);
    			}	
    		}
    	}
    }
    
    public function addFootball()
    {
    	header('Content-Type:application/json;charset=utf-8');
    	$res = file_get_contents("php://input");
    	if(!empty($res)){
    		$data = json_decode($res);
    		foreach ($data as $key => $value) {
				$game_info            = $data[$key];
				$fb_game['week']      = substr($game_info[0][0],0,6);
				$fb_game['game_no']   = substr($game_info[0][0],-3,3);				
				$fb_game['game_name'] = $game_info[0][1];
				$fb_game['end_time']  = strtotime($game_info[0][3])-3600;
				$arr                  = explode("$", $game_info[0][2]);
				$fb_game['home_team'] = $arr[0];
				$fb_game['let_score'] = $arr[1];
				$fb_game['road_team'] = $arr[2];
				$fb_game['add_time']  = time();

				$id = db('fb_game')
						->where('week','=',$fb_game['week'])
						->where('game_no','=',$fb_game['game_no'])
						->where('end_time','=',$fb_game['end_time'])
						->value('id');
				if(!empty($id) && $id >0){
					//db('fb_game')->where('id','=',$id)->delete();
					Log::info($game_info[0][0]."数据已存在");
    				continue;
    			}else{
					try{
						$game_id = db('fb_game')->insertGetId($fb_game);
						if(!empty($game_id) && $game_id > 0){
							Log::info($game_info[0][0].'比赛信息添加成功');
						}else{
							throw new Exception("数据库操作异常");
						}
					}catch(\Exception $e){
						Log::error($e->getMessage());
						continue;
					}
					$fb_game_cate['home_win']            = empty($game_info[5][0])?0:$game_info[5][0];
					$fb_game_cate['home_eq']             = empty($game_info[5][1])?0:$game_info[5][1];
					$fb_game_cate['home_lose']           = empty($game_info[5][2])?0:$game_info[5][2];
					$fb_game_cate['let_score_home_win']  = empty($game_info[1][0])?0:$game_info[1][0];
					$fb_game_cate['let_score_home_eq']   = empty($game_info[1][1])?0:$game_info[1][1];
					$fb_game_cate['let_score_home_lose'] = empty($game_info[1][2])?0:$game_info[1][2];
					$fb_game_cate['one_zero']            = empty($game_info[2][0])?0:$game_info[2][0];
					$fb_game_cate['two_zero']            = empty($game_info[2][1])?0:$game_info[2][1];
					$fb_game_cate['two_one']             = empty($game_info[2][2])?0:$game_info[2][2];
					$fb_game_cate['three_zero']          = empty($game_info[2][3])?0:$game_info[2][3];
					$fb_game_cate['three_one']           = empty($game_info[2][4])?0:$game_info[2][4];
					$fb_game_cate['three_two']           = empty($game_info[2][5])?0:$game_info[2][5];
					$fb_game_cate['four_zero']           = empty($game_info[2][6])?0:$game_info[2][6];
					$fb_game_cate['four_one']            = empty($game_info[2][7])?0:$game_info[2][7];
					$fb_game_cate['four_two']            = empty($game_info[2][8])?0:$game_info[2][8];
					$fb_game_cate['five_zero']           = empty($game_info[2][9])?0:$game_info[2][9];
					$fb_game_cate['five_one']            = empty($game_info[2][10])?0:$game_info[2][10];
					$fb_game_cate['five_two']            = empty($game_info[2][11])?0:$game_info[2][11];
					$fb_game_cate['win_other']           = empty($game_info[2][12])?0:$game_info[2][12];				
					$fb_game_cate['zero_zero']           = empty($game_info[2][13])?0:$game_info[2][13];
					$fb_game_cate['one_one']             = empty($game_info[2][14])?0:$game_info[2][14];
					$fb_game_cate['two_two']             = empty($game_info[2][15])?0:$game_info[2][15];
					$fb_game_cate['three_three']         = empty($game_info[2][16])?0:$game_info[2][16];			
					$fb_game_cate['eq_other']            = empty($game_info[2][17])?0:$game_info[2][17];				
					$fb_game_cate['zero_one']            = empty($game_info[2][18])?0:$game_info[2][18];
					$fb_game_cate['zero_two']            = empty($game_info[2][19])?0:$game_info[2][19];
					$fb_game_cate['one_two']             = empty($game_info[2][20])?0:$game_info[2][20];
					$fb_game_cate['zero_three']          = empty($game_info[2][21])?0:$game_info[2][21];
					$fb_game_cate['one_three']           = empty($game_info[2][22])?0:$game_info[2][22];
					$fb_game_cate['two_three']           = empty($game_info[2][23])?0:$game_info[2][23];
					$fb_game_cate['zero_four']           = empty($game_info[2][24])?0:$game_info[2][24];
					$fb_game_cate['one_four']            = empty($game_info[2][25])?0:$game_info[2][25];
					$fb_game_cate['two_four']            = empty($game_info[2][26])?0:$game_info[2][26];
					$fb_game_cate['zero_five']           = empty($game_info[2][27])?0:$game_info[2][27];
					$fb_game_cate['one_five']            = empty($game_info[2][28])?0:$game_info[2][28];
					$fb_game_cate['two_five']            = empty($game_info[2][29])?0:$game_info[2][29];			
					$fb_game_cate['lose_other']          = empty($game_info[2][30])?0:$game_info[2][30];			
					$fb_game_cate['total_zero']          = empty($game_info[3][0])?0:$game_info[3][0];
					$fb_game_cate['total_one']           = empty($game_info[3][1])?0:$game_info[3][1];
					$fb_game_cate['total_two']           = empty($game_info[3][2])?0:$game_info[3][2];
					$fb_game_cate['total_three']         = empty($game_info[3][3])?0:$game_info[3][3];
					$fb_game_cate['total_four']          = empty($game_info[3][4])?0:$game_info[3][4];
					$fb_game_cate['total_five']          = empty($game_info[3][5])?0:$game_info[3][5];
					$fb_game_cate['total_six']           = empty($game_info[3][6])?0:$game_info[3][6];
					$fb_game_cate['total_seven_gt']      = empty($game_info[3][7])?0:$game_info[3][7];			
					$fb_game_cate['win_win']             = empty($game_info[4][0])?0:$game_info[4][0];
					$fb_game_cate['win_eq']              = empty($game_info[4][1])?0:$game_info[4][1];
					$fb_game_cate['win_lose']            = empty($game_info[4][2])?0:$game_info[4][2];
					$fb_game_cate['eq_win']              = empty($game_info[4][3])?0:$game_info[4][3];
					$fb_game_cate['eq_eq']               = empty($game_info[4][4])?0:$game_info[4][4];
					$fb_game_cate['eq_lose']             = empty($game_info[4][5])?0:$game_info[4][5];
					$fb_game_cate['lose_win']            = empty($game_info[4][6])?0:$game_info[4][6];
					$fb_game_cate['lose_eq']             = empty($game_info[4][7])?0:$game_info[4][7];
					$fb_game_cate['lose_lose']           = empty($game_info[4][8])?0:$game_info[4][8];
	     			//dump($fb_game);
	     			//dump($fb_game_cate);

	     			$array = array('home_win','home_eq','home_lose','let_score_home_win','let_score_home_eq','let_score_home_lose');
					foreach ($fb_game_cate as $k => $v) {
						$fb_game_cate_data['game_id']   = $game_id;
						$fb_game_cate_data['cate_name'] = db('fb_code')->where('code="'.$k.'"')->value('code_name');
						$fb_game_cate_data['cate_code'] = $k;
						$fb_game_cate_data['cate_odds'] = $v==0?0:(in_array($k, $array)?floor($v*1.085*100)/100:$v);
						$fb_game_cate_data['status']    = 1;
						$fb_game_cate_data['is_win']    = 0;
						try{
							$fb_game_cate_res = db('fb_game_cate')->insertGetId($fb_game_cate_data);
							if($fb_game_cate_res <= 0){
								throw new Exception($game_info[0][0]."赔率添加异常");
							}	
						}catch(\Exception $e){
							Log::error($e->getMessage());
							continue;
						}	
					}
    			}
    		}
    		//dump($data);
    		return 'success';
    	}else{
    		$crawler = new Crawler();
    		$crawler->getGameData();
    	}
    }

    public function addBasketball($value='')
    {
    	header('Content-Type:application/json;charset=utf-8');
    	$res = file_get_contents("php://input");
    	if(!empty($res)){
    		$data = json_decode($res);
    		//dump($data);
    		foreach ($data as $key => $value) {
    			$game_info = $data[$key];
    			if($game_info[0][1] == "美职篮"){
    				$nba_game['game_name']     = "NBA";
    				$nba_game['game_cate']     = 1;
					$nba_game['week']          = substr($game_info[0][0],0,6);
    				$nba_game['game_no']       = substr($game_info[0][0],-3,3);   				
    				$nba_game['road_team']     = $game_info[0][2];
    				$nba_game['home_team']     = $game_info[0][3];
    				$nba_game['total_score']   = !isset($game_info[3][0])?0:str_replace("+", "", $game_info[3][0]); // 总分
    				$nba_game['let_score']     = !isset($game_info[2][0])?0:$game_info[2][0]; // 让分   				
    				$nba_game['end_time']      = strtotime($game_info[0][4])-3600;
    				$nba_game['add_time']      = time(); // 生成时间

    				$id = db('nba_game')
						->where('week','=',$nba_game['week'])
						->where('game_no','=',$nba_game['game_no'])
						->where('end_time','=',$nba_game['end_time'])
						->value('id');
					if(!empty($id) && $id >0){
						//db('nba_game')->where('id','=',$id)->delete();
						Log::info($game_info[0][0]."数据已存在");
	    				continue;
	    			}else{
						try{
							$game_id = db('nba_game')->insertGetId($nba_game);
							if(!empty($game_id) && $game_id > 0){
								Log::info($game_info[0][0].'比赛信息添加成功');
							}else{
								throw new Exception("数据库操作异常");
							}
						}catch(\Exception $e){
							Log::error($e->getMessage());
							continue;
						}
						$nba_game_cate['win_road']           = !isset($game_info[1][0])?0:$game_info[1][0];
		                $nba_game_cate['win_home']           = !isset($game_info[1][1])?0:$game_info[1][1];
		                $nba_game_cate['let_score_win_road'] = !isset($game_info[2][1])?0:$game_info[2][1];
		                $nba_game_cate['let_score_win_home'] = !isset($game_info[2][2])?0:$game_info[2][2];
		                $nba_game_cate['total_small']        = !isset($game_info[3][1])?0:$game_info[3][2];
		                $nba_game_cate['total_big']          = !isset($game_info[3][2])?0:$game_info[3][1];                 
		                $nba_game_cate['differ_road_5']      = !isset($game_info[4][0])?0:$game_info[4][0];
		                $nba_game_cate['differ_road_10']     = !isset($game_info[4][1])?0:$game_info[4][1];
		                $nba_game_cate['differ_road_15']     = !isset($game_info[4][2])?0:$game_info[4][2];
		                $nba_game_cate['differ_road_20']     = !isset($game_info[4][3])?0:$game_info[4][3];
		                $nba_game_cate['differ_road_25']     = !isset($game_info[4][4])?0:$game_info[4][4];
		                $nba_game_cate['differ_road_26']     = !isset($game_info[4][5])?0:$game_info[4][5];
		                $nba_game_cate['differ_home_5']      = !isset($game_info[4][6])?0:$game_info[4][6];
		                $nba_game_cate['differ_home_10']     = !isset($game_info[4][7])?0:$game_info[4][7];
		                $nba_game_cate['differ_home_15']     = !isset($game_info[4][8])?0:$game_info[4][8];
		                $nba_game_cate['differ_home_20']     = !isset($game_info[4][9])?0:$game_info[4][9];
		                $nba_game_cate['differ_home_25']     = !isset($game_info[4][10])?0:$game_info[4][10];
		                $nba_game_cate['differ_home_26']     = !isset($game_info[4][11])?0:$game_info[4][11];
						//dump($nba_game);
						//dump($nba_game_cate);
						$array = array('win_road','win_home','let_score_win_road','let_score_win_home','total_small','total_big');
						foreach ($nba_game_cate as $k => $v) {
							$nba_game_cate_data['game_id']   = $game_id;
							$nba_game_cate_data['cate_name'] = db('nba_code')->where('code="'.$k.'"')->value('code_name');
							$nba_game_cate_data['cate_code'] = $k;
							$nba_game_cate_data['cate_odds'] = $v==0?0:(in_array($k, $array)?floor($v*1.085*100)/100:$v);
							$nba_game_cate_data['status']    = 1;
							$nba_game_cate_data['is_win']    = 0;                    
		                    try{
		                    	$fb_game_cate_res = db('nba_game_cate')->insertGetId($nba_game_cate_data);
		                    	if($fb_game_cate_res <= 0){
		                    		throw new Exception($game_info[0][0]."赔率添加异常");
		                    	}	
		                    }catch(\Exception $e){
		                    	Log::error($e->getMessage());
		                    	continue;
		                    }
		                }
    				}
    			}else{
    				continue;
    			}	
    		}
    		//dump($data);
    		return 'success';
    	}else{
    		$crawler = new Crawler();
    		$crawler->getGameList();
    	}
    }
}