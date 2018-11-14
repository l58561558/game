<?php
namespace app\adminz\controller;
use think\Db;
class Nba extends Base
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
    public function get_nba_list()
    {
        $count = db("nba_game")->where('status>0')->order('end_time desc')->count();
        $list = db("nba_game")->where('status>0')->order('end_time desc')->paginate(20,$count);

        //获取分页
        $page = $list->render();
        //遍历数据
        if(!empty($list)){
            $list->each(function($item,$key){
                $item['home_team'] = db('nba_team')->where('team_id='.$item['home_team'])->value('team_name');
                $item['road_team'] = db('nba_team')->where('team_id='.$item['road_team'])->value('team_name');
                if(!empty($item['win_team_id'])){
                    $item['win_team'] = db('nba_team')->where('team_id='.$item['win_team_id'])->value('team_name');
                }
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
        $html = $this->fetch("nba/nba_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function add()
    {
        if(IS_POST){
            $data = $_REQUEST;
            // echo strtotime($data['end_time']);echo "<br>";
            $start = strtotime(date('Y-m-d',strtotime($data['end_time'])));
            $end = strtotime(date('Y-m-d',strtotime($data['end_time'])))+86400;
            $count = db('nba_game')->where('end_time>='.$start.' and end_time<='.$end)->count();
            
            // $game['game_no'] = 301+$count;
            $game['game_no'] = $data['game_no'];
            $game['week'] = $this->weekday(strtotime($data['end_time']));
            // $game['week'] = (int)date('w',strtotime($data['end_time']));
            $game['game_name'] = $data['game_name'];
            $game['end_time'] = strtotime($data['end_time']);
            $game['game_cate'] = 1;
            $game['home_team'] = $data['home_team'];
            $game['road_team'] = $data['road_team'];
            $game['let_score'] = $data['let_score'];
            $game['total_score'] = $data['total_score'];
            $game['add_time'] = time();
            unset($data['game_no']);
            unset($data['game_name']);
            unset($data['end_time']);
            unset($data['home_team']);
            unset($data['road_team']);
            unset($data['let_score']);
            unset($data['total_score']);
            $game_id = db('nba_game')->insert($game,false,true);
            if($game_id > 0){
                $cate = array();
                $array = array('win_road','win_home','let_score_win_road','let_score_win_home','total_small','total_big');
                foreach ($data as $key => $value) {
                    $cate['game_id'] = $game_id;
                    $cate['cate_name'] = db('nba_code')->where('code="'.$key.'"')->value('code_name');
                    $cate['cate_code'] = $key;
                    $cate['cate_odds'] = in_array($key, $array)?floor($value*1.085*100)/100:$value;
                    $game_cate[] = $cate;
                }
                $res = db('nba_game_cate')->insertAll($game_cate);
                if($res > 0){
                    $this->success('创建成功');
                }else{
                    $this->error('插入失败');
                }
            }
        }
        $team = db('nba_team')->select();
        $code = db("nba_code")->where('code_pid>0')->select();

        $this->assign('team',$team);
        $this->assign('code',$code);
        return view();
    }



    public function look($id)
    {
        $data = db('nba_game')->where("id=".$id)->find();
        $data['home_team_logo'] = db('nba_team')->where('team_id='.$data['home_team'])->value('logo');
        $data['road_team_logo'] = db('nba_team')->where('team_id='.$data['road_team'])->value('logo');
        $data['home_team'] = db('nba_team')->where('team_id='.$data['home_team'])->value('team_name');
        $data['road_team'] = db('nba_team')->where('team_id='.$data['road_team'])->value('team_name');
        $data['win_team'] = empty($data['win_team_id'])?$data['win_team_id']:db('nba_team')->where('team_id='.$data['win_team_id'])->value('team_name');
        $game_cate = db('nba_game_cate')->where('game_id='.$id)->select();
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
            $count = db('nba_game')->where('end_time>='.$start.' and end_time<='.$end)->count();

            // $data['game_no'] = date('d',$end_time)*100+$count+1;
            $game['game_no'] = $data['game_no'];
            $data['week'] = $this->weekday($end_time);

            if($data['home_score'] > $data['road_score']){
                $data['win_team_id'] = $data['home_team'];
            }
            if($data['home_score'] < $data['road_score']){
                $data['win_team_id'] = $data['road_team'];
            }
            $data['end_time'] = $end_time;
            $flag = db("nba_game")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $code = db("nba_game_cate")->where('game_id='.$id)->select();
        $this->assign('code',$code);

        $data = db('nba_game')->where("id=".$id)->find();
        $this->assign("data",$data);
        $team = db('nba_team')->where('team_id in ('.$data['home_team'].','.$data['road_team'].')')->select();
        $this->assign("team",$team);

        return view();
    }

    public function order_info($order_id)
    {
        $order = db('nba_order')->where('order_id='.$order_id)->order('add_time desc')->find();
        $data['order_money'] = $order['order_money'];
        $data['win_money'] = $order['win_money'];
        $data['order_no'] = $order['order_no'];
        $data['add_time'] = date('Y-m-d H:i:s' ,$order['add_time']);
        $order_info = db('nba_order_info')->where('order_id='.$order_id)->select();
        $data['game_num'] = count($order_info);
        // $data['chuan'] = db('nba_chuan')->where('chuan_id='.$order['chuan'])->value('chuan_name');
        $chuan = explode(',', $order['chuan']);
        $chuann = '';
        for ($i=0; $i < count($chuan); $i++) { 
            $chuann[] = db('nba_chuan')->where('chuan_id='.$chuan[$i])->value('chuan_name');
        }
        $data['chuan'] = implode(',', $chuann);
        $data['multiple'] = $order['multiple'];
        foreach ($order_info as $key => $value) {
            $game[$key] = db('nba_game')->where('id='.$order_info[$key]['game_id'])->find();
            $data['order_info'][$key]['week'] = $game[$key]['week'];
            $data['order_info'][$key]['game_no'] = $game[$key]['game_no'];
            $data['order_info'][$key]['home_team'] = db('nba_team')->where('team_id='.$game[$key]['home_team'])->value('team_name');
            $data['order_info'][$key]['road_team'] = db('nba_team')->where('team_id='.$game[$key]['road_team'])->value('team_name');
            $data['order_info'][$key]['home_score'] = $game[$key]['home_score']==0?'':$game[$key]['home_score'];
            $data['order_info'][$key]['road_score'] = $game[$key]['road_score']==0?'':$game[$key]['road_score'];
            $data['order_info'][$key]['win_result'] = '';
            $data['order_info'][$key]['win_game_result'] = '';
            $data['order_info'][$key]['status'] = $game[$key]['status'];
            if(strpos($order_info[$key]['tz_result'] , ',') === false){
                $data['order_info'][$key]['tz_result'][0] = db('nba_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$order_info[$key]['tz_result'])->find();
            }else{
                if(!empty($order_info[$key]['tz_result'])){
                    $tz_result = explode(',', $order_info[$key]['tz_result']);
                    foreach ($tz_result as $ke => $val) {
                        $tz_result[$ke] = db('nba_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$tz_result[$ke])->find();
                    }
                    $data['order_info'][$key]['tz_result'] = $tz_result;
                }  
            }  
            if(!empty($order_info[$key]['win_result'])){
                if(strpos($order_info[$key]['win_result'] , ',') === false){
                    $data['order_info'][$key]['win_result'][0] = db('nba_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$order_info[$key]['win_result'])->find();
                }else{
                    $win_result = explode(',', $order_info[$key]['win_result']);
                    foreach ($win_result as $ke => $val) {
                        $win_result[$ke] = db('nba_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$win_result[$ke])->find();
                    }
                    $data['order_info'][$key]['win_result'] = $win_result;                      
                }  
            }
            if(!empty($order_info[$key]['win_game_result'])){
                $win_game_result = explode(',', $order_info[$key]['win_game_result']);
                foreach ($win_game_result as $ke => $val) {
                    $win_game_result[$ke] = db('nba_game_cate')->field('cate_name,cate_odds,is_win')->where('cate_id='.$win_game_result[$ke])->find();
                }
                $data['order_info'][$key]['win_game_result'] = $win_game_result;  
            }
        }
        // dump($data);die;
        $this->assign("data",$data);
        return view();
    }

    // 订单
    public function nba_order()
    {
        return view();
    }
    public function get_nba_order()
    {
        $data = $_REQUEST;

        $count = db("nba_order")->order('add_time desc')->count();
        $list = db("nba_order")->order('add_time desc')->paginate(20,$count);

        $list->each(function($item,$key){
            $item['user_id'] = db('yh')->where('id='.$item['user_id'])->value('yhid');
            $chuan = explode(',', $item['chuan']);
            $chuann = '';
            for ($i=0; $i < count($chuan); $i++) { 
                $chuann[] = db('nba_chuan')->where('chuan_id='.$chuan[$i])->value('chuan_name');
            }
            $item['chuan'] = implode(',', $chuann);
            return $item;
        });
        $page = $list->render();

        $this->assign("page",$page);
        $this->assign("_list",$list);
        
        $html = $this->fetch("tpl/nba_order_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
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
    // 结算
    // 通过比赛ID查其所有的投注选项ID -> 在通过投注选项ID查询所有的订单 -> 结算
    public function nba_over($id)
    {
        $game = db('nba_game')->where('id='.$id)->find();
        if(empty($game['home_score']) && empty($game['road_score']) && empty($game['win_team_id'])){
            $this->error("请输入分数比和获胜队伍!");
        }
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
        $game_cate = db('nba_game_cate')->where('game_id='.$id)->select();
        foreach ($game_cate as $key => $value) {
            if($home_score > $road_score){
                $differ = $home_score - $road_score;
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_home"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_road"')->setField('is_win',2);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code like "%differ_road_%"')->setField('is_win',2);
                $differ_score = $this->rank($differ);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="differ_home_'.$differ_score.'"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and is_win=0 and cate_code like "differ_home_%"')->setField('is_win',2);
            }else{
                $differ = $road_score - $home_score;
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_home"')->setField('is_win',2);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="win_road"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code like "%differ_home_%"')->setField('is_win',2);
                $differ_score = $this->rank($differ);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="differ_road_'.$differ_score.'"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and is_win=0 and cate_code like "differ_road_%"')->setField('is_win',2);
            }
            if($home_let_score > $road_score){
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_home"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_road"')->setField('is_win',2);
            }else{
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_home"')->setField('is_win',2);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="let_score_win_road"')->setField('is_win',1);
            }
            if($total > $total_score){
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_big"')->setField('is_win',1);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_small"')->setField('is_win',2);
            }else{
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_big"')->setField('is_win',2);
                db('nba_game_cate')->where('game_id='.$id.' and cate_code="total_small"')->setField('is_win',1);
            }
        }
        // 获取这场比赛的所有中奖选项ID::cate_ids
        $cate_id_arr = db('nba_game_cate')->where('game_id='.$id.' and is_win=1')->column('cate_id');
        $cate_ids = implode(',', $cate_id_arr);
        db('nba_order_info')->where('game_id='.$id.' and game_status=0')->setField('win_game_result',$cate_ids);
        // 查询订单明细里所有有关于这场比赛数据 并更改他们的比赛状态和中奖彩果
        $order_info_all = db('nba_order_info')->where('game_id='.$id.' and game_status=0')->select();
        foreach ($order_info_all as $key => $value) {
            if(strpos(',', $order_info_all[$key]['tz_result']) === false){
                $win_result = $order_info_all[$key]['tz_result']; 
            }else{
                $tz_result = explode(',', $order_info_all[$key]['tz_result']);
                foreach ($tz_result as $ke => $val) {
                    if(in_array($tz_result[$ke], $cate_id_arr)){
                        $win_result[] = $tz_result[$ke];
                    }
                }
                $win_result = implode(',', $win_result);                
            }
            db('nba_order_info')->where('order_info_id='.$order_info_all[$key]['order_info_id'])->update(array('win_result'=>$win_result,'game_status'=>1));
        }

        $order_ids = db('nba_order_info')->where('game_id='.$id)->column('order_id');
        foreach ($order_ids as $key => $value) {
            $nba_order_info = db('nba_order_info')->where('order_id='.$order_ids[$key])->select();
            $nba_order = db('nba_order')->where('order_id='.$order_ids[$key])->find();
            foreach ($nba_order_info as $ke => $val) {
                $game_status[] = $nba_order_info[$ke]['game_status'];
                if(!in_array('0', $game_status)){
                    $nba_order_group = db('nba_order_group')->where('order_id='.$order_ids[$key])->select();
                    foreach ($nba_order_group as $k => $v) {
                        if(strpos($nba_order_group[$k]['group_res'], ',') === false){
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
                }
            }
            $game_status = db('nba_order_info')->where('order_id='.$order_ids[$key])->column('game_status');
            if(!in_array('0', $game_status)){
                $win_money = db('nba_order_group')->where('order_id='.$order_ids[$key])->sum('win_money');
                if($win_money > 0){
                    db('nba_order')->where('order_id='.$order_ids[$key])->update(array('win_money'=>$win_money,'is_win'=>1));
                }else{
                    db('nba_order')->where('order_id='.$order_ids[$key])->update(array('is_win'=>2));
                }                        
            }
        }
        $nba_order_data = db('nba_order no')
        ->field('no.*')
        ->join('nba_order_info noi','no.order_id=noi.order_id')
        ->where('noi.game_id='.$id.' and noi.game_status=1 and no.is_win=1')
        ->select();
        // dump($nba_order_data);die;
        if(!empty($nba_order_data)){
            foreach ($nba_order_data as $key => $value) {
                $win_money = db('nba_order')->where('order_id='.$nba_order_data[$key]['order_id'])->value('win_money');

                $arr['yhid'] = db('yh')->where('id='.$nba_order_data[$key]['user_id'])->value('yhid');
                $arr['Jylx'] = 4;
                $arr['jyje'] = $win_money;
                $arr['new_money'] = $win_money+db('yh')->where('id="'.$nba_order_data[$key]['user_id'].'"')->value('balance');
                $arr['Jysj'] = time();
                $arr['Srhzc'] = 1;
                $res = db('account_details')->insert($arr);
                /**添加明细**/
                if($res > 0){
                    db('yh')->where('id='.$nba_order_data[$key]['user_id'])->setInc('balance',$win_money);
                    db('yh')->where('id='.$nba_order_data[$key]['user_id'])->setInc('amount_money',$win_money);
                }
            }    
        }
        
        // 操作成功 修改比赛状态
        $res = db('nba_game')->where('id='.$id)->setField('status',0);
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
            $weekday = array('星期日','星期一','星期二','星期三','星期四','星期五','星期六'); 
            return $weekday[date('w', $time)]; 
        } 
        return false; 
    } 

    public function team()
    {
        $team = db('nba_team')->select();

        $this->assign('data',$team);
        return view();
    }

    public function add_team()
    {
        if(IS_POST){
            $data = $_REQUEST;
            $file = request()->file('logo');
            unset($data['logo']);
            if($file){
                $info = $file->move(config('uploads_path.team'));
                if($info){
                    $data['logo'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            $res = db('nba_team')->insert($data);
            if($res > 0){
                $this->success('添加成功');
            }
            $this->error('添加失败');
        }

        return view();
    }

    public function edit_team($team_id)
    {
        if(IS_POST){
            $data = $_REQUEST;
            $file = request()->file('logo');
            unset($data['logo']);
            if($file){
                $info = $file->move(config('uploads_path.path').DS.'team',false);
                if($info){
                    $data['logo'] = '__UPLOADS__/team/'.$info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            $res = db('nba_team')->where('team_id',$team_id)->update($data);
            if($res > 0){
                $this->success('添加成功');
            }
            $this->error('添加失败');
        }

        $list = db('nba_team')->where('team_id',$team_id)->find();
        $this->assign('data',$list);
        return view();
    }

    public function drop_team($team_id)
    {
        $res = db('nba_team')->where('team_id',$team_id)->delete();
        if($res > 0){
            $this->success("操作成功");
        }
        $this->error("操作失败");
    }
}
