<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Ph_post_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'ph_posts';
	
	var $default_values = array(
		'owner_type'=>'',
		'owner_id'=>'0',
		'section'=>'',
		'default_locale'=>'',
		
		'status'=>'1',

		'album_id'=>'',
		'cover_id'=>'',
		'parameters'=>'',

		'priority'=>0,

		'plain'=>'0',
		'title'=>'',
		'slug'=>'',
		'description'=>'',
		'content'=>'',
		'category_id'=>'',
		'publish_date'=>'',
		'start_date'=>NULL,
		'end_date'=>NULL,
	);

	var $locale_table = 'text_locales';

	var $section       = '';
	var $table_indexes = array(
		array('owner_type', 'owner_id'),
		array('is_live', 'status'),
		array('is_pushed'),
		array('section', 'slug'),
		array('priority'),
	);

	var $fields_details = array(
		'id' => array(
			'type'           => 'BIGINT',
			'constraint'     => 20,
			'pk'             => TRUE,
			'auto_increment' => TRUE
		),
		'owner_type' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'owner_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
		),
		'default_locale' => array(
			'type'       => 'VARCHAR',
			'constraint' => '5',
		),
		'slug' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'null'       => TRUE,
			'validate'=>'trim|required',
		),
		'section' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'validate'=>'trim|required',
		),
		'album_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'       => TRUE,
		),
		'cover_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'       => TRUE,
		),
		'category_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null'       => TRUE,
		),

		'title' => array(
			'type'       => 'VARCHAR',
			'constraint' => '200',
			'validate'=>'trim|required',
		),
		'description' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'content' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'parameters' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),

		'priority' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
			'default' => '0',
		),

		'plain' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'default'=>'0',
		),

		'start_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'end_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),

		'status' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'default'=>'1',
		),
		'lock_status' => array(
			'type'       => 'INT',
			'constraint' => 1,
			'default'    => '0',
		),
		'lock_reason' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),
		'lock_date' => array(
			'type' => 'DATETIME',
			'null' => TRUE,
		),
		'lock_by' => array(
			'type'       => 'VARCHAR',
			'constraint' => 40,
		),
		'lock_by_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
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

	public function install() {
		parent::install();

		if ($this->auto_install && !empty($this->tag_table) && !empty($this->tag_fields_details)) {
			$this->install_table($this->tag_table, array_keys($this->tag_fields_details), $this->tag_fields_details);
		}
		if ($this->auto_install && !empty($this->category_table) && !empty($this->category_fields_details)) {
			$this->install_table($this->category_table, array_keys($this->category_fields_details), $this->category_fields_details);
		}
	}

	public function uninstall() {
		parent::uninstall();
		if ($this->auto_install && !empty($this->tag_table) && !empty($this->perms_fields_details)) {
			$this->load->dbforge();
			$this->dbforge->drop_table($this->tag_table);
		}
		if ($this->auto_install && !empty($this->category_table) && !empty($this->category_fields_details)) {
			$this->load->dbforge();
			$this->dbforge->drop_table($this->category_table);
		}
	}

	protected function selecting_options($options = false, $cache = false) {

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
		// load locale text content (LEFT JOIN)
		if (!empty($options['_with_locale'])) {

			$prefix = isset($options['_with_locale_prefix']) ? $options['_with_locale_prefix'] : 'loc_';

			$this->db->select($this->table.'.*,' .
				$locale_table . '.title as '.$prefix.'title,' .
				$locale_table . '.content as '.$prefix.'content,' .
				$locale_table . '.description as '.$prefix.'description,' .
				$locale_table . '.parameters as '.$prefix.'parameters,' .
				$locale_table . '.status as '.$prefix.'status,' .
				$locale_table . '.cover_id as '.$prefix.'cover_id,' .
				$locale_table . '.locale as locale'
			);
			$this->db->join($locale_table,
				$this->_field($this->table . '.id',false,false,true) . ' = ' . $this->_field($this->locale_table . '.ref_id',false,false,true) . ' '
				. 'AND ' . $this->_field($this->table . '.is_live',false,false,true) . ' = ' . $this->_field($this->locale_table . '.is_live',false,false,true) . ' '
				. 'AND ' . $this->_field($this->locale_table . '.ref_table',false,false,true) . ' = \'' . $this->db->escape_str($this->table) . '\' ',
				'LEFT');
			$this->_or(array('locale IS NULL', $this->_array_to_in_case('locale',$options['_with_locale'] )));

		}

		// check the date for this record
		if(!empty($options['_date_available'])){

			//$this->db->where('(start_date IS NULL OR start_date = \'\' OR start_date <= \''.$this->db->escape_str($options['_date_available']).'\')');
			//$this->db->where('(end_date IS NULL OR end_date = \'\' OR end_date >= \''.$this->db->escape_str($options['_date_available']).'\')');
			$this->db->where('(publish_date IS NULL OR publish_date = \'\' OR publish_date <= \''.$this->db->escape_str($options['_date_available']).'\')');
		}

		if (isset($options['section'])) {
			$this->_where_match('section', $options['section']);
		} elseif(!empty($this->section)){
			$this->_where_match('section', $this->section);
		}

		if (!empty($options['_with_relationship']['fields']) && is_array($options['_with_relationship']['fields'])) {
		
			$relationship_model_name = 'relationships';

			$q = array();
			$q[] = ($this->table . '.section').' = ' . $this->_field($relationship_model_name. '.section') ;
			$q[] = $this->_field($relationship_model_name. '.ref_table').' = '.$this->db->escape_str( $this->table ) ;
			$q[] = ($this->table . '.id').' = ' . $this->_field($relationship_model_name. '.ref_id') ;
			$q[] = $this->_field($this->table . '.is_live',false,false,true).' = ' . $this->_field($relationship_model_name . '.is_live',false,false,true);

			if(!empty($options['_with_relationship']['fields'])&& is_array($options['_with_tag']['fields'])){

				foreach($options['_with_relationship']['fields'] as $_c_field => $_val){
					$_field_name   =$this->_field($relationship_model_name.'.'.$_c_field);
					$this->_where_match($_c_field, $_val);
				}
			}
			$this->db->join($relationship_model_name, implode(' AND ', $q));
		}

		if (!empty($options['_with_tag'])) {
			$q = array();
			$q[] = ($this->table . '.id').' = ' . $this->_field($this->tag_table. '.post_id') ;
			$q[] = $this->_field($this->table . '.is_live',false,false,true).' = ' . $this->_field($this->tag_table . '.is_live',false,false,true);

			if(empty($options['_select'])){
				$this->db->distinct();
				$this->db->select($this->table.'.*');
			}
			
			if(!empty($options['_with_tag']['fields'])&& is_array($options['_with_tag']['fields'])){

				foreach($options['_with_tag']['fields'] as $_c_field => $_val){
					$_field_name   =$this->_field($this->tag_table.'.'.$_c_field);
					$this->_where_match($_c_field, $_val);
				}

		
			}else{
				$this->_where_match($this->_field($this->tag_table.'.tag_id'), $options['_with_tag']);
			}
			$this->db->join($this->tag_table, implode(' AND ', $q));
		}

		if (!empty($options['_with_category'])) {
			$q = array();
			$q[] = ($this->table . '.id').' = ' . $this->_field($this->category_table. '.post_id') ;
			$q[] = $this->_field($this->table . '.is_live',false,false,true).' = ' . $this->_field($this->category_table . '.is_live',false,false,true);


			if(empty($options['_select'])){
				$this->db->distinct();
				$this->db->select($this->table.'.*');
			}

			if(!empty($options['_with_category']['fields']) && is_array($options['_with_category']['fields'])){

				foreach($options['_with_category']['fields'] as $_c_field => $_val){
					$_field_name   =$this->_field($this->category_table.'.'.$_c_field);
					$this->_where_match($_c_field, $_val);
				}
		
			}else{
				$this->_where_match( $this->_field($this->category_table.'.category_id') , $options['_with_category']);
			}
			$this->db->join($this->category_table, implode(' AND ', $q));
		}
	}

	protected function save_pre_data_attr(&$sql_data, $is_insert = true, $options = false) {

		if ($is_insert && !isset($sql_data['section'])) {
			if(!empty($this->section))
				$sql_data['section'] = $this->section;
		}

		parent::save_pre_data_attr($sql_data, $is_insert, $options);
	}

	protected function post_save_action($id) {
		cache_remove('ph/post/' . $this->section . '/' . $id . '/*');
		cache_remove('ph/post/' . $this->section . '/' . $id);
	}


	public function result_row($row, $options = false) {
		$row = parent::result_row($row, $options);
		if(empty($row['loc_title']) && !empty($row['title'])){
			$row['loc_title'] = $row['title'];
		}
		if(empty($row['loc_description'])){
			$row['loc_description'] = !empty($row['description']) ? $row['description'] : NULL;
		}
		if(empty($row['loc_content'])){
			$row['loc_content'] = !empty($row['content']) ?  $row['content'] : NULL;
		}
		if(empty($row['loc_cover_id'])){
			$row['loc_cover_id'] = !empty($row['cover_id']) ?  $row['cover_id'] : NULL;
		}
		if(empty($row['loc_parameters']) ){
			$row['loc_parameters'] = !empty($row['parameters']) ? $row['parameters'] : NULL;
		}elseif(isset($row['loc_parameters'])){
			$row ['loc_parameters'] = $this->decode_parameters($row['loc_parameters']);
		}
		if(empty($row['loc_status'])){
			$row['loc_status'] = !empty($row['status']) ?  $row['status'] : NULL;
		}

		return $row;
	}

	/***** Handling assoicated table: tags *****/
	var $tag_table          = 'ph_posts_tags';
	var $tag_fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE,
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
		),

		'post_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
		),
		'tag_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null'       => TRUE,
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

	public function clear_tags($record_id, $is_live = 1) {
		$this->db->where_in('post_id', $record_id);
		$this->db->where_in('is_live', $is_live);
		$this->db->delete($this->tag_table);
	}

	public function set_tags($record_id, $tag_ids, $is_live = 1) {
		$old_tag_ids = $this->get_tags($record_id);

		$remove_ids = array();
		$insert_ids = array();
		if (is_array($old_tag_ids) && count($old_tag_ids) > 0) {
			foreach ($old_tag_ids as $idx => $tag_id) {
				if (!is_array($tag_ids) || count($tag_ids) < 1 || !in_array($tag_id, $tag_ids)) {
					$remove_ids[] = $tag_id;
				}
			}
		}
		if (is_array($tag_ids) && count($tag_ids) > 0) {
			foreach ($tag_ids as $idx => $tag_id) {
				if (!is_array($old_tag_ids) || count($old_tag_ids) < 1 || !in_array($tag_id, $old_tag_ids)) {
					$insert_ids[] = $tag_id;
				}
			}
		}

		if (is_array($remove_ids) && count($remove_ids) > 0) {
			$this->db->where('post_id', $record_id);
			$this->db->where('is_live', $is_live);
			$this->db->where_in('tag_id', $remove_ids);
			$this->db->delete($this->tag_table);
		}

		if (is_array($insert_ids) && count($insert_ids) > 0) {
			$this->load->helper('guid');
			foreach ($insert_ids as $idx => $tag_id) {
				$this->db->insert($this->tag_table, array(
						'post_id'     => $record_id,
						'tag_id'      => $tag_id,
						'is_live'     => $is_live,
						'create_date' => date('Y-m-d H:i:s'),
						'id'          => guid(),
					));
			}
		}
	}

	public function get_tags($record_id, $is_live = 1) {
		$this->db->where('post_id', $record_id);
		$this->db->where('is_live', $is_live);
		$query = $this->db->get($this->tag_table);

		$data = array();
		if ($query) {
			$result = $query->result_array();
			foreach ($result as $idx => $row) {
				$data[] = $row['tag_id'];
			}
		}
		return $data;
	}

	public function get_id_by_tags($tag_ids, $is_live = 1) {
		if (is_array($tag_ids)) {
			$this->db->where_in('tag_id', $tag_ids);
		} else {
			$this->db->where('tag_id', $tag_ids);
		}
		$this->db->where('is_live', $is_live);
		$query = $this->db->get($this->tag_table);

		$data = array();
		if ($query) {
			$result = $query->result_array();
			foreach ($result as $idx => $row) {
				$data[] = $row['post_id'];
			}
		}
		return $data;

	}

	public function remove_tags($tag_ids, $is_live='1'){
		if (is_array($tag_ids)) {
			$this->db->where_in('tag_id', $tag_ids);
		} else {
			$this->db->where('tag_id', $tag_ids);
		}
		$this->db->where('is_live', $is_live);
		$this->db->delete($this->tag_table);
	}

	/***** Handling assoicated table: categories *****/
	var $category_table          = 'ph_posts_categories';
	var $category_fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE,
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
		),

		'post_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null'       => TRUE,
		),
		'category_id' => array(
			'type'       => 'BIGINT',
			'constraint' => 20,
			'null'       => TRUE,
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

	public function clear_categories($record_id, $is_live = 1) {
		$this->db->where_in('post_id', $record_id);
		$this->db->where_in('is_live', $is_live);
		$this->db->delete($this->category_table);
	}

	public function set_categories($record_id, $category_ids, $is_live = 1) {
		$old_category_ids = array();
		$this->clear_categories($record_id,$is_live);

		$remove_ids = array();
		$insert_ids = array();
		if (is_array($old_category_ids) && count($old_category_ids) > 0) {
			foreach ($old_category_ids as $idx => $category_id) {
				if (!is_array($category_ids) || count($category_ids) < 1 || !in_array($category_id, $category_ids)) {
					$remove_ids[] = $category_id;
				}
			}
		}
		if (is_array($category_ids) && count($category_ids) > 0) {
			foreach ($category_ids as $idx => $category_id) {
				if (!is_array($old_category_ids) || count($old_category_ids) < 1 || !in_array($category_id, $old_category_ids)) {
					$insert_ids[] = $category_id;
				}
			}
		}

		if (is_array($remove_ids) && count($remove_ids) > 0) {
			$this->db->where('post_id', $record_id);
			$this->db->where('is_live', $is_live);
			$this->db->where_in('category_id', $remove_ids);
			$this->db->delete($this->category_table);
		}

		if (is_array($insert_ids) && count($insert_ids) > 0) {
			$this->load->helper('guid');
			foreach ($insert_ids as $idx => $category_id) {
				$this->db->insert($this->category_table, array(
					'post_id'     => $record_id,
					'category_id' => $category_id,
					'is_live'     => $is_live,
					'create_date' => date('Y-m-d H:i:s'),
					'id'          => guid(),
				));
			}
		}
	}

	public function get_categories($record_id, $is_live = 1) {
		$this->db->where('post_id', $record_id);
		$this->db->where('is_live', $is_live);
		$query = $this->db->get($this->category_table);

		$data = array();
		if ($query) {
			$result = $query->result_array();
			foreach ($result as $idx => $row) {
				$data[] = $row['category_id'];
			}
		}
		return $data;
	}

	public function get_id_by_categories($category_ids, $is_live = 1) {
		if (is_array($category_ids)) {
			$this->db->where_in('category_id', $category_ids);
		} else {
			$this->db->where('category_id', $category_ids);
		}
		$this->db->where('is_live', $is_live);
		$query = $this->db->get($this->category_table);

		$data = array();
		if ($query) {
			$result = $query->result_array();
			foreach ($result as $idx => $row) {
				$data[] = $row['post_id'];
			}
		}
		return $data;

	}

	public function remove_categories($category_ids, $is_live='1'){
		if (is_array($category_ids)) {
			$this->db->where_in('category_id', $category_ids);
		} else {
			$this->db->where('category_id', $category_ids);
		}
		$this->db->where('is_live', $is_live);
		$this->db->delete($this->category_table);
	}
}
