<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Relationship_model extends \Dynamotor\Core\HC_Model {
	var $table = 'relationships';

	var $auto_increment = false;
	var $use_guid       = true;
	var $table_indexes = array(
		array('ref_table','ref_id'),
		array('term_table','term_type'),
		);
	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE,
		),
		'is_live'=>array(
			'type'       => 'INT',
			'constraint' => 1,
			'default'=>'0',
			'pk'         => TRUE,
		),
		'ref_table' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
			'null'         => TRUE,
		),
		'ref_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'         => TRUE
		),

		'term_table' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
			'null'         => TRUE,
		),
		'term_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'         => TRUE,
		),
		'term_type'=>array(
			'type'       => 'VARCHAR',
			'constraint' => 50,
			'null'         => TRUE,
		),
		'sequence'=>array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'default'         => '0',
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