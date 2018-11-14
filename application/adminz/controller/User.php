<?php
namespace app\adminz\controller;

class User extends Base
{
    public function index(){
        $click = db('admin')->where('id=1')->value('click');
        $this->assign('click',$click);
        return view();
    }

    public function add($login_name = ''){
        if(IS_POST){
            $data = $_REQUEST;

            $map=array();
            $map['login_name']= $login_name;
            $users = db("user")->where($map)->find();
            if($users) $this->error('用户名已存在!');

            $data['login_pass'] = md5($data['login_pass']);
            $data['add_time'] = time();
            //删除非数据库字段
            // unset($data['pic_img']);
            $id = db("user")->insert($data,false,true);
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
            $arr = array();
            if(!empty($data['Mm'])){
                // $key = GetRandStr();// 4位秘钥
                $key = db('yh')->where('id='.$data['id'])->value('key');// 4位秘钥
                // $arr['key'] = $key;
                $arr['Mm'] = md5($data['Mm'].$key);
            }

            $arr['id'] = $data['id'];
            $arr['status'] = $data['status'];
            
            //删除非数据库字段
            // unset($data['pic_img']);
            $flag = db("yh")->update($arr);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $user = db('yh')->where("id=".$id)->find();
        foreach ($user as $key => $value) {
            $user['yhk'] = db('yhk')->where("yhid='".$user['yhid']."'")->find();
        }
        $user['Sjhm'] = empty($user['Sjhm'])?'':phone_decode($user['Sjhm']);
        $user['Sfby'] = empty($user['Sfby'])?'':phone_decode($user['Sfby']);
        
        $this->assign("user",$user);
        return view();
    }

    public function get_user_list(){
        $data = $_REQUEST;
        $map = '1=1';
        if(!empty($data['result']['yhid'])){
            $map .= ' and yhid like "%'.$data['result']['yhid'].'%"';
        }
        if(!empty($data['result']['Sjhm'])){
            $map .= ' and Sjhm like "%'.phone_encrypt($data['result']['Sjhm']).'%"';
        }
        if(!empty($data['result']['pid'])){
            $map .= ' and pid="'.db('yh')->where('Sjhm="'.phone_encrypt($data['result']['pid']).'"')->value('id').'"';
        }
        if(!empty($data['result']['add_time'])){
            $map .= ' and zcsj>='.strtotime($data['result']['add_time']);
        }
        if(!empty($data['result']['end_time'])){
            $map .= ' and zcsj<='.strtotime($data['result']['end_time']);
        }
        
        $count = db("yh")->where($map)->order('zcsj')->count();
        $list = db("yh")->where($map)->order('zcsj')->paginate(20,$count);
        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            // $item['role_name'] = db("Role")->where("role_id=".$item['role_id'])->value('role_name');
            $item['Sjhm'] = phone_decode($item['Sjhm']);
            if(!empty($item['Sfby'])){
                $item['Sfby'] = phone_decode($item['Sfby']);
            }
            
            return $item;
        });
        $this->assign("page",$page);
        $this->assign("count",$count);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/user_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function user_edit_field($id = 0){
        //模块化更新
        // $flag = model('user')->allowField(true)->save($_REQUEST,['id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['id'] = $id;
        $flag = db("yh")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function user_delete($id = 0){
        if($id==1)
            $this->eroor("无法删除超级管理员");
        $map = array();
        $map['id'] = $id;
        $flag = db('yh')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
