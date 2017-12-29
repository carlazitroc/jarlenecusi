<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Album_photo_model extends \Dynamotor\Core\HC_Model {
	var $table  = 'albums_photos';
	var $table_indexes = array(
		array('is_live','status'),
	);
	var $fields_details = array(
		'id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'pk'         => TRUE
		),
		'is_live' => array(
			'type'       => 'INT',
			'pk'         => TRUE,
			'constraint' => 1,
			'default'=>'0',
		),
		'is_pushed' => array(
			'type'       => 'INT',
			'constraint' => 1,
		),
		'last_pushed' => array(
			'type'       => 'DATETIME',
			'null'		 => TRUE,
		),
		'album_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'       => TRUE,
		),
		'main_file_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'       => TRUE,
		),
		'main_url' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'       => TRUE,
		),
		'thumb_file_id' => array(
			'type'       => 'VARCHAR',
			'constraint' => 36,
			'null'       => TRUE,
		),
		'thumb_url' => array(
			'type'       => 'VARCHAR',
			'constraint' => 200,
			'null'       => TRUE,
		),
		'croparea' => array(
			'type' => 'TEXT',
			'null' => TRUE,
		),
		'sequence' => array(
			'type'       => 'INT',
			'constraint' => 11,
		),
		'parameters' => array(
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

	var $photo_table = 'files';

	var $auto_increment = false;
	var $use_guid       = true;

	function selecting_options($options = false, $cache = false) {
		parent::selecting_options($options, $cache);
		if (isset($options['keyword'])) {
			$fields = array('main_url', 'thumb_url', 'parameters');
			$this->_like_fields($fields, $options['keyword']);
		}

	}

	function get_relationship($options = false) {
		$this->db->select($this->photo_table . '.*,' . $this->table . '.*');
		$this->db->join($this->photo_table, $this->photo_table . '.id = main_file_id');
		$this->selecting_options($options);
		$this->db->order_by('sequence asc');

		$query = $this->db->get();
		if (!$query) {
			log_message('error', 'Album_photo_model/get_photos: ' . $this->db->last_query());
			return NULL;
		}
		log_message('debug', 'Album_photo_model/get_photos: ' . $this->db->last_query());
		$result = $query->result_array();
		$photos = array();
		if (count($result) > 0) {
			foreach ($result as $idx => $row) {
				$photos[] = $this->result_row($row);
			}
		}
		return $photos;
	}
	function get_relationship_ids($album_id) {

		$this->db->select('id');
		$this->db->where('album_id', $album_id);
		$this->db->order_by('sequence asc');

		$query = $this->db->get($this->table);
		if (!$query) {
			return NULL;
		}
		$result    = $query->result_array();
		$photo_ids = array();
		if (count($result) > 0) {
			foreach ($result as $idx => $row) {
				$photo_ids[] = $row['id'];
			}
		}
		return $photo_ids;
	}
	
}
