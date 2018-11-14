<?php
namespace app\adminz\controller;

class Tixian extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index(){
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

            //删除非数据库字段
            unset($data['__cfduid']);
            unset($data['PHPSESSID']);
            unset($data['Hm_lvt_24b7d5cc1b26f24f256b6869b069278e']);
            unset($data['cf_use_ob']);
            unset($data['Hm_lpvt_24b7d5cc1b26f24f256b6869b069278e']);

            $flag = db("tixian")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        
        $data = db('tix')->where("id=".$id)->find();
        $this->assign("data",$data);
        return view();
    }

    // 确认提现
    public function succeed($id,$yhid,$status=3){
        // $this->error('维护中');
        $tx = db('tix')->where('id='.$id)->find();
        if($tx['Txzt'] == 2 || $tx['Txzt'] == 4){
            $this->error('用户已取消提现');
        }
        $tix_money = db('tix')->where('id='.$id)->value('Txtxje');

        db('yh')->where("yhid='".$yhid."'")->setDec('amount_money',$tix_money);
        db('yh')->where("yhid='".$yhid."'")->setDec('freezing_amount',$tix_money);
        db('account_details')->where("Txqqid='".$tx['Txqqid']."'")->setField('present_status',$status);

        $arr['Txzt'] = $status;
        $arr['Txczsj'] = time();

        $tix_res = db('tix')->where('id='.$id)->update($arr);

        if($tix_res){
            $this->success("操作成功");
        }
        $this->error('操作失败');

    }

    // 拒绝提现
    public function refuse($id,$yhid,$status=4){
        // $this->error('维护中');
        $tx = db('tix')->where('id='.$id)->find();
        if($tx['Txzt'] == 2 || $tx['Txzt'] == 4){
            $this->error('用户已取消提现');
        }
        $tix_money = db('tix')->where('id='.$id)->value('Txtxje');

        db('yh')->where("yhid='".$yhid."'")->setInc('balance',$tix_money);
        db('yh')->where("yhid='".$yhid."'")->setDec('freezing_amount',$tix_money);
        db('account_details')->where("Txqqid='".$tx['Txqqid']."'")->setField('present_status',$status);

        $arr['Txzt'] = $status;
        $arr['Txczsj'] = time();

        $tix_res = db('tix')->where('id='.$id)->update($arr);

        if($tix_res){
            $this->success("操作成功");
        }
        $this->error('操作失败');

    }


    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_tixian_list(){
        $map = '';

        $data = $_REQUEST;

        if(!empty($data['id'])){
            $map .= 'id='.$data['id'];
        }

        $count = db("tix")->where($map)->order('Txsqsj desc')->count();
        $list = db("tix")->where($map)->order('Txsqsj desc')->paginate(20,$count);

        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            $item['status'] = $item['Txzt'];
            $item['Txzt'] = db('code_info')->where('code_info_id=32 and dxhzy='.$item['Txzt'])->value('code_name');
            $item['username'] = db('yhk')->where('yhid="'.$item['yhid'].'"')->value('username');
            $item['Yhkh'] = db('yhk')->where('yhid="'.$item['yhid'].'"')->value('Yhkh');
            return $item;
        });
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/tixian_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function tixian_edit_field($id = 0){
        //模块化更新
        // $flag = model('article')->allowField(true)->save($_REQUEST,['article_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("tixian")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function tixian_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('tixian')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
