<?php
namespace app\reptile\model;

use Symfony\Component\DomCrawler\Crawler;
use think\Model;
use think\Log;
class Basketball extends Model {
    
    function __construct(){
        Log::init([
            'type' =>  'File',
            'path' =>  LOG_PATH,
        ]);
    }

    /**
     * 获取赛事结算信息
     * @param  string $gamedate 赛事日期
     * @return array  $game_data 赛事数据
     */
    public function getScore($gamedate = '')
    {	
    	//$gamedate = "2018-12-09";
        $mrand = sprintf('%.0f',(floatval(lcg_value()*1e20)));
        $url = "http://lanqiu.zgzcw.com/live/LivePlay.action?code=200&date=".$gamedate."&r=".$mrand;
    	$res = $this->getCurl($url);
        $data = json_decode($res,true);
        // dump($gamedate);
    	// dump($data);die;
        $gamelist = array();
    	foreach ($data as $key => $value) {
            $match_info = $data[$key];
    		$matchdate = date('Y-m-d',strtotime($match_info[7])-43200);
    		if($gamedate == $matchdate){
    			$status = $match_info[8];
    			if($status == '-1'){
                    $matchno            = $match_info[39];
                    $game['gamedate']   = $matchdate;
                    $game['week']       = substr($matchno,0,6);
                    $game['game_no']    = substr($matchno,-3,3);
                    $road_team          = substr($match_info[14],0,strpos($match_info[14],'['));
                    $home_team          = substr($match_info[11],0,strpos($match_info[11],'['));
                    $game['road_team']  = $road_team;
                    $game['home_team']  = $home_team;
                    $game['road_score'] = $match_info[17];
                    $game['home_score'] = $match_info[16];
                    //dump($game);
					$gamelist[] = $game;
    			}else{
    				continue;
    			}
    		}else{
    			continue;
    		}
    	}
    	//dump($gamelist);
    	if(!empty($gamelist)){
    		return $gamelist;
    	}else{
    		return null;
    	}
    }
    

    /**
     * 获取获胜队伍
     * @param  [int] $id 游戏id
     * @return void 
     */
    public function edit_field($id){
        $nba_game = db('nba_game')->where('id='.$id)->find();
        if(!empty($nba_game['home_score']) && !empty($nba_game['road_score'])){
            if($nba_game['home_score'] > $nba_game['road_score']){
                $win_team = $nba_game['home_team'];
            }else if($nba_game['home_score'] < $nba_game['road_score']){
                $win_team = $nba_game['road_team'];
            }    
            db('nba_game')->where('id='.$id)->setField('win_team',$win_team);
        }        
    }

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
     * 结算 通过比赛ID查其所有的投注选项ID -> 在通过投注选项ID查询所有的订单 -> 结算
     * @param  int $id 游戏id
     * @return boolean 是否结算成功
     */
    public function nba_over($id)
    {
        Log::info('调用nba_over()');
        $game = db('nba_game')->where('id='.$id)->find();
        //主场分数
        $home_score = $game['home_score'];
        //客场分数
        $road_score = $game['road_score'];
        //让分分数
        $home_let_score = $home_score+$game['let_score']; 
        //预设俩队总分
        $total_score = $game['total_score'];
        //实际俩队总分
        $total = $home_score+$road_score;
        //根据比赛分数修改本场比赛的所有投注选项的中奖状态
        //主客胜负差
        if($home_score > $road_score){
            $differ = $home_score - $road_score;
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_home"')->setField('is_win',1);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_road"')->setField('is_win',2);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code like "%differ_%"')->setField('is_win',2);
            $differ_score = $this->rank($differ);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="differ_home_'.$differ_score.'"')->setField('is_win',1);
            // db('nba_game_cate')->where('game_id='.$id.' and is_win=0 and cate_code like "differ_home_%"')->setField('is_win',2);
        }else{
            $differ = $road_score - $home_score;
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_home"')->setField('is_win',2);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_road"')->setField('is_win',1);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code like "%differ_%"')->setField('is_win',2);
            $differ_score = $this->rank($differ);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="differ_road_'.$differ_score.'"')->setField('is_win',1);
            // db('nba_game_cate')->where('game_id='.$id.' and is_win=0 and cate_code like "differ_road_%"')->setField('is_win',2);
        }
        //让分胜负
        if($home_let_score > $road_score){
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_home"')->setField('is_win',1);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_road"')->setField('is_win',2);
        }else{
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_home"')->setField('is_win',2);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_road"')->setField('is_win',1);
        }
        //总分胜负
        if($total > $total_score){
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_big"')->setField('is_win',1);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_small"')->setField('is_win',2);
        }else{
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_big"')->setField('is_win',2);
            db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_small"')->setField('is_win',1);
        }
        Log::info('成功写入彩果');
        // 获取这场比赛的所有中奖选项ID::cate_ids
        $cate_id_arr = db('nba_game_cate')->where('game_id='.$id.' and is_win=1')->column('cate_id');
        $cate_ids = implode(',', $cate_id_arr);
        db('nba_order_info')->where('game_id='.$id)->setField('win_game_result',$cate_ids);
        // 查询订单明细里所有有关于这场比赛数据 并更改他们的比赛状态和中奖彩果
        $order_info_all = db('nba_order_info')->where('game_id='.$id)->select();
        foreach ($order_info_all as $key => $value) {
            //当投注选项仅有一个时
            if(strpos($order_info_all[$key]['tz_result'], ',') === false){
                if(in_array($order_info_all[$key]['tz_result'], $cate_id_arr)){
                    $win_result = $order_info_all[$key]['tz_result']; 
                }else{
                    //查询投注选项相同的一组（胜负差、让分胜负、总分、胜分差）,获取中奖的投注选项
                    $cate_code = db('nba_game_cate')->where('cate_id='.$order_info_all[$key]['tz_result'])->value('cate_code');
                    $pid = db('nba_code')->where('code="'.$cate_code.'"')->value('code_pid');
                    $code_arr = db('nba_code')->where('code_pid='.$pid)->column('code');
                    foreach ($code_arr as $ke => $val) {
                        $cid = db('nba_game_cate')->where('game_id='.$id.' and is_win=1 and cate_code ="'.$val.'"')->value('cate_id');
                        if(!empty($cid)){
                            $cate_id[] = $cid;
                        }
                    }
                    $cate_id = array_unique($cate_id);
                    $cate_id = array_values($cate_id);
                    //dump($cate_id);
                    if(count($cate_id) > 1){
                        $win_result = implode(',', $cate_id);
                    }else{
                        $win_result = $cate_id[0];
                    }
                    unset($cate_id);
                }
            //当投注选项有多个时
            }else{
                $tz_result = explode(',', $order_info_all[$key]['tz_result']);
                foreach ($tz_result as $ke => $val) {
                    if(in_array($tz_result[$ke], $cate_id_arr)){
                        $result[] = $tz_result[$ke];
                    }else{
                        $cate_code = db('nba_game_cate')->where('cate_id='.$tz_result[$ke])->value('cate_code');
                        $pid = db('nba_code')->where('code="'.$cate_code.'"')->value('code_pid');
                        $code_arr = db('nba_code')->where('code_pid='.$pid)->column('code');

                        foreach ($code_arr as $k => $v) {
                            $cid = db('nba_game_cate')->where('game_id='.$id.' and is_win=1 and cate_code ="'.$v.'"')->value('cate_id');
                            if(!empty($cid)){
                                $cate_id[] = $cid;
                            }
                        }
                        
                        $result[] = implode(',', $cate_id);
                        unset($cate_id);
                    }
                }
                $result = array_unique($result);
                $result = array_values($result);
                $win_result = implode(',', $result);                
            }
            db('nba_order_info')->where('order_info_id='.$order_info_all[$key]['order_info_id'])->update(array('win_result'=>$win_result,'game_status'=>1));
        }
        Log::info('成功更改订单明细彩果');
        $order_ids = db('nba_order_info')->where('game_id='.$id)->column('order_id');
        foreach ($order_ids as $key => $value) {
            $nba_order = db('nba_order')->where('order_id='.$order_ids[$key])->find();
            // 判断该笔订单先所有比赛的比赛状态(0:未结束|1:已结算)
            $game_status = db('nba_order_info')->where('order_id='.$order_ids[$key])->column('game_status');
            if(!in_array('0', $game_status)){
                // 如果所有比赛全部为 已结算 结算该笔订单
                // 获取该笔订单的所有 注
                $nba_order_group = db('nba_order_group')->where('order_id='.$order_ids[$key])->select();
                foreach ($nba_order_group as $k => $v) {
                    //判断该注里面有几个投注选项
                    if(strpos($nba_order_group[$k]['group_res'], ',') === false){
                        //  -- 如果为一个的话
                        $cate = db('nba_game_cate')->where('cate_id='.$nba_order_group[$k]['group_res'])->find();
                        if($cate['is_win'] == 1){
                            db('nba_order_group')
                            ->where('group_id='.$nba_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_money'=>$nba_order['multiple']*$cate['cate_odds']*2,'win_status'=>1));
                        }else if($cate['is_win'] == 2){
                            db('nba_order_group')
                            ->where('group_id='.$nba_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_status'=>0));
                        } 
                    }else{
                        // 如果为多个的话
                        $group_res = explode(',', $nba_order_group[$k]['group_res']);
                        $cate = db('nba_game_cate')->where('cate_id in ('.$nba_order_group[$k]['group_res'].')')->column('is_win');
                        if(!in_array('0', $cate) && !in_array('2', $cate)){
                            $odds = 1;
                            for ($i=0; $i < count($group_res); $i++) { 
                                $odds = $odds*db('nba_game_cate')->where('cate_id='.$group_res[$i])->value('cate_odds');
                            }
                            db('nba_order_group')
                            ->where('group_id='.$nba_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_money'=>$nba_order['multiple']*$odds*2,'win_status'=>1));
                        }else if(in_array('2', $cate)){
                            db('nba_order_group')
                            ->where('group_id='.$nba_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_status'=>0));
                        }
                    }
                } 
                $win_status = db('nba_order_group')->where('order_id='.$order_ids[$key])->column('win_status');
                if(in_array('1', $win_status)){
                    $win_money = db('nba_order_group')->where('order_id='.$order_ids[$key])->sum('win_money');
                    db('nba_order')->where('order_id='.$order_ids[$key])->update(array('win_money'=>$win_money,'is_win'=>1));
                }else{
                    db('nba_order')->where('order_id='.$order_ids[$key])->update(array('is_win'=>2));
                } 
            }
        }
        Log::info('中奖金额结算成功');
        $nba_order_data = db('nba_order no')
        ->field('no.*')
        ->join('nba_order_info noi','no.order_id=noi.order_id')
        ->where('noi.game_id='.$id.' and noi.game_status=1 and no.is_win=1')
        ->select();
        if(!empty($nba_order_data)){
            foreach ($nba_order_data as $key => $value) {
                $win_money = $nba_order_data[$key]['win_money'];
                $yh = db('yh')->where('id='.$nba_order_data[$key]['user_id'])->find();
                $arr['yhid'] = $yh['yhid'];
                $arr['Jylx'] = 4;
                $arr['jyje'] = $win_money;
                $arr['new_money'] = $win_money+$yh['balance'];
                $arr['Jysj'] = time();
                $arr['Srhzc'] = 1;
                $arr['game_id'] = 0;
                $res = db('account_details')->insert($arr);
                Log::info($yh['yhid'].'余额结算成功');
                /**添加明细**/
                if($res > 0){
                    db('yh')->where('id='.$nba_order_data[$key]['user_id'])->setInc('balance',$win_money);
                    db('yh')->where('id='.$nba_order_data[$key]['user_id'])->setInc('amount_money',$win_money);
                    Log::info($nba_order_data[$key]['user_id'].'明细添加成功');
                }
            }    
        }
        
        // 操作成功 修改比赛状态
        $res = db('nba_game')->where('id='.$id)->setField('status',0);
        if($res > 0) {
            Log::info('结算成功');
            return true;
        }else{
            Log::error($id.'结算失败,比赛状态未更改状态');
            return false;
        }
    }

    public function rank($score)
    {
        switch (true) 
        {
            case $score > 25:  return 26;
            case $score > 20:  return 25;
            case $score > 15:  return 20;
            case $score > 10:  return 15;
            case $score > 5:   return 10;
            case $score > 0:   return 5;
        }
    }
}