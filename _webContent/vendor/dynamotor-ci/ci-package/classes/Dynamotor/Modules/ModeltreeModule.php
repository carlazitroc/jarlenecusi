<?php

namespace Dynamotor\Modules{

class ModeltreeModule extends  \Dynamotor\Core\HC_Module
{
	private $table = 'categories';
	public $parent_id_key = 'parent_id';
	public $id_key = 'id';
	public $pk = 'id';

	private $model ;

	private $cfg = array(
		'default_options'=>array('is_live'=>'1','status'=>'1'),
	);

	function __construct($cfg = NULL){

		if(is_array($cfg)){
			foreach($cfg as $key => $val)
				if(is_string($key))
					$this->cfg[$key] = $val;
		}
	}

	public function config($key){
		if(isset($this->cfg[$key])){
			return $this->cfg[$key];
		}
		return NULL;
	}

	public function set_model (&$model, $id_key='id', $parent_id_key = 'parent_id', $pk = 'id'){
		$this->model = $model;
		$this->id_key = $id_key;
		$this->parent_id_key = $parent_id_key;
		$this->pk = $pk;
		$this->localized = $model->localized;
	}

	public function parse_path($path, $options = NULL){
		if(substr($path,0,1) == '/') $path = substr($path, 1);
		if(substr($path,-1,1) == '/') $path = substr($path, 0, strlen($path)-1);

		$segments = explode('/', $path);

		return $this->parse_segments($segments, 0, -1, $options);
	}

	public function parse_segments($segments, $offset=0, $length = -1, $options = NULL){


		if(empty($options)){
			$options = $this->config('default_options');
		}
		$result = new ModeltreeResult($this);

		if ($length < 0) {
			$length = count($segments) - $offset;
		}

		for ($i = $offset; $i <= $total; $i++) {
			$deep++;
			if (!isset($segments[$i])) {
				break;
			}
			$_seg = $seguments[$i];
			$_options = $options;

			// if upper level is exist
			if($result->get_last_node() != NULL ){
				$_options[ $this->parent_id_key ] = $result->get_last()->get_id();

			// use empty string to represent root node.
			}else{
				$_options[$this->parent_id_key] = '';
			}

			// read 1 entry from db according to query options.
			$row = $this->read($_seg, $_options);

			if(!empty($row))
				$result->push($row);
		}

		return $result;
	}

	function get_path($mapping, $options = NULL){
		if(empty($options)){
			$options = $this->config('default_options');
		}
		$result = new ModeltreeResult($this);

		$parent_mapping = $mapping;

		while(!empty($parent_mapping)){
			// read 1 entry from db according to query options.
			$row = $this->read($parent_mapping, $options);

			if(!empty($row))
				$result->head($row);
		}

		return $result;
	}

	function get_children($mapping, $options = NULL){
		if(empty($options)){
			$options = $this->config('default_options');
		}
	}

	function read($mapping, $options = NULL){
		if(empty($options)){
			$options = $this->config('default_options');
		}
		$_options = $options;
		$_options[ '_mapping' ] = $mapping;

		// read 1 entry from db according to query options.
		$row = $this->model->read($_options);

		if (empty($row[ $this->id_key ])) {
			throw new ModeltreeException("Node's identify does not found in returned entry or entry does not exist.");
			return;
		}

		return $row;
	}

}

class ModeltreeException extends Exception
{

}

class ModeltreeResult {

	private $_helper;

	// @return list of nodes
	private $_nodes;

	// @return last node
	private $_last_node;

	// @return list of _mapping
	private $_mappings;
	
	// @return list of id
	private $_ids;

	private $_counter = 0;

	function __construct($helper){
		$this->_helper = $helper;

		$this->_nodes = array();
		$this->_paths = array();
		$this->_ids = array();
	}

	public function get_nodes(){return $this->_nodes;}

	public function get_mappings(){return $this->_mappings;}

	public function get_ids(){return $this->_ids;}

	public function get_last(){
		return $this->_last_node;
	}

	public function at($offset){
		if(isset($this->_nodes[ $offset ])){
			return $this->_nodes[$offset];
		}
		return NULL;
	}

	public function head($row){
		return $this->insert_at($row,0);
	}

	public function insert_at($row, $offset=0){

		if($offset > $this->_counter){
			$offset = $this->_counter;
		}

		$node = new ModeltreeNode($this->_helper, $row);

		// replace old one to this node
		$old_node = $this->at($offset);
		if(!empty($old_node)){
			$parent_node = $old_node->get_parent();
			
			$node->set_parent( $parent_node );

			$old_node->set_parent($node);
		}
		// we dont need to do anything if there are no nodes.

		array_splice($this->_nodes, $offset, 0, $node);
		array_splice($this->_mappings, $offset, 0, $node->get_mapping() );
		array_splice($this->_ids, $offset, 0, $node->get_id() );

		$this->_counter ++;

		if(!empty($this->_nodes[ $this->_counter - 1 ]) && $this->_nodes[ $this->_counter - 1 ]->equals($node)){
			$this->_last_node = $node;
		}

		return $this;
	}

	public function push($row){

		$node = new ModeltreeNode($this->_helper, $row);

		if(!empty($this->_last_node))
			$node->set_parent($this->_last_node);

		$this->_nodes[] = $node;
		$this->_mappings[] = $node->get_mapping();
		$this->_ids[] = $node->get_id();


		$this->_counter ++;

		$this->_last_node = $node;


		return $this;
	}
}

class ModeltreeNode {

	private $_helper = NULL;

	private $_row = NULL;

	private $_id = NULL;

	private $_parent = NULL;

	function __construct($helper, $row){
		$this->_helper = $helper;
		$this->_row = $row;
		$this->_id = $row[ $helper->id_key ];
	}

	function equals($node){
		if(is_string($node) && $node == $this->_id)
			return TRUE;
		if(is_object($node) && $node->get_id() == $this->_id)
			return TRUE;
		return FALSE;
	}

	function get_row(){
		return $this->_row;
	}

	function get_id(){
		return $this->_id;
	}

	function get_mapping(){
		return !empty($this->_row[ '_mapping' ]) ? $this->_row['_mapping'] : $this->_id;
	}

	function set_parent($node){
		$this->_parent = $node;
	}

	function get_parent(){
		return $this->_parent;
	}

	function get_ids_path(){
		$p = array();
		$parent = $this->parent;
		while(!empty($parent)){

			$p[] = $parent->id;

			$parent = $parent->get_parent();
		}

		return $p;
	}
}
}