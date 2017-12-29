<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Admin_account_permission_model extends \Dynamotor\Core\HC_Model {
	var $table = 'admin_accounts_permissions';
	var $auto_increment = true;
	var $table_indexes = array(
		array('admin_id','permission_id'),
	);
	var $fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'pk'=>TRUE,
		),
		'admin_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'permission_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'value' => array(
			'type' => 'INT',
			'constraint' => 5,
			'default'=>'1',
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
		if (!empty($options['_with_permission'])) {

			$child_table = 'admin_permissions';

			$this->db->select($this->table.'.*,' .
				$this->_field($child_table . '.name',false,false,true) . ' as permission_name, '.
				$this->_field($child_table . '.key',false,false,true) . ' as permission_key'
			);
			$this->db->join($child_table,
				$this->_field($this->table . '.permission_id',false,false,true) . ' = ' . $this->_field($child_table . '.id',false,false,true) . ' '
			);
			
			//$this->_where_match('locale', $options['_with_locale']);

		}
	}
}
