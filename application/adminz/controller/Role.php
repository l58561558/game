<?php
namespace app\adminz\controller;

class Role extends Base
{
    public function index(){
        return view();
    }

    public function add(){
        if(IS_POST){
            $data = $_REQUEST;
            $data['is_enable'] = input("is_enable")?1:0;
            //删除非数据库字段
            // unset($data['pic_img']);
            $role_id = db("role")->insert($data,false,true);
            if($role_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        return view();
    }

    public function edit($role_id = 0){
        if(IS_POST){
            $data = $_REQUEST;
            $data['is_enable'] = input("is_enable")?1:0;
            //删除非数据库字段
            // unset($data['pic_img']);
            $flag = db("role")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $role = db('role')->where("role_id=".$role_id)->find();
        $this->assign("role",$role);
        return view();
    }

    public function get_role_list(){
        $count = db("role")->count();
        $list = db("role")->paginate(10,$count);
        //获取分页
        $page = $list->render();
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/role_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function role_edit_field($id = 0){
        //模块化更新
        // $flag = model('role')->allowField(true)->save($_REQUEST,['role_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['role_id'] = $id;
        $flag = db("role")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function role_delete($id = 0){
        $map = array();
        $map['role_id'] = $id;

        $adminCount = db("admin")->where($map)->count();
        if($adminCount){
            $this->error('删除失败,该角色下还有管理员');
        }

        $flag = db('role')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
