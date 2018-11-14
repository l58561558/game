<?php
/**
 * 换牌工具类
 */
namespace app\bjl\model;

class Change{
    
    function __construct(){
        
    }
    /**
     * 换牌
     * @param  string $kjid 开奖id
     * @return array  $data 牌数组
     */
    public function changeCard($kjid)
    {

    	$bjl = db('bjl_kj')->where('kjid="'.$kjid.'"')->find();
        $time = $bjl['win_time']-time();
        if($time <= 15){
            $this->error('换牌失败');
        }
        $card = db('bjl_codex')->select();
        $data = array();

        // 随机获取闲家左牌数据 并从数组中剔除
        $player_left_key = array_rand($card);
        $player_left_val = $card[$player_left_key];
        unset($card[$player_left_key]);
        $data['player_left'] = $player_left_val['code_id']; 
        $player_left_num = $player_left_val['code_info_id']>10?10:$player_left_val['code_info_id']; 
        // 随机获取闲家右牌数据 并从数组中剔除
        $player_right_key = array_rand($card);
        $player_right_val = $card[$player_right_key];
        unset($card[$player_right_key]);
        $data['player_right'] = $player_right_val['code_id'];
        $player_right_num = $player_right_val['code_info_id']>10?10:$player_right_val['code_info_id'];
        //闲家最终点数
        $player_result = ($player_left_num+$player_right_num)%10;
        $data['player_three'] = ''; 
        if($player_result >= 0 && $player_result <= 5){
            //如果闲家左牌点数加上右牌点数大于0 并且 小于等于5 补上第三张拍
            $player_three_key = array_rand($card);
            $player_three_val = $card[$player_three_key];
            unset($card[$player_three_key]);
            $data['player_three'] = $player_three_val['code_id']; 
            $player_three_num = $player_three_val['code_info_id']>10?10:$player_three_val['code_info_id']; 
            //闲家最终点数
            $player_result = ($player_left_num+$player_right_num+$player_three_num)%10;
            $data['player_result'] = $player_result;
        }else{
            $data['player_result'] = $player_result;
        }

        // 随机获取庄家左牌数据 并从数组中剔除
        $banker_left_key = array_rand($card);
        $banker_left_val = $card[$banker_left_key];
        unset($card[$banker_left_key]);
        $data['banker_left'] = $banker_left_val['code_id']; 
        $banker_left_num = $banker_left_val['code_info_id']>10?10:$banker_left_val['code_info_id']; 
        // 随机获取庄家右牌数据 并从数组中剔除
        $banker_right_key = array_rand($card);
        $banker_right_val = $card[$banker_right_key];
        unset($card[$banker_right_key]);
        $data['banker_right'] = $banker_right_val['code_id']; 
        $banker_right_num = $banker_right_val['code_info_id']>10?10:$banker_right_val['code_info_id']; 
        //庄家最终点数
        $data['banker_three'] = ''; 
        $banker_result = ($banker_left_num+$banker_right_num)%10;
        if($banker_result >= 0 && $banker_result < 7){
            if($banker_result == 3 && (!isset($player_three_num) || $player_three_num != 8)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==4 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==5 && (!isset($player_three_num) || $player_three_num!=0 || $player_three_num!=1 || $player_three_num!=2 || $player_three_num!=3 || $player_three_num!=8 || $player_three_num!=9)){
                $data['banker_result'] = $banker_result;
            }else if($banker_result==6 && (!isset($player_three_num) || $player_three_num!=6 || $player_three_num!=7)){
                $data['banker_result'] = $banker_result;
            }else{
                //如果庄家左牌点数加上右牌点数大于0 并且 小于等于2 补上第三张拍
                $banker_three_key = array_rand($card);
                $banker_three_val = $card[$banker_three_key];
                unset($card[$banker_three_key]);
                $data['banker_three'] = $banker_three_val['code_id']; 
                $banker_three_num = $banker_three_val['code_info_id']>10?10:$banker_three_val['code_info_id']; 
                //庄家最终点数
                $data['banker_result'] = ($banker_left_num+$banker_right_num+$banker_three_num)%10;
            }            
        }else{
            $data['banker_result'] = $banker_result;
        }
        /*dump($data); */       
        return $data;
    }
    /**
     * 获胜结果数组
     * @param  array $data 卡牌数组
     * @return array $win_result 中奖结果集
     */
    public function winResult($data)
    {
    	$win_result = array();    	
    	if($data['banker_result'] > $data['player_result']){
            $win_result[] = 1;
        }else if($data['banker_result'] < $data['player_result']){
            $win_result[] = 2;
        }else if($data['banker_result'] == $data['player_result']){
            $win_result[] = 3;
        }
		$banker_left_val  = db('bjl_codex')->where('code_id',"=",$data['banker_left'])->value('code_info_id');
		$banker_right_val = db('bjl_codex')->where('code_id',"=",$data['banker_right'])->value('code_info_id');
		$player_left_val  = db('bjl_codex')->where('code_id',"=",$data['player_left'])->value('code_info_id');
		$player_right_val = db('bjl_codex')->where('code_id',"=",$data['player_right'])->value('code_info_id');
    	//判断是否生成庄对闲对
    	if($banker_left_val == $banker_right_val){
            $win_result[] = 4;
            return $win_result;
        }
        if($player_left_val == $player_right_val){
            $win_result[] = 5;
            return $win_result;
        }
        /*dump($win_result);*/
        return $win_result;
    }

    /**
     * 获取预期中奖金额最小值对应的投注选项(仅限庄闲和)
     * @param  array $expect 预期中奖金额数组 
     * @return int   投注选项
     */
    public function minResult($expect)
    {           
        //遍历数组获取数组最小值
        $min = $expect[0];
        $result = 1;
        for($i=1;$i<3;$i++){
            if($min > $expect[$i]){
                $min = $expect[$i];
                $result = $i+1;
            }
        }
        return $result;
    }
}