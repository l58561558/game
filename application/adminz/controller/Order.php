<?php
namespace app\adminz\controller;

class Order extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index(){
        $cate = db('game_cate')->select();

        $this->assign('cate',$cate);
        return view();
    }


    /**
     * 编辑记录
     * @param  integer $id [description]
     * @return [type]          [description]
     */
    public function edit($id = ''){
		
        // if(IS_POST){

        //     $data = $_REQUEST;

        //     //删除非数据库字段
        //     unset($data['__cfduid']);
        //     unset($data['PHPSESSID']);
        //     unset($data['Hm_lvt_24b7d5cc1b26f24f256b6869b069278e']);
        //     unset($data['cf_use_ob']);
        //     unset($data['Hm_lpvt_24b7d5cc1b26f24f256b6869b069278e']);

        //     $flag = db("order")->update($data);
        //     if($flag || $flag === 0){
        //         $this->success("保存成功");
        //     }
        //     $this->error("保存失败");
        // }
        $game_id = db('order')->where('Tzid="'.$id.'"')->value('game_id');
        $data = db('order_info')->where("order_id='".$id."'")->select();
        // dump($data);die;
        foreach ($data as $key => $value) {
            $user = db('yh')
                    ->alias('y')
                    ->field('y.radio')
                    ->join('order o','y.yhid = o.yhid','LEFT')
                    ->where('o.Tzid="'.$data[$key]['order_id'].'"')
                    ->find();
            if($game_id == 1){
                    
                    $data[$key]['odds'] = round(db('codex_dxh')->where('id='.$data[$key]['tz_result'])->value('odds')*$user['radio'],2);
                    $data[$key]['tz_result'] = db('codex_dxh')->where('id='.$data[$key]['tz_result'])->value('desc');  
                if(!empty($data[$key]['win_result'])){
                    $data[$key]['win_code'] = explode(',', $data[$key]['win_code']);
                    foreach ($data[$key]['win_code'] as $k => $v) {
                        $data[$key]['win_code'][$k] = db('codex_dxh')->where('id='.$data[$key]['win_code'][$k])->value('desc');
                    }
                    $data[$key]['win_code'] = implode(',', $data[$key]['win_code']);
                    $data[$key]['win_result'] = $data[$key]['win_code'].'('.$data[$key]['win_result'].')'; 
                    // $data[$key]['win_result'] = db('codex_dxh')->where('id='.substr($data[$key]['win_result'], 0, 1))->value('desc').'('.$data[$key]['win_code'].')';            
                }
            }
            if($game_id == 2){
                $code_dice = db('code_dice')->where('code_id='.$data[$key]['tz_result'])->find();
                $data[$key]['tz_result'] = $code_dice['code_name'];
                $data[$key]['odds'] = round($code_dice['odds']*$user['radio'],2);
                $str1 = substr($data[$key]['win_result'], 0, 1);
                $str2 = substr($data[$key]['win_result'], -1, 1);
                $str = $str1."(左) , ".$str2."(右)";
                $data[$key]['win_result'] = $str;               
            }
            if($game_id == 3){
                $data[$key]['odds'] = 2;
                $data[$key]['tz_result'] = db('code_nine')->where('code_info_id=20 and code='.$data[$key]['tz_result'])->value('desc');
                if(!empty($data[$key]['win_result'])){
                    $num1 = db('code_nine')->where('code_info_id=30 and code='.substr($data[$key]['win_result'], 0,1))->value('desc');
                    $num2 = db('code_nine')->where('code_info_id=30 and code='.substr($data[$key]['win_result'], 1,1))->value('desc');
                    $num3 = db('code_nine')->where('code_info_id=30 and code='.substr($data[$key]['win_result'], 2,1))->value('desc');
                    $data[$key]['win_result'] = '上门('.$num1.') | '.'天门('.$num2.') | '.'下门('.$num3.')';
                    $data[$key]['win_money'] = $data[$key]['win_money'];    
                }
            }
            if($game_id == 4){
                $code_dice = db('code_dial')->where('code_id='.$data[$key]['tz_result'])->find();
                $data[$key]['tz_result'] = $code_dice['code_name'];
                $data[$key]['odds'] = $code_dice['odds'];             
            }
            if($game_id == 5){
                $bjl_code = db('bjl_code')->where('id='.$data[$key]['tz_result'])->find();
                $data[$key]['tz_result'] = $bjl_code['desc'];
                $data[$key]['odds'] = $bjl_code['odds'];          
                if(!empty($data[$key]['win_result'])){
                    if(strlen($data[$key]['win_result']) == 1){
                        $data[$key]['win_result'] = db('bjl_code')->where('id='.$data[$key]['win_result'])->value('desc');
                    }else if(strlen($data[$key]['win_result']) > 1){
                        $data[$key]['win_result'] = explode(',', $data[$key]['win_result']);
                        foreach ($data[$key]['win_result'] as $k => $v) {
                            $data[$key]['win_result'][$k] = db('bjl_code')->where('id='.$data[$key]['win_result'][$k])->value('desc');
                        }
                        $data[$key]['win_result'] = implode(',', $data[$key]['win_result']);
                    }
                }   
            }

        }
        
        $this->assign("game_id",$game_id);
        $this->assign("data",$data);
        return view();
    }

    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_order_list(){
        $map = '1=1';

        $data = $_REQUEST;

        if(!empty($data['result']['cate'])){
            $map .= ' and game_id='.$data['result']['cate'];
        }
        if(!empty($data['result']['kjid'])){
            $map .= ' and kjid="'.$data['result']['kjid'].'"';
        }
        if(!empty($data['result']['add_time'])){
            $map .= ' and Tzzfsj>='.strtotime($data['result']['add_time']);
        }
        if(!empty($data['result']['end_time'])){
            $map .= ' and Tzzfsj<='.strtotime($data['result']['end_time']);
        }
        if(!empty($data['result']['yhid'])){
            $map .= ' and yhid="'.$data['result']['yhid'].'"';
        }
        $count = db("order")->where($map)->order('Tzzfsj desc')->count();
        $list = db("order")->where($map)->order('Tzzfsj desc')->paginate(20,$count);
        // dump($list);die;
        //获取分页
        $page = $list->render();
        //遍历数据
        if(!empty($list)){
            $list->each(function($item,$key){
                // $item['Tzjg'] = db('code_info')->where('code_info_id=22 and dxhzy='.$item['Tzjg'])->value('code_name');

                if($item['Tzcgbz']==2){
                    $item['cause'] = db('code_info')->where('code_info_id=28 and dxhzy='.$item['Tzcgbz'])->value('code_name');
                }

                $item['game_id'] = db('game_cate')->where('game_id='.$item['game_id'])->value('game_name');

                $item['Tzcgbz'] = db('code_info')->where('code_info_id=27 and dxhzy='.$item['Tzcgbz'])->value('code_name');

                $item['Tzzffs'] = db('code_info')->where('code_info_id=25 and dxhzy='.$item['Tzzffs'])->value('code_name');

                $item['TzScbz'] = db('code_info')->where('code_info_id=20 and dxhzy='.$item['TzScbz'])->value('code_name');

                // $item['win_result'] = db('code_info')->where('code_info_id=23 and dxhzy='.$item['win_result'])->value('code_name');

                return $item;
            });
        }
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/order_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function order_edit_field($id = 0){
        //模块化更新
        // $flag = model('article')->allowField(true)->save($_REQUEST,['article_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("order")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function order_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('order')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
