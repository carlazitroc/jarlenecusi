<?php 
// class Singer_model extends \Dynamotor\Core\HC_Model
// {
// 	var $table = 'singer';
// 	var $auto_increment = true;
// 	var $use_guid = false;
	
// 	var $table_indexes = array(
// 		array('slug'),
// 	);

// 	var $fields_details = array(
	
// 		'id' => array(
// 			'type'           => 'BIGINT',
// 			'constraint'     => 20,
// 			'auto_increment' => TRUE,
// 			'pk'=>TRUE,
// 			'listing'=>TRUE,
// 		),


// 		'slug' => array(
// 			'type'       => 'VARCHAR',
// 			'null' => TRUE,
// 			'constraint' => 255,
// 			'control'=>'text',
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'priority' => array(
// 			'type'       => 'INT',
// 			'constraint' => 50,
// 			'control'=>'text',
// 			'control_type'=>'number',
// 			'listing'=>TRUE,
// 			'default'=>NULL,
// 			'description'=>'Set order 1 is the highest priority'
// 		),

// 		'title' => array(
// 			'type'           => 'VARCHAR',
// 			'constraint'     => 255,
// 			'control'		 => 'text',
// 			'listing'		 => TRUE,
// 			'validate'		 => 'required',
// 		),

// 		'client' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'text',
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'project_date' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'text',
// 			'listing'=>TRUE,
// 			'default'=>NULL,
// 			'description'=>'MONTH YEAR'
// 		),

// 		'category' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'textarea',
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'objective' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'textarea',
// 			'listing'=>FALSE,
// 			'default'=>''
// 		),

// 		'cover' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'select',
// 			'control_type'=>'file',
// 			'is_image'=>TRUE,
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'cover_hex_color' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 80,
// 			'control'=>'text',
// 			'control_type'=>'color',
// 			'listing'=>TRUE

// 		),

// 		'thumbnail_image' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'select',
// 			'control_type'=>'file',
// 			'is_image'=>TRUE,
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'details' => array(
// 			'type'       => 'LONGTEXT',
// 			'control'=>'textarea',
// 			'control_type'=>'rich',
// 			'default'=>NULL,
// 			'description'=>'Set order 1 is the highest priority'
// 		),

// 		'slider_banner' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 1,
// 			'control'=>'select',
// 			'options'=>array(
// 				'0'=>'No',
// 				'1'=>'Yes',
// 			),
// 			'listing'=>TRUE,
// 			'default'=>'0',
// 			'description'=>'Set yes to turn on banner, no for turn off'
// 		),

// 		'slider_subtitle' => array(
// 			'type'       => 'VARCHAR',
// 			'null' => TRUE,
// 			'constraint' => 255,
// 			'control'=>'textarea',
// 			'control_type'=>'rich',
// 			'listing'=>TRUE,
// 			'default'=>NULL,
// 			'description'=>'Banner subtitle'
// 		),

// 		'slider_image' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'select',
// 			'control_type'=>'file',
// 			'is_image'=>TRUE,
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'slider_bg' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 255,
// 			'control'=>'select',
// 			'control_type'=>'file',
// 			'is_image'=>TRUE,
// 			'listing'=>TRUE,
// 			'default'=>NULL
// 		),

// 		'create_date' => array(
// 			'type' => 'DATETIME',
// 			'null' => TRUE,
// 			'listing'=>TRUE,
// 		),

// 		'create_by' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 40,
// 		),
		
// 		'create_by_id' => array(
// 			'type'       => 'BIGINT',
// 			'constraint' => 20,
// 		),
		
// 		'modify_date' => array(
// 			'type' => 'DATETIME',
// 			'null' => TRUE,
// 			'listing'=>TRUE,
// 		),

// 		'modify_by' => array(
// 			'type'       => 'VARCHAR',
// 			'constraint' => 40,
// 		),
		
// 		'modify_by_id' => array(
// 			'default'=>0,
// 			'type'       => 'BIGINT',
// 			'constraint' => 20,
// 		),

// 		'is_live' => array(
// 		   'type'       => 'INT',
// 		   'pk'         => TRUE,
// 		   'constraint' => 1,
// 		   'default'=>'0',
// 		),
		
// 		'is_pushed' => array(
// 		   'type'       => 'INT',
// 		   'constraint' => 1,
// 		),
		
// 		'last_pushed' => array(
// 		   'type'       => 'DATETIME',
// 		   'null'   => TRUE,
// 		),

// 	);
// }