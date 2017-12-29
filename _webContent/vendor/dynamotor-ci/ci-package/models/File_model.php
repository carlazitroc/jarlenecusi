<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class File_model extends \Dynamotor\Core\HC_Model {
	var $table = 'files';

	var $table_indexes = array(
		array('ref_table','ref_id'),
		array('owner_type','owner_id'),
		array('bucket_source'),
	);

	var $auto_increment = false;
	var $use_guid       = true;
	var $mapping_field  = array('id');
	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE
		),
		'owner_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
            'null'=>true,
		),
		'owner_id' => array(
            'type' => 'VARCHAR',
            'constraint' => 36,
            'null'=>true,
		),
		'path_prefix' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'folder' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'ref_table' => array(
			'type'       => 'VARCHAR',
			'constraint' => 100,
			'null'=>true,
		),
		'ref_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'=>true,
		),
		'bucket_source'=>array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'bucket_id'=>array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'bucket_path'=>array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
		),
		'sys_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
		),
		'description' => array(
			'type' => 'TEXT',
			'null' => true,
		),
		'file_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
		),
		'file_ext' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'file_size' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'default'=>0,
		),
		'create_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'create_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'create_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'modify_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'modify_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'modify_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
	);


}