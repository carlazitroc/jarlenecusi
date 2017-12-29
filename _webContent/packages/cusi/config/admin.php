<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['admin_encryption_key'] = 'HASHED_KEY';

$config['admin_auth_config'] = array(
	'table'			=>'admin_accounts',
	'path'			=>array(
		'login'		=>'auth/signin',
	),
	'field'			=>array(
		'loginkey'	=>'login_name',
		'password'  =>'login_pass',
	),
	'activiate'		=>'admin',
	'encrypt_config_key'=>'admin_encryption_key',
);

$config['admin_acl_config'] = array(
	'tables'=>array(

		'users'=>'admin_accounts',
		'users_roles'=>'admin_accounts_roles',
		'users_permissions'=>'admin_accounts_permissions',
		'roles'=>'admin_roles',
		'roles_permissions'=>'admin_roles_permissions',
		'permissions'=>'admin_permissions',
	),
	'fields'=>array(

		'created'=>'create_date',
		'user_id'=>'admin_id',
		'perm_id'=>'permission_id',
	),
	'cache_key_prefix'=>'admin',
	'cache_ttl'=>86400 * 3,
);

$config['pref_sections'] = array(
	'general'=>array(
		'title'=>'General',
		'fields'=>array(
			array(
				'name'=>'site_enabled',
				'label'=>'Site Enabled',
				'control'=>'boolean',
				'localized'=>FALSE,
				'default_value'=>TRUE,
			),
			array(
				'name'=>'site_name',
				'label'=>'Site Title',
				'control'=>'text',
				'localized'=>TRUE,
				'description'=>'This content will be appear in browser title, search engine, shared feed in Social Network Platform.'
			),
			array(
				'name'=>'site_keywords',
				'label'=>'Site Keywords',
				'control'=>'text',
				'localized'=>TRUE,
				'description'=>'This content will be appear in search engine, shared feed in Social Network Platform.<br />Please use comma to separate your words.'
			),
			array(
				'name'=>'site_description',
				'label'=>'Site Description',
				'control'=>'text',
				'localized'=>TRUE,
				'description'=>'This content will be appear in search engine, shared feed in Social Network Platform.'
			),
			array(
				'name'=>'site_cover_id',
				'label'=>'Default Sharing Image',
				'control'=>'select',
				'control_type'=>'file',
				'is_image'=>TRUE,
				'localized'=>TRUE,
				'description'=>'This content will be appear in search engine, shared feed in Social Network Platform.<br />Suggested size: 400 x 400 pixel or larger square image.'
			),
			array(
				'name'=>'site_footer',
				'label'=>'Site Footer',
				'control'=>'textarea',
				'control_type'=>'rich',
				'localized'=>TRUE,
				'description'=>'This content will be appear in footer. (Depends on theme design.)'
			),

			array(
				'name'=>'site_theme',
				'label'=>'Theme',
				'control'=>'select',
				'options'=>array(
					'' => 'Default',
				),
				'localized'=>TRUE,
				'description'=>'Theme for portal app.'
			),
		),
	),

	'other'=>array(
		'title'=>'Other',
		'fields'=>array(

			array(
				'name'=>'ga_account',
				'label'=>'GA Account Profile',
				'control'=>'text',
				'placeholder'=>'UA-XXXXXXXX-1',
			),
		),
	),


);

$config['layout_menu'] = array(
	array(
		'tree'=>array('dashboard'),
		'url'=>'',
		'text'=>'dashboard_heading',
		'icon'=>'fa fa-dashboard',
		//'perms'=>'',
	),
	array(
		'url'=>'file',
		'text'=>'file_heading',
		'icon'=>'fa fa-folder-open',
		'perms'=>'FILE',
	),
	array(
		'url'=>'contact',
		'text'=>'contact_header',
		'icon'=>'fa fa-folder-open',
		'perms'=>'CONTACT',
	),
	// array(
	// 	'url'=>'menu',
	// 	'text'=>'menu_heading',
	// 	'icon'=>'fa fa-folder-open',
	// 	'perms'=>'Menu',
	// ),
	
    array(
		'url'=>'admin',
		'text'=>'admin_heading',
		'icon'=>'fa fa-folder-open',
		'tree'=>array('user','admin'),
		'perms'=>'ADMIN_ACCOUNT_RECORD_LISTING|ADMIN_ROLE_RECORD_LISTING|ADMIN_PERMISSION_RECORD_LISTING',
		'subitems'=>array(

		    array(
				'url'=>'user/admin/account',
				'text'=>'admin_account_heading',
				'icon'=>'fa fa-user',
				'tree'=>array('user','admin','account'),
				'perms'=>'ADMIN_ACCOUNT_RECORD_LISTING',
			),
		    array(
				'url'=>'user/admin/role',
				'text'=>'admin_role_heading',
				'icon'=>'fa fa-user',
				'tree'=>array('user','admin','role'),
				'perms'=>'ADMIN_ROLE_RECORD_LISTING',
			),
		    array(
				'url'=>'user/admin/permission',
				'text'=>'admin_permission_heading',
				'icon'=>'fa fa-user',
				'tree'=>array('user','admin','permission'),
				'perms'=>'ADMIN_PERMISSION_RECORD_LISTING',
			),
		),
	),
);