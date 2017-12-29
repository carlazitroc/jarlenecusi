<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Album_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'albums';
	var $table_indexes = array(
		array('is_live','status'),
	);

	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
			'default'=>'0',
		),
		'is_pushed' => array(
			'type'       => 'INT',
			'constraint' => 1,
		),
		'last_pushed' => array(
			'type'       => 'DATETIME',
			'null'		 => TRUE,
		),
		'owner_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'owner_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'cover_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'title' => array(
			'type'       => 'VARCHAR',
			'constraint' => 100,
		),
		'num_photo' => array(
			'type'       => 'INT',
			'constraint' => 11,
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


	var $auto_increment = false;
	var $use_guid       = true;

}
