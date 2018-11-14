<?php 
	return [
		'admin_site' =>[
			'session_name' => 'admin_pro',
			'root_role' => 1
		],
		//分页配置
	    'paginate'               => [
	        'type'      => 'AdminBootstrap',
	        'var_page'  => 'page',
	        'list_rows' => 15,
	    ],
	];