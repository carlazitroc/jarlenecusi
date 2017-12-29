<?php if (!defined('BASEPATH')) die('No direct script access allowed');

use Dynamotor\Helpers\PostHelper;

class Pref_model extends \Dynamotor\Core\HC_Model
{
	var $table = 'prefs';
	
	var $_cached_items = array();
	var $fields = array('id','sys_name','data','scope');
	var $fields_details = array(
		'id'		=> array(
			'type'  => 'VARCHAR',
			'pk'    => TRUE,
			'constraint' => 36, 
		),
		'scope'	=> array(
			'type' => 'VARCHAR',
			'constraint' => 40, 
			'default'=>'default',
			'validate'=>'trim|required',
		),

		'sys_name'	=> array(
			'type' => 'VARCHAR',
			'constraint' => 60, 
			'validate'=>'trim|required',
		),
		'data'		=> array(
			'type' => 'TEXT',
			'null' => TRUE,
		),
    );
	var $table_indexes = array(
		array('sys_name', 'scope'),
	);

    var $use_guid = true;
    var $auto_increment = false;

    protected $scope = 'default';
    protected $_cache_key = 'prefs/default';
	
	function __construct(){
		parent::__construct();
		
		$this->load->helper('cache');
		$this->load_cache();
	}

	function scope(){return $this->scope;}

	function set_scope($scope='default'){

		$this->scope = $scope;
		$this->_cache_key = 'prefs/'.$scope;
		
		//$this->items(TRUE);
		$this->load_cache();
	}

	function load_cache(){
			
		$this->_cached_items = cache_get($this->_cache_key);
		if(!empty($this->_cached_items)){
			$this-> items(TRUE);
			$this->save_cache();
		}
	}
	
	function rebuild_cache(){
		
		cache_remove($this->_cache_key);
		cache_remove($this->_cache_key.'/*');
		
		cache_set($this->_cache_key,$this->_cached_items);
	}

	function save_cache(){
		cache_set($this->_cache_key, $this->_cached_items);
	}
	
	function set_item($key, $val = NULL){
		$options = array('sys_name'=>$key,'scope'=>$this->scope);

		$row = $this->read($options);
		
		$this->_cached_items[$key] = $val;

		if(isset($row['sys_name'])){
			
			if($val === NULL){
				$this->db->where('id',$row['id']);
				$this->db->delete($this->table);
			}else{
				$this->db->where('id',$row['id']);
				$this->db->update($this->table, array(
					'data'=> $this->encode_parameters($val),
				));
			}
		}else{
			$this->load->helper('guid');

			$new_data = array(
				'sys_name'=> $key,
				'data'=> $this->encode_parameters($val),
				'scope'=>$this->scope,
				'id'=>guid(),
			);
			if(!empty($locale)) {
				$new_data['locale'] = $locale;
			}

			$this->db->insert($this->table, $new_data);
		}
	}
	
	function items($reload = false){
		if(!$reload || !empty($this->_cached_items))
			return $this->_cached_items;

		$options = array('_field_based'=>'sys_name','scope'=>$this->scope);

		$items = $this->find($options);

		$data = array();
		if(is_array($items)){
			foreach($items as $key => $row){
				$data[$key] = $this->decode_parameters($row['data']);
			}
		}
		$this->_cached_items = $data;
		return $data;
	}
	
	function item($key,$use_cache=true){
		
		if($use_cache && isset($this->_cached_items[$key]))
			return ($this->_cached_items[$key]);
		
		$options = array('sys_name'=>$key, 'scope'=>$this->scope);

		$row = $this->read($options);
		if(isset($row['sys_name'])){
			
			$this->_cached_items[$row['sys_name']] = $this->decode_parameters($row['data']);

			$this->save_cache();

			return $this->_cached_items[$row['sys_name']];
		}
		return NULL;
	}

	function locale_item($locale, $key, $use_cache=true){
		$data = $this->item($key,$use_cache);

		if(is_array($data)){
			return isset($data[$locale]) ? $data[$locale] : NULL;
		}
		return NULL;
	}
}
