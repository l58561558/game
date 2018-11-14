<?php
namespace app\adminz\controller;

class Authority extends Base
{
    public function index(){
        return view();
    }

    public function add(){
        if(IS_POST){
            $data = $_REQUEST;

            //删除非数据库字段
            // unset($data['pic_img']);
            $data['is_enable'] = input("is_enable")?1:0;
            $authority_id = db("authority")->insert($data,false,true);
            if($authority_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        $authority_list = db("authority")->field('authority_id,name')->where("pid=0")->order('sort_order desc')->select();
        $this->assign('authority_list',$authority_list);
        return view();
    }

    public function edit($authority_id = 0){
        if(IS_POST){
            $data = $_REQUEST;
            //删除非数据库字段
            // unset($data['pic_img']);
            
            $data['is_enable'] = input("is_enable")?1:0;
            $flag = db("authority")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $authority = db('authority')->where("authority_id=".$authority_id)->find();
        $this->assign("authority",$authority);
        $authority_list = db("authority")->field('authority_id,name')->where("pid=0")->order('sort_order desc')->select();
        $this->assign('authority_list',$authority_list);
        return view();
    }

    public function get_authority_list(){
        $map = 'pid=0';
        $count = db("authority")->where($map)->count();
        $list = db("authority")->where($map)->paginate(30,$count);
        //遍历数据
        $list->each(function($item,$key){
            $item['child'] = db("authority")->where("pid=".$item['authority_id'])->select();
            return $item;
        });
        //获取分页
        $page = $list->render();
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/authority_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    public function authority_edit_field($id = 0){
        //模块化更新
        // $flag = model('authority')->allowField(true)->save($_REQUEST,['authority_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['authority_id'] = $id;
        $flag = db("authority")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function authority_delete($id = 0){
        $count = db('authority')->where('pid='.$id)->count();
        if($count){
            $this->error('删除失败,该节点下还有子节点');
        }

        $map = array();
        $map['authority_id'] = $id;
        $flag = db('authority')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
