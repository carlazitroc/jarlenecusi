<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Album extends MY_Controller
{
	
	function __construct(){
		parent::__construct();
		
		$this->load->model(array('album_model','album_photo_model'));
	}
	
	function _remap(){
		if($this->_restrict(null)){
			return;
		}
		
		// one level : 2
		// second level : 3
		$offset = 2;
		$s1 = $this->uri->segment($offset);
		$s2 = $this->uri->segment($offset+1);
		
		//print_r($this->uri->segments);
		
		if(in_array($s1, array('','index','selector'))){
			return $this->_list();
		}
		if(in_array($s1, array('search'))){
			return $this->_search();
		}
		if(in_array($s1, array('batch'))){
			return $this->_batch();
		}
		if(in_array($s1, array('save'))){
			return $this->_save();
		}
		if(in_array($s1, array('remove'))){
			return $this->_remove();
		}
		if(in_array($s1, array('add'))){
			return $this->_editor();
		}
		if(preg_match("/^[\w\-_\.]+$/",$s1) || strlen($s1) == 36){
			if($s2 == 'picture'){
				return $this->_picture($s1);
			}
			if($s2 == 'edit')
				return $this->_editor($s1);
			if($s2 == 'get')
				return $this->_get($s1);
			if(empty($s2))
				return $this->_view($s1);
		}
		
		return $this->_show_404();
	}
	
	function _mapping_row($row){
		
		$photos = $this->album_photo_model->find(array(
			'is_live'=>isset($row['is_live']) ? $row['is_live'] : '0',
			'album_id'=> $row['id'],
			'_order_by'=>array('sequence'=>'asc')));
		$photo_row = NULL;
		if(count($photos)>0){
			$photo_row = $photos[0];
		}
		
		$new_row = array();

		$new_row['id'] = $row['id'];
		$new_row['is_image'] = count($photos)>0;

		if($new_row['is_image']){
			$new_row['image'] = array(
				'thumbnail'=>site_url('file/'.$photo_row['main_file_id'].'/picture?width=200&height=200'),
				'large'=>site_url('file/'.$photo_row['main_file_id'].'/picture?size=large'),
				'source'=>site_url('file/'.$photo_row['main_file_id'].'/picture?size=source'),
			);
		}
		$new_row['photos'] = array();
		if(count($photos)>0){
			foreach($photos as $idx => $r_row){
				$new_row['photos'][] = array(
					'id'=> $r_row['id'],
					'sequence'=> $r_row['sequence'],
					'is_live'=> $r_row['is_live'],
					'file_id'=>$r_row['main_file_id'],
					'is_image'=>true,
					'image'=> array(
						'thumbnail'=>site_url('file/'.$r_row['main_file_id'].'/picture?size=thumbnail'),
						'large'=>site_url('file/'.$r_row['main_file_id'].'/picture?size=large'),
						'source'=>site_url('file/'.$r_row['main_file_id'].'/picture?size=source'),
					),
					'croparea'=> isset($r_row['mail_file_croparea']) ? $r_row['mail_file_croparea']  : '',
					'parameters'=> $r_row['parameters'],
				);
			}
		}

		$new_row['create_date'] = $row['create_date'];
		$new_row['create_by'] = $row['create_by'];
		$new_row['create_by_id'] = $row['create_by_id'];

		
		return $new_row;
	}
	
	
	function _list(){
		
		if($this->_restrict()){
			return;
		}
		
		$vals = array();
		
		$this->_render('album/item_index',$vals);
	}
	
	function _view($record_id=false){
		return $this->_show_404();
	}
	
	function _get($record_id=false){
		
		if($this->_restrict())return;
		if(empty($record_id))
			return $this->_show_404();
		
		
		$record = $this->album_model->read(array('id'=>$record_id));
		if(empty($record['id'])){
			return $this->_show_404();
		}
		
		return $this->_api($this->_mapping_row($record));
	}
	
	function _editor($record_id=false){
		
		if($this->_restrict()){
			return;
		}
		
		
		
		$vals = array();
		
		$vals['data'] = $this->album_model->new_default_values();
		
		if(!empty($record_id)){
			
			$record = $this->album_model->read(array('id'=>$record_id ,'is_live'=>'0'));
			
			if(!empty($record['id'])){
				$vals['data'] = $record;
				$row = $this->_mapping_row($record);

				$vals['photos'] = $row['photos'];
			}
		}
		 
		if($this->uri->is_extension('js'))
			return $this->_render('album/item_editor.js',$vals);
		
		if($this->_is_ext('html'))
			return $this->_render('album/item_editor',$vals);

		return $this->_show_404('extension_not_matched');
	}
	
	function _search(){
		
		if($this->_restrict())return;
		
		if(!$this->_is_ext('data')){ 
			return $this->_show_404('extension_not_matched');
		}
		
		$vals = array();
		
		$direction = 'asc';
		$sort = 'priority';
		$start = 0;
		$limit = 50;
		
		if($this->input->get('direction')!==false){
			$direction = $this->input->get('direction');
			$paginator->params['direction'] =$this->input->get('direction');
		}
		if($this->input->get('sort')!==false){
			$sort = $this->input->get('sort');
		}
		
		if($this->input->get('start')!==false){
			$start = $this->input->get('start');
		}
		if($this->input->get('limit')!==false){
			$limit = $this->input->get('limit');
		}
			
		if($this->input->get('page')!==FALSE){
			$start = ($this->input->get('page') - 1 ) * $limit;
		}
		
		if($this->input->get('q')!=false && $this->input->get('q')!=''){
			$options['_keyword'] = $this->input->get('q');
			$options['_keyword_fields'] = array('id','title');
		}
		
		if($this->input->get('id')!=''){
			$options['id'] = $this->input->get('id');
			if(empty($options['id'])) unset($options['id']);
		}
		
		if($start<0) $start = 0;
		if($limit < 5) $limit = 10;
		elseif($limit%5 != 0) $limit = 10; 
		
		if(!in_array($sort,array('id', 'create_date','modify_date'))) $sort = 'create_date';
		if(strtolower($direction) != 'desc') $direction = 'asc';
		
		$options['_order_by'] = array($sort=>$direction,$this->album_model->table.'.create_date'=>'desc');
		$options['is_live'] = '0';
		
		$result = $this->album_model->find_paged($start,$limit,$options,false);
		
		
		$vals['data'] = array();
		$vals['paging'] = array(
			'offset'=>0,
			'total'=>0,
			'limit'=>0,
			'page'=>0,
			'total_page'=>0,
		);

		if(isset($result['data'])){
			
			$data = array();
			foreach($result['data'] as $idx => $row){
				$new_row = $this->_mapping_row($row);
				$new_row['_index'] = $result['index_from']  + $idx;
				$data[] = $new_row;
			}
			
			$vals['paging']['offset'] = $result['index_from'];
			$vals['paging']['total'] = $result['total_record'];
			$vals['paging']['limit'] = $result['limit'];
			$vals['paging']['page'] = $result['page'];
			$vals['paging']['total_page'] = $result['total_page'];
			$vals['data'] = $data;
		}
		
		return $this->_api($vals);
	}
	
	
	function _batch(){
		
		if($this->_restrict())return;
		
		$vals = array();
		return $this->_api($vals);
	}
	
	function _save($id=NULL){
		
		if($this->_restrict())return;
		$vals = array();
		
		$fields = array();
		$success = true;
		$messages = array();
		$data = array();
		
		if(empty($id))
			$id = $this->input->post('id');
		
		$record = !empty( $id )? $this->album_model->read(array('is_live'=>'0', 'id'=>$id)) : NULL;
		if($id && (!isset($record['id']) || $record['id'] != $id)){
			$id = NULL;
			//return $this->_show_404();
		}
		
		foreach($this->album_model->default_values as $key=>$defVal){
			$data[$key] = $defVal;
			if(isset($record[$key])) 
				$data[$key] = $record[$key];
			if($this->input->post($key)!==false) 
				$data[$key] = $this->input->post($key);
		}
		
		$edit_info =$this->_get_editor_info();
		
		$success = true;	
			
		if($success){
			//$locale = $this->lang->locale();

			if(!$id){
				$data['is_live'] = '0';
				$result = $this->album_model->save($data, NULL, $edit_info);
				$result['action'] = 'add';
			}else{
				unset($data['id']);
				$result = $this->album_model->save($data,array('id'=>$id,'is_live'=>'0'), $edit_info);
				$result['action'] = 'edit';
			}

			$id = $result['id'];
			//$messages['sqls'][]= $this->db->last_query();
			
			$cell_ids = $this->input->post('cell_ids');
			$cell_object_ids = $this->input->post('cell_object_ids');
			$cell_params = $this->input->post('cell_params');
			$cell_files = $this->input->post('cell_files');
			
			// get existing images
			if($result['action'] == 'edit'){
				$this->album_photo_model->delete(array(
					'album_id'=>$id,
					'is_live'=>'0',
				));
			}
			
			$num_photo = 0;

			if(is_array($cell_files)){
				foreach($cell_files as $idx => $main_file_id){
					$cell_param = NULL;
					try{
						$cell_param = isset($cell_params[$idx]) ? json_decode($cell_params[$idx]) : NULL;
					}catch(Exception $cell_exp){}

					$cell_row = array(
						'sequence'=>''.$idx,
						'main_file_id'=>$main_file_id,
						'parameters'=>$cell_param,
					);
					$cell_row['is_live'] = '0';
					$cell_row['album_id'] = $id;
					
					$num_photo ++;
					$this->album_photo_model->save($cell_row, NULL, $edit_info);

				}
			}
			
			cache_remove('album/'.$result['id'].'/*');
			cache_remove('ph/album/'.$result['id'].'/*');
				
			cache_remove('album/'.$result['id']);
			cache_remove('ph/album/'.$result['id']);
			//$vals['_server_cells'] = $server_cells;
			//$vals['_keep_cell_ids'] = $keep_cell_ids;
			//$vals['_removed_cell_ids'] = $removed_cell_ids;
			//$vals['_queries'] = $this->db->queries;
			$result = $this->album_model->save(array('num_photo'=>$num_photo),array('id'=>$id,'is_live'=>'0'), $edit_info);

			$vals = $this->_mapping_row($this->album_model->read(array('id'=>$id,'is_live'=>'0')));
		}
	
		if($this->uri->is_extension('')){
			redirect('album/'.$id);
			return;
		}
		return $this->_api($vals);
	}
	
	function _remove(){
		if($this->_restrict())return;
		if(!$this->_is_ext('data')){ return $this->_show_404('extension_not_matched');}
		
		$vals = array();
		$vals['data'] = array();

		
		$ids = explode(",",$this->input->post('ids'));
		
		
		if(is_array($ids) && count($ids)>0)
		{
			$records = $this->album_model->find(array('id'=>$ids));
			$ids = array();
			if(is_array($records) && count($records)>0){
				foreach($records as $idx => $record){
					$ids[] = $record['id'];
				}
				$this->album_photo_model->delete(array('album_id'=>$ids));
				$this->album_model->delete(array('id'=>$ids));
				$vals['data'] = $ids;
			}
		}
		
		return $this->_api($vals);
	}
	
	// create thumbnail in realtime by passing parameters
	// all special sized image should be created when setting into related table
	function _picture($record_id=false,$offset=0){
			
		if($this->_restrict()){
			return;
		}
		
		$album_row = $this->album_model->read(array('is_live'=>'0', 'id'=>$record_id));
		if(!isset($album_row['id'])){
			return $this->_show_404();;
		}
		$photo_row = $this->album_photo_model->read(array('is_live'=>'0','album_id'=>$record_id,'sequence'=>$offset));
		
		$this->load->model('file_model');
		
		$file_row = $this->file_model->read(array('id'=>$photo_row['main_file_id']));
		if(!isset($file_row['id'])){
			return $this->_show_404();;
		}
		
		if(!in_array(strtolower($file_row['file_ext']),array('jpg','png','gif'))){
			return $this->_error('500','Target is not an image',404);
		}
		
		
		if($this->uri->is_extension('')){
			
			$size_group = 'file';
			$size_name = 'thumbnail';
			
			$size = $this->input->get('size');
			$width = $this->input->get('width');
			$height = $this->input->get('height') ;
			
			
			$scale = $this->input->get('scale') == 'fill' ? 'fill' : 'fit';
			$rebuild = $this->input->get('rebuild') == 'yes' || $this->input->get('rebuild') == 'true' ? true: false;
			$crop = $this->input->get('crop') == 'no' || $this->input->get('crop') == 'false' ? false : true;
			
			$crop_x = intval($this->input->get('crop_x'));
			$crop_y = intval($this->input->get('crop_y'));
			
			$crop_width = intval($this->input->get('crop_width'));
			$crop_height = intval($this->input->get('crop_height'));
			
			$params = compact('size','width','height','scale','rebuild','crop','crop_x','crop_y','crop_width','crop_height'); 
			
			$dest_filepath = $this->_build_image($file_row,$params,'private','filepath');
			
			if(!is_file($dest_filepath)){
				return $this->_error('500','Cached file does not created at '.$dest_filepath,200);
			}

			header('Content-type: image/jpeg');
			readfile($dest_filepath);
		}else if($this->_is_ext('data')){
			$this->_api(array(
				'url'=>site_url('album/'.$album_row['id'].'/picture')
			));
		}else{
			$this->_error(404, 'File not found',404);
		}
	}

	function _build_image($record,$params=false,$app_type='private',$return_type='filepath'){
			
		$subfolder = '';
		if(!empty($record['folder'])){
			$subfolder = DS.$record['folder'];
		}
		
		$options = array();
		$dir_path = '/files'.$subfolder;
		$options['src'] = PRV_DATA_DIR.$dir_path;
		$dest_base_path = PUB_DIR.'_admin';
		$dest_base_url = pub_url('_admin'.$dir_path);
		if($app_type == 'public'){
			$dest_base_path = PUB_DIR;
			$dest_base_url = pub_url($dir_path);
		}
		$options['dest'] = $dest_base_path.$dir_path;
		
		if(!is_dir($options['src'])){
			return $this->_error('500','System file storage seem not exist at '. $options['src'],404);
		}
		
		if(!file_exists($options['src'].DS.$record['sys_name'])){
			return $this->_error('500','File source does not exist at '. $options['src'].DS.$record['sys_name'],404);
		}

		if(!is_dir($options['dest'])){
			@mkdir($options['dest'], 0777,true);
		}
		
		
		$size_group = 'file';
		$size_name = 'thumbnail';
		
		$req_size = isset($params['size']) ? $params['size'] : NULL;
		$req_width = isset($params['width']) ? $params['width'] : NULL;
		$req_height = isset($params['height']) ? $params['height'] : NULL ;
		
		$has_req_width = !empty($req_width)  && $req_width != 'auto' && is_int(intval($req_width)) && intval($req_width) > 0;
		$has_req_height = !empty($req_width) && $req_height != 'auto' && is_int(intval($req_height)) && intval($req_height)> 0;
		
		if($this->_is_debug())
			log_message('debug','File/build_image, requested:'.print_r(compact('req_size','req_width','req_height','has_req_width','has_req_height'),true));
		
		
		$req_scalemode = isset($params['scale']) && $params['scale'] == 'fill' ? 'fill' : 'fit';
		$req_rebuild = isset($params['rebuild']) && ($params['rebuild'] == 'yes' || $params['rebuild'] == 'true') ? true: false;
		$req_crop = isset($params['crop']) && ($params['crop'] == 'no' || $params['crop'] == 'false') ? false : true;
		
		$req_crop_x = isset($params['crop_x']) ? intval($params['crop_x']) : NULL;
		$req_crop_y = isset($params['crop_y']) ? intval($params['crop_y']) : NULL;
		
		if(!is_int($req_crop_x)) $req_crop_x = 0;
		if(!is_int($req_crop_y)) $req_crop_y = y;
		
		$req_crop_width = isset($params['crop_width']) ? intval($params['crop_width']) : 0;
		$req_crop_height = isset($params['crop_width']) ? intval($params['crop_height']) : 0;
		$has_req_crop_width = !empty($req_crop_width) && is_int($req_crop_width) && $req_crop_width > 0;
		$has_req_crop_height = !empty($req_crop_height) && is_int($req_crop_height) && $req_crop_height> 0;
		$has_custom_crop = $has_req_crop_width && $has_req_crop_height;
		
		if($req_size=='small')
			$size_name = 'small';
		
		if($req_size=='large')
			$size_name = 'large';
		
		if($req_size=='source')
			$size_name = 'large';
		
		if($has_req_width || $has_req_height){
			$size_name = '';
			$size_info = array(
				'scale'=>$req_scalemode,
				'crop'=>$req_crop,
				'format'=>'jpg',
			);
			if($has_req_width){
				
				$req_width = min(800, intval($req_width));
				
				$size_name.= 'w'.$req_width;
				$size_info['width'] = $req_width;
			}
			if($has_req_height){
				
				$req_height = min(800, intval($req_height));
				
				$size_name.= 'h'.$req_height;
				$size_info['height'] = $req_height;
			}
			if($has_custom_crop){
				$size_name.='-x'.$req_crop_x.'y'.$req_crop_y.'w'.$req_crop_width.'h'.$req_crop_height;
				$options['crop_x'] = $req_crop_x;
				$options['crop_y'] = $req_crop_y;
				$options['crop_width'] = $req_crop_width;
				$options['crop_height'] = $req_crop_height;
			}
			$size_info['suffix'] = '_'.$size_name;
			
			
			$size_group = array(
				'custom'=> $size_info,
			);
			$size_name = 'custom';
		}
		
		$options['rebuild'] = $req_rebuild;
		
		$dest_filename = \Dynamotor\Helpers\PictureRenderer::make($record['sys_name'],$size_group,$size_name,$options);
		$dest_path = $options['dest'].'/'.($dest_filename);
		$dest_url = $dest_base_url. '/'.($dest_filename);
		
		if($return_type == 'filepath')
			return $dest_path;	
		if($return_type == 'dirpath')
			return $options['dest'];		
		if($return_type == 'url')
			return $dest_url;
		return TRUE;
	}
}