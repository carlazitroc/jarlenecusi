<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Menu_item_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'menus_items';
	var $table_indexes = array(
		array('menu_id','sequence'),
		array('ref_table','ref_id'),
		array('is_live','status'),
	);

	var $use_guid       = true;
	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE,
		),
		'menu_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),
		'type' => array(
			'type'       => 'VARCHAR',
			'constraint' => '24',
			'default'=>'db',
		),
		'ref_table' => array(
			'type'       => 'VARCHAR',
			'constraint' => '100',
		),
		'ref_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => '36',
		),
		'sequence' => array(
			'type'       => 'INT',
			'constraint' => 11,
		),
		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
		),
		'is_pushed' => array(
			'type'       => 'INT',
			'constraint' => 1,
		),
		'last_pushed' => array(
			'type'       => 'DATETIME',
			'null'		 => TRUE,
		),
		'parameters' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),
		'create_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'create_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'modify_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'modify_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
	);
}
