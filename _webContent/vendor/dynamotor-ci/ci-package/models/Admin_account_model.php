<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Admin_account_model extends \Dynamotor\Core\HC_Model {
	var $table = 'admin_accounts';

	var $auto_increment = true;
	var $table_indexes = array(
		array('login_name','status'),
		array('email'),
	);
	var $fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'auto_increment' => TRUE,
			'pk'         => TRUE,
			'listing'=>TRUE,
		),
		'login_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'listing'=>TRUE,
		),
		'login_pass' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
		),
		'name' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'listing'=>TRUE,
		),
		'email' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'control'=>'text',
			'control_type'=>'email',
			'listing'=>TRUE,
		),
		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'listing'=>TRUE,
			'control'=>'bool',
		),
		'last_access' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
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

	public function validate($data, $options = false) {
		$success = true;
		$fields  = array();
		$issues  = array();

		if (isset($data['login_name'])) {
			if (strlen($data['login_name']) < 4) {
				$success                        = false;
				$fields['login_name']           = TRUE;
				$issues['login_name_too_short'] = true;
			} elseif (strlen($data['login_name']) > 40) {
				$success                       = false;
				$fields['login_name']          = TRUE;
				$issues['login_name_too_long'] = true;
			} elseif (!preg_match("/^[a-zA-Z].+$/", $data['login_name'])) {
				$success                               = false;
				$fields['login_name']                  = TRUE;
				$issues['login_name_not_letter_first'] = true;
			}
		}

		if (isset($data['login_pass']) && !empty($data['login_pass'])) {
			if (strlen($data['login_pass']) < 8) {
				$success                        = false;
				$fields['login_pass']           = TRUE;
				$issues['login_pass_too_short'] = true;
			} elseif (strlen($data['login_pass']) > 32) {
				$success                       = false;
				$fields['login_pass']          = TRUE;
				$issues['login_pass_too_long'] = true;

			} elseif (preg_match("/^[0-9].+$/", $data['login_pass'])) {
				$success                                   = false;
				$fields['login_pass']                      = TRUE;
				$issues['login_pass_cannot_numeric_first'] = true;

			} elseif (isset($data['login_retype_pass'])) {
				if ($data['login_pass'] != $data['login_retype_pass']) {

					$success                            = false;
					$fields['login_retype_pass']        = TRUE;
					$issues['login_retype_not_matched'] = true;
				}
			}
		}

		if (isset($data['email'])) {
			if (strlen($data['email']) < 4) {
				$success                   = false;
				$fields['email']           = TRUE;
				$issues['email_too_short'] = true;
			} elseif (!preg_match("/^[a-zA-Z-_\.]+\@[a-zA-Z-_\.]+[a-zA-Z0-9]+$/", $data['email'])) {
				$success                     = false;
				$fields['email']             = TRUE;
				$issues['email_not_correct'] = true;
			}
		}

		return compact('success', 'fields', 'issues');
	}
}
