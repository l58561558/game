<?php
namespace app\adminz\controller;

class Ads extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index($position_id=0){
        
    	$position = db("Position")->select();
        $this->assign('position_list',$position);
        if($position_id>0){
            $position_id = $position_id;
        }else{
            $position_id = '';
        }
        $this->assign('position_id',$position_id);
        return view();
    }

    /**
     * 插入数据
     */
    public function add(){
        if(IS_POST){
            $file = request()->file('pic_img');
            $data = $_REQUEST;
            //删除非数据库字段
            unset($data['pic_img']);

            if($file){
                $info = $file->move(config('uploads_path.path').DS.'ads');
                if($info){
                    $data['pic'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            // else{
            //     $this->error("请上传封面图片");
            // }
            $data['add_time'] = time();
            $ads_id = db("ads")->insert($data,false,true);
            if($ads_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        $position = db("Position")->select();
        $this->assign('position_list',$position);
        return view();
    }

    /**
     * 编辑记录
     * @param  integer $ads_id [description]
     * @return [type]          [description]
     */
    public function edit($ads_id = 0){
        if(IS_POST){
            $file = request()->file('pic_img');
            $data = $_REQUEST;
            //删除非数据库字段
            unset($data['pic_img']);

            if($file){
                $info = $file->move(config('uploads_path.path').DS.'ads');
                if($info){
                    $data['pic'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            $data['update_time'] = time();
            $flag = db("ads")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $position = db("Position")->select();
        $this->assign('position_list',$position);
        $ads = db('ads')->where("ads_id=".$ads_id)->find();
        $this->assign("ads",$ads);
        return view();
    }

    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_ads_list($position_id = 0){
        $map = '';
        if($position_id){
            $map = "position_id=".$position_id;
        }
        $count = db("Ads")->where($map)->count();
        $list = db("Ads")->where($map)->paginate(20,$count,['type'=> 'AdminBootstrap','var_page' =>'page','list_rows'=>15]);
        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            $item['position_name'] = db("Position")->where("position_id=".$item['position_id'])->value('position_name');
            return $item;
        });
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/ads_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function ads_edit_field($id = 0){
        //模块化更新
        // $flag = model('Ads')->allowField(true)->save($_REQUEST,['ads_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['ads_id'] = $id;
        $flag = db("Ads")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function ads_delete($id = 0){
        $map = array();
        $map['ads_id'] = $id;
        $flag = db('ads')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
