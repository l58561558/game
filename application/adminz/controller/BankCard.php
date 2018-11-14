<?php
namespace app\adminz\controller;

class BankCard extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index(){
        return view();
    }


    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_bank_card_list(){
        $map = '';

        $data = $_REQUEST;

        if(!empty($data['id'])){
            $map .= 'id='.$data['id'];
        }

        $count = db("yhk")->where($map)->count();
        $list = db("yhk")->where($map)->paginate(20,$count);

        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            // $item['Txzt'] = db('code_info')->where('code_info_id=32 and dxhzy='.$item['Txzt'])->value('code_name');

            return $item;
        });
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/bank_card_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function bank_card_edit_field($id = 0){
        //模块化更新
        // $flag = model('article')->allowField(true)->save($_REQUEST,['article_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("yhk")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function bank_card_delete($id = 0){
        $map = array();
        $map['id'] = $id;
        $flag = db('yhk')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
