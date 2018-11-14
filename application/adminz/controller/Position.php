<?php
namespace app\adminz\controller;

class Position extends Base
{
    public function index(){
        return view();
    }

    public function add(){
        if(IS_POST){
            $data = $_REQUEST;
            //删除非数据库字段
            // unset($data['pic_img']);
            $position_id = db("position")->insert($data,false,true);
            if($position_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        return view();
    }

    public function edit($position_id = 0){
        if(IS_POST){
            $data = $_REQUEST;
            //删除非数据库字段
            // unset($data['pic_img']);
            $flag = db("position")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $position = db('position')->where("position_id=".$position_id)->find();
        $this->assign("position",$position);
        return view();
    }

    public function get_position_list(){
        $count = db("position")->count();
        $list = db("position")->paginate(10,$count);
        //获取分页
        $page = $list->render();
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/position_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function position_edit_field($id = 0){
        //模块化更新
        // $flag = model('position')->allowField(true)->save($_REQUEST,['position_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['position_id'] = $id;
        $flag = db("position")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function position_delete($id = 0){
        $map = array();
        $map['position_id'] = $id;
        $flag = db('position')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
