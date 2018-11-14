<?php
namespace app\adminz\controller;

class Contact extends Base
{
    /**
     * 列表页面
     * @return [type] [description]
     */
    public function index(){
    	$list = db("contact")->select();
        //遍历数据
        // dump($list);
        $this->assign('list',$list);
        return view();
    }



    /**
     * 编辑记录
     * @param  integer $article_id [description]
     * @return [type]          [description]
     */
    public function edit($article_id = 0){
        if(IS_POST){
            $file = request()->file('qr_cord');
            $wx_service = request()->file('wx_service');
            $data = $_REQUEST;
            //删除非数据库字段
            unset($data['qr_cord']);
            unset($data['wx_service']);
            
            if($file){
                $info = $file->move(config('uploads_path.path').DS.'contact');
                if($info){
                    $data['qr_cord'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }


            if($wx_service){
                $info = $wx_service->move(config('uploads_path.path').DS.'contact');
                if($info){
                    $data['wx_service'] = $info->getSaveName();
                }else{
                    $this->error($wx_service->getError());
                }
            }
            // $data['is_show'] = input("is_show")?1:0;
            // $data['is_hot'] = input("is_hot")?1:0;
            // dump($data);
            $flag = db("contact")->where('id=1')->update($data);
            // dump($flag);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
            
        }
        // $cate_list = db("article_cate")->where('is_show=1 and parent_id=0')->paginate(1000);
        //遍历数据
        // $cate_list->each(function($item,$key){
        //     $item['child'] = db("article_cate")->where("is_show=1 and parent_id=".$item['cate_id'])->select();
        //     return $item;
        // });
        // $this->assign('cate_list',$cate_list);
        // $qr_cord = db('contact')->where("id=1")->find();
        // $this->assign("list",$qr_cord);
        // return view();
    }

    /**
     * 获取数据
     * @param  integer $position_id [description]
     * @return [type]               [description]
     */
    public function get_contact_list($cate_id = 0){
        $map = '';
        if($cate_id){
            $map = "cate_id=".$cate_id;
        }
        $count = db("article")->where($map)->count();
        $list = db("article")->where($map)->paginate(10,$count);
        //获取分页
        $page = $list->render();
        //遍历数据
        $list->each(function($item,$key){
            $item['cate_name'] = db("article_cate")->where("cate_id=".$item['cate_id'])->value('cate_name');
            return $item;
        });
        $this->assign("page",$page);
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/article_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }

    /**
     * 编辑指定字段
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function article_edit_field($id = 0){
        //模块化更新
        // $flag = model('article')->allowField(true)->save($_REQUEST,['article_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['article_id'] = $id;
        $flag = db("article")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function article_delete($id = 0){
        $map = array();
        $map['article_id'] = $id;
        $flag = db('article')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
