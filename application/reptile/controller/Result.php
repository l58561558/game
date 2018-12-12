<?php
namespace app\reptile\controller;

use app\reptile\controller\Base;
use app\reptile\model\Football;
use app\reptile\model\Basketball;
use think\Db;
use think\Log;
class Result extends Base {
public function __construct()
    {
        Log::init([
            'type' =>  'File',
            'path' =>  LOG_PATH,
        ]);
    }
    public function test()
    {
        $gamedate = "2018-12-10";
    	$basketball = new Basketball();
    	$basketball->getScore($gamedate);
    	// $football = new Football();
    	// $football->getScore($gamedates);
    }
   
    /**
     * 获取足球彩果彩果，并进行结算
     * @return [type] [description]
     */
    public function get_football_result()
    {
        
        //获取未结算比赛的时间组
        $gamedate_arr = db('fb_game')
                            ->field("FROM_UNIXTIME(end_time,'%Y-%m-%d') as gamedate")
                            ->where('status = 2')
                            ->group('gamedate')
                            ->select();
        Log::info(json_encode(['gamedate' => $gamedate_arr]));
        if(count($gamedate_arr) > 0){
            foreach ($gamedate_arr as $key => $value){
               	$gamedate = $gamedate_arr[$key]['gamedate'];
                $football = new Football();
                $data = $football->getScore($gamedate);	
                //dump($data);die;
                if(!empty($data)){
                	//krsort($data);
                    foreach ($data as $key => $value) {
                        $game_info = $data[$key];
                        dump($game_info);
                        Log::info(json_encode(['game_info'=>$game_info]));
                        $game_id = db('fb_game')
                                    ->where('week="'.$game_info['week'].'" and game_no="'.$game_info['game_no'].'" and status = 2')
                                    ->where('road_team','=',$game_info['road_team'])
                                    ->where('home_team','=',$game_info['home_team'])
                                    ->value('id');
                        Log::info(json_encode(['game_id'=>$game_id]));
                        if(!empty($game_id) && $game_id > 0){
                            $score_arr['top_score'] = $game_info['halfscore'];
                            $score_arr['down_score'] = $game_info['score'];
                            //开启事务
                            Db::startTrans();
                            try{
                                $res = db('fb_game')->where('id='.$game_id)->update($score_arr);
                                //dump($res);
                                if($res > 0){
                                    Db::commit();
                                    Log::info('比分添加成功');
                                    //计算总分
                                    $football->edit_field($game_id);
                                }else{
                                    throw new \Exception("数据重复提交");
                                }
                                //调用结算接口
                                $football->fb_over($game_id);
                            }catch(\Exception $e){
                                Db::rollback();
                                Log::info($e->getMessage());
                                continue;
                            }
                            //调用结算接口
                            //$football->fb_over($game_id);                            
                        }
                    }
                }
            }
        }else{
            Log::info('未做任何更改');
            return;
        }
    }
    /**
     * 获取篮球彩果彩果，并进行结算
     * @return [type] [description]
     */
    public function get_basketball_result()
    {
    	//获取未结算赛事时间组
    	$gamedate_arr = db('nba_game')
                            ->field("FROM_UNIXTIME(end_time-43200,'%Y-%m-%d') as gamedate")
                            ->where('status = 2')
                            ->group('gamedate')
                            ->select();
        Log::info(json_encode(['gamedate' => $gamedate_arr]));
        if(count($gamedate_arr)>0){
        	foreach ($gamedate_arr as $key => $value) {
        		$gamedate = $gamedate_arr[$key]['gamedate'];
        		$basketball = new Basketball();
                $data = $basketball->getScore($gamedate);
                //dump($data);
                if(!empty($data)){
                	//krsort($data);
                	foreach ($data as $key => $value) {
                	    $game_info = $data[$key];
                	    Log::info(json_encode(['game_info'=>$game_info]));
                	    $game_id = db('nba_game')                                                               
                	                ->where('status = 2 and week = "'.$game_info['week'].'" and game_no="'.$game_info['game_no'].'"')
                                    ->where('road_team','=',$game_info['road_team'])
                                    ->where('home_team','=',$game_info['home_team'])
                	                ->value('id');
                	    Log::info(json_encode(['game_id'=>$game_id]));
                	    //dump($game_id);
                	    if(!empty($game_id) && $game_id > 0){
                	        $game_score['home_score'] = $game_info['home_score'];
                	        $game_score['road_score'] = $game_info['road_score'];
                	         //开启事务
                	        Db::startTrans();
                	        try{
                	            $res = db('nba_game')->where('id='.$game_id)->update($game_score);
                	            //dump($res);
                	            if($res > 0){
                	                Db::commit();
                	                Log::info('比分添加成功');
                	                $basketball->edit_field($game_id);
                	            }else{
                	                throw new \Exception("数据重复提交");
                	            }
                	            //调用结算接口
                	        	$basketball->nba_over($game_id);
                	        }catch(\Exception $e){
                	            Db::rollback();
                	            Log::error($e->getMessage());
                	            continue;
                	        }
                	        //调用结算接口
                	        //$basketball->nba_over($game_id);
                	    } 
                	}
                }
        	}
        }else{
        	return;
        }
    }


}