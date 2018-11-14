<?php
namespace app\adminz\controller;

class ArticleCate extends Base
{
    public function index(){
        $cate_list = db("article_cate")->select();
        $list = $this->get_data($cate_list);
        $this->assign("_list",$list);
        return view();
    }

    public function add(){
        if(IS_POST){
            $file = request()->file('column_img');
            $data = $_REQUEST;
            //删除非数据库字段
            // unset($data['column_img']);
            unset($data['__cfduid']);
            unset($data['PHPSESSID']);
            unset($data['Hm_lvt_24b7d5cc1b26f24f256b6869b069278e']);
            unset($data['cf_use_ob']);
            unset($data['Hm_lpvt_24b7d5cc1b26f24f256b6869b069278e']);

            if($file){
                $info = $file->move(config('uploads_path.path').DS.'articlecate');
                if($info){
                    $data['column_img'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            // else{
            //     $this->error("请上传封面图片");
            // }           
            $data['add_time'] = time();
            $cate_id = db("article_cate")->insert($data,false,true);
            if($cate_id){
                $this->success("添加成功");
            }
            $this->error("添加失败");
        }
        $cate_list = db("article_cate")->where('is_show=1')->select();
        $cate_list = $this->get_data($cate_list);
        // dump($cate_list);die;
        $this->assign('cate_list',$cate_list);
        return view();
    }

    public function edit($cate_id = 0){
        if(IS_POST){
            $file = request()->file('column_img');
            $data = $_REQUEST;
            //删除非数据库字段
            unset($data['column_img']);
            unset($data['__cfduid']);
            unset($data['PHPSESSID']);
            unset($data['Hm_lvt_24b7d5cc1b26f24f256b6869b069278e']);
            unset($data['cf_use_ob']);
            unset($data['Hm_lpvt_24b7d5cc1b26f24f256b6869b069278e']);

            if($file){
                $info = $file->move(config('uploads_path.path').DS.'articlecate');
                if($info){
                    $data['column_img'] = $info->getSaveName();
                }else{
                    $this->error($file->getError());
                }
            }
            // else{
            //     $this->error("请上传封面图片");
            // }          
            $flag = db("article_cate")->update($data);
            if($flag || $flag === 0){
                $this->success("保存成功");
            }
            $this->error("保存失败");
        }
        $articlecate = db('article_cate')->where("cate_id=".$cate_id)->find();
        $parent = db('article_cate')->field('cate_id,cate_name')->where("cate_id=".$articlecate['parent_id'])->find();
        // dump($parent);die;
        $this->assign("parent",$parent);
        $this->assign("cate",$articlecate);
        $cate_list = db("article_cate")->where('is_show=1')->select();
        $cate_list = $this->get_data($cate_list);
        $this->assign('cate_list',$cate_list);
        return view();
    }
    public function get_articlecate_list(){
        $cate_list = db("article_cate")->select();
        $list = $this->get_data($cate_list);
        //获取分页
        // dump($list);die;
        $this->assign("_list",$list);
        $html = $this->fetch("tpl/articlecate_list");
        $this->ajaxReturn(['data'=>$html,'code'=>1]);
    }



    /*
     * 递归遍历
     * @param $data array
     * @param $id int
     * return array
     * */
    //四级分类查询
    public function get_data($data, $id=0){
        $list = array();
        foreach($data as $v) {
            if($v['parent_id'] == $id) {
                $v['child'] = $this->get_data($data, $v['cate_id']);
                if(empty($v['child'])) {
                    unset($v['child']);
                }
                array_push($list, $v);
            }
        }
        return $list;     
    }

    // public function get_articlecate_list(){
    //     $map = 'parent_id=0';
    //     $count = db("article_cate")->where($map)->count();
    //     $list = db("article_cate")->where($map)->paginate(30,$count);
    //     //遍历数据
    //     $list->each(function($item,$key){
    //         $item['child'] = db("article_cate")->where("parent_id=".$item['cate_id'])->select();            
    //         return $item;
    //     });
    //     //获取分页
    //     // dump($list);die;
    //     $page = $list->render();
    //     $this->assign("page",$page);
    //     $this->assign("_list",$list);
    //     $html = $this->fetch("tpl/articlecate_list");
    //     $this->ajaxReturn(['data'=>$html,'code'=>1]);
    // }

    public function articlecate_edit_field($id = 0){
        //模块化更新
        // $flag = model('articlecate')->allowField(true)->save($_REQUEST,['cate_id'=>$id]);
        $data = $_REQUEST;
        //删除非数据库字段
        unset($data['id']);
        $data['cate_id'] = $id;
        $flag = db("article_cate")->update($data);
        ($flag || $flag===0)  && $this->success("保存成功");
        $this->error("保存失败");
    }

    /**
     * 删除数据
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function articlecate_delete($id = 0){
        $count = db('article_cate')->where('parent_id='.$id)->count();
        if($count){
            $this->error('删除失败,该分类下还有子分类');
        }
        $articleCount = db("article")->where("cate_id=".$id)->count();
        if($articleCount){
            $this->error('删除失败,该分类下还有文章');
        }

        $map = array();
        $map['cate_id'] = $id;
        $flag = db('article_cate')->where($map)->delete();
        if($flag){
            $this->success("删除成功");
        }
        $this->error('删除失败');
    }
}
