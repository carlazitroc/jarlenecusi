<?php

/**
* "HC_Model" Model Class for CodeIgniter
* @author      Leman Kwok
* @copyright   Copyright (c) 2013, LMSWork.
* @license     http://codeigniter.com/user_guide/license.html
* @link        http://lmswork.com
* @version     Version 1.2
*
*/

// ------------------------------------------------------------------------

namespace Dynamotor\Core;
use \CI_Model;

use \Dynamotor\Core\HC_Exception;

class HC_Model extends CI_Model {
	public function __construct($config = NULL) {
		parent::__construct();

		$this->initialize($config);
	}

	var $db_group_name = NULL;

	var $auto_increment = true;// true = counted the record identifier by database AUTO_INCREMENT if supported
	var $use_guid       = false;// true = use guid() to create 36-char unique id by mac address, ip address

	var $_log_query = false;

	var $table          =  NULL;//'table_name' table name without prefix
	var $default_values = array();
	var $fields = NULL;
	var $fields_alias = NULL;
	/*
	var $fields         = array('id', 'create_date', 'create_by', 'create_by_id', 'modify_date', 'modify_by', 'modify_by_id');
	var $fields_alias   = array('id' => 'id', 'create_date' => NULL, 'modify_date' => NULL);// fields
	//*/
	var $pk_field       = 'id';
	var $mapping_field  = array('id', 'sys_name', 'slug', 'shortcode');
	var $table_indexes  = array();

	var $fields_details = NULL;
	var $auto_install   = TRUE;

	var $no_space_fields = array('sys_name','slug');
	

	var $create_date_field = 'create_date';
	var $modify_date_field = 'modify_date';
	var $create_by_field = 'create_by';
	var $modify_by_field = 'modify_by';
	var $create_by_id_field = 'create_by_id';
	var $modify_by_id_field = 'modify_by_id';

	/*
	$fields_details = array(
	'blog_id' => array(
	'type' => 'INT',
	'constraint' => 5,
	'unsigned' => TRUE,
	'auto_increment' => TRUE
	),
	'blog_title' => array(
	'type' => 'VARCHAR',
	'constraint' => '100',
	),
	'blog_author' => array(
	'type' =>'VARCHAR',
	'constraint' => '100',
	'default' => 'King of Town',
	),
	'blog_description' => array(
	'type' => 'TEXT',
	'null' => TRUE,
	),
	);
	 */
	
	public function initialize($config= NULL){

		$this->init_db();


		$this->init_fields();

		$this->perform_auto_install();
	}
	
	public function init_db(){
		$this->load->database();
	}

	public function init_fields(){

		if(empty($this->fields) && !empty($this->fields_details)){
			$this->fields = array_keys($this->fields_details);
		}
	}

	public function perform_auto_install(){

		if ($this->auto_install) {
			$this->install();
		}
	}

	public function get_db($group = NULL){
		/*
		if(empty($group)) $group = $this->db_group_name;
		if(empty($group)) $group = 'db';
		if(empty($group)) 
		return isset($this->$group) ? $this->$group : NULL;
		//*/
		return $this->db;
	}

	/***************************************************************************************************/
// Public functions

	public function install() {

		if(empty($this->fields) && !empty($this->fields_details)){
			$this->fields = array_keys($this->fields_details);
		}
			
		if ($this->auto_install && !empty($this->table) && $this->fields_details != NULL) {
			return $this->install_table($this->table, $this->fields, $this->fields_details, $this->table_indexes);
		}
	}

	public function table(){
		return $this->get_db()->dbprefix($this->table);
	}

	public function table_exist($table = false) {
		if (!$table) {
			$table = $this->table;
		}

		return $this->get_db()->table_exists($table);
	}

	protected function install_table($table, $table_fields, $table_field_details, $table_indexes = NULL) {

		$db = $this->get_db();
		if ($db && !$db->table_exists($table)) {
			$forge = $this->load->dbforge($db, true);

			$_fields = array();
			foreach ($table_fields as $idx => $field_name) {
				if (!isset($table_field_details[$field_name])) {
					$class_name = get_class($this);
					log_message('error',$class_name.'/install_table, cannot find details to install '.print_r($table_fields, true));

					// TODO: Undefined exception code
					throw new HC_Exception(-1, 'Cannot install model since missing field\'s detail of "' . $field_name . '" in Model Class: ' . $class_name, compact('table_fields'));
					return;
				}
				$field_detail = $table_field_details[$field_name];

				if (isset($field_detail['pk']) && $field_detail['pk'] == true) {
					$forge->add_key($field_name, true);
				} elseif ($table == $this->table && is_array($this->pk_field) && in_array($field_name, $this->pk_field)) {
					$forge->add_key($field_name, true);
				} elseif ($table == $this->table && $this->pk_field == $field_name) {
					$forge->add_key($field_name, true);
				}
				$_fields[$field_name] = $field_detail;
			}
			$forge->add_field($_fields);

			if (!empty($table_indexes)) {
				foreach ($table_indexes as $idx => $index_group) {
					$forge->add_key($index_group);
				}
			}

			$forge->create_table($table);
		}
	}

	public function uninstall() {

		$db = $this->get_db();
		if ($db ) {
			$forge = $this->load->dbforge($db, true);

			if ($this->auto_install && !empty($this->table) && !empty($this->table) && !empty($this->fields_details)) {
				$forge->drop_table($this->table);
			}
		}
	}

	public function new_default_values() {

		$def_vals = array();
		foreach ($this->fields as $idx => $field_name) {
			if (isset($this->default_values[$field_name])) {
				$def_vals[$field_name] = $this->default_values[$field_name];
			}elseif(isset($this->fields_details[$field_name]['default_value'])){
				$def_vals[$field_name] = $this->fields_details[$field_name]['default_value'];
			}elseif(isset($this->fields_details[$field_name]['default'])){
				$def_vals[$field_name] = $this->fields_details[$field_name]['default'];
			}elseif(isset($this->fields_details[$field_name]['null']) && $this->fields_details[$field_name]['null']){
				$def_vals[$field_name] = NULL;
			}else{
				$def_vals[$field_name] = '';
			}
		}
		return $def_vals;
	
	}

	public function validate($data, $options = false) {
		$success = true;
		$fields  = array();
		$issues  = array();
		return compact('success', 'fields', 'issues');
	}

	/*
	 * save
	 *	save  a record by given query options
	 * @param Array	$data - Data to be update
	 * @param Array $conds - Query data to be fetched. Pass NULL for inserting a new record. Otherwise, it will run the save action as UPDATE.
	 * @param Array $options - Other option will be used. Most of time it will pass editor information.
	 */
	public function save($data, $conds = false, $options = false) {
		$this->_init_db();

		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ .':'.__LINE__. ', '."\r\nargs=" . print_r(compact('data','conds','options'), true));
		}


		$id_field = $this->_field($this->pk_field, false);

		$table     = $this->_table($options, $this->table);
		$is_insert = $this->save_is_insert($conds, $options);

		$sql_data = $this->save_pre_data($data, $is_insert, $options);

		$id = NULL;
		$is_assigned_id = false;

		$action = 'insert';

		if ($is_insert) {
			
			$info = $this->assign_insert_record_id($data, $sql_data);

			if(isset($info['is_assigned_id'])){
				$is_assigned_id = $info['is_assigned_id'];
			}

			if(isset($info['id'])){
				$id = $info['id'];
			}
			
			foreach($sql_data as $field => $val){
				$this->get_db()->set($field, $val, $val !== NULL);
			}

		} else {
			$action = 'update';

			if (is_string($conds)) {
				$id = $conds;
				$this->get_db()->where($this->pk_field, $id);
			} else {

				$this->selecting_options($conds);

				if (isset($conds[$this->pk_field ])) {
					$id = $conds[$this->pk_field];
				} elseif (isset($data[ $this->pk_field ])) {
					$id = $data[$this->pk_field];
				} elseif (isset($sql_data[ $this->pk_field ])) {
					$id = $sql_data[$this->pk_field];
				}

			}

			if(isset($sql_data[ $this->create_date_field ])){
				unset($sql_data[$this->create_date_field]);
			}

			if(isset($sql_data[$this->create_by_field])){
				unset($sql_data[$this->create_by_field]);
			}

			if(isset($sql_data[$this->create_by_id_field])){
				unset($sql_data[$this->create_by_id_field]);
			}

		}

		//$this->get_db()->limit(1);
		$result = $this->get_db()->$action($table, $sql_data);

		// if auto_increment is on
		// getting the insert id from query
		if ($action == 'insert' && !$is_assigned_id && $this->auto_increment) {
			$data[$this->pk_field] = $id = $this->get_db()->insert_id();
		}

		$sql = $this->get_db()->last_query();

		$this->post_save_action($id);

		$output = compact('is_insert', 'result', 'sql', 'id');

		if ($this->_log_query) {
			log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ .':'.__LINE__. ", \r\nDATA=" . print_r($sql_data, true) . "\r\nOUTPUT=" . print_r($output , true));
		}

		return $output;
	}

	protected function assign_insert_record_id( &$data, &$sql_data){
		$is_assigned_id = false;
		$id = null;
		if (!empty($data[$this->pk_field])) {
			$id                  = $data[$this->pk_field];
			$sql_data[$this->pk_field] = $id;
			$is_assigned_id      = true;
			// if auto_increment is off
			// generate id by getting the last value from current table
		} elseif ($this->use_guid) {
			if (!function_exists('guid')) {
				$this->load->helper('guid');
			}
			$id                  = guid();
			$is_assigned_id = TRUE;
			$sql_data[$this->pk_field] = $data[$this->pk_field] = $id;
		}elseif (!$this->auto_increment) {
			$is_assigned_id = TRUE;
			$id                  = $this->get_last() + 1;
			$sql_data[$this->pk_field] = $data[$this->pk_field] = $id;

			// if use_guid, <code>guid()</code> will be identified as the record's id
		}

		return compact('is_assigned_id', 'id');
	}

	protected function post_save_action($id) {
		// TODO: should be override
	}

	protected function save_is_insert($conds = false, $options = false) {
		if (!$conds || empty($conds)) {
			return TRUE;
		}

		return FALSE;
	}

	protected function save_pre_data($data, $is_insert = true, $options = false) {

		$sql_data = NULL;

		$def_vals = $this->new_default_values();

		// only listed variables are able to be executed by sql command
		if (!empty($this->fields)) {
			$sql_data = array();
			foreach ($this->fields as $idx => $field_name) {
				if (isset($data[$field_name])) {
					$sql_data[$field_name] = $data[$field_name];
				} elseif (isset($def_vals[$field_name]) && $is_insert) {
					$sql_data[$field_name] = $def_vals[$field_name];
				}

				if (isset($data[$field_name])) {
					if(isset($this->fields_details[ $field_name]['type']) && strtoupper($this->fields_details[ $field_name]['type']) == 'DATE' || $this->fields_details[ $field_name]['type'] == 'DATETIME'){
						if(empty($sql_data[$field_name]) || substr($sql_data[$field_name],0,4) == '0000'){
							$sql_data[$field_name] = isset($sql_data['null']) && $sql_data['null'] ?  NULL : '';
						}
					}
				}
			}

		} else {
			$sql_data = $data;
		}

		$this->save_pre_data_attr($sql_data, $is_insert, $options);

		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', Before save:' . "\r\ninfo=" . print_r(compact('is_insert', 'data', 'sql_data', 'options'), true));
		}

		return $sql_data;
	}

	protected function handle_nonspace_value_to_sql(&$sql_data, $is_insert = true, $options = false){
		if(!is_array($this->no_space_fields))return;

		$this->load->helper('inflector');

		foreach($this->no_space_fields as $field_name){

			if (isset($sql_data[$field_name])) {
				$sql_data[$field_name] = underscore($sql_data[$field_name]);
			}
		}
	}

	protected function handle_parameter_value_to_sql(&$sql_data, $is_insert = true, $options = false){

		if(isset($sql_data['parameters'])){
			$sql_data['parameters'] = $this->encode_parameters($sql_data['parameters']);
		}
	}

	protected function handle_owner_attribute_value_to_sql(&$sql_data, $is_insert = true, $options = false){

		$_user_type =  empty( $options['_user_type']) ? 'unknown':  $options['_user_type'];
		$_user_id =  empty( $options['_user_id']) ? '':  $options['_user_id'];


		$now = time_to_date();

		if ($is_insert) {
			if(empty($sql_data[ $this->create_date_field ])){
				if (in_array( $this->create_date_field , $this->fields)) {
					$sql_data[ $this->create_date_field ] = $now;
				}
			}
			if(empty($sql_data[$this->create_by_field ])){
				if (in_array($this->create_by_field, $this->fields)) {
					$sql_data[$this->create_by_field]    = $_user_type;
				}
			}
			if(empty($sql_data[$this->create_by_id_field])){
				if (in_array($this->create_by_id_field, $this->fields)) {
					$sql_data[$this->create_by_id_field] = $_user_id;
				}
			}
		}

		if(empty($sql_data[$this->modify_date_field])){
			if (in_array($this->modify_date_field, $this->fields)) {
				$sql_data[$this->modify_date_field] = $now;
			}
		}

		if(empty($sql_data[$this->modify_by_field])){
			if (in_array($this->modify_by_field, $this->fields)) {
				$sql_data[$this->modify_by_field] = $_user_type;
			}
		}

		if(empty($sql_data[$this->modify_by_id_field])){
			if (in_array($this->modify_by_id_field, $this->fields)) {
				$sql_data[$this->modify_by_id_field] = $_user_id;
			}
		}
	}

	protected function save_pre_data_attr(&$sql_data, $is_insert = true, $options = false) {

		$this->handle_nonspace_value_to_sql($sql_data, $is_insert, $options);

		$this->handle_parameter_value_to_sql($sql_data, $is_insert, $options);

		$this->handle_owner_attribute_value_to_sql($sql_data, $is_insert, $options);

	}

	/*
	 * get		getting field value by given query options
	 * @param	string	$field - field name. If value does not given or passed 'false' value, return all fields
	 * @param	array	$options - Query Options
	 * @return	mixed	Value fetected according select options
	 */
	public function get($field = false, $options = false) {
		$this->_init_db();

		$row = $this->read($options);
		if (!$field) {return $row;
		}

		if (!isset($row[$field])) {return NULL;
		}

		return $row[$field];
	}

	/*
	 * read
	 *	read a record by given query options
	 * @param	Array	$options - Query Options
	 */
	public function read($options = false, $offset = 0) {
		$this->_init_db();

		if ($offset < 0) {
			$total_record = $this->get_total($options);
			$this->get_db()->limit(1, $total_record + $offset);
		} else {
			$this->get_db()->limit(1, $offset);
		}
		$this->selecting_options($options);

		$query = $this->get_db()->get();
		if (!$query) {
			if ($this->_log_query) {
				log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query error:' . $this->get_db()->last_query());
			}

			return NULL;
		}
		if ($query->num_rows() < 1) {
			//if ($this->_log_query) {
				log_message('info', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', empty result when getting row with sql:' . $this->get_db()->last_query());
			//}

			return NULL;
		}
		$row = $query->row_array();

		if (!empty($row) && is_array($row) && !isset($options['_no_result_row'])) {
			return $this->result_row($row, $options);
		}
		return $row;
	}

	// simple delete function that applying query options
	public function delete($options = false, $limit = -1) {
		$this->_init_db();

		$this->selecting_options($options);

		if ($limit > 0) {
			$this->get_db()->limit($limit);
		}

		$query = $this->get_db()->delete();

		$this->get_db()->flush_cache();
		
		return $this->get_db()->affected_rows();
	}
	// simple delete function that applying query options
	public function remove($options = false, $limit = -1) {
		return $this->delete($options, $limit);
	}

	public function find($options = false) {
		if (!isset($this->db)) $this->load->database();

		$this->selecting_options($options);

		$query = $this->get_db()->get();

		if (!$query) {
			ob_start();
			debug_print_backtrace();
			$data = ob_get_clean();
			
			log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query error:' . $this->get_db()->last_query()."\r\nBacktrace:".$data);
			

			return NULL;
		}
		if ($query->num_rows() < 1) {
			if ($this->_log_query) {
				log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', empty when getting maximum value with sql:' . $this->get_db()->last_query());
			}

			return NULL;
		}
		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query success:' . $this->get_db()->last_query());
		}

		$this->get_db()->flush_cache();
		
		return $this->result_query($query, $options);
	}

	public function find_paged($offset = -1, $per_page = 30, $options = false) {
		if (!isset($this->db)) {
			$this->load->database();
		}

		$offset = intval($offset);
		$per_page = intval($per_page);
		if(!is_int($offset)){
			ob_start();
			debug_print_backtrace();
			$data = ob_get_clean();
			log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', invalid offset'."\r\nBacktrace:".$data);
			throw new Exception('Unmatched data type of requesting paging data.', -1);
		}

		if(!is_int($per_page)){
			ob_start();
			debug_print_backtrace();
			$data = ob_get_clean();
			log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', invalid per_page.'."\r\nBacktrace:".$data);
			throw new Exception('Unmatched data type of requesting paging data.', -1);
		}

		$total_record = $this->get_total($options);

		$per_page = max(1, $per_page);

		$total_page = ceil($total_record / $per_page);
		$page       = floor($offset / $per_page) + 1;

		$this->selecting_options($options);
		if ($offset >= 0) {
			$this->get_db()->limit($per_page, $offset);
		} else {
			$this->get_db()->limit($per_page);
		}
		$query = $this->get_db()->get();

		if (!$query) {
			if ($this->_log_query) {
				log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query error:' . $this->get_db()->last_query());
			}

			return NULL;
		}
		if ($query->num_rows() < 1) {
			if ($this->_log_query) {
				log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', empty when getting maximum value with sql:' . $this->get_db()->last_query());
			}

			return NULL;
		}
		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query success:' . $this->get_db()->last_query());
		}

		$result = array();

		if ($total_page < 0) {
			$total_page = ceil($total_record / $per_page);
		}

		if ($page < 0) {
			$page = floor($offset / $per_page) + 1;
		}

		$result['data']         = $this->result_query($query, $options);
		$result['total_record'] = intval($total_record);
		$result['total_page']   = intval($total_page);
		$result['page']         = intval($page);

		$result['index_from'] = $offset;
		$result['index_to']   = min($offset + $per_page, $total_record);
		$result['offset']     = $offset;
		$result['limit']      = $per_page;

		$this->get_db()->flush_cache();

		return $result;
	}

	public function get_mapping_fields() {
		$fields = array();
		foreach ($this->mapping_field as $idx => $field_name) {
			if (in_array($field_name, $this->fields)) {
				$fields[] = $field_name;
			}
		}

		return $fields;
	}

	// @param {Dictionary},{Boolean} Optional, options dictionary for querying
	// @return {int} Total number of the query options
	public function get_total($options = false) {
		if (!isset($this->db)) $this->load->database();

		$this->selecting_options($options);

		return intval($this->get_db()->count_all_results());
	}

	public function get_sum($options = false, $field = 'id', $table = false) {
		if (!isset($this->db)) $this->load->database();

		$this->selecting_options($options);
		$this->get_db()->select_sum($field, 'value');

		if(!empty($table))
			$query = $this->get_db()->get($table);
		else
			$query = $this->get_db()->get();

		if (!$query) {
			if ($this->_log_query) {
				log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query error:' . $this->get_db()->last_query());
			}

			return 0;
		}
		if ($query->num_rows() < 1) {
			if ($this->_log_query) {
				log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', empty when getting maximum value with sql:' . $this->get_db()->last_query());
			}

			return 0;
		}
		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query success:' . $this->get_db()->last_query());
		}

		$row = $query->row_array();

		if (isset($row["value"])) {
			return intval($row["value"]);
		}

		return 0;
	}

	public function get_max($options = false, $field = 'id', $table = false) {
		if (!isset($this->db)) $this->load->database();

		$this->selecting_options($options);
		$this->get_db()->select_max($field, 'value');

		if(!empty($table))
			$query = $this->get_db()->get($table);
		else
			$query = $this->get_db()->get();

		if (!$query) {
			if ($this->_log_query) {
				log_message('error', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query error:' . $this->get_db()->last_query());
			}

			return 0;
		}
		if ($query->num_rows() < 1) {
			if ($this->_log_query) {
				log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', empty when getting maximum value with sql:' . $this->get_db()->last_query());
			}

			return 0;
		}
		if ($this->_log_query) {
			log_message('debug', get_called_class() . '(' . $this->table . ')/' . __METHOD__ . ', query success:' . $this->get_db()->last_query());
		}

		$row = $query->row_array();

		if (isset($row["value"])) {
			return intval($row["value"]);
		}

		return 0;
	}

	public function get_last($options = false, $field = 'id', $table = false) {
		return $this->get_max($options, $field, $table);
	}

	public function result($data, $options = false) {

		$need_custom_mapping = true;
		// the way of mapping the key for each row, passing with '_pk_based' for pre-configured primary key field, '_id_based' for 'id' field or '_field_based' for custom field
		// otherwise, use the offset of the result as the mapping key
		$_target_field = NULL;
		if (isset($options['_pk_based']) && in_array($this->pk_field, $this->fields)) {
			$_target_field = $this->pk_field;
		} elseif (isset($options['_id_based']) && in_array('id', $this->fields)) {
			$_target_field = 'id';
		} elseif (isset($options['_mapping_based'])) {
			$_target_field = '_mapping';
		} elseif (isset($options['_field_based']) && in_array($options['_field_based'], $this->fields)) {
			$_target_field = $options['_field_based'];
		} else {
			$need_custom_mapping = false;
		}

		if (!$need_custom_mapping) {
			return $data;
		}

		$_output = array();

		if ($data && is_array($data) && count($data) > 0) {

			foreach ($data as $idx => $row) {
				if (!empty($row) && is_array($row) && !isset($options['_no_result_row'])) {
					$row = $this->result_row($row, $options);
				}

				// mapping to row
				if (!empty($_target_field)) {
					$_output[$_row[$_target_field]] = $row;
				} else {
					$_output[$idx] = $row;
				}
			}
		}

		return $_output;
	}

	public function result_query($query, $options) {
		$need_custom_mapping = true;
		// the way of mapping the key for each row, passing with '_pk_based' for pre-configured primary key field, '_id_based' for 'id' field or '_field_based' for custom field
		// otherwise, use the offset of the result as the mapping key
		$_target_field = NULL;
		$is_group      = false;

		if (isset($options['_pk_based']) && in_array($this->pk_field, $this->fields)) {
			$_target_field = $this->pk_field;
		} elseif (isset($options['_id_based']) && in_array('id', $this->fields)) {
			$_target_field = 'id';
		} elseif (isset($options['_mapping_based'])) {
			$_target_field = '_mapping';
		} elseif (isset($options['_field_based'])) {
			$_target_field = $options['_field_based'];
		} elseif (isset($options['_field_based_array'])) {
			$_target_field = $options['_field_based_array'];
			$is_group      = true;
		} else {
			$need_custom_mapping = false;
		}

		$_output = array();
		$_total  = $query->num_rows();

		if ($query && $_total > 0) {

			$row = $query->first_row('array');

			for ($idx = 0; $idx < $_total; $idx++) {

				if (!empty($row) && is_array($row) && !isset($options['_no_result_row'])) {
					$row = $this->result_row($row, $options);
				}

				// mapping to row
				if ($need_custom_mapping && isset($row[$_target_field])) {
					$key = $row[$_target_field];
					if ($is_group) {
						$_output[$key][] = $row;
					} else {

						$_output[$key] = $row;
					}
				} else {
					$_output[$idx] = $row;
				}
				$row = $query->next_row('array');
			}
		}

		return $_output;
	}
	/*
	* Encode the extra parameters 
	* @param Any type
	* @return String
	* JSON Formatted string
	*/
	public function encode_parameters($val){
		return json_encode($val, true);
	}

	/*
	* Decode the extra parameters 
	* @param String
	* Formatted string, it will try to decode in JSON format. if fail, will try to use php.net/unserialize for supporting old format.
	*/
	public function decode_parameters($val){
		try{
			return  json_decode($val, TRUE);
		}catch(Exception $exp){
			return unserialize($val);	
		}
		return NULL;
	}

	public function result_row($row, $options = false) {
		if (is_array($row) ) {
			$row['_mapping'] = $this->get_row_mapping($row, $options);

			if(isset($row['parameters'])){
				$row['parameters_str'] = $row['parameters'];
				$row['parameters'] = $this->decode_parameters($row['parameters']);
			}
		}
		return $row;
	}

	public function get_row_mapping($row, $options = false) {
		$fields = array_reverse($this->get_mapping_fields());

		foreach ($fields as $idx => $field_name) {
			if (isset($row[$field_name]) && !empty($row[$field_name])) {
				return $row[$field_name];
			}
		}
		if (isset($row[$this->pk_field])) {
			// default mapping key
			return $row[$this->pk_field];
		}
		return NULL;
	}

	protected function _query_fields($conds, $all_fields = false, $options=false, $def_table=false){
		foreach ($conds as $key => $val) {
			// skip if the first character is underscore
			if(substr($key,0,1) == '_') continue;

			$is_field_name_valid = true;

			$_field = $key;
			if(!preg_match('#^[a-zA-Z\_].*$#', $_field)){
				$is_field_name_valid = false;
			}

			if($is_field_name_valid){


				$_operator = '=';
				if (strpos($key, ' ') > 0) {
					$pair = explode(' ', $key, 2);
					$_field = $pair[0];
					$_operator = strtoupper($pair[1]);
				}


				$field_info = $this->_get_field_info($_field,$def_table);
				$field = $field_info['prefix'].$field_info['field'];

				$val_is_array = is_array($val);
				$val_is_null = is_null($val);

				//print_r(compact('field','_operator'));

				if (!$all_fields || in_array($field_info['field'], $all_fields)){

					if($val_is_null && in_array($_operator, array('IS','IS NOT') )){
						$this->get_db()->where($field.' '.$_operator .' NULL',NULL);
					}elseif (in_array( $_operator , array('<', '<=', '=', '!', '!=', '>', '>=', 'LIKE')) && !$val_is_array) {
						if($_operator == '!') $_operator = '!=';
						$this->get_db()->where($field.' '.$_operator, $val);
					}elseif ($_operator == 'IN' || $_operator == '=') {
						$this->_where_match($field, $val);
					}elseif ($_operator == 'NOT IN' || $_operator == '!=') {
						$this->_where_match($field.' NOT', $val);
					} else{
						$this->_where_match($field, $val, $options);
					}
				}

				// handle inner or case
			}elseif(!empty($val)){

				// if it's array form. 
				if(is_array($val)){
					$_or = array();
					foreach($val as $_field2 => $_val2){
						$is_field_name_valid2 = true;
						if(!preg_match('#^[a-zA-Z\_].*$#', $_field2)){
							$is_field_name_valid2 = false;
						}

						if($is_field_name_valid2){
							
							$_operator = '=';
							if (strpos($_field2, ' ') > 0) {
								$pair2 = explode(' ', $_field2, 2);
								$_field2 = $pair2[0];
								$_operator = strtoupper($pair2[1]);
							}

							$field_info2 = $this->_get_field_info($_field2,$def_table);
							$field2 = $field_info2['prefix'].$field_info2['field'];


							$_or[] = $field2.' '.$_operator.' ' .$this->get_db()->escape($_val2);
						}else{
							$_or[] = $_val2;
						}

					}
					$this->_or( $_or );

				// if it's string form.
				}elseif(is_string($val)){
					$this->get_db()->where($val, NULL, NULL);
				}

			}
		}
	}

	// you may necessary to overwrite this function for extra selecting options
	protected function selecting_options($options = false) {

		$table = $this->_table($options);

		if (isset($options['_table'])) {
			$table = $options['_table'];
		}

		$this->get_db()->from($table);

		// Advanced selecting method
		$selected_fields = array();
		if (isset($options['_select'])  ) {
			$selected_fields = is_string($options['_select']) ?  explode(',', $options['_select']) : $options['_select'];
		}

		$all_fields = $this->fields;
		if (empty($all_fields)) {
			$all_fields = array_keys($this->default_values);
		}

		if (isset($options['_fields'])) {
			$all_fields = $options['_fields'];
		}

		// Handling for JOIN Case
		if (isset($options['_join']) && is_array($options['_join'])) {

			if(empty($selected_fields))
				$selected_fields[] = $table.'.*';

			$_joins = $options['_join'];
			if(isset($options['_join']['table'])){
				$_joins = array();
				$_joins[] = $options['_join']['table'];
			}

			foreach($_joins as $join_info){

				// handling select select case from join
				if(!empty($join_info['select']))
					if(is_string($join_info['select']))
						$selected_fields = array_merge($selected_fields , explode(',',$join_info['select']));
					elseif(is_array($join_info['select']))
						$selected_fields = array_merge($selected_fields , $join_info['select']);

				if(!empty($join_info['prefix'])){
					$this->get_db()->join($join_info['table'], $join_info['on'],$join_info['prefix']);
				}else{
					$this->get_db()->join($join_info['table'], $join_info['on']);
				}
			}
		
		}

		if(isset($options['_group_by'])){
			$this->get_db()->group_by($options['_group_by']);
		}

		if(!empty($selected_fields)){
			$_selected_fields = array();
			foreach($selected_fields as $field_key => $field_label){
				if(is_string($field_key) && !empty($field_key)){
					$_selected_fields[] = $field_key .' AS ' . $field_label;
				}else{
					$_selected_fields[] = $field_label;
				}
			}

			if(!empty($_selected_fields))
				$this->get_db()->select(implode(',', $_selected_fields));
		}

		if (is_array($options)) {
			$this->_query_fields($options,$all_fields, $options);
		}


		if (isset($options['_keyword']) && isset($options['_keyword_fields'])) {
			$fields = $options['_keyword_fields'];
			$this->_like_fields($fields, $options['_keyword']);
		}

		if (isset($options['_mapping'])) {

			$_fields = array($this->pk_field);
			$mapping_fields = $this->get_mapping_fields();
			if(!empty($mapping_fields)){
				foreach ($mapping_fields as $idx => $_mapping_field) {
					if(!in_array($_mapping_field, $_fields))
						$_fields[] = $_mapping_field;
				}
			}

			$sqls = array();
			foreach ($_fields as $idx => $key) {
				$sqls[] = $this->_array_to_in_case($this->_field($key), $options['_mapping']);
			}
			$this->get_db()->where('(' . implode(' OR ', $sqls) . ')');
		
		}

		if (isset($options['_order_by'])) {
			// To handle multiple tables query and prevent same field name when selecting
			// we have to knowing each setting and add table name in front of field name.
			$_order_by = $options['_order_by'];
			if (!is_array($options['_order_by'])) {
				$_order_by = array($options['_order_by']);
			}
			foreach ($_order_by as $key => $val) {

				if (!is_string($key)) {
					$_ary = explode(' ',$val);
					$key = $_ary[0];
					$val = '';
					if(!empty($_ary[1]))
						$val = $_ary[1];
				}
				$_field = $this->_field($key);
				$this->get_db()->order_by($_field . ' ' . strtoupper($val) );
			}
		}
	}
/***************************************************************************************************/
// Protected functions

	protected function _or($conds,$protected=TRUE) {
		$this->get_db()->where("(" . implode(" OR ", $conds) . ")",NULL,$protected);
	}

	protected function _like_fields($fields, $val, $is_not = false) {

		$filter_val = $this->get_db()->escape_like_str($val);
		$filter_val = preg_replace('#[\s\t]+#','%',$filter_val);
		$ary = array();
		if (isset($fields) && is_array($fields) && count($fields)>0) {
			foreach ($fields as $idx => $key) {
				$ary[] =  $this->_field($key,false,false,true). ($is_not ? ' NOT':'') ." LIKE '%" . $filter_val . "%'";
			}
			$this->_or($ary);
		}
	}

	protected function _table($options = true, $table_name = false) {
		if (!$table_name) {
			$table_name = $this->table;
		}

		return ($table_name);
	}

	protected function _field($field, $use_quote = false,$def_table=false,$use_prefix = false) {

		if(strpos($field,')') !==FALSE || strpos($field,'(') !==FALSE){
			return $field;
		}

		$_info = $this->_get_field_info($field,$def_table,$use_prefix);

		if(empty($_info)){
			return $field;
		}

		$field = $_info['field'];

		if (isset($this->fields_alias[$_info['field']])) {
			$field = $this->fields_alias[ $_info['field']];
		}
		return $this->_fill_table($_info['prefix'] .$field,$use_quote);
	}

	protected function _get_field_info($field,$def_table=false,$use_prefix=false){

		$prefix = '';
		$table = '';
		$suffix = '';

		if(strpos($field,' ')>0){
			$pairs = explode(' ',$field,2);
			$field = $pairs[0];
			$suffix = ' '.$pairs[1];
		}

		if(strpos($field,'.')>0){
			$pairs = explode('.',$field,2);
			$table = $pairs[0];
			$field = $pairs[1];
		}elseif(!empty($def_table)){
			$table = $def_table;
		}else{
			if(in_array($field, $this->fields)){
				$table = $this->table;
			}

		}
		if(!empty($table)){
			$prefix =  $use_prefix ? $this->get_db()->dbprefix($table) : $table;
			$prefix.='.';
		}
		return compact('prefix','table','field','suffix');
	}

	protected function _fill_table($field, $use_quote = true){

		$info =$this->_get_field_info($field);

		if(!in_array($field,$this->fields)){
			return $info['prefix'].($use_quote ? $this->_element_quoted($info['field']) : $info['field']);
		}
		
		return $info['prefix'].($use_quote ? $this->_element_quoted($info['field']) : $info['field']);
	}

	// for mysql, use ` for query as table/field name
	protected function _element_quoted($field) {
		return '`' . $field . '`';
	}

	protected function _where_match($field_name, $vals = '', $options = false) {
		if (is_array($vals)) {
			$this->get_db()->where('(' . $this->_array_to_in_case($field_name, $vals) . ')');
		} else {
			$field_name   =$this->_field($field_name);

			$this->get_db()->where($field_name, $vals);
		}
	}

	protected function _array_to_in_case($key, $vals) {
		if (!is_array($vals)) {
			$vals = array($vals);
		}

		$vals_ary = array();
		foreach ($vals as $idx => $val) {
			$vals_ary[] = $this->get_db()->escape($val);
		}

		$field_info = $this->_get_field_info($key,false,true);

		$field_name = $field_info['prefix'].$field_info['field'].$field_info['suffix'];

		if( count($vals_ary) < 1){
			log_message('error', 'HC_Model['.$this->table.'] '.$field_name.' does not include any values for IN Case.'."\r\nBacktrace:\r\n".print_r(debug_backtrace(),true));
			
			// TODO: Undefined exception code
			throw new HC_Exception('HC_Model['.$this->table.'] '.$field_name.' does not include any values for IN Case.');
		}

		return '' . $field_name . ' IN (' . implode(", ", $vals_ary) . ')';
	}

	protected function _init_db() {
		if (!isset($this->db)) {
			$this->load->database();
		}
	}
}
