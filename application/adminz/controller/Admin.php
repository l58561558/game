<?php
namespace app\adminz\controller;

class Admin extends Base
{
    public function index(){
        return view();
    }

    public function root($id){
        $role_id = db('admin')->where('id='.$id)->value('role_id');
        $list = db('role_auth')->where('role_id='.$role_id)->select();
        // dump($list);die;

        $this->assign('role_id',$role_id);
        $this->assign('_list',$list);
        return view();
    }

    public function add_root($role_id = ''){
        if(IS_POST){
            $data = $_REQUEST;

            $node_data = db('node')->field('node_name,authority_id,authority_code')->where('node_id='.$data['node_id'])->find();
            
            $map=array();
            $map['role_id']= $data['role_id'];
            $map['authority_code']= $node_data['authority_code'];
            $role_auth = db("role_auth")->where($map)->find();
            if($role_auth) $this->error('权限已存在!');

            $role_data['role_id'] = $data['role_id'];
            $role_data['authority_name'] = $node_data['node_name'];
            $role_data['authority_id'] = $node_data['authority_id'];
            $role_data['authority_code'] = $node_data['authority_code'];
            $role_data['authority_pid'] = 0;
            $role_data['sort_order'] = 50;
            $role_data['is_enable'] = 1;
            //删除非数据库字段
            // unset($data['pic_img']);
            $id = db("role_auth")->insert($role_data,false,true);
            if($id){
                $this->success("添加成功",'Admin/index');
            }
            $this->error("添加失败");
        }
        $root_list = db("node")->where('pid=0 and is_menu=1')->select();
        // dump($root_list);die;

        $this->assign('role_id',$role_id);
        $this->assign('root_list',$root_list);
        return view();
    }

    public function root_delete($role_node_id = 0){
        $map = array();
        $map['role_node_id'] = $role_node_id;
        $flag = db('role_auth')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }

    public function root_edit_field($id = 0){
        //模块化更新
        // $flag = model('admin')->allowField(true)->save($_REQUEST,['id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['role_node_id'] = $id;
        $flag = db("role_auth")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    public function add($login_name = ''){
        if(IS_POST){
            $data = $_REQUEST;

            $map=array();
            $map['login_name']= $login_name;
            $admins = db("Admin")->where($map)->find();
            if($admins) $this->error('用户名已存在!');

            $data['login_pass'] = md5($data['login_pass']);
            $data['add_time'] = time();
            //删除非数据库字段
            // unset($data['pic_img']);
            $id = db("admin")->insert($data,false,true);
            if($id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        $role_list = db("role")->select();
        $this->assign('role_list',$role_list);
        return view();
    }
    
    public function edit($id = 0){
        if(IS_POST){
            $data = $_REQUEST;

            $map=array();
            $map = "login_name='".$data['login_name']."' and id!=".$id;
            $admins = db("Admin")->where($map)->find();
            if($admins) $this->error('用户名已存在!');

            if($data['login_pass']){
                $data['login_pass'] = md5($data['login_pass']);
            }else{
                unset($data['login_pass']);
            }
            //删除非数据库字段
            // unset($data['pic_img']);
            $flag = db("admin")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $role_list = db("role")->select();
        $this->assign('role_list',$role_list);
        $admin = db('admin')->where("id=".$id)->find();
        $this->assign("admin",$admin);
        return view();
    }

    public function get_admin_list(){
        $count = db("admin")->count();
        $list = db("admin")->paginate(10,$count);
        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            $item['role_name'] = db("Role")->where("role_id=".$item['role_id'])->value('role_name');
            return $item;
        });
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/admin_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function admin_edit_field($id = 0){
        //模块化更新
        // $flag = model('admin')->allowField(true)->save($_REQUEST,['id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("admin")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function admin_delete($id = 0){
        if($id==1)
            $this->eroor("无法删除超级管理员");
        $map = array();
        $map['id'] = $id;
        $flag = db('admin')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }

    // 上分
    public function add_money()
    {
        if(IS_POST){
            $data = $_REQUEST;

            $balance = db('yh')->where('yhid="'.$data['yhid'].'"')->value('balance');
            $arr['yhid'] = $data['yhid'];
            $arr['Jylx'] = 7;
            $arr['jyje'] = $data['price'];
            $arr['new_money'] = $data['price']+$balance;
            $arr['Srhzc'] = 1;
            $arr['Jysj'] = time();
            $arr['Zfywc'] = 1; // 1.已完成|2.未完成，Yjlx=1的情况
            $id = db("account_details")->insert($arr);
            db('yh')->where('yhid="'.$data['yhid'].'"')->setInc('balance',$data['price']);
            db('yh')->where('yhid="'.$data['yhid'].'"')->setInc('amount_money',$data['price']);
            if($id>0){
                $this->success('上分成功');
                // echo json_encode(['msg'=>'上分成功','code'=>1,'success'=>true]);
                // exit;
            }else{
                $this->error('上分失败');
            }          
        }

        return view();
    } 
}
