var tips = {
	_arguments:{},
	type:'info',
	aligns:{left:'1%',center:'35%',right:'69%'},
	icons:{success:'<i class="fa fa-check"></i>',warning:'<i class="fa fa-warning"></i>',danger:'<i class="fa fa-times-circle"></i>',info:'<i class="fa fa-exclamation-circle"></i>',load:'<i class="fa fa-spinner"></i>'},
	success:function(){
		this._arguments = arguments;
		this.type = 'success';
		return this.show();
	},
	info:function(){
		this._arguments = arguments;
		this.type = 'info';
		return this.show();
	},
	warning:function(){
		this._arguments = arguments;
		this.type = 'warning';
		return this.show();
	},
	danger:function(){
		this._arguments = arguments;
		this.type = 'danger';
		return this.show();
	},
	load:function(){
		this._arguments = arguments;
		this.type = 'load';
		return this.show();
	},
	show:function(){
		var callback = null;
		var time = 2000;
		var content = '';
		var align = 'right';

		for (var i = 0; i < this._arguments.length; i++) {
			if(typeof this._arguments[i] == 'string' && i==0){
				content = this._arguments[i];
			}else if(typeof this._arguments[i] == 'function'){
				callback = this._arguments[i];
			}else if(typeof this._arguments[i] == 'number'){
				time = this._arguments[i];
				if(time<0){
					time = 0;
				}
			}else if(typeof this._arguments[i] == 'string'){
				align = this._arguments[i];
			}
		}
		var node = this._tips(content,align);
		node.find(".tip-close").click(function(){
			tips._closeWin(node);
			if(callback && typeof callback == "function"){
	            callback();
	        }
	        return false;
		});
		if(time!=0){
			setTimeout(function(){
				tips._closeWin(node);
				if(callback && typeof callback == "function"){
		            callback();
		        }
			},time);
			return node;
		}else{
			return node;
		}
	},
	close:function(node){
		var height = node.height();
		var top = node.css('top');
		top = top.toString().replace('px',"");
		top = top.replace('PX',"");
		top = top-height-30;
		node.css("z-index",9990);
		node.animate({top:top+"px"},function(){
			node.remove();
		});
		// node.animate({height:"0px"},function(){
		// 	node.remove();
		// });
	},
	_tips:function(content,align){
		if(this.type == undefined){
			this.type = "info";
		}
		if(align == undefined){
			align = 'right';
		}
		var top = '10';
		var len = $('.alert').length;
		if(len>0){
			top = (len*53)+(len*10)+10;
		}
		var type2 = this.type;
		if(this.type=='load'){
			this.type = 'info';
		}

		var html = '<div class="alert alert-'+this.type+' alert-dismissible" style="left:'+this.aligns[align]+'; top:'+top+'px;"><button type="button" class="close tip-close">×</button>'+this.icons[type2]+'&nbsp;&nbsp;'+content+'</div>';
		var node = $(html);
		$(".tips-list").append(node[0]);
		return node;
	},
	_closeWin:function(node){
		node.nextAll('.alert').each(function(index){
			var top = $(this).css('top');
			top = top.toString().replace('px',"");
			top = top.replace('PX',"");
			top = top-63;
			$(this).animate({top:top+"px"});
		});
		this.close(node);
	}
}
// var aligns = {left:'1%',center:'35%',right:'69%'};
// var icons = {success:'<i class="fa fa-check"></i>',warning:'<i class="fa fa-warning"></i>',danger:'<i class="fa fa-times-circle"></i>',info:'<i class="fa fa-exclamation-circle"></i>',load:'<i class="fa fa-spinner"></i>'}
// window._success = function(){
// 	var callback = null;
// 	var time = 2000;
// 	var content = '';
// 	var align = 'right';

// 	for (var i = 0; i < arguments.length; i++) {
// 		if(typeof arguments[i] == 'string' && i==0){
// 			content = arguments[i];
// 		}else if(typeof arguments[i] == 'function'){
// 			callback = arguments[i];
// 		}else if(typeof arguments[i] == 'number'){
// 			time = arguments[i];
// 		}else if(typeof arguments[i] == 'string'){
// 			align = arguments[i];
// 		}
// 	}
// 	var node = tips(content,'success',align);
// 	node.find(".tip-close").click(function(){
// 		closeWin(node);
// 		$(this).parent().remove();
// 		if(callback && typeof callback == "function"){
//             callback();
//         }
//         return false;
// 	});
// 	if(time!=0){
// 		setTimeout(function(){
// 			closeWin(node);
// 			node.remove();
// 			if(callback && typeof callback == "function"){
// 	            callback();
// 	        }
// 		},time);
// 		return node;
// 	}else{
// 		return node;
// 	}
// }
// window._info = function(){
// 	var callback = null;
// 	var time = 2000;
// 	var content = '';
// 	var align = 'right';

// 	for (var i = 0; i < arguments.length; i++) {
// 		if(typeof arguments[i] == 'string' && i==0){
// 			content = arguments[i];
// 		}else if(typeof arguments[i] == 'function'){
// 			callback = arguments[i];
// 		}else if(typeof arguments[i] == 'number'){
// 			time = arguments[i];
// 		}else if(typeof arguments[i] == 'string'){
// 			align = arguments[i];
// 		}
// 	}
// 	var node = tips(content,'info',align);
// 	node.find(".tip-close").click(function(){
// 		closeWin(node);
// 		$(this).parent().remove();
// 		if(callback && typeof callback == "function"){
//             callback();
//         }
//         return false;
// 	});
// 	if(time!=0){
// 		setTimeout(function(){
// 			closeWin(node);
// 			node.remove();
// 			if(callback && typeof callback == "function"){
// 	            callback();
// 	        }
// 		},time);
// 	}else{
// 		return node;
// 	}
// }
// window._warning = function(){
// 	var callback = null;
// 	var time = 2000;
// 	var content = '';
// 	var align = 'right';

// 	for (var i = 0; i < arguments.length; i++) {
// 		if(typeof arguments[i] == 'string' && i==0){
// 			content = arguments[i];
// 		}else if(typeof arguments[i] == 'function'){
// 			callback = arguments[i];
// 		}else if(typeof arguments[i] == 'number'){
// 			time = arguments[i];
// 		}else if(typeof arguments[i] == 'string'){
// 			align = arguments[i];
// 		}
// 	}
// 	var node = tips(content,'warning',align);
// 	node.find(".tip-close").click(function(){
// 		closeWin(node);
// 		$(this).parent().remove();
// 		if(callback && typeof callback == "function"){
//             callback();
//         }
//         return false;
// 	});
// 	if(time!=0){
// 		setTimeout(function(){
// 			closeWin(node);
// 			node.remove();
// 			if(callback && typeof callback == "function"){
// 	            callback();
// 	        }
// 	        return ;
// 		},time);
// 	}else{
// 		return node;
// 	}
// }
// window._danger = function(){
// 	var callback = null;
// 	var time = 2000;
// 	var content = '';
// 	var align = 'right';

// 	for (var i = 0; i < arguments.length; i++) {
// 		if(typeof arguments[i] == 'string' && i==0){
// 			content = arguments[i];
// 		}else if(typeof arguments[i] == 'function'){
// 			callback = arguments[i];
// 		}else if(typeof arguments[i] == 'number'){
// 			time = arguments[i];
// 		}else if(typeof arguments[i] == 'string'){
// 			align = arguments[i];
// 		}
// 	}
// 	var node = tips(content,'danger',align);
// 	node.find(".tip-close").click(function(){
// 		closeWin(node);
// 		$(this).parent().remove();
// 		if(callback && typeof callback == "function"){
//             callback();
//         }
//         return false;
// 	});
// 	if(time!=0){
// 		setTimeout(function(){
// 			closeWin(node);
// 			node.remove();
// 			if(callback && typeof callback == "function"){
// 	            callback();
// 	        }
// 		},time);
// 	}else{
// 		return node;
// 	}
// }

// window._load = function(){
// 	var callback = null;
// 	var time = 2000;
// 	var content = '';
// 	var align = 'right';

// 	for (var i = 0; i < arguments.length; i++) {
// 		if(typeof arguments[i] == 'string' && i==0){
// 			content = arguments[i];
// 		}else if(typeof arguments[i] == 'function'){
// 			callback = arguments[i];
// 		}else if(typeof arguments[i] == 'number'){
// 			time = arguments[i];
// 		}else if(typeof arguments[i] == 'string'){
// 			align = arguments[i];
// 		}
// 	}
// 	var node = tips(content,'load',align);
// 	node.find(".tip-close").click(function(){
// 		closeWin(node);
// 		$(this).parent().remove();
// 		if(callback && typeof callback == "function"){
//             callback();
//         }
//         return false;
// 	});
// 	if(time!=0){
// 		setTimeout(function(){
// 			closeWin(node);
// 			node.remove();
// 			if(callback && typeof callback == "function"){
// 	            callback();
// 	        }
// 		},time);
// 	}else{
// 		return node;
// 	}
// }

// function closeWin(node){
// 	node.nextAll('.alert').each(function(index){
// 		var top = $(this).css('top');
// 		top = top.toString().replace('px',"");
// 		top = top.replace('PX',"");
// 		top = top-63;
// 		$(this).animate({top:top+"px"});
// 	});
// }
// //type[success,warning,danger,info]
// //align[left:1%,center:35%,right:69%]
// function tips(content,type,align){
// 	if(type == undefined){
// 		type = "info";
// 	}
// 	if(align == undefined){
// 		align = 'right';
// 	}
// 	var top = '10';
// 	var len = $('.alert').length;
// 	if(len>0){
// 		top = (len*53)+(len*10)+10;
// 	}
// 	var type2 = type;
// 	if(type=='load'){
// 		type = 'info';
// 	}

// 	var html = '<div class="alert alert-'+type+' alert-dismissible" style="left:'+aligns[align]+'; top:'+top+'px;"><button type="button" class="close tip-close">×</button>'+icons[type2]+'&nbsp;&nbsp;'+content+'</div>';
// 	var node = $(html);
// 	$(".tips-list").append(node[0]);
// 	return node;
// }