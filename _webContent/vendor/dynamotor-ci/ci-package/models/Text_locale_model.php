<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Text_locale_model extends \Dynamotor\Core\HC_Model{

	var $table          = 'text_locales';
	var $table_indexes = array(
		array('id', 'is_live'),
		array('ref_table', 'ref_id'),
	);

	var $auto_increment = false;
	var $use_guid       = true;

	var $fields = array(
		'id',
		'is_live',
		'ref_table',
		'ref_id',
		'locale',
		'cover_id',

		'title',
		'description',
		'content',
		'parameters',
		'status',

		'create_date',
		'create_by',
		'create_by_id',
		'modify_date',
		'modify_by',
		'modify_by_id',
	);
	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE,
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
		),
		'ref_table' => array(
			'type'       => 'VARCHAR',
			'constraint' => 48,
		),
		'ref_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),
		'locale' => array(
			'type'       => 'VARCHAR',
			'constraint' => 5,
		),
		'cover_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),

		'title' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
		),
		
		'description' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'content' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'parameters' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'default'=>'1',
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