<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Admin_role_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'admin_roles';

	var $auto_increment = true;
	var $table_indexes = array(
		array('sys_name'),
	);
	var $fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'listing'=>TRUE,
			'pk'         => TRUE,
		),
		'name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'listing'=>TRUE,
		),
		'sys_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'listing'=>TRUE,
		),
		'create_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
			'listing'=>TRUE,
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
		'modify_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
	);

	// Admin role's Permissions relationship
	var $perms_table          = 'admin_roles_permissions';
	var $perms_fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'pk'=>TRUE,
		),
		'role_id' => array(
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

	// data validation, should be called before calling save function
	// @return {Dictionary}, Elements contain 'success'{Boolean} value of validation, 'fields'{Array} name of highlighted fields, and 'messages'{Dictionary} key of the message/issue.

	public function validate($data, $options = false) {

		$success = true;
		$fields  = array();
		$issues  = array();

		if (isset($data['name'])) {
			if (strlen($data['name']) < 1) {
				$success              = false;
				$fields['name']       = true;
				$issues['name_empty'] = true;
			}
		}

		if (isset($data['sys_name'])) {
			if (!preg_match("/^[a-zA-Z][a-zA-Z0-9\.\-\_]{3,39}$/", $data['sys_name'])) {
				$success                           = false;
				$fields['sys_name']                = true;
				$issues['sys_name_format_invalid'] = true;
			}
		}

		return compact('success', 'fields', 'issues');
	}
}
