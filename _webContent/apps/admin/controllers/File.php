<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File extends MY_Controller
{
	var $file_types = array(
		'image'=>array('jpg','gif','png'),
	);

	var $routes = array(
		'' => array('index'),
		'selector'=>array('index'),
		'selector\/(.+)'=>array('index','$1'),
		'(upload|remove|save|search)'=>array('$1'),
		'(croparea|temp)\/(.+)\/(.+)'=>array('$1','$2','$3'),
		'(croparea|temp)\/(.+)'=>array('$1','$2'),
		'([a-zA-Z0-9\-]+)\/(download|get|picture|remove|croparea)'=>array('$2','$1'),
		'([a-zA-Z0-9\-]+)'=>array('get','$1'),
	);

	public function __construct(){
		parent::__construct();
		$this->load->model('file_model');
		$this->load->config('file');
		$this->file_types = $this->config->item('file_types');


	}
	
	public function _remap(){
		return $this->do_custom_route();
	}

	protected function human_filesize($bytes, $decimals = 2) {
	    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
	    $factor = floor((strlen($bytes) - 1) / 3);
	    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}
	
	protected function _mapping_row($row){
		
		$new_row = array();
		$new_row['id'] = $row['id'];
		$new_row['types'] = array();

		foreach($this->file_types as $file_type => $file_type_exts){
		//	$new_row['is_'.$file_type] = in_array($row['file_ext'],$file_type_exts);
			$new_row['types'][$file_type] = in_array($row['file_ext'],$file_type_exts);
		}
		$new_row['is_image'] = isset($new_row['types']['image']) && $new_row['types']['image'];

		if(isset($new_row['is_image']) && $new_row['is_image']){
			$new_row['image'] = array(
				'thumbnail'=>site_url('file/'.$row['id'].'/picture?width=200&height=200'),
				'large'=>site_url('file/'.$row['id'].'/picture?size=large'),
				'source'=>site_url('file/'.$row['id'].'/picture?size=source'),
			);
		}
		$file_path = $row['folder'].'/'.$row['sys_name'];
		$file_dir = PRV_DATA_DIR.'files'.'/'.$file_path;

		$file_size = 0;

		if(file_exists($file_dir))
			$file_size = filesize($file_dir );

		$new_row['sys_name'] =$row['sys_name'];
		$new_row['file_path'] = $file_path;
		$new_row['file_size'] = $file_size;
		$new_row['file_size_readable'] = $this->human_filesize($file_size);

		$new_row['file_name'] = $row['file_name'];
		$new_row['file_ext'] = $row['file_ext'];

		$new_row['create_date'] = $row['create_date'];
		//$new_row['create_by'] = $row['create_by'];
		$new_row['create_by_id'] = $row['create_by_id'];
		
		return $new_row;
	}
	
	public function index($filetype='all'){
		
		if($this->_restrict()){
			return;
		}
		
		$vals = array();
		$vals['default_type'] = isset($this->file_types[$filetype]) ? $filetype : 'all';
		
		if($this->uri->is_extension('js')){
			$this->_render('file/item_index.js', $vals);
			return;
		}
		$this->_render('file/item_index', $vals);
	}
	
	public function search(){
		if($this->_restrict()){
			return ;
		}
		
		
		$vals = array();
		
		$offset = $this->input->get('offset') !== NULL ? intval($this->input->get('offset')) : 0;
		$limit = $this->input->get('limit') !== NULL ? intval($this->input->get('limit')) : 30;
		$file_type = $this->input->get('type') !== NULL ? ($this->input->get('type')) : 'all';
		$direction = strtolower($this->input->get('direction')) == 'asc' ? 'asc':'desc';
		
		if($offset<0) $offset = 0;
		if($limit< 1) $limit = 1;
		if($limit>1000) $limit = 1000;
		
		// query options
		$opts = array();
		$opts['_order_by'] = array();
		
		if(!empty($this->file_types[$file_type])){
			$opts['file_ext'] = $this->file_types[$file_type];
		}
		$opts ['owner_type'] = 'admin';

		if($this->input->get_post('owner_type') !== NULL)
			$opts ['owner_type'] = $this->input->get_post('owner_type');


		if($this->input->get_post('owner_id') !== NULL)
			$opts ['owner_id'] = $this->input->get_post('owner_id');
		
		if($this->input->get('q')!= NULL){
			$opts['_keyword'] = $this->input->get('q');
			$opts['_keyword_fields'] = array('id', 'name','file_name','file_ext');
		}
		
		$req_sort_field = $this->input->get('sort');
		if(!empty($req_sort_field) && isset($this->file_model->fields[ $req_sort_field] )){
			$opts['_order_by'][ $req_sort_field ] = $direction;
		}else{
			$opts['_order_by']['create_date'] = $direction;
		}
		
		
		$vals['data'] = array();
		$vals['paging'] = array(
			'offset'=>0,
			'total'=>0,
			'limit'=>0,
			'page'=>0,
			'total_page'=>0,
		);
		
		$result = $this->file_model->find_paged($offset,$limit,$opts);
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
	
	public function get($record_id=false){
		if($this->_restrict()){
			return ;
		}
		
		$record = $this->file_model->read(array('id'=>$record_id));
		if(!isset($record['id'])){
			return $this->_error('404','Record not found');
		}
		$subfolder = '';
		if(!empty($record['folder'])){
			$subfolder = DS.$record['folder'];
		}
		$dest_filedir = PRV_DATA_DIR.DS.'files'.$subfolder.DS.$record['sys_name'];
		$name = $record['file_name'];
		
		
		$_row = $this->_mapping_row($record);

		$file_size = 0;

		if(file_exists($dest_filedir))
			$file_size = filesize($dest_filedir);
		
		$rst = array(
			'id'=> $record['id'],
			'is_image'=>$_row['is_image'],
			'file_name'=>$name,
			'file_size'=>$file_size,
			'download_url'=>site_url('file/'.$record['id'].'/download'),
		);
		if($_row['is_image']){
			
			$rst['image']=$_row['image'];
		}

		if($this->input->get('need-pub-url')=='yes'){
			$rst['pub_url'] = $this->resource->public_uploaded_file_url($record);
		}
		
		return $this->_api($rst);
	}
	
	public function download($record_id=false)
	{
		try{
			$file_row = $this->file->get_file($record_id);

			$data = $this->file->get_data($file_row['id']);
		}catch(HC_Exception $exp){
			return $this->_error($exp->code, $exp->getMessage());
		}

		if($this->uri->is_extension('')){
			if(!empty($data)){
				$this->load->helper('download');
			
				force_download($file_row['file_name'], $data);

				return;
			}else{
				return $this->_show_404('source_file_not_found');
			}
		}


		if($this->uri->is_extension(array('json','xml','plist'))){
			return $this->_api(array(
				'file_name'=>$file_row['file_name'],
				'file_size'=>$file_row['file_size'],
				'url'=>site_url('file/'.$file_row['id'].'/download')
			));
		}else{
			return $this->_error(404, 'File not found');
		}
	}
	
	public function croparea($record_id=false){
			
		if($this->_restrict()){
			return;
		}
		if($this->uri->is_extension('js')){
			return $this->_render('file/croparea.js');
		}
		if(empty($record_id))
			$record_id = $this->input->get('id');
		
		$record = $this->file_model->read(array('id'=>$record_id));
		if(!isset($record['id'])){
			return $this->_error('404','Record not found');
		}
		
		if(!in_array(strtolower($record['file_ext']),array('jpg','png','gif'))){
			return $this->_error('500','Target is not an image');
		}
		
		if($this->uri->is_extension('','html','js')){
			$vals = array('file_id'=> $record['id']);
			$vals['tar_width'] = intval($this->input->get('width'));
			$vals['tar_height'] = intval($this->input->get('height'));
			$vals['src_croparea'] = ($this->input->get('val'));
			return $this->_render('file/croparea',$vals);
		}
		return $this->_show_404();
	}
	
	// create thumbnail in realtime by passing parameters
	// all special sized image should be created when setting into related table
	public function picture($record_id=false){
			
		if($this->_restrict()){
			return;
		}
		
		$record = $this->file_model->read(array('id'=>$record_id));
		if(!isset($record['id'])){
			return $this->_error('404','Record not found');
		}
		
		if(!in_array(strtolower($record['file_ext']),array('jpg','png','gif'))){
			return $this->_error('500','Target is not an image');
		}
	
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
		
		if($this->input->get('croparea') != ''){
			$crop = 'yes';
			$_crop_info = explode(',',$this->input->get('croparea'),5);
			if(count($_crop_info)>=4){
			list($crop_x, $crop_y, $crop_width, $crop_height) = $_crop_info;
			}
			//$rebuild = true;
			$size = 'custom';
		}
		
		$params = compact('size','width','height','scale','rebuild','crop','crop_x','crop_y','crop_width','crop_height'); 
		
		
		if($this->uri->is_extension('')){
			
			$result = $this->_build_image($record,$params,'private','all');
			/*
			if(empty($result['path'])){
				return $this->_error('500','System does not return a path for this image.');
			}
			
			if(!is_file($result['path'])){
				return $this->_error('500','Cached file does not created at '.$result['path']);
			}
			//*/

			if(empty($result['url'])){
				log_message('error',__METHOD__.'@'.__LINE__.', ImageNotGenerated, data='.print_r($result,true));
				return $this->_error('500','ImageNotGenerated',500);
			}

			redirect($result['url']); return;
			header('Content-type: image/jpeg');
			readfile($result['path']);
		}else if($this->uri->is_extension(array('json','xml','plist'))){
			return $this->_api(array(
				'url'=>site_url('file/item/'.$record['id'].'/picture'),
				'params'=>$params,
			));
		}else{
			return $this->_error(404, 'FileNotFound');
		}
	}

	protected function _build_image($record,$params=false,$app_type='private',$return_type='url'){
		
		
		$options = [];
		
		$size_group = 'file';
		$size_name = 'thumbnail';
		
		$req_size = isset($params['size']) ? $params['size'] : NULL;
		$req_width = isset($params['width']) ? $params['width'] : NULL;
		$req_height = isset($params['height']) ? $params['height'] : NULL ;
		
		$has_req_width = !empty($req_width)  && $req_width != 'auto' && is_int(intval($req_width)) && intval($req_width) > 0;
		$has_req_height = !empty($req_width) && $req_height != 'auto' && is_int(intval($req_height)) && intval($req_height)> 0;
		
		
		$req_scalemode = isset($params['scale']) && $params['scale'] == 'fill' ? 'fill' : 'fit';
		$req_rebuild = isset($params['rebuild']) && ($params['rebuild'] == 'yes' || $params['rebuild'] == 'true') ? true: false;
		$req_crop = isset($params['crop']) && ($params['crop'] === FALSE || $params['crop'] == 'no' || $params['crop'] == 'false') ? false : true;
		
		$req_crop_x = isset($params['crop_x']) ? intval($params['crop_x']) : NULL;
		$req_crop_y = isset($params['crop_y']) ? intval($params['crop_y']) : NULL;
		
		if(!is_int($req_crop_x)) $req_crop_x = 0;
		if(!is_int($req_crop_y)) $req_crop_y = 0;
		
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
			$size_name = 'source';
		
		if($has_req_width || $has_req_height || $has_custom_crop){
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
	
			
		$config = ['file'=>$record, 'size_group'=>$size_group, 'size_name'=>$size_name, 'path'=>'_admin'];
		$config['rebuild'] = $req_rebuild;

		log_message('debug','File/build_image, requested:'.print_r(compact('config','options'),true));

		$picture_info = $this->resource->picture($config, $options);
		
		if($return_type == 'all')
			return $picture_info;
			
		if($return_type == 'url')
			return $picture_info['url'];
		return TRUE;
	}
	
	public function upload(){
		if($this->_restrict()){
			return ;
		}
		
		$accepted_group=$this->input->post('group');
		
		//$this->_restrict(array('FILE_UPLOAD'));
		
		// separate into different folder for speed up listing time when access to filesystem
		
		
		$file_upload_size = $this->config->item('sys_upload_max_size');

		log_message('debug','request extension:'.$this->uri->extension());
			
		$tmp_dir = TMP_DIR.DS.'uploaded';

		if(!is_dir($tmp_dir)){
			mkdir($tmp_dir , 0777, true);
		}

		$cfg = array();
		$cfg['upload_path'] = $tmp_dir;
		$cfg['max_size'] = $file_upload_size * 1024;
		$cfg['allowed_types'] = '*';
		$cfg['encrypt_name'] = true;
		$cfg['file_ext_tolower'] = true;	
		
		ini_set('upload_max_filesize',$file_upload_size.'M');
		ini_set('post_max_size',($file_upload_size*4).'M');
		
		
		if($accepted_group == 'image'){
			$cfg['allowed_types'] = 'jpg|png|gif';
			$cfg['max_width'] = 2048;
			$cfg['max_height'] = 2048;
		}
		
		$this->load->library('upload');
		$this->upload->initialize($cfg);
		
		if(!$this->upload->do_upload('new_file')){
			return $this->_error(801, 'No file selected or not accepted.',200, array(
				'reason'=>strip_tags($this->upload->display_errors()),
				'upload_max_filesize'=> ini_get('upload_max_filesize'),
				'post_max_size'=> ini_get('post_max_size'),
			));
		}else{
			$upload_data = $this->upload->data();

			
			if($accepted_group == 'image' && !$upload_data['is_image']){
				@unlink($upload_data['full_path']);
				
				return $this->_error(802, 'Not accepted file');
			}
			
			$attrs = ['owner_type'=>'admin','owner_id'=>$this->config->item('account_id')];

			if($this->input->post('ref_table')!= NULL){
				$attrs['ref_table'] = $this->input->post('ref_table');

				if($this->input->post('ref_id')!= NULL){
					$attrs['ref_id'] = $this->input->post('ref_id');
				}
			}
			
			try{
				// import_file($tmp_path, $orig_name = NULL, $mime_type = '', $attribute = null, $bucket_source = null)
				$result = $this->file->import_file($upload_data['full_path'], $upload_data['orig_name'], null, $attrs);
			}catch(\Dynamotor\Core\HC_Exception $exp){

				if(file_exists($upload_data['full_path']))
					@unlink($upload_data['full_path']);

				return $this->_error(802, $exp->getMessage());
			}
			
			if(!isset($result['id'])){
				if(file_exists($upload_data['full_path']))
					@unlink($upload_data['full_path']);

				log_message('error',__METHOD__.'#insert error, cannot save new document for file: ',print_r($upload_data,true));
				return $this->_error(802,'Record could not be saved, please try again later.');
			}
			if(file_exists($upload_data['full_path']))
				@unlink($upload_data['full_path']);
			
			$output = array(
				'id'=>$result['id'],
				'is_image'=> $upload_data['is_image'] ? true:false,
			);
			return $this->_api($output);
		}
	}

	public function remove(){
		if($this->_restrict()){
			return ;
		}
		
		$ids = $this->input->post('ids');
		$ids = explode(",", trim($ids));
		if(!is_array($ids)){
			return $this->_error(ERROR_INVALID_DATA, ERROR_INVALID_DATA_MSG);
		}
		
		$records = $this->file_model->find(array('id'=>$ids));
		if(is_array($records) && count($records)>0){
			foreach($records as $idx => $file_row){


				$this->file->remove_file($file_row['id']);
				
				cache_remove('file/'.$file_row['id']);
				cache_remove('res/file/'.$file_row['id']);
				cache_remove('ph/file/'.$file_row['id']);

			}
			
			return $this->_api(array('data'=>$ids));
		}else{
			return $this->_error(150,'No record has been loaded.');
		}
		
	}

	public function temp($action = 'list',$id=FALSE){

		$perms = NULL;// array('FILE_UPLOAD');
		if($this->_restrict($perms)){
			return ;
		}
		$this->load->helper('tempupload');

		if($action == 'remove'){

			$rst = tempupload_get_all();
			if(isset($rst['items'][$id])){

				$path = realpath($rst['items'][$id]['full_path']);

				if(!substr($path,0, strlen(PRV_DATA_DIR)) == PRV_DATA_DIR){
					return $this->_error(-1, "Stored full path is not allowed to made changes.",500);
				}

				if(file_exists($path) ){
					log_message('info','File/temp/remove, file remove for id '.$id.' : '.$path);
					@unlink($path);
				}

				unset($rst['items'][$id]);
				$this->session->set_userdata('tempfiles', $rst);
				return $this->_api(array('success'=>TRUE));
			}
			return $this->_api(array('success'=>FALSE));
		}
		if($action == 'info'){

			$rst = tempupload_get_all();
			if(isset($rst['items'][$id])){

				$upload_data = $rst['items'][$id];
				$output = array(
					'id'=>$id,
					'file_name'=>$upload_data['file_name'],
					'file_ext'=>$upload_data['file_ext'],
					'sys_name'=>$upload_data['sys_name'],
					'file_size'=>$upload_data['file_size'],
					'file_size_readable' => $this->human_filesize($upload_data['file_size']*1024),
				);
				if($this->_is_debug())
					$output['data'] = $new_data;
				return $this->_api($output);

			}
			return $this->_error(-1, 'file_not_found');
		}
		if($action == 'list'){
			$rst = tempupload_get_all();

			$items = array();
			if(!empty($rst['items'])) {
				foreach($rst['items'] as $tmp_id => $upload_data){

					$output = array(
						'id'=>$tmp_id,
						'name'=>$upload_data['file_name'],
						'file_name'=>$upload_data['file_name'],
						'file_ext'=>$upload_data['file_ext'],
						'sys_name'=>$upload_data['sys_name'],
						'file_size'=>$upload_data['file_size'],
						'file_size_readable' => $this->human_filesize($upload_data['file_size']*1024),
					);
					$items[] = $output;
				}
			}

			return $this->_api(array('data'=> $items));
		}
		if($action == 'removeAll'){
			$rst = tempupload_get_all();

			$items = array();
			if(!empty($rst['items'])) {
				foreach($rst['items'] as $tmp_id => $upload_data){

					if(!empty($upload_data['full_path']) && is_file($upload_data['full_path']))
						@unlink($upload_data['full_path']);
					tempupload_remove($tmp_id);
				}
			}

			return $this->_api(array('success'=>TRUE));
		}

		if($action == 'upload'){
		
			if($this->_is_debug())
				log_message('debug','request extension:'.$this->uri->extension());
			
			$file_upload_size = 8;
			
			
			// separate into different folder for speed up listing time when access to filesystem
			$folder = gmdate('Ymd');
			
			$cfg = array();
			$cfg['upload_path'] = tempupload_get_dir();
			$cfg['max_size'] = $file_upload_size * 1024;
			$cfg['allowed_types'] = '*';
			$cfg['encrypt_name'] = true;
			
			ini_set('upload_max_filesize',$file_upload_size.'M');
			ini_set('post_max_size',($file_upload_size*4).'M');
			
			if(!is_dir($cfg['upload_path'])){
				@mkdir($cfg['upload_path'],0777,true);
			}


			$accepted_group = $this->input->get_post('group');
			$accepted_ext = $this->input->get_post('ext');

			if(!empty($accepted_ext))
				$cfg['allowed_types'] = $accepted_ext;

			if($accepted_group == 'image'){
				$cfg['allowed_types'] = 'jpg|png|gif|jpeg';
				$cfg['max_width'] = 2048;
				$cfg['max_height'] = 2048;
			}
			
			$this->load->library('upload');
			$this->upload->initialize($cfg);
			
			if(!$this->upload->do_upload('new_file')){
				return $this->_error(801, 'No file selected or not accepted.',200, array(
					'_reason'=>$this->upload->display_errors(),
					'_upload_max_filesize'=> ini_get('upload_max_filesize'),
					'_post_max_size'=> ini_get('post_max_size'),
				));
			}else{
				$upload_data = $this->upload->data();
				
				if($accepted_group == 'image' && !$upload_data['is_image']){
					@unlink($upload_data['full_path']);
					
					return $this->_error(802, 'Not accepted file');
				}
				
				if(substr($upload_data['file_ext'],0,1) == '.'){
					$upload_data['file_ext'] = substr($upload_data['file_ext'],1);
				}
				
				$new_data = array(
					'name'=>$upload_data['orig_name'],
					'orig_name'=>$upload_data['orig_name'],
					'file_name'=>$upload_data['orig_name'],
					'file_ext'=>$upload_data['file_ext'],
					'sys_name'=>$upload_data['file_name'],
					'full_path'=>$upload_data['full_path'],
					'file_size'=>$upload_data['file_size'],
				);

				$tmp_id = tempupload_add($new_data);
				
				$output = array(
					'id'=>$tmp_id,
					'name'=>$upload_data['orig_name'],
					'file_name'=>$upload_data['orig_name'],
					'file_ext'=>$upload_data['file_ext'],
					'sys_name'=>$upload_data['file_name'],
					'file_size'=>$upload_data['file_size'],
					'file_size_readable' => $this->human_filesize($upload_data['file_size']*1024),
				);
				if($this->_is_debug())
					$output['data'] = $new_data;
				return $this->_api($output);
			}
		}
		return $this->_error(-1, 'action_not_matched');
	}
}
