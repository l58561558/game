<?php
namespace app\adminz\controller;

class Node extends Base
{
    public function index(){
        return view();
    }

    public function add(){
        if(IS_POST){
            $data = $_REQUEST;

            //删除非数据库字段
            // unset($data['pic_img']);
            $data['authority_code'] = db("authority")->where("authority_id=".$data['authority_id'])->value("code");
            $data['is_menu'] = input("is_menu")?1:0;
            $data['url'] = strtolower($data['controller']."/".$data['action']);
            $node_id = db("node")->insert($data,false,true);
            if($node_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        $node_list = db("node")->where('pid=0')->select();
        $this->assign('node_list',$node_list);
        $authority_list = db("authority")->field('authority_id,name')->where("pid=0")->order('sort_order desc')->select();
        foreach ($authority_list as $key => $value) {
            $authority_list[$key]['child'] = db("authority")->field('authority_id,name')->where("pid=".$value['authority_id'])->order('sort_order desc')->select();
        }
        $this->assign('authority_list',$authority_list);
        return view();
    }

    public function edit($node_id = 0){
        if(IS_POST){
            $data = $_REQUEST;
            //删除非数据库字段
            // unset($data['pic_img']);
            
            $data['authority_code'] = db("authority")->where("authority_id=".$data['authority_id'])->value("code");
            $data['is_menu'] = input("is_menu")?1:0;
            $data['url'] = strtolower($data['controller']."/".$data['action']);
            $flag = db("node")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $node = db('node')->where("node_id=".$node_id)->find();
        $this->assign("node",$node);
        $node_list = db("node")->where('pid=0')->select();
        $this->assign('node_list',$node_list);
        $authority_list = db("authority")->field('authority_id,name')->where("pid=0")->order('sort_order desc')->select();
        foreach ($authority_list as $key => $value) {
            $authority_list[$key]['child'] = db("authority")->field('authority_id,name')->where("pid=".$value['authority_id'])->order('sort_order desc')->select();
        }
        $this->assign('authority_list',$authority_list);
        return view();
    }

    public function get_node_list(){
        $map = 'pid=0';
        $count = db("node")->where($map)->count();
        $list = db("node")->where($map)->paginate(30,$count);
        //遍历数据
        $list->each(function($item,$key){
            $item['child'] = db("node")->where("pid=".$item['node_id'])->select();
            return $item;
        });
        //获取分页
        $page = $list->render();
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/node_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function node_edit_field($id = 0){
        //模块化更新
        // $flag = model('node')->allowField(true)->save($_REQUEST,['node_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['node_id'] = $id;
        $flag = db("node")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function node_delete($id = 0){
        $count = db('node')->where('pid='.$id)->count();
        if($count){
            $this->error('删除失败,该节点下还有子节点');
        }

        $map = array();
        $map['node_id'] = $id;
        $flag = db('node')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
