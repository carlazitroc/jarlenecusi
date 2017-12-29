<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Ph_tag_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'ph_tags';

	var $default_values = array(
		'owner_type'=>'',
		'owner_id'=>'0',
		'section'=>'',
		'default_locale'=>'',
		
		'status'=>'1',
		'plain'=>'0',
		'title'=>'',
		'slug'=>'',
		'description'=>'',
		'content'=>'',
		'category_id'=>'',
		'publish_date'=>NULL,
	);

	var $locale_table = 'text_locales';

	var $section       = '';
	var $table_indexes = array(
		array('owner_type', 'owner_id'),
		array('is_live', 'status'),
		array('is_pushed'),
		array('section', 'slug'),
	);

	var $fields_details = array(
		'id' => array(
			'type'            => 'BIGINT',
			'constraint'      => 20,
			'pk'              => TRUE,
			'auto_increment' => TRUE
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
		),
		'default_locale' => array(
			'type'       => 'VARCHAR',
			'constraint' => '5',
		),

		'owner_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'owner_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),

		'section' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'validate'=>'trim|required',
		),

		'slug' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'null'       => TRUE,
		),
		'title' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'validate'=>'trim|required',
		),

		'parameters' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'status' => array(
			'type'       => 'INT',
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

		'publish_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
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
	protected function save_pre_data_attr(&$sql_data, $is_insert = true, $options = false) {

		if ($is_insert && !isset($sql_data['section'])) {
			if(!empty($this->section))
				$sql_data['section'] = $this->section;
		}

		parent::save_pre_data_attr($sql_data, $is_insert, $options);
	}

	function selecting_options($options = false, $cache = false) {
		$locale_table = $this->locale_table;
		

		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_locale'])) {
			if (isset($options['_keyword_fields'])) {
				$options['_keyword_fields'][] = $locale_table . '.title';
				$options['_keyword_fields'][] = $locale_table . '.content';
				$options['_keyword_fields'][] = $locale_table . '.description';
				//$options['_keyword_fields'][] = $locale_table . '.parameters';
			}
		}

		parent::selecting_options($options, $cache);

		// check the date for this record
		if(!empty($options['_date_available'])){
			$this->db->where('(publish_date IS NULL OR publish_date = \'\' OR publish_date <= \''.$this->db->escape_str($options['_date_available']).'\')');
		}

		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_locale'])) {
			$prefix = isset($options['_with_locale_prefix']) ? $options['_with_locale_prefix'] : 'loc_';

			$this->db->select($this->table.'.*,' .
				$locale_table . '.title as '.$prefix.'title,' .
				$locale_table . '.content as '.$prefix.'content,' .
				$locale_table . '.description as '.$prefix.'description,' .
				$locale_table . '.parameters as '.$prefix.'parameters,' .
				$locale_table . '.status as '.$prefix.'status,' .
				$locale_table . '.locale as locale'
			);
			$this->db->join($locale_table,
				$this->_field($this->table . '.id',false,false,true) . ' = ' . $this->_field($this->locale_table . '.ref_id',false,false,true) . ' '
				. 'AND ' . $this->_field($this->table . '.is_live',false,false,true) . ' = ' . $this->_field($this->locale_table . '.is_live',false,false,true) . ' '
				. 'AND ' . $this->_field($this->locale_table . '.ref_table',false,false,true) . ' = \'' . $this->db->escape_str($this->table) . '\' ',
				'LEFT');
			$this->_where_match('locale', $options['_with_locale']);

		}

		if (isset($options['section'])) {
			$this->_where_match('section', $options['section']);
		} elseif(!empty($this->section)){
			$this->_where_match('section', $this->section);
		}

	}

	protected function post_save_action($id) {
		cache_remove('ph/tag/' . $this->section . '/' . $id . '/*');
		cache_remove('ph/tag/' . $this->section . '/' . $id);
	}


	public function result_row($row, $options = false) {
		$row = parent::result_row($row, $options);
		if(empty($row['loc_title']) && !empty($row['title'])){
			$row['loc_title'] = $row['title'];
		}
		if(empty($row['loc_description']) && !empty($row['description'])){
			$row['loc_description'] = $row['description'];
		}
		if(empty($row['loc_content']) && !empty($row['content'])){
			$row['loc_content'] = $row['content'];
		}
		if(empty($row['loc_parameters']) ){
			$row['loc_parameters'] = !empty($row['parameters']) ? $row['parameters'] : NULL;
		}elseif(isset($row['loc_parameters'])){
			$row ['loc_parameters'] = $this->decode_parameters($row['loc_parameters']);
		}

		return $row;
	}
}
