<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Menu_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'menus';
	
	var $auto_increment = FALSE;
	var $use_guid       = TRUE;

	var $locale_table = 'text_locales';

	var $table_indexes = array('sys_name','default_locale');

	var $fields_details = array(
		'id' => array(
			'type'           => 'VARCHAR',
			'constraint'     => 36,
			'pk'             => TRUE,
			'listing'=>TRUE,
		),
		'sys_name' => array(
			'type'       => 'VARCHAR',
			'constraint' => '50',
			'validate'=>'required|trim',
			'listing'=>TRUE,
		),
		'default_locale' => array(
			'type'       => 'VARCHAR',
			'constraint' => '5',
		),

		'title' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'validate'=>'required|trim',
			'listing'=>TRUE,
		),
		'description' => array(
			'type' => 'TEXT',
			'null' => TRUE,
			'listing'=>TRUE,
		),
		'content' => array(
			'type' => 'TEXT',
			'null' => TRUE,
			'listing'=>TRUE,
		),

		'sort_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => '36',
			'default'    => 'sequence',
		),
		'display_limit' => array(
			'type'       => 'INT',
			'constraint' => 10,
		),

		'start_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
			'listing'=>TRUE,
		),
		'end_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
			'listing'=>TRUE,
		),

		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'listing'=>TRUE,
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

		'parameters'=>array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'create_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'create_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
			'null' => TRUE,
		),
		'create_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null' => TRUE,
		),
		'modify_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'modify_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
			'null' => TRUE,
		),
		'modify_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null' => TRUE,
		),
	);
	
	var $default_values = array(
		'status'=>'1',
	);


	protected function selecting_options($options = false, $cache = false) {

		$locale_table = $this->locale_table;
		
		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_locale'])) {
			if (isset($options['_keyword_fields'])) {
				$options['_keyword_fields'][] = $locale_table . '.title';
				$options['_keyword_fields'][] = $locale_table . '.parameters';
			}
		}

		if(isset($options['_available_date']) && $options['_available_date']){
			$conds = array();

			$field_name = $this->_field($this->table . '.start_date',false,false,true);

			$conds[] = $field_name.' IS NULL';
			$conds[] = $field_name.' LIKE "0000%"';
			$conds[] = $field_name.' <= '.$this->db->escape($options['_available_date']);
			$this->_or($conds );

			$field_name = $this->_field($this->table . '.end_date',false,false,true);

			$conds = array();
			$conds[] = $field_name.' IS NULL';
			$conds[] = $field_name.' LIKE "0000%"';
			$conds[] = $field_name.' > '.$this->db->escape($options['_available_date']);
			$this->_or($conds );
		}

		parent::selecting_options($options, $cache);
		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_locale'])) {

			$prefix = isset($options['_with_locale_prefix']) ? $options['_with_locale_prefix'] : 'loc_';

			$this->db->select($this->table.'.*,' .
				$locale_table . '.title as '.$prefix.'title,' .
				$locale_table . '.parameters as '.$prefix.'parameters,' .
				$locale_table . '.locale as locale'
			);
			$this->db->join($locale_table,
				$this->_field($this->table . '.id',false,false,true) . ' = ' . $this->_field($this->locale_table . '.ref_id',false,false,true) . ' '
				. 'AND ' . $this->_field($this->table . '.is_live',false,false,true) . ' = ' . $this->_field($this->locale_table . '.is_live',false,false,true) . ' '
				. 'AND ' . $this->_field($this->locale_table . '.ref_table',false,false,true) . ' = \'' . $this->db->escape_str($this->table) . '\' ',
				'LEFT');
			
			$this->_where_match('locale', $options['_with_locale']);

		}
	}

	public function result_row($raw_row, $options= NULL){
		$row = parent::result_row($raw_row, $options);


		if(isset($row['end_date']) && substr($row['start_date'],0,4) == '0000')
			$row['start_date'] = '';

		if(isset($row['end_date']) && substr($row['end_date'],0,4) == '0000')
			$row['end_date'] = '';

		return $row;
	}
}
