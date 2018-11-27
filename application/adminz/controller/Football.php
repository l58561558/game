<?php
namespace app\adminz\controller;
use think\Db;
class Football extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index()
    {
        return view();
    }
    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_fb_list()
    {
        $count = db("fb_game")->where('status>0')->order('end_time asc')->count();
        $list = db("fb_game")->where('status>0')->order('end_time asc')->paginate(20,$count);

        //获取分页
        $page = $list->render();
        //遍历数据
        if(!empty($list)){
            $list->each(function($item,$key){
                if($item['status'] == 0){
                    $item['tz_status'] = '已结算';
                }else{
                    if($item['end_time'] <= time()){
                        $item['tz_status'] = '停止投注';
                    }else{
                        $item['tz_status'] = '可以投注';
                    }    
                }
                return $item;
            });
        }
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("football/fb_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function add()
    {
        if(IS_POST){
            $data = $_REQUEST;
            // dump($data);die;
            $end_time = strtotime($data['end_time']);
            $start = strtotime(date('Y-m-d',$end_time));
            $end = strtotime(date('Y-m-d',$end_time))+86400;
            $count = db('fb_game')->where('end_time>='.$start.' and end_time<='.$end)->count();
            
            // $game_no = $count+1;
            // if(strlen($game_no) == 1){
            //     $game['game_no'] = '00'.$game_no;
            // }else if(strlen($game_no) == 2){
            //     $game['game_no'] = '0'.$game_no;
            // }
            $game['game_no'] = $data['game_no'];
            $weekday = array('周日','周一','周二','周三','周四','周五','周六'); 
            if(($end_time) >= (strtotime(date('Y-m-d', $end_time))+43200)){
                $fb_game['week'] = $weekday[date('w', $end_time)];
            }else{
                $fb_game['week'] = $weekday[date('w', (strtotime(date('Y-m-d', $end_time))-43200))];
            }
            // $game['week'] = $this->weekday($end_time);
            $game['game_name'] = $data['game_name'];
            $game['end_time'] = $end_time;
            $game['home_team'] = $data['home_team'];
            $game['road_team'] = $data['road_team'];
            $game['let_score'] = $data['let_score'];
            $game['add_time'] = time();
            unset($data['game_no']);
            unset($data['game_name']);
            unset($data['end_time']);
            unset($data['home_team']);
            unset($data['road_team']);
            unset($data['let_score']);
            $game_id = db('fb_game')->insert($game,false,true);
            if($game_id > 0){
                $cate = array();
                $array = array('home_win','home_eq','home_lose','let_score_home_win','let_score_home_eq','let_score_home_lose');
                foreach ($data as $key => $value) {
                    $fb_code_date = db('fb_code')->where('code="'.$key.'"')->find();
                    $cate['game_id'] = $game_id;
                    $cate['cate_name'] = $fb_code_date['code_name'];
                    $cate['cate_code'] = $key;
                    // $cate['cate_odds'] = $value==0?0:(in_array($key, $array)?floor($value*1.085*100)/100:$value);
                    $cate['cate_odds'] = $value;
                    $game_cate[] = $cate;
                }
                $res = db('fb_game_cate')->insertAll($game_cate);
                if($res > 0){
                    $this->success('创建成功');
                }else{
                    $this->error('插入失败');
                }
            }
        }
        $code = db("fb_code")->where('code_pid=0')->select();
        foreach ($code as $key => $value) {
            if($code[$key]['id'] == 3){
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id<=13')->select();
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id>13 and code_id<=18')->select();
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id>18')->select();
            }else{
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'])->select();
            }
        }
        $this->assign('code',$fb_code);
        return view();
    }

    public function drop($id = 0)
    {
        db('fb_game')->where('id='.$id)->delete();
        db('fb_game_cate')->where('game_id='.$id)->delete();
        $this->success('删除成功');
    }

    public function look($id)
    {
        $data = db('fb_game')->where("id=".$id)->find();
        $game_cate = db('fb_game_cate')->where('game_id='.$id)->select();
        $data['cate'] = $game_cate;

        $this->assign("data",$data);
        return view();
    }

    /**
     * 编辑记录
     * @param  integer $id [description]
     * @return [type]          [description]
     */
    public function edit($id = 0){
        if(IS_POST){
            $data = $_REQUEST;
            $end_time = strtotime($data['end_time']);

            $start = strtotime(date('Y-m-d',$end_time));
            $end = strtotime(date('Y-m-d',$end_time))+86400;
            $count = db('fb_game')->where('end_time>='.$start.' and end_time<='.$end)->count();

            $game['game_no'] = $data['game_no'];
            $game['week'] = $this->weekday($end_time);
            $game['game_name'] = $data['game_name'];
            $game['end_time'] = $end_time;
            $game['home_team'] = $data['home_team'];
            $game['road_team'] = $data['road_team'];
            $game['let_score'] = $data['let_score'];
            $game['top_score'] = $data['top_score'];
            $game['down_score'] = $data['down_score'];

            unset($data['game_name']);
            unset($data['end_time']);
            unset($data['home_team']);
            unset($data['road_team']);
            unset($data['let_score']);
            unset($data['top_score']);
            unset($data['down_score']);
            unset($data['id']);
            $flag = db("fb_game")->where('id='.$id)->update($game);
            foreach ($data as $key => $value) {
                db("fb_game_cate")->where('game_id='.$id.' and cate_code="'.$key.'"')->setField('cate_odds',$data[$key]);
            }
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $code = db("fb_code")->where('code_pid=0')->select();
        foreach ($code as $key => $value) {
            if($code[$key]['id'] == 3){
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id<=13')->select();
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id>13 and code_id<=18')->select();
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'].' and code_id>18')->select();
            }else{
                $fb_code[] = db("fb_code")->where('code_pid='.$code[$key]['id'])->select();
            }
        }

        // $fb_game_cate = db("fb_game_cate")->where('game_id='.$id)->select();
        for ($i=0; $i < count($fb_code); $i++) { 
            for ($j=0; $j < count($fb_code[$i]); $j++) { 
                $fb_cate[$i][$j] = db("fb_game_cate")->where('game_id='.$id.' and cate_code="'.$fb_code[$i][$j]['code'].'"')->find();
            }
        }

        $this->assign('code',$fb_cate);

        $data = db('fb_game')->where("id=".$id)->find();
        $this->assign("data",$data);

        return view();
    }

    public function order_info($order_id)
    {
        $order = db('fb_order')->where('order_id='.$order_id)->order('add_time desc')->find();
        $data['order_money'] = $order['order_money'];
        $data['win_money'] = $order['win_money'];
        $data['order_no'] = $order['order_no'];
        $data['add_time'] = date('Y-m-d H:i:s' ,$order['add_time']);
        $order_info = db('fb_order_info')->where('order_id='.$order_id)->select();
        $data['game_num'] = count($order_info);
        // $data['chuan'] = db('fb_chuan')->where('chuan_id='.$order['chuan'])->value('chuan_name');
        $chuan = explode(',', $order['chuan']);
        $chuann = [];
        for ($i=0; $i < count($chuan); $i++) { 
            $chuann[] = db('fb_chuan')->where('chuan_id='.$chuan[$i])->value('chuan_name');
        }
        $data['chuan'] = implode(',', $chuann);
        $data['multiple'] = $order['multiple'];
        foreach ($order_info as $key => $value) {
            $game[$key] = db('fb_game')->where('id='.$order_info[$key]['game_id'])->find();
            $data['order_info'][$key]['week'] = $game[$key]['week'];
            $data['order_info'][$key]['game_no'] = $game[$key]['game_no'];
            $data['order_info'][$key]['home_team'] = $game[$key]['home_team'];
            $data['order_info'][$key]['road_team'] = $game[$key]['road_team'];
            $data['order_info'][$key]['down_score'] = $game[$key]['down_score'];
            $data['order_info'][$key]['win_result'] = '';
            $data['order_info'][$key]['win_game_result'] = '';
            $data['order_info'][$key]['status'] = $game[$key]['status'];
            if(strpos($order_info[$key]['tz_result'] , ',') === false){
                $data['order_info'][$key]['tz_result'][0] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$order_info[$key]['tz_result'])->find();
            }else{
                if(!empty($order_info[$key]['tz_result'])){
                    $tz_result = explode(',', $order_info[$key]['tz_result']);
                    foreach ($tz_result as $ke => $val) {
                        $tz_result[$ke] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$tz_result[$ke])->find();
                    }
                    $data['order_info'][$key]['tz_result'] = $tz_result;
                }  
            }  
            if(!empty($order_info[$key]['win_result'])){
                if(strpos($order_info[$key]['win_result'] , ',') === false){
                    $data['order_info'][$key]['win_result'][0] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$order_info[$key]['win_result'])->find();
                }else{
                    $win_result = explode(',', $order_info[$key]['win_result']);
                    foreach ($win_result as $ke => $val) {
                        $win_result[$ke] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$win_result[$ke])->find();
                    }
                    $data['order_info'][$key]['win_result'] = $win_result;                      
                }  
            }
            if(!empty($order_info[$key]['win_game_result'])){
                $win_game_result = explode(',', $order_info[$key]['win_game_result']);
                foreach ($win_game_result as $ke => $val) {
                    $win_game_result[$ke] = db('fb_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$win_game_result[$ke])->find();
                }
                $data['order_info'][$key]['win_game_result'] = $win_game_result;  
            }
        }
        // dump($data);die;
        $this->assign("data",$data);
        return view();
    }

    // 订单
    public function fb_order()
    {
        return view();
    }
    public function get_fb_order()
    {
        $data = $_REQUEST;

        $count = db("fb_order")->order('add_time desc')->count();
        $list = db("fb_order")->order('add_time desc')->paginate(20,$count);

        $list->each(function($item,$key){
            $item['user_id'] = db('yh')->where('id='.$item['user_id'])->value('yhid');
            $chuan = explode(',', $item['chuan']);
            $chuann = [];
            for ($i=0; $i < count($chuan); $i++) { 
                $chuann[] = db('fb_chuan')->where('chuan_id='.$chuan[$i])->value('chuan_name');
            }
            $item['chuan'] = implode(',', $chuann);
            return $item;
        });
        $page = $list->render();

        $this->assign("page",$page);
        $this->assign("_list",$list);
        
        $html = $this->fetch("tpl/fb_order_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
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
    // 结算
    // 通过比赛ID查其所有的投注选项ID -> 在通过投注选项ID查询所有的订单 -> 结算
    public function fb_over($id)
    {
        set_time_limit(0);
        $game = db('fb_game')->where('id='.$id)->find();
        if(empty($game['top_score']) && empty($game['down_score'])){
            $this->error("请输入分数比!");
        }
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
                        if(!empty($tz_result[$ke])){
                            $win_result[] = $tz_result[$ke];
                        }
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
                        
                        if(count($cate_id) > 1){
                            $win_result[] = implode(',', $cate_id);
                        }else{
                            $win_result[] = $cate_id[0];
                        }
                        unset($cate_id);
                    }
                }
                $win_result = implode(',', $win_result);           
            }
            db('fb_order_info')->where('order_info_id='.$order_info_all[$key]['order_info_id'])->update(array('win_result'=>$win_result,'game_status'=>1));
        }
        // die;
        $order_ids = db('fb_order_info')->where('game_id='.$id)->column('order_id');
        foreach ($order_ids as $key => $value) {
            $fb_order = db('fb_order')->where('order_id='.$order_ids[$key])->find();
            // 判断该笔订单先所有比赛的比赛状态(0:未结束|1:已结算)
            $game_status = db('fb_order_info')->where('order_id='.$order_ids[$key])->column('game_status');
            if(!in_array('0', $game_status)){
                // 如果所有比赛全部为 已结算 结算该笔订单
                // 获取该笔订单的所有 注
                $fb_order_group = db('fb_order_group')->where('order_id='.$order_ids[$key])->select();
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
                            // ->update(array('status'=>1,'win_money'=>$fb_order['multiple']*$odds*2,'win_status'=>1));
                            ->update(array('status'=>1,'win_money'=>$fb_order['multiple']*$odds*1.06*2,'win_status'=>1));
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
        $fb_order_data = db('fb_order no')
        ->field('no.*')
        ->join('fb_order_info noi','no.order_id=noi.order_id')
        ->where('noi.game_id='.$id.' and noi.game_status=1 and no.is_win=1')
        ->select();
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
                $res = db('account_details')->insert($arr);
                /**添加明细**/
                if($res > 0){
                    db('yh')->where('id='.$fb_order_data[$key]['user_id'])->setInc('balance',$win_money);
                    db('yh')->where('id='.$fb_order_data[$key]['user_id'])->setInc('amount_money',$win_money);
                }
            }    
        }
        
        // 操作成功 修改比赛状态
        $res = db('fb_game')->where('id='.$id)->setField('status',0);
        if($res > 0) {
            $this->success("操作成功");
        }
    }
     /** 

      * 根据时间戳返回星期几 

      * @param string $time 时间戳 

      * @return 星期几 

      */

    public function weekday($time) 
    { 
        if(is_numeric($time)) 
        { 
            $weekday = array('周日','周一','周二','周三','周四','周五','周六'); 
            return $weekday[date('w', $time)]; 
        } 
        return false; 
    } 

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function edit_field(){
        $data = $_REQUEST;
        $flag = db("fb_game")->update($data);
        $fb_game = db('fb_game')->where('id='.$data['id'])->find();
        if(!empty($fb_game['top_score']) && !empty($fb_game['down_score'])){
            $total_score = explode(':', $fb_game['down_score']);
            $total = $total_score[0]+$total_score[1];
            db('fb_game')->where('id='.$data['id'])->setField('total_score',$total);
        }
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }
}
