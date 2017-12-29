<?php 
class Contact_model extends \Dynamotor\Core\HC_Model
{
	var $table = 'contact';
	var $auto_increment = true;
	var $use_guid = false;
	
	var $table_indexes = array(
		array('id'),
	);

	var $fields_details = array(
	
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'pk'=>TRUE,
			'listing'=>TRUE,
		),

		'contact_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 255,
			'control'=>'text',
			'listing'=>TRUE,
			'default'=>NULL
		),

		'email' => array(
			'type'       => 'VARCHAR',
			'constraint' => 255,
			'control'=>'text',
			'listing'=>TRUE,
			'default'=>NULL
		),

		'mobile' => array(
			'type'       => 'VARCHAR',
			'constraint' => 255,
			'control'=>'text',
			'listing'=>TRUE,
			'default'=>NULL
		),

		'message' => array(
			'type'       => 'TEXT',
			'control'=>'textarea',
			'control_type'=>'rich',
			'listing'=>TRUE,
			'default'=>''
		),

		'create_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
			'listing'=>TRUE,
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
			'listing'=>TRUE,
		),

		'modify_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		
		'modify_by_id' => array(
			'default'=>0,
			'type'       => 'BIGINT',
			'constraint' => 20,
		)
	);
}