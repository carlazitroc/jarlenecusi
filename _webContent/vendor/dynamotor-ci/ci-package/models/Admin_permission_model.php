<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Admin_permission_model extends \Dynamotor\Core\HC_Model {
	var $table = 'admin_permissions';

	var $auto_increment = true;
	var $table_indexes = array(
		array('key'),
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
		'key' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'listing'=>TRUE,
		),
		'description' => array(
			'type'       => 'TEXT',
			'control'=>'textarea',
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

		if (isset($data['key'])) {
			if (!preg_match("/^[a-zA-Z0-9\.\-\_]{4,40}$/", $data['key'])) {
				$success                      = false;
				$fields['key']                = true;
				$issues['key_format_invalid'] = true;
			}
		}

		return compact('success', 'fields', 'issues');
	}
}
