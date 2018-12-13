<?php
namespace app\reptile\model;

use Symfony\Component\DomCrawler\Crawler;
use think\Model;
use think\Log;
class Football extends Model {
	public function __construct()
    {
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
    public function getScore($gamedate)
    {	
    	//$gamedate = "2018-12-09";
    	$url = "http://live.zgzcw.com";
    	$html = $this->getCurl($url);
    	//dump($html);die;
    	//将html编码替换为utf-8
    	$coding = mb_detect_encoding($html);
    	if ($coding != "UTF-8" || !mb_check_encoding($html, "UTF-8")){$html = mb_convert_encoding($html, 'utf-8', 'GBK,UTF-8,ASCII');}
    	$crawler = new Crawler($html);
    	$table = $crawler->filterXPath("//table[@id='matchTable']/tbody/tr");
        //dump($table);
        $gamelist = array();
    	foreach ($table as $docElement) {
    		$matchdate = $docElement->getElementsByTagName('td')->item(3)->getAttribute('start-time');
            //dump(date('Y-m-d',$matchdate/1000-43200));
    		if($gamedate == date('Y-m-d',$matchdate/1000)){
    			$status_code = $docElement->getElementsByTagName('td')->item(3)->getAttribute('status');
                dump($status_code);
                $match_status = $docElement->getElementsByTagName('td')->item(4)->textContent;
                dump($match_status);
    			if($status_code == -1 && $match_status == "完"){
    				$matchno = $docElement->getElementsByTagName('td')->item(0)->textContent;
	    			//$game['gamedate'] = $matchdate;
	    			$game['week'] = substr($matchno,0,6);
	    			$game['game_no'] = substr($matchno,-3,3);
                    $game['home_team'] = $docElement->getElementsByTagName('td')->item(5)->getElementsByTagName('a')->item(0)->textContent;
                    $game['road_team'] = $docElement->getElementsByTagName('td')->item(7)->getElementsByTagName('a')->item(0)->textContent;
                    $halfscore = $docElement->getElementsByTagName('td')->item(8)->textContent;
                    $score = $docElement->getElementsByTagName('td')->item(6)->textContent;
					$game['halfscore'] = str_replace('-',':',$halfscore);
					$game['score'] = str_replace('-',':',$score);
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
     * 计算总分
     * @param  [int] $id 游戏id
     * @return void 
     */
    public function edit_field($id){
        $fb_game = db('fb_game')->where('id='.$id)->find();
        if(!empty($fb_game['top_score']) && !empty($fb_game['down_score'])){
            $total_score = explode(':', $fb_game['down_score']);
            $total = $total_score[0]+$total_score[1];
            //dump($total);
            db('fb_game')->where('id='.$id)->setField('total_score',$total);
            Log::info('总分计算成功');
        }        
    }
    
    /**
     * 结算 通过比赛ID查其所有的投注选项ID -> 在通过投注选项ID查询所有的订单 -> 结算
     * @param  [int] $id 比赛id
     * @return [boolean] 结算是否成功
     */
    public function fb_over($id)
    {
        Log::info('调用fb_over($id)');
        $game = db('fb_game')->where('id='.$id)->find();

        $home_score = explode(':', $game['down_score'])[0];
        $road_score = explode(':', $game['down_score'])[1];
        $top_home_score = explode(':', $game['top_score'])[0];
        $top_road_score = explode(':', $game['top_score'])[1];
        //让分分数
        $home_let_score = $home_score+$game['let_score'];
        //俩队总进球数
        $total_score = $game['total_score'];
        //获取比分所有选项
        $score = db('fb_code')->where('code_pid=3')->column('code_name');
        //根据比赛分数修改本场比赛的所有投注选项的中奖状态
        $game_cate = db('fb_game_cate')->where('game_id='.$id)->select();
        foreach ($game_cate as $key => $value) {
            db('fb_game_cate')->where('game_id='.$id)->setField('is_win',2);
            if($top_home_score > $top_road_score){
                $half_full = 'win_';
            }else if($top_home_score < $top_road_score){
                $half_full = 'lose_';
            }else{
                $half_full = 'eq_';
            }
            if($home_score > $road_score){
                $half_full .= 'win';
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="home_win"')->setField('is_win',1);
            }else if($home_score < $road_score){
                $half_full .= 'lose';
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="home_lose"')->setField('is_win',1);
            }else{
                $half_full .= 'eq';
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="home_eq"')->setField('is_win',1);
            }
            db('fb_game_cate')->where('game_id='.$id.' and cate_code="'.$half_full.'"')->setField('is_win',1);

            if($home_let_score > $road_score){
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="let_score_home_win"')->setField('is_win',1);
            }else if($home_let_score < $road_score){
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="let_score_home_lose"')->setField('is_win',1);
            }else{
                db('fb_game_cate')->where('game_id='.$id.' and cate_code="let_score_home_eq"')->setField('is_win',1);
            }
            if(in_array($game['down_score'], $score)){
                db('fb_game_cate')->where('game_id='.$id.' and cate_name="'.$game['down_score'].'"')->setField('is_win',1);
            }else{
                if($home_score > $road_score){
                    db('fb_game_cate')->where('game_id='.$id.' and cate_code="win_other"')->setField('is_win',1);
                }else if($home_score < $road_score){
                    db('fb_game_cate')->where('game_id='.$id.' and cate_code="lose_other"')->setField('is_win',1);
                }else{
                    db('fb_game_cate')->where('game_id='.$id.' and cate_code="eq_other"')->setField('is_win',1);
                }
            }
            db('fb_game_cate')->where('game_id='.$id.' and cate_code="'.$this->rank($total_score).'"')->setField('is_win',1);
            
            
        }
        Log::info('输入彩果');
        // 获取这场比赛的所有中奖选项ID::cate_ids
        $cate_id_arr = db('fb_game_cate')->where('game_id='.$id.' and is_win=1')->column('cate_id');
        $cate_ids = implode(',', $cate_id_arr);
        db('fb_order_info')->where('game_id='.$id.' and game_status=0')->setField('win_game_result',$cate_ids);
        // 查询订单明细里所有有关于这场比赛数据 并更改他们的比赛状态和中奖彩果
        $order_info_all = db('fb_order_info')->where('game_id='.$id.' and game_status=0')->select();
        foreach ($order_info_all as $key => $value) {
            if(strpos($order_info_all[$key]['tz_result'], ',') === false){
                if(in_array($order_info_all[$key]['tz_result'], $cate_id_arr)){
                    $win_result = $order_info_all[$key]['tz_result'];
                }else{
                    $cate_code = db('fb_game_cate')->where('cate_id='.$order_info_all[$key]['tz_result'])->value('cate_code');
                    $pid = db('fb_code')->where('code="'.$cate_code.'"')->value('code_pid');
                    $code_arr = db('fb_code')->where('code_pid='.$pid)->column('code');
                    foreach ($code_arr as $ke => $val) {
                        $cid = db('fb_game_cate')->where('game_id='.$id.' and is_win=1 and cate_code ="'.$val.'"')->value('cate_id');
                        if(!empty($cid)){
                            $cate_id[] = $cid;
                        }
                    }
                    if(count($cate_id) > 1){
                        $win_result = implode(',', $cate_id);
                    }else{
                        $win_result = $cate_id[0];
                    }
                    unset($cate_id);
                }
            }else{
                $tz_result = explode(',', $order_info_all[$key]['tz_result']);
                foreach ($tz_result as $ke => $val) {
                    if(in_array($tz_result[$ke], $cate_id_arr)){
                        // if(!empty($tz_result[$ke])){
                            $result[] = $tz_result[$ke];
                        // }
                    }else{
                        $cate_code = db('fb_game_cate')->where('cate_id='.$tz_result[$ke])->value('cate_code');
                        $pid = db('fb_code')->where('code="'.$cate_code.'"')->value('code_pid');
                        $code_arr = db('fb_code')->where('code_pid='.$pid)->column('code');

                        foreach ($code_arr as $k => $v) {
                            $cid = db('fb_game_cate')->where('game_id='.$id.' and is_win=1 and cate_code ="'.$v.'"')->value('cate_id');
                            if(!empty($cid)){
                                $cate_id[] = $cid;
                            }
                        }
                        
                        $result[] = implode(',', $cate_id);
                        unset($cate_id);
                    }
                }
                $win_result = implode(',', $result);           
            }
            db('fb_order_info')->where('order_info_id='.$order_info_all[$key]['order_info_id'])->update(array('win_result'=>$win_result,'game_status'=>1));
        }
        Log::info('成功更改订单明细彩果');
        $order_ids = db('fb_order_info')->where('game_id='.$id)->column('order_id');
        foreach ($order_ids as $key => $value) {
            //Log('ids: '.json_decode($order_ids));
            $fb_order = db('fb_order')->where('order_id='.$order_ids[$key])->find();
            // 判断该笔订单先所有比赛的比赛状态(0:未结束|1:已结算)
            $game_status = db('fb_order_info')->where('order_id='.$order_ids[$key])->column('game_status');
            if(!in_array('0', $game_status)){
                // 如果所有比赛全部为 已结算 结算该笔订单
                // 获取该笔订单的所有 注
                $fb_order_group = db('fb_order_group')->where('order_id='.$order_ids[$key])->select();
                //Log('fb_order_group: '.json_decode($fb_order_group));
                foreach ($fb_order_group as $k => $v) {                   
                    //判断该注里面有几个投注选项
                    if(strpos($fb_order_group[$k]['group_res'], ',') === false){
                        //如果为一个的话
                        $cate = db('fb_game_cate')->where('cate_id='.$fb_order_group[$k]['group_res'])->find();
                        if($cate['is_win'] == 1){
                            db('fb_order_group')
                            ->where('group_id='.$fb_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_money'=>$fb_order['multiple']*$cate['cate_odds']*2,'win_status'=>1));
                        }else if($cate['is_win'] == 2){
                            db('fb_order_group')
                            ->where('group_id='.$fb_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_status'=>0));
                        } 
                    }else{
                        // 如果为多个的话
                        $group_res = explode(',', $fb_order_group[$k]['group_res']);
                        $cate = db('fb_game_cate')->where('cate_id in ('.$fb_order_group[$k]['group_res'].')')->column('is_win');
                        if(!in_array('0', $cate) && !in_array('2', $cate)){
                            $odds = 1;
                            for ($i=0; $i < count($group_res); $i++) { 
                                $odds = $odds*db('fb_game_cate')->where('cate_id='.$group_res[$i])->value('cate_odds');
                            }
                            db('fb_order_group')
                            ->where('group_id='.$fb_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_money'=>$fb_order['multiple']*$odds*2,'win_status'=>1));
                        }else if(in_array('2', $cate)){
                            db('fb_order_group')
                            ->where('group_id='.$fb_order_group[$k]['group_id'])
                            ->update(array('status'=>1,'win_status'=>0));
                        }
                    }
                } 
                $win_status = db('fb_order_group')->where('order_id='.$order_ids[$key])->column('win_status');
                if(in_array('1', $win_status)){
                    $win_money = db('fb_order_group')->where('order_id='.$order_ids[$key])->sum('win_money');
                    db('fb_order')->where('order_id='.$order_ids[$key])->update(array('win_money'=>$win_money,'is_win'=>1));
                }else{
                    db('fb_order')->where('order_id='.$order_ids[$key])->update(array('is_win'=>2));
                } 
            }
        }
        Log::info('中奖金额结算成功');
        $fb_order_data = db('fb_order no')
        ->field('no.*')
        ->join('fb_order_info noi','no.order_id=noi.order_id')
        ->where('noi.game_id='.$id.' and noi.game_status=1 and no.is_win=1')
        ->select();
        //Log::info('fb_order_data: '.$fb_order_data);
        if(!empty($fb_order_data)){
            foreach ($fb_order_data as $key => $value) {
                $win_money = $fb_order_data[$key]['win_money'];
                $yh = db('yh')->where('id='.$fb_order_data[$key]['user_id'])->find();
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
                    db('yh')->where('id='.$fb_order_data[$key]['user_id'])->setInc('balance',$win_money);
                    db('yh')->where('id='.$fb_order_data[$key]['user_id'])->setInc('amount_money',$win_money);
                    Log::info($fb_order_data[$key]['user_id'].'明细添加成功');
                }
            }    
        }
        
        // 操作成功 修改比赛状态
        $res = db('fb_game')->where('id='.$id)->setField('status',0);
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
            case $score >= 7:  return 'total_seven_gt';
            case $score == 6:  return 'total_six';
            case $score == 5:  return 'total_five';
            case $score == 4:  return 'total_four';
            case $score == 3:  return 'total_three';
            case $score == 2:  return 'total_two';
            case $score == 1:  return 'total_one';
            case $score == 0:  return 'total_zero';
        }
    }
}