<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class File_group_model extends \Dynamotor\Core\HC_Model {
	var $table = 'file_groups';
	var $table_indexes = array(
		array('parent_id'),
		array('owner_id'),
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
		'parent_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'         => TRUE
		),
		'owner_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'owner_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),

		'name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
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