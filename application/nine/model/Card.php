<?php
namespace app\nine\model;
use think\Model;

class Card extends Model {

    // 随机生成9张牌
    public function get_card()
    {
        $card = db('codex_nine')->select();
        //生出中间的牌 并在牌堆中将其剔除
        $data = array();
        $data['deal_order'] = mt_rand(1,40);
        foreach ($card as $key => $value) {
            if($card[$key]['code_id'] == $data['deal_order']){
                unset($card[$key]);
            }
        }

        // 随机获取庄家左牌数据 并从数组中剔除
        $banker_left_key = array_rand($card);
        $banker_left_val = $card[$banker_left_key];
        unset($card[$banker_left_key]);
        $data['banker_left'] = $banker_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $banker_right_key = array_rand($card);
        $banker_right_val = $card[$banker_right_key];
        unset($card[$banker_right_key]);
        $data['banker_right'] = $banker_right_val['code_id']; 
        if($banker_left_val['code_info_id'] == $banker_right_val['code_info_id']){
            $data['banker_result'] = $banker_left_val['code_info_id'].$banker_right_val['code_info_id'];
        }else{
            $data['banker_result'] = ($banker_left_val['code_info_id']+$banker_right_val['code_info_id'])%10;
        }

        // 随机获取闲家的牌
        // 随机获取上门左牌数据 并从数组中剔除
        $visit_left_key = array_rand($card);
        $visit_left_val = $card[$visit_left_key];
        unset($card[$visit_left_key]);
        $data['visit_left'] = $visit_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $visit_right_key = array_rand($card);
        $visit_right_val = $card[$visit_right_key];
        unset($card[$visit_right_key]);
        $data['visit_right'] = $visit_right_val['code_id']; 
        if($visit_left_val['code_info_id'] == $visit_right_val['code_info_id']){
            $data['visit_result'] = $visit_left_val['code_info_id'].$visit_right_val['code_info_id'];
        }else{
            $data['visit_result'] = ($visit_left_val['code_info_id']+$visit_right_val['code_info_id'])%10;
        }
        

        // 随机获取上门左牌数据 并从数组中剔除
        $nostril_left_key = array_rand($card);
        $nostril_left_val = $card[$nostril_left_key];
        unset($card[$nostril_left_key]);
        $data['nostril_left'] = $nostril_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $nostril_right_key = array_rand($card);
        $nostril_right_val = $card[$nostril_right_key];
        unset($card[$nostril_right_key]);
        $data['nostril_right'] = $nostril_right_val['code_id']; 
        if($nostril_left_val['code_info_id'] == $nostril_right_val['code_info_id']){
            $data['nostril_result'] = $nostril_left_val['code_info_id'].$nostril_right_val['code_info_id'];
        }else{
            $data['nostril_result'] = ($nostril_left_val['code_info_id']+$nostril_right_val['code_info_id'])%10;
        }

        // 随机获取上门左牌数据 并从数组中剔除
        $surname_left_key = array_rand($card);
        $surname_left_val = $card[$surname_left_key];
        unset($card[$surname_left_key]);
        $data['surname_left'] = $surname_left_val['code_id']; 
        // 随机获取上门右牌数据 并从数组中剔除
        $surname_right_key = array_rand($card);
        $surname_right_val = $card[$surname_right_key];
        unset($card[$surname_right_key]);
        $data['surname_right'] = $surname_right_val['code_id']; 
        if($surname_left_val['code_info_id'] == $surname_right_val['code_info_id']){
            $data['surname_result'] = $surname_left_val['code_info_id'].$surname_right_val['code_info_id'];
        }else{
            $data['surname_result'] = ($surname_left_val['code_info_id']+$surname_right_val['code_info_id'])%10;
        }

        return $data;
    }
}