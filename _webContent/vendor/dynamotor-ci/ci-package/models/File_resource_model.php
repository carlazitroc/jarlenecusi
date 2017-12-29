<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class File_resource_model extends \Dynamotor\Core\HC_Model
{
	var $table = 'files_resources';

	var $table_indexes = array(
		array('file_id'),
		array('path','filename_prefix','filename_suffix','sys_name'),
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
		'file_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),
		'path' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'filename_prefix' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'filename_suffix' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'sys_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'=>true,
		),
		'file_ext' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
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
		'parameters'=>array(
			'type'       => 'TEXT',
			'null'=>true,
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