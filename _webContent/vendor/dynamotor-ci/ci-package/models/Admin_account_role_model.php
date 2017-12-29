<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Admin_account_role_model extends \Dynamotor\Core\HC_Model {
	// Admin's Roles relationship
	var $table          = 'admin_accounts_roles';
	var $auto_increment = true;
	var $table_indexes = array(
		array('admin_id','role_id'),
	);
	var $fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'pk'         => TRUE,
		),
		'admin_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'role_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
		),
		'start_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'end_date' => array(
			'type' => 'DATETIME',
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

	protected function selecting_options($options = false, $cache = false) {

		parent::selecting_options($options, $cache);
		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_role'])) {

			$child_table = 'admin_roles';

			$this->db->select($this->table.'.*,' .
				$this->_field($child_table . '.name',false,false,true) . ' as role_name'
			);
			$this->db->join($child_table,
				$this->_field($this->table . '.role_id',false,false,true) . ' = ' . $this->_field($child_table . '.id',false,false,true) . ' '
			);
			
			//$this->_where_match('locale', $options['_with_locale']);

		}
	}
}
