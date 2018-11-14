<?php
namespace app\adminz\controller;

class Jackpot extends Base
{

    public function save_status($status,$game_id,$money = 0)
    {
        if($status>0){
            $res = db('game_cate')->where('game_id='.$game_id)->setField('status',$status);
            if($game_id == 1){
                $url = 'Jackpot/dxh';
            }else if($game_id == 2){
                $url = 'Jackpot/dice';
            }else if($game_id == 3){
                db('game_cate')->where('game_id='.$game_id)->setField('money',$money);
                $url = 'Jackpot/nine';
            }else if($game_id == 4){
                $url = 'Jackpot/dial';
            }else if($game_id == 5){
                $url = 'Jackpot/bjl';
            }

            $this->redirect($url);
        }
    }

    // 本期奖池 -- 大小和
    public function dxh($game_id=1)
    {
        $list = db('dxh_kj')->where('kjsjzt>0 and game_id='.$game_id)->order('Kjsj desc')->find();

        if(!empty($list)){
            $list['Kjdjs'] = $list['Kjsj']-time();

            $list['tz'] = db('codex_dxh')->where('pid>0')->select();
        }

        $status = db('game_cate')->where('game_id='.$game_id)->value('status');

        $this->assign("status",$status);
        $this->assign("_list",$list);
        return view();
    }

    public function get_dxh_data($kjid)
    {
        $list = db('dxh_kj')->where('game_id=1 and kjid="'.$kjid.'"')->find();

        $list['Kjdjs'] = $list['Kjsj']-time();

        $list['tz'] = db('codex_dxh')->where('pid>0')->select();

        $this->ajaxReturn(['data'=>$list,'code'=>1]);
    }


    // 本期奖池 -- 骰子
    public function dice($game_id=2)
    {
        $list['new'] = db('dice_kj')->where('kjsjzt>0 and game_id='.$game_id)->order('Kjsj desc')->find();

        if(!empty($list['new'])){
            $list['new']['Kjdjs'] = $list['new']['Kjsj']-time();
        }

        // 获取所有的骰子点数和预计中奖金额
        $codex_dice = db('codex_dice')->field('arr_id,win_money')->select();
        foreach ($codex_dice as $key => $value) {
            $data[$codex_dice[$key]['arr_id']] = $codex_dice[$key]['win_money'];
        }
        // 获取其中最小值
        $min = min($data); 
        // 判断是否有重复的彩果 如果有随机抽取一种彩果
        $array = db('codex_dice')->where('win_money='.$min)->column('arr_id');
        $rand = array_rand($array);
        $win_result = $array[$rand];
        $str1 = substr($win_result,0,1);
        $str2 = substr($win_result,-1,1);
        $str = $str1.','.$str2;
        if(!empty($list['new'])){
            $list['new']['win_result'] = $str;
        }
        
        $data = db('codex_dice')->select();
        foreach ($data as $key => $value) {
            $data[$key]['sum_numbers'] = db('code_dice')->where('code_info_id=10 and value='.$data[$key]['sum_numbers'])->value('code_name');
            // $data[$key]['is_leopard'] = db('code_dice')->where('code_info_id=20 and value='.$data[$key]['is_leopard'])->value('code_name');
            $data[$key]['dx'] = db('code_dice')->where('code_info_id=90 and value='.$data[$key]['dx'])->value('code_name');
            // $data[$key]['sum'] = db('code_dice')->where('code_info_id=30 and value='.$data[$key]['sum'])->value('code_name');
            $data[$key]['num1_left'] = db('code_dice')->where('code_info_id=40 and value='.$data[$key]['num1_left'])->value('code_name');
            $data[$key]['num1_right'] = db('code_dice')->where('code_info_id=50 and value='.$data[$key]['num1_right'])->value('code_name');
            $data[$key]['l_dice'] = db('code_dice')->where('code_info_id=60 and value='.$data[$key]['l_dice'])->value('code_name');
            $data[$key]['r_dice'] = db('code_dice')->where('code_info_id=70 and value='.$data[$key]['r_dice'])->value('code_name');
            // $data[$key]['two_dice'] = db('code_dice')->where('code_info_id=80 and value='.$data[$key]['two_dice'])->value('code_name');
        }
        $list['data'] = $data;

        $status = db('game_cate')->where('game_id='.$game_id)->value('status');
        $this->assign("status",$status);

        $this->assign("_list",$list);
        return view();
    }
    public function get_dice_data($kjid)
    {
        $list = db('dice_kj')->where('kjsjzt>0 and game_id=2 and kjid="'.$kjid.'"')->find();
        $list['Kjdjs'] = $list['Kjsj']-time();
        // 获取所有的骰子点数和预计中奖金额
        $codex_dice = db('codex_dice')->field('arr_id,win_money')->select();
        foreach ($codex_dice as $key => $value) {
            $codex_data[$codex_dice[$key]['arr_id']] = $codex_dice[$key]['win_money'];
        }
        // 获取其中最小值
        $min = min($codex_data); 
        // 判断是否有重复的彩果 如果有随机抽取一种彩果
        $array = db('codex_dice')->where('win_money='.$min)->column('arr_id');
        $rand = array_rand($array);
        $win_result = $array[$rand];
        $str1 = substr($win_result,0,1);
        $str2 = substr($win_result,-1,1);
        $str = $str1.','.$str2;
        if(!empty($list)){
            $list['win_result'] = $str;
            $data = db('codex_dice')
                // ->field('sum_numbers_money','is_leopard_money','sum_money','num1_left_money','l_dice_money','r_dice_money','two_dice_money','tz_money','win_money')
                ->select();

            $list['data'] = $data;
            $this->ajaxReturn(['data'=>$list,'code'=>1]);
        }else{
            $this->ajaxReturn(['data'=>'数据为空','code'=>0]);
        }
        
    }


    // 本期奖池 -- 牌九
    public function nine($game_id=3)
    {
        $list = db('nine_kj')->where('win_status>0')->order('add_time desc')->find();

        if(!empty($list)){
            $list['Kjdjs'] = $list['win_time']-time();

            $list['banker_left'] = db('codex_nine')->where('code_id='.$list['banker_left'])->value('desc');
            $list['banker_right'] = db('codex_nine')->where('code_id='.$list['banker_right'])->value('desc');
            $list['visit_left'] = db('codex_nine')->where('code_id='.$list['visit_left'])->value('desc');
            $list['visit_right'] = db('codex_nine')->where('code_id='.$list['visit_right'])->value('desc');
            $list['nostril_left'] = db('codex_nine')->where('code_id='.$list['nostril_left'])->value('desc');
            $list['nostril_right'] = db('codex_nine')->where('code_id='.$list['nostril_right'])->value('desc');
            $list['surname_left'] = db('codex_nine')->where('code_id='.$list['surname_left'])->value('desc');
            $list['surname_right'] = db('codex_nine')->where('code_id='.$list['surname_right'])->value('desc');

            if(!empty($list['win_result'])){
                // if($list['win_result'] == '000'){
                //     $list['result_status'] = 1;
                // }else if($list['win_result'] == '111'){
                //     $list['result_status'] = 4;
                // }else if($list['win_result'] == '011' || $list['win_result'] == '101' || $list['win_result'] == '110'){
                //     $list['result_status'] = 2;
                // }else if($list['win_result'] == '001' || $list['win_result'] == '010' || $list['win_result'] == '100'){
                //     $list['result_status'] = 3;
                // }

                // $list['result_status'] = db('code_nine')->where('code_info_id=10 and code='.$list['result_status'])->value('desc');
                $num1 = db('code_nine')->where('code_info_id=30 and code='.substr($list['win_result'], 0,1))->value('desc');
                $num2 = db('code_nine')->where('code_info_id=30 and code='.substr($list['win_result'], 1,1))->value('desc');
                $num3 = db('code_nine')->where('code_info_id=30 and code='.substr($list['win_result'], 2,1))->value('desc');
                $list['win_result'] = '上门('.$num1.')|'.'天门('.$num2.')|'.'下门('.$num3.')';                
            }
        }

        $status = db('game_cate')->where('game_id='.$game_id)->find();
        $this->assign("status",$status);

        $this->assign("_list",$list);
        return view();
    }
    public function get_nine_data($kjid)
    {
        $list = db('nine_kj')->where('kjid="'.$kjid.'"')->find();

        if(!empty($list)){
            $list['Kjdjs'] = $list['win_time']-time();
        }

        $this->ajaxReturn(['data'=>$list,'code'=>1]);
    }


    // 本期奖池 -- 大转盘
    public function dial($game_id=4)
    {
        $list['new'] = db('dial_kj')->where('kjsjzt>0 and game_id='.$game_id)->order('Kjsj desc')->find();

        if(!empty($list['new'])){
            $list['new']['Kjdjs'] = $list['new']['Kjsj']-time();
        }
        
        $data = db('codex_dial')->select();
        foreach ($data as $key => $value) {
            $data[$key]['two'] = db('code_dial')->where('code_info_id=10 and value='.$data[$key]['two'])->value('code_name');
            $data[$key]['six'] = db('code_dial')->where('code_info_id=20 and value='.$data[$key]['six'])->value('code_name');
            $data[$key]['twelve'] = db('code_dial')->where('code_info_id=30 and value='.$data[$key]['twelve'])->value('code_name');
            $data[$key]['special'] = db('code_dial')->where('code_info_id=40 and value='.$data[$key]['special'])->value('code_name');
        }
        $list['data'] = $data;

        $status = db('game_cate')->where('game_id='.$game_id)->value('status');
        $this->assign("status",$status);

        $this->assign("_list",$list);
        return view();
    }
    public function get_dial_data($kjid)
    {
        $list = db('dial_kj')->where('game_id=4 and kjid="'.$kjid.'"')->find();
        $list['Kjdjs'] = $list['Kjsj']-time();
        // 获取所有的骰子点数和预计中奖金额
        $data = db('codex_dial')->select();
        foreach ($data as $key => $value) {
            $data[$key]['two'] = db('code_dial')->where('code_info_id=10 and value='.$data[$key]['two'])->value('code_name');
            $data[$key]['six'] = db('code_dial')->where('code_info_id=20 and value='.$data[$key]['six'])->value('code_name');
            $data[$key]['twelve'] = db('code_dial')->where('code_info_id=30 and value='.$data[$key]['twelve'])->value('code_name');
            $data[$key]['special'] = db('code_dial')->where('code_info_id=40 and value='.$data[$key]['special'])->value('code_name');
        }
        if(!empty($list)){
            $list['data'] = $data;
            // dump($list);
            $this->ajaxReturn(['data'=>$list,'code'=>1]);
        }else{
            $this->ajaxReturn(['data'=>'数据为空','code'=>0]);
        }
    }

    // 本期奖池 -- 百家乐
    public function bjl($game_id=5)
    {
        $list = db('bjl_kj')->where('win_status>0')->order('add_time desc')->find();

        if(!empty($list)){
            $list['Kjdjs'] = $list['win_time']-time();

            $list['banker_left'] = db('bjl_codex')->where('code_id='.$list['banker_left'])->value('desc');
            $list['banker_right'] = db('bjl_codex')->where('code_id='.$list['banker_right'])->value('desc');
            $list['banker_three'] = empty($list['banker_three'])?$list['banker_three']:db('bjl_codex')->where('code_id='.$list['banker_three'])->value('desc');
            $list['player_left'] = db('bjl_codex')->where('code_id='.$list['player_left'])->value('desc');
            $list['player_right'] = db('bjl_codex')->where('code_id='.$list['player_right'])->value('desc');
            $list['player_three'] = empty($list['player_three'])?$list['player_three']:db('bjl_codex')->where('code_id='.$list['player_three'])->value('desc');

            if(!empty($list['win_result'])){
                $win_result = explode(',', $list['win_result']);
                foreach ($win_result as $key => $value) {
                    $result[] = db('bjl_code')->where('id='.$value)->value('desc');
                }
                $list['win_result'] = implode(',', $result);                
            }
            $tz_money = db('bjl_code')->select();
            $this->assign("tz_money",$tz_money);
        }


        $status = db('game_cate')->where('game_id='.$game_id)->value('status');
        $this->assign("status",$status);

        $this->assign("_list",$list);
        return view();
    }





}
