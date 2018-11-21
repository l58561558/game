//删除数据
function delete_data(_this){
    var id = $(_this).data("id");
    var url = $(_this).data("url");
    $.post(url,{id:id},function(data){
        if(data.code==1){
            layer.msg(data.msg,function(){
                getData();
            });
            return ;
        }
        layer.msg(data.msg);
    },'json');
}

function win_delete(_this){
    setTimeout(function(){
        $(_this).popover('toggle');
    },100);
}

$(function(){
    $("body").on("click",'ul.nav-tabs li',function(){
        var id = $(this).data("id");
        $("#form_submit .box-body").hide();
        $("#"+id).show();

        $("ul.nav-tabs li").removeClass("active");
        $(this).addClass("active");
    });
    //清除缓存
    $("body").on("click",'.btn-cache',function(){ 
        var curWwwPath=window.document.location.href; 
        var pathName=window.document.location.pathname;
        var pos=curWwwPath.indexOf(pathName);
        var localhostPaht=curWwwPath.substring(0,pos); 
        $.post(localhostPaht+'/adminz/base/clear_temp',{flag:true},function(res){
           layer.msg('清除成功!');
        },'json');
    });
    //多级列表
    $("body").on("click",'i.treegrid',function(){
        var id = $(this).data("nodeid");
        if($(this).hasClass('fa-caret-down')){
            $(this).removeClass('fa-caret-down').addClass('fa-caret-right');
            $(".treegrid-parent-"+id).hide();
        }else{
            $(this).removeClass('fa-caret-right').addClass('fa-caret-down');
            $(".treegrid-parent-"+id).show();
        }
    });
    //处理表格全选
    $("input[name='table-check']").click(function(){
        if($(this).is(':checked')==true){
            $("input[name='table-check-list']").prop("checked",true);
        }else{
            $("input[name='table-check-list']").prop("checked",false);
        }
    });
    $("input[name='table-check-list']").click(function(){
        if($("input[name='table-check-list']:checked").length==$("input[name='table-check-list']").length){
            $("input[name='table-check']").prop("checked",true);
        }else{
            $("input[name='table-check']").prop("checked",false);
        }
    });
    //end

    //表单提交和验证
    $("#form_submit").Validform({
        showAllError:true,
        tiptype:3,
        ajaxPost:false,
        ignoreHidden:false,
        callback:function(data){
            var nf = tips.load("正在执行操作...");
            $("#form_submit").find("button[type='submit']").attr('disabled','disabled');
            var options = {
                success: function (res) {
                    tips.close(nf);
                    if(res.code == 1){
                        //返回列表页面
                        if($('#jump_list').is(':checked')){
                            // alert(123)
                            var return_url = $('#jump_list').data("url");
                            layer.msg(res.msg+",正在跳转...",function(){
                                location.href = return_url;
                            });
                        }else{
                            layer.msg(res.msg+",正在重新载入...",function(){
                                window.location.reload();
                            });
                        }
                    }else{
                        $("#form_submit").find("button[type='submit']").attr('disabled',false);
                        layer.msg(res.msg);
                    }
                }
            };
            $("#form_submit").ajaxSubmit(options);
            return false;
        }
    });

    //删除数据处理
    $("a[name='delete']").popover({
        html:true,
        trigger: 'focus',
        container: 'body',
        placement: 'left',
    });

    $("a[name='delete']").on('shown.bs.popover', function(){
        $("button[name='delete']").on('click',function(){
            var id = $(this).data("id");
            var url = $(this).data("url");
            $.post(url,{id:id},function(res){
                if(res.code==1){
                    layer.msg(res.msg,function(){
                        location.reload();
                    });
                    return ;
                }
                layer.msg(res.msg);
            },'json');
        });
    });
    //end
    
    //列表修改排序
    $("body").on('blur','input.inputs',function(){
        var _this = $(this);
        var name = _this.attr("name");
        var align = "center";
        var id = $(this).data("id");
        if(id==undefined || id==null || id==""){
            return false;
        }
        var val = $(this).val();
        var url = $(this).data('url');

        var nf = tips.load("正在执行操作...",align);
        $("input.inputs").attr("disabled",'disabled');
        var obj = {};
        obj['id'] = id;
        obj[name] = val;
        $.post(url,obj,function(res){
            tips.close(nf);
            if(res.code == 1){
                layer.msg(res.msg);
            }else{
                layer.msg(res.msg,1000,align);
            }
            setTimeout(function(){
                $("input.inputs").attr("disabled",false);
            },900);
        },'json');
    });
    //end

    //列表修改状态
    $("body").on('click','a.btn-field',function(){
        var _this = $(this);
        var name = _this.attr("name");
        var align = "center";
        var _this = $(this);
        var id = $(this).data("id");
        var status = $(this).attr('data-status');
        var url = $(this).data('url');
        var obj = {};
        obj['id'] = id;
        obj[name] = status;
        $.post(url,obj,function(res){
            if(res.code == 1){
                if(status==1){
                    _this.find("i").removeClass('fa-close').addClass('fa-check');
                    _this.attr('data-status',0);
                }else{
                    _this.find("i").removeClass('fa-check').addClass('fa-close');
                    _this.attr('data-status',1);
                }
            }else{
                layer.msg(res.msg,1000,align);
            }
        },'json');
    });
    //end
    
    //列表页面预览图片和内容
    $("img[name='img'],a[name='content']").popover({
        html:true,
        trigger: 'hover',
        container: 'body',
        placement: 'auto',
    });

    $("a[name='img']").popover({ 
        html:true,
        trigger: 'focus',
        container: 'body',
        placement: 'auto',
    });
    //end
});

/**
 * 省市联动
 * @param  {[type]} _this [description]
 * @param  {[type]} type  [description]
 * @param  {[type]} dom   [description]
 * @return {[type]}       [description]
 */
function selectChange(_this,type,dom){
    var rid = $(_this).val();
    $.get(basic_config.region_url,{rid:rid,type:type},function(res){
        if(res.code==1){
            $(dom).html(res.data.options);
        }
    },'json');
}