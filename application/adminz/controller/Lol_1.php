<?php
namespace app\adminz\controller;

class Lol extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index($status=0){
        switch ($status) {
            case 0:
                $where = '1=1';
                break;
            case 1:
                $where = 'status < 3';
                break;
            case 2:
                $where = 'status = 3';
                break;
        }

        $list = db('lol_game')->where('pid<0 and '.$where)->select();

        $this->assign('_list',$list);
        $this->assign('status',$status);
        return view();
    }

    public function add()
    {
        if(IS_POST){
            $data = $_REQUEST;

            $game_cate = array();

            $game_cate['team1_odds'] = $data['team1_odds'];
            $team2_odds = $data['team1_odds']/((20/19*$data['team1_odds'])-1);
            $game_cate['team2_odds'] = floor($team2_odds*100)/100;
            $game_cate['team1_odds_old'] = $game_cate['team1_odds'];
            $game_cate['team2_odds_old'] = $game_cate['team2_odds'];
            unset($data['team1_odds']);

            $data['start_time'] = strtotime($data['start_time']);
            $data['add_time'] = time();
            $data['pid'] = -1;

            $game_id = db('lol_game')->insert($data,false,true);
            
            $game_cate['game_id'] = $game_id;
            $game_cate['name'] = '胜负';
            $game_cate['team1_money'] = $game_cate['team2_odds']*10000;
            $game_cate['team2_money'] = $game_cate['team1_odds']*10000;
            $game_cate['status'] = 1;

            $res = db('lol_cate')->insert($game_cate,false,true);

            if($res > 0) {
                $this->success("保存成功");
            }
        }
        return view();
    }

    public function add_tz_content($id)
    {
        if(IS_POST){
            $data = $_REQUEST;
            $team2_odds = $data['team1_odds']/((20/19*$data['team1_odds'])-1);
            $data['team2_odds'] = floor($team2_odds*100)/100;

            $data['team1_odds_old'] = $data['team1_odds'];
            $data['team2_odds_old'] = $data['team2_odds'];
            
            $data['team1_money'] = $data['team2_odds']*10000;
            $data['team2_money'] = $data['team1_odds']*10000;
            unset($data['id']);
            $res = db('lol_cate')->insert($data,false,true);
            if($res > 0) {
                $this->success("添加成功");
            }
        }
        $data = db('lol_game')->where("id=".$id)->find();
        $this->assign("data",$data);
        return view();
    }

    public function look($id)
    {
        $data = db('lol_game')->where("id=".$id)->find();
        $game_cate = db('lol_cate')->where('game_id='.$id)->select();
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
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);

            $flag = db("lol_game")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $data = db('lol_game')->where("id=".$id)->find();
        $this->assign("data",$data);
        return view();
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function lol_edit_field($id){
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("lol_game")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function lol_details_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('lol_game')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function lol_tz_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('lol_cate')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }

    // 订单
    public function get_lol_order()
    {
        $map = '1=1';

        $data = $_REQUEST;

        $count = db("lol_order")->where($map)->order('add_time desc')->count();
        $list = db("lol_order")->where($map)->order('add_time desc')->paginate(20,$count);

        $page = $list->render();

        $list->each(function($item,$key){
            $item['user_id'] = db('yh')->where('id='.$item['user_id'])->value('yhid');
            
            $item['tz_result'] = db('lol_game')
            ->where('id='.db('lol_cate')->where('id='.$item['cid'])->value('game_id'))
            ->value('team'.$item['tz_result']);

            return $item;
        });

        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/lol_order_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function lol_order()
    {
        return view();
    }

    // 修改开奖结果
    public function edit_win_res()
    {
        $data = $_REQUEST;
        $res = db("lol_cate")->update($data);
        if($res > 0) {
            $this->success("保存成功");
        }else{
            $this->error("保存失败");
        }
    }
    // 结算
    // 通过比赛ID查其所有的投注选项ID -> 在通过投注选项ID查询所有的订单 -> 结算
    public function lol_over($id)
    {
        $game_cate = db('lol_cate')->where('game_id='.$id)->select();
        foreach ($game_cate as $key => $value) {
            if(empty($game_cate[$key]['win_res'])){
                $this->error("请输入比赛结果!");
            }
            $order = db('lol_order')->where('cid='.$game_cate[$key]['id'])->select();
            foreach ($order as $k => $val) {
                if($order[$k]['tz_result'] == $game_cate[$key]['win_res']) {
                    dump($order[$k]['id']);
                    $res = db('lol_order')
                    ->where('id='.$order[$k]['id'])
                    ->update(array('is_win'=>1,'win_result'=>$game_cate[$key]['win_res'],'win_money'=>$order[$k]['tz_odds']*$order[$k]['tz_money']));
                    
                    if($res == 0) {
                        $this->error("操作失败");
                    }else{
                    

                    $arr[$k]['yhid'] = db('yh')->where('id='.$order[$k]['user_id'])->value('yhid');
                    $arr[$k]['Jylx'] = 4;
                    $arr[$k]['jyje'] = $order[$k]['tz_odds']*$order[$k]['tz_money'];
                    $arr[$k]['new_money'] = $order[$k]['tz_odds']*$order[$k]['tz_money']+db('yh')->where('id="'.$order[$k]['user_id'].'"')->value('balance');
                    $arr[$k]['Jysj'] = time();
                    $arr[$k]['Srhzc'] = 1;
                    $res = db('account_details')->insert($arr[$k]);
                    /**添加明细**/
                    if($res > 0){
                        db('yh')->where('id='.$order[$k]['user_id'])->setInc('balance',$order[$k]['tz_odds']*$order[$k]['tz_money']);
                        db('yh')->where('id='.$order[$k]['user_id'])->setInc('amount_money',$order[$k]['tz_odds']*$order[$k]['tz_money']);
                    }
                    


                    }
                }else{
                    $res = db('lol_order')
                    ->where('id='.$order[$k]['id'])
                    ->update(array('is_win'=>2,'win_result'=>$game_cate[$key]['win_res']));
                }   
            }
        }
        // 操作成功 修改比赛状态
        $res = db('lol_game')->where('id='.$id)->setField('status',3);
        if($res > 0) {
            $this->success("操作成功");
        }
    }





    // 冠军竞猜
    public function cham_contest()
    {
        $data = db('lol_team')->select();

        $this->assign('_list',$data);
        return view();
    }
    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function cham_edit_field($id){
        $data = $_REQUEST;
        //删除非数据库字段
        $flag = db("lol_team")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }
    /**
     * 编辑记录
     * @param  integer $id [description]
     * @return [type]          [description]
     */
    public function cham_contest_edit($id = 0){
        if(IS_POST){
            $data = $_REQUEST;

            $flag = db("lol_team")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $data = db('lol_team')->where("id=".$id)->find();
        $this->assign("data",$data);
        return view();
    }

    public function champion_order()
    {
        $list = db('lol_team_order')->order('add_time desc')->select();
        foreach ($list as $key => $value) {
            $list[$key]['user_id'] = db('yh')->where('id='.$list[$key]['user_id'])->value('yhid');
            $list[$key]['tz_result'] = db('lol_team')->where('id='.$list[$key]['tz_result'])->value('team_name');
        }
        $this->assign("_list",$list);
        return view();
    }
    // 结算
    public function champion_over()
    {   
        $win_team = db('lol_team')->where('status=1')->find();
        $list = db('lol_team_order')->select();
        foreach ($list as $key => $value) {
            if($list[$key]['tz_result'] == $win_team['id']) {
                $win_money = $list[$key]['tz_odds']*$list[$key]['tz_money'];
                db('lol_team_order')->where('id='.$list[$key]['id'])->update(array('win_money'=>$win_money,'is_win'=>1));
                db('yh')->where('id='.$list[$key]['user_id'])->setInc('balance',$win_money);
                db('yh')->where('id='.$list[$key]['user_id'])->setInc('amount_money',$win_money);
            }else{
                db('lol_team_order')->where('id='.$list[$key]['id'])->setField('is_win',2);
            }
        }
        $res = db('lol_team')->where('1=1')->update(array('win_team'=>$win_team['team_name']));
        if($res > 0) {
            $this->success("结算成功");
        }else{
            $this->error();
        }
        
    }
}
