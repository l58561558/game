<?php
namespace app\adminz\controller;

class History extends Base
{
    /**
     * 列表页面 -- 大小和
     * @return [type] [description]
     */
    public function dxh($game_id=1){
        $count = db("dxh_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->count();
        $list = db("dxh_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->paginate(20,$count);
        // dump($list);die;
        if(!empty($list)){
            $list->each(function($item,$key){
                if(!empty($item['Kjjg'])){
                    $item['Kjjg'] = explode(',', $item['Kjjg']);
                    foreach ($item['Kjjg'] as $k => $v) {
                        $item['Kjjg'][$k] = db('codex_dxh')->where('id='.$item['Kjjg'][$k])->value('desc');
                    }
                    $item['Kjjg'] = implode(',', $item['Kjjg']);
                }
                $item['kjsjzt'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['kjsjzt'].'"')->value('code_name');
                return $item;
            });
        }

        //获取分页
        $page = $list->render();

        $this->assign('game_id',$game_id);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    /**
     * 列表页面 -- 骰子
     * @return [type] [description]
     */
    public function dice($game_id=2){
        $count = db("dice_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->count();
        $list = db("dice_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->paginate(20,$count);
        if(!empty($list)){
            $list->each(function($item,$key){
                $item['kjsjzt'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['kjsjzt'].'"')->value('code_name');
                return $item;
            });
        }
        // dump($list);
        //获取分页
        $page = $list->render();

        $this->assign('game_id',$game_id);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    /**
     * 列表页面 -- 牌九
     * @return [type] [description]
     */
    public function nine($game_id=3){
        $count = db("nine_kj")->order('add_time desc')->count();
        $list = db("nine_kj")->order('add_time desc')->paginate(20,$count);
        if(!empty($list)){
            $list->each(function($item,$key){
                $item['win_status'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['win_status'].'"')->value('code_name');
                if(!empty($item['win_result'])){
                    // if($item['win_result'] == '000'){
                    //     $item['result_status'] = 1;
                    // }else if($item['win_result'] == '111'){
                    //     $item['result_status'] = 4;
                    // }else if($item['win_result'] == '011' || $item['win_result'] == '101' || $item['win_result'] == '110'){
                    //     $item['result_status'] = 2;
                    // }else if($item['win_result'] == '001' || $item['win_result'] == '010' || $item['win_result'] == '100'){
                    //     $item['result_status'] = 3;
                    // }
                    // $item['result_status'] = db('code_nine')->where('code_info_id=10 and code='.$item['result_status'])->value('desc');
                    $num1 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 0,1))->value('desc');
                    $num2 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 1,1))->value('desc');
                    $num3 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 2,1))->value('desc');
                    $visit = '';
                    $nostril = '';
                    $surname = '';
                    if($num1 == 1){
                        $visit = '_win';
                    }
                    if($num2 == 1){
                        $nostril = '_win';
                    }
                    if($num3 == 1){
                        $surname = '_win';
                    }
                    $banker_left = db('codex_nine')->where('code_id='.$item['banker_left'])->value('code_info_id');
                    $banker_right = db('codex_nine')->where('code_id='.$item['banker_right'])->value('code_info_id');
                    $visit_left = db('codex_nine')->where('code_id='.$item['visit_left'])->value('code_info_id');
                    $visit_right = db('codex_nine')->where('code_id='.$item['visit_right'])->value('code_info_id');
                    $nostril_left = db('codex_nine')->where('code_id='.$item['nostril_left'])->value('code_info_id');
                    $nostril_right = db('codex_nine')->where('code_id='.$item['nostril_right'])->value('code_info_id');
                    $surname_left = db('codex_nine')->where('code_id='.$item['surname_left'])->value('code_info_id');
                    $surname_right = db('codex_nine')->where('code_id='.$item['surname_right'])->value('code_info_id');
                    $item['win_result'] = '庄家('.$banker_left.','.$banker_right.') | '.'上门('.$visit_left.','.$visit_right.')('.$num1.$item['visit'.$visit.'_money'].') | '.'天门('.$nostril_left.','.$nostril_right.')('.$num2.$item['nostril'.$nostril.'_money'].') | '.'下门('.$surname_left.','.$surname_right.')('.$num3.$item['surname'.$surname.'_money'].')';
                    $item['tz_money'] = db('order')->where('kjid="'.$item['kjid'].'" and win_result > 0')->sum('order_money');
                }
                return $item;
            });
        }
        // dump($list);
        //获取分页
        $page = $list->render();

        $this->assign('game_id',$game_id);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    /**
     * 列表页面 -- 大转盘
     * @return [type] [description]
     */
    public function dial($game_id=4){
        $count = db("dial_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->count();
        $list = db("dial_kj")->where('kjsjzt>=0 and game_id='.$game_id)->order('add_time desc')->paginate(20,$count);
        if(!empty($list)){
            $list->each(function($item,$key){
                $item['kjsjzt'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['kjsjzt'].'"')->value('code_name');
                if(!empty($item['Kjjg'])){
                    $item['win_res'] = explode(',', $item['Kjjg']);
                    for ($i=0; $i < count($item['win_res']); $i++) { 
                        if(!empty($item['win_res'][$i])){
                            $item['win_res'][$i] = db('code_dial')->where('code_id='.$item['win_res'][$i])->value('code_name');
                        }
                        
                    }
                    $item['Kjjg'] = implode(',', $item['win_res']);                    
                }

                return $item;
            });
        }
        // dump($list);
        //获取分页
        $page = $list->render();

        $this->assign('game_id',$game_id);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }

    /**
     * 列表页面 -- 百家乐
     * @return [type] [description]
     */
    public function bjl($game_id=5){
        $count = db("bjl_kj")->order('add_time desc')->count();
        $list = db("bjl_kj")->order('add_time desc')->paginate(20,$count);
        if(!empty($list)){
            $list->each(function($item,$key){
                $item['win_status'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['win_status'].'"')->value('code_name');
                if(!empty($item['win_result'])){
                    $win_result = explode(',', $item['win_result']);
                    foreach ($win_result as $key => $value) {
                        $result[] = db('bjl_code')->where('id='.$value)->value('desc');
                    }
                    $item['win_result'] = implode(',', $result); 
                }
                $item['tz_money'] = db('order')->where('kjid="'.$item['kjid'].'"')->sum('order_money');
                $item['win_money'] = db('order')->where('kjid="'.$item['kjid'].'"')->sum('win_money');
                return $item;
            });
        }
        // dump($list);
        //获取分页
        $page = $list->render();

        $this->assign('game_id',$game_id);
        $this->assign("page",$page);
        $this->assign("_list",$list);
        return view();
    }
    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_bjl_list($result){
        $map = '1=1';
        if(!empty($result['kjid'])){
            $map .= ' and kjid like "%'.$result['kjid'].'%"';
        }
        if(!empty($result['add_time'])){
            $map .= ' and add_time>='.strtotime($result['add_time']);
        }
        if(!empty($result['end_time'])){
            $map .= ' and add_time<='.strtotime($result['end_time']);
        }
        $count = db("bjl_kj")->where($map)->order('add_time desc')->count();
        $list = db("bjl_kj")->where($map)->order('add_time desc')->paginate(20,$count);

        $list->each(function($item,$key){
            $item['win_status'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['win_status'].'"')->value('code_name');
            if(!empty($item['win_result'])){
                $win_result = explode(',', $item['win_result']);
                foreach ($win_result as $key => $value) {
                    $result[] = db('bjl_code')->where('id='.$value)->value('desc');
                }
                $item['win_result'] = implode(',', $result); 
            }
            $item['tz_money'] = db('order')->where('kjid="'.$item['kjid'].'"')->sum('order_money');
            $item['win_money'] = db('order')->where('kjid="'.$item['kjid'].'"')->sum('win_money');
            return $item;
        });
        //获取分页
        $page = $list->render();
        //遍历数据

        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/bjl_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }
    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_nine_list($result){
        $map = '1=1';
        if(!empty($result['kjid'])){
            $map .= ' and kjid like "%'.$result['kjid'].'%"';
        }
        if(!empty($result['add_time'])){
            $map .= ' and add_time>='.strtotime($result['add_time']);
        }
        if(!empty($result['end_time'])){
            $map .= ' and add_time<='.strtotime($result['end_time']);
        }
        $count = db("nine_kj")->where($map)->order('add_time desc')->count();
        $list = db("nine_kj")->where($map)->order('add_time desc')->paginate(20,$count);

        $list->each(function($item,$key){
            $item['win_status'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['win_status'].'"')->value('code_name');
            if(!empty($item['win_result'])){
                // if($item['win_result'] == '000'){
                //     $item['result_status'] = 1;
                // }else if($item['win_result'] == '111'){
                //     $item['result_status'] = 4;
                // }else if($item['win_result'] == '011' || $item['win_result'] == '101' || $item['win_result'] == '110'){
                //     $item['result_status'] = 2;
                // }else if($item['win_result'] == '001' || $item['win_result'] == '010' || $item['win_result'] == '100'){
                //     $item['result_status'] = 3;
                // }
                // $item['result_status'] = db('code_nine')->where('code_info_id=10 and code='.$item['result_status'])->value('desc');
                $num1 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 0,1))->value('desc');
                $num2 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 1,1))->value('desc');
                $num3 = db('code_nine')->where('code_info_id=30 and code='.substr($item['win_result'], 2,1))->value('desc');
                $visit = '';
                $nostril = '';
                $surname = '';
                if($num1 == 1){
                    $visit = '_win';
                }
                if($num2 == 1){
                    $nostril = '_win';
                }
                if($num3 == 1){
                    $surname = '_win';
                }
                $banker_left = db('codex_nine')->where('code_id='.$item['banker_left'])->value('code_info_id');
                $banker_right = db('codex_nine')->where('code_id='.$item['banker_right'])->value('code_info_id');
                $visit_left = db('codex_nine')->where('code_id='.$item['visit_left'])->value('code_info_id');
                $visit_right = db('codex_nine')->where('code_id='.$item['visit_right'])->value('code_info_id');
                $nostril_left = db('codex_nine')->where('code_id='.$item['nostril_left'])->value('code_info_id');
                $nostril_right = db('codex_nine')->where('code_id='.$item['nostril_right'])->value('code_info_id');
                $surname_left = db('codex_nine')->where('code_id='.$item['surname_left'])->value('code_info_id');
                $surname_right = db('codex_nine')->where('code_id='.$item['surname_right'])->value('code_info_id');
                $item['win_result'] = '庄家('.$banker_left.','.$banker_right.') | '.'上门('.$visit_left.','.$visit_right.')('.$num1.$item['visit'.$visit.'_money'].') | '.'天门('.$nostril_left.','.$nostril_right.')('.$num2.$item['nostril'.$nostril.'_money'].') | '.'下门('.$surname_left.','.$surname_right.')('.$num3.$item['surname'.$surname.'_money'].')';
                $item['tz_money'] = db('order')->where('kjid="'.$result['kjid'].'"')->sum('order_money');
            }
            return $item;
        });
        //获取分页
        $page = $list->render();
        //遍历数据

        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/nine_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_history_list($game_id,$result){

        $map = 'game_id = '.$game_id;
        if(!empty($result['kjid'])){
            $map .= ' and kjid like "%'.$result['kjid'].'%"';
        }
        if(!empty($result['add_time'])){
            $map .= ' and add_time>='.strtotime($result['add_time']);
        }
        if(!empty($result['end_time'])){
            $map .= ' and add_time<='.strtotime($result['end_time']);
        }

        $db = strtolower(db('game_cate')->where('game_id='.$game_id)->value('game_code')).'_kj';

        $count = db($db)->where($map)->order('add_time desc')->count();
        $list = db($db)->where($map)->order('add_time desc')->paginate(20,$count);
        $list->each(function($item,$key){
            if($item['game_id'] == 1){
                if(!empty($item['Kjjg'])){
                    $item['Kjjg'] = explode(',', $item['Kjjg']);
                    foreach ($item['Kjjg'] as $k => $v) {
                        $item['Kjjg'][$k] = db('codex_dxh')->where('id='.$item['Kjjg'][$k])->value('desc');
                    }
                    $item['Kjjg'] = implode(',', $item['Kjjg']);
                }
                
            }else if($item['game_id'] == 4){
                if(!empty($item['Kjjg'])){
                    $item['win_res'] = explode(',', $item['Kjjg']);
                    for ($i=0; $i < count($item['win_res']); $i++) { 
                        if(!empty($item['win_res'][$i])){
                            $item['win_res'][$i] = db('code_dial')->where('code_id='.$item['win_res'][$i])->value('code_name');
                        }  
                    }
                    $item['Kjjg'] = implode(',', $item['win_res']);                     
                }
            }
            $item['kjsjzt'] = db('code_info')->where('code_info_id=21 and dxhzy="'.$item['kjsjzt'].'"')->value('code_name');
            return $item;
        });            


        //获取分页
        $page = $list->render();
        //遍历数据

        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/history_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    // /**
    //  * 编辑指定字段
    //  * @param  integer $id [description]
    //  * @return [type]      [description]
    //  */
    // public function edit_field($id = 0){
    //     //模块化更新
    //     // $flag = model('article')->allowField(true)->save($_REQUEST,['article_kjid'=>$kjid]);
    //     $data = $_REQUEST;
    //     //删除非数据库字段
    //     unset($data['id']);
    //     $data['kjid'] = $kjid;
    //     $flag = db("kj")->update($data);
    //     ($flag || $flag===0)  && $this->success("保存成功");
    //     $this->error("保存失败");
    // }

    // /**
    //  * 删除数据
    //  * @param  integer $kjid [description]
    //  * @return [type]      [description]
    //  */
    // public function delete($id = 0){
    //     $map = array();
    //     $map['kjid'] = $id;
    //     $flag = db('kj')->where($map)->delete();
    //     if($flag){
    //         $this->success("删除成功");
    //     }
    //     $this->error('删除失败');
    // }
}
