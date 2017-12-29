<?php 

/**
 * Package: Dynamotor\Modules
 */

// Define package namespace
namespace Dynamotor\Modules;

// Import class
use \Dynamotor\Core\HC_Module;
use \Dynamotor\Core\HC_Exception;
use \Dynamotor\Modules\IFileBucketProvider;

class FileModule extends HC_Module
{

	// Upload process
	const STATUS_ERROR_UPLOAD_EMPTY = 1401;
	const STATUS_ERROR_FILE_REJECTED = 1405;

	// For CFS
	const STATUS_ERROR_OBJECT_NOT_EXIST = 1441;
	const STATUS_ERROR_UPLOAD_OBJECT = 1442;

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('file');
		$this->load->helper('string');

		$this->load->model('file_model');
		$this->load->model('file_resource_model');
	}


	protected $bucket_providers = [];
	protected $default_bucket_provider = null;

	/**
	 * Add bucket provider
	 * 
	 * @param \Dynamotor\Modules\IFileBucketProvider $provider   [description]
	 * @param boolean                                $is_default [description]
	 */
	public function add_bucket_provider(IFileBucketProvider $provider, $is_default = true)
	{
		$bucket_source = $provider -> get_bucket_source();

		$provider->set_parent($this);

		if($is_default){
			$this->default_bucket_provider = $provider;
		}

		$this->bucket_providers[ $bucket_source ] = $provider;
	}

	public function get_supported_bucket_provider()
	{
		return array_keys($this->bucket_providers);
	}

	/**
	 * [get_bucket_provider description]
	 * @param  [type] $bucket_source [description]
	 * @return [type]                [description]
	 */
	public function get_bucket_provider ($bucket_source)
	{
		if(isset($this->bucket_providers[ $bucket_source]))
			return $this->bucket_providers[ $bucket_source];

		return null;
	}

	/**
	 * [remove_bucket_provider description]
	 * @param  string $bucket_source [description]
	 * @return [type]                [description]
	 */
	public function remove_bucket_provider ($bucket_source)
	{

		if(isset($this->bucket_providers[ $bucket_source])){

			if( $this->bucket_providers[ $bucket_source] == $this->default_bucket_provider ){
				$this->default_bucket_provider = null;
			}

			unset($this->bucket_providers[ $bucket_source]);
		}

		return null;
	}

	public function get_path($directory = '', $subpath = '')
	{
		$path = $directory;
		if(strlen($path) > 0 && substr($path,-1,1) != '/'){

			if(strlen($subpath)>0)
				$path .= '/';
		}

		return $path . $subpath;
	}

	/**
	 * Private data path
	 * @var string
	 */
	protected $public_path = 'pub';
	public function get_public_storage_path($subpath= '')
	{
		return $this->get_path($this->public_path, $subpath);
	}

	/**
	 * [get_public_storage_directory description]
	 * @param  string $subpath [description]
	 * @return [type]          [description]
	 */
	public function get_public_storage_directory($subpath= '')
	{
		return $this->get_path(dirname(PUB_DIR), $this->get_public_storage_path( $subpath));
	}

	/**
	 * Generate public url
	 * 
	 * @param  string $subpath [description]
	 * @return string          [description]
	 */
	public function get_public_storage_url($subpath='')
	{
		return pub_url($subpath);
	}

	/**
	 * Private data path
	 * @var string
	 */
	protected $prvdata_path = 'prvdata/files';

	public function get_private_storage_path($subpath='')
	{
		return $this->get_path($this->prvdata_path,$subpath);
	}

	/**
	 * Returning system private storage path.
	 * 
	 * @return string [description]
	 */
	public function get_private_storage_directory($subpath='')
	{
		return $this->get_path($this->get_path(WEBCONTENT_DIR,$this->prvdata_path),$subpath);
	}


	/**
	 * Check if the field name contain any upload data
	 * 
	 * @param  string  $field_name Field name
	 * @return boolean             True if any file input is available
	 */
	public function has_upload_file($field_name)
	{
		return !empty($_FILES[$field_name]) && $_FILES[$field_name]['size'] >0 ;
	}

	/**
	 * Do upload process for the given field.
	 * 
	 * @param  string             $field_name Field name in $_FILES
	 * @param  string             $accepted_file_types, Enter comma separated value for supported file extensions, list of mime types of in array format, or support all files by passing "*"
	 * @param  integer            Maximum file size, negative value represent no limitation in application
	 * @param  mixed array        Attributes for the file (owner_type, owner_id, etc...)
	 * @param  mixed array        Options for the process (max_files)
	 * @param  string             Optional parameter, the name of bucket source.
	 * @return mixed              [description]
	 */
	public function upload_file($field_name, $accepted_file_types = '*', $max_file_size = -1, $attributes = null, $options = null, $bucket_source= null)
	{
		if(!isset($_FILES[$field_name])){
	    	throw new HC_Exception(self::STATUS_ERROR_UPLOAD_EMPTY, 'NoUploadedFile');
		}
		if(is_array($_FILES[$field_name]) && isset($_FILES[$field_name][0])){
			$output = array();

			$max_files = isset($options['max_files']) && is_int($options['max_files']) ? $options['max_files'] : 10;

			foreach($_FILES[$field_name] as $idx => $_file){

				// If reaching maximum number of uploaded files , break here
				if($max_files > 0 && $idx + 1 > $max_files) 
					break;

				try{
					$output[$idx] = $this->_handle_upload_file($_file, $accepted_file_types, $max_file_size, $attributes, $bucket_source);
				}catch(HC_Exception $exp){
					$output[$idx] = array('status'=>'error','exception'=>array('code'=>$exp->code, 'message'=>$exp->getMessage(), 'data'=>$exp->data ))	;
				}catch(Exception $exp){
					$output[$idx] = array('status'=>'error','exception'=>array('code'=>-1, 'message'=>$exp->getMessage()))	;
				}
			}

			return $output;
		}
	    return $this->_handle_upload_file($_FILES[$field_name], $accepted_file_types, $max_file_size, $attributes, $bucket_source);
	}

	protected function _handle_upload_file($_file, $accepted_file_types = '*', $max_file_size = -1, $attributes = null, $bucket_source= null)
	{
		// Check server side reported error code
		if(isset($_file['error'])){
			switch ($_file['error']) {
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_INI_SIZE:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileSizeOverLimitError',200);

				case UPLOAD_ERR_PARTIAL:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFilePartialError',200);

				case UPLOAD_ERR_EXTENSION:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileRejectedExtensionError',200);

				case UPLOAD_ERR_CANT_WRITE:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileIOError',200);

				case UPLOAD_ERR_NO_TMP_DIR:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileTempDirIOError',200);
				
				case UPLOAD_ERR_OK:
					break;

				default:
					return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileUnkownError',200);
					break;
			}
		}

		// Application based file size limit check
		if(isset($_file['size'])){
			if($max_file_size * 1024 > 0 && $_file['size'] > $max_file_size * 1024){
				return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileSizeTooLarge',200,array(
					'uploaded_size'=>$_file['size'], 
					'max_file_size'=>$max_file_size,
				));
			}
		}

		// File extension / mime type check
		if(isset($_file['type'])){
			if(!$this->is_mime_type_allowed($_file['type'], $accepted_file_types)){
				return $this->error(self::STATUS_ERROR_FILE_REJECTED,'UploadedFileRejectedByMimeType',200);
			}
		}

		// Start importing
		return $this->import_file($_file['tmp_name'], $_file['name'], $_file['type'], $attributes, $bucket_source);
	}

	/**
	 * Check given mime type is accepted.
	 * 
	 * @param  string  $mime_type           [description]
	 * @param  string  $accepted_file_types [description]
	 * @return boolean                      [description]
	 */
	public function is_mime_type_allowed($mime_type = '', $accepted_file_types = '*')
	{
		$_accepted_file_types = null;

		if(is_string($accepted_file_types)){
			if($accepted_file_types == '*'){
				
				return true;
			}else{
				$_accepted_file_exts = explode('|', $accepted_file_types);

				foreach($_accepted_file_exts as $ext){
					$_mime_type = get_mime_by_extension($ext);
					if(!empty($_mime_type))
						$_accepted_file_types [] = $_mime_type;
				}

			}
		}elseif(is_array($accepted_file_types)){
			$_accepted_file_types = $accepted_file_types ;
		}

		if(empty($_accepted_file_types)){
			return true;
		}

		if(in_array($mime_type, $_accepted_file_types)){
			return true;
		}

		return false;
	}

	/**
	 * Import existing file(s)
	 * 
	 * @param  string $tmp_path   Local path of the file for import
	 * @param  string $orig_name  File name of this binary data
	 * @param  string $mime_type  Mime type of this binary data
	 * @param  mixed  $attributes An array contains user_type, user_id, ref_table, ref_id
	 * @param  string $bucket_source The name of the bucket for the imported file
	 * @return array              Return file id in an array, or exception during processing
	 */
	public function import_file($tmp_path, $orig_name = NULL, $mime_type = '', $attribute = null, $bucket_source = null)
	{
		$file_ext = '';

		$pathinfo = pathinfo($orig_name);

		if(!empty($pathinfo['extension'])){
			$file_ext = $pathinfo['extension'];
		}


		if(empty($mime_type)){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($finfo, $tmp_path);
		}

		$file_size = filesize($tmp_path);

		$file_upload_size = $this->config->item('sys_upload_max_size');
		if($file_upload_size > 0){
			if($file_size > $file_upload_size * 1024 * 1024){
				throw new HC_Exception(ERROR_RECORD_SAVE_ERROR, 'FileSizeTooLarge');
			}
		}

		$file_ext = strtolower($file_ext);
		$folder = $this->generate_folder_path();

		$sys_name = $this->generate_system_filename().'.'.$file_ext;

    	$new_file = $this->before_save_file_record($tmp_path, $sys_name, $file_ext, $orig_name, $mime_type, $folder, $file_size, $bucket_source);

    	if(isset($attribute['owner_type']))
    		$new_file['owner_type'] = $attribute['owner_type'];
    	if(isset($attribute['owner_id']))
    		$new_file['owner_id'] = $attribute['owner_id'];
    	if(isset($attribute['ref_table']))
    		$new_file['ref_table'] = $attribute['ref_table'];
    	if(isset($attribute['ref_id']))
    		$new_file['ref_id'] = $attribute['ref_id'];

    	$result = $this->file_model->save($new_file);
    	if(empty($result['id'])){
    		throw new HC_Exception(ERROR_RECORD_SAVE_ERROR, 'FileRecordNotSaved');
    	}

    	$file_record = $this->file_model->read(array('id'=>$result['id']));

    	$this->after_save_file_record($file_record, $tmp_path);
    
    	return array(
    		'id'=>$result['id'], 
    	);
	}

	/**
	 * Import existing file(s)
	 * 
	 * @param  string $data       Binary Data
	 * @param  string $orig_name  File name of this binary data
	 * @param  string $mime_type  Mime type of this binary data
	 * @param  mixed  $attributes An array contains user_type, user_id, ref_table, ref_id
	 * @return array              Return file id in an array, or exception during processing
	 */
	public function import_data($data, $orig_name = NULL, $mime_type = '', $attributes = null, $bucket_source = null)
	{

		$file_ext = '';
		$pathinfo = pathinfo($orig_name);

		if(!empty($pathinfo['extension'])){
			$file_ext = $pathinfo['extension'];
		}

		$file_ext = strtolower($file_ext);
		$folder = $this->generate_folder_path();

		$sys_name = $this->generate_system_filename().'.'.$file_ext;

    	$new_file = $this->before_save_file_record($tmp_path, $sys_name, $file_ext, $orig_name, $mime_type, $folder, sizeof($data), $bucket_source);

    	if(isset($attribute['owner_type']))
    		$new_file['owner_type'] = $attribute['owner_type'];
    	if(isset($attribute['owner_id']))
    		$new_file['owner_id'] = $attribute['owner_id'];
    	if(isset($attribute['ref_table']))
    		$new_file['ref_table'] = $attribute['ref_table'];
    	if(isset($attribute['ref_id']))
    		$new_file['ref_id'] = $attribute['ref_id'];

    	$result = $this->file_model->save($new_file);
    	if(empty($result['id'])){
    		throw new HC_Exception(ERROR_RECORD_SAVE_ERROR, 'FileRecordNotSaved');
    	}

    	$this->after_save_file_record($file_record);
    
    	return array(
    		'id'=>$result['id'], 
    	);
	}

	/**
	 * Generate system storing folder randomly
	 * @return string directory path
	 */
	public function generate_folder_path()
	{
		return strtolower(random_string('alnum',1).'/'.random_string('alnum',1).'/'.random_string('alnum',1));;
	}

	/**
	 * Generate system file name randomly
	 * @return string file name
	 */
	public function generate_system_filename()
	{
		return strtolower(random_string('alnum',32));
	}

	/**
	 * Action before saving a file as a record
	 * 
	 * @param  string $file_path [description]
	 * @param  string $sys_name  [description]
	 * @param  string $file_ext  [description]
	 * @param  string $orig_name [description]
	 * @param  string $mime_type [description]
	 * @param  string $folder    [description]
	 * @param  string $file_size 
	 * @param  string $bucket_source 
	 * @return mixed            [description]
	 */
	protected function before_save_file_record($file_path = '', $sys_name = '', $file_ext = '', $orig_name = '', $mime_type = '', $folder = '', $file_size = -1, $bucket_source = null)
	{
		$provider = null;

		if($file_size <0){
			if(is_file($file_path))
				$file_size = filesize($file_path);
		}

		if(empty($bucket_source) ){
			if(!empty($this->default_bucket_provider))	
				$provider = $this->default_bucket_provider;
		}else{
			$provider = $this->get_bucket_provider($bucket_source);
		}


		$record = [
			'name'=>$orig_name,
			'file_name'=>$orig_name,
			'orig_name'=>$orig_name,
			'file_ext'=>$file_ext,
			'folder'=>$folder,
			'sys_name'=>$sys_name,
			'mime_type'=>$mime_type,
			'file_size' =>$file_size,
		];

		if(!empty($provider)){
			$provider->put_file($record, $file_path);
		}

		return $record;
	}

	protected function after_save_file_record($file_record, $tmp_path = null)
	{
		if(!empty($tmp_path)){
			$file_dir = $this->get_private_storage_directory($file_record['folder']);


			if(!is_dir($file_dir)){
				@mkdir($file_dir, 0777, true);
			}

			if(!is_writable($file_dir)){
				throw new HC_Exception(ERROR_INVALID_DATA, 'PrivateStorageUnwritable');
			}

			$file_path = $this->get_path($file_dir,$file_record['sys_name']);
			@copy($tmp_path, $file_path);
			if(!file_exists($file_path)){
				throw new HC_Exception(ERROR_INVALID_DATA, 'UnableCopyFileIntoPrivateStorage');
			}
		}
	}

	/**
	 * Get binary data from a path
	 * 
	 * @param  [type] $file_id [description]
	 * @return [type]       [description]
	 */
	public function get_data($file_id, $options = null)
	{
		if(empty($file_id)){
	    	return $this->error(ERROR_INVALID_DATA, 'FileIdRequired');
		}

		$file_row = $this->file_model->read(array('id'=>$file_id));
		if(empty($file_row['id']) || $file_id != $file_row['id']){
	    	return $this->error(self::STATUS_ERROR_OBJECT_NOT_EXIST, 'FileServiceFileRecordNotFound');
		}

		// Prepare default local path
        $file_row['local_prvdata_path'] = $this->get_path($file_row['folder'],$file_row['sys_name']);

        $file_storage_path = $this->get_private_storage_directory($file_row['local_prvdata_path']);

        if (!file_exists($file_storage_path)){
        	if(!empty($file_row['bucket_source'])){

				$provider = $this->get_bucket_provider($file_row['bucket_source']);
				if(!empty($provider)){

					$file_storage_dir = dirname( $file_storage_path);
					if(!is_dir($file_storage_dir) ){
						@mkdir($file_storage_dir, 0777, true);
					}

					if(!is_dir($file_storage_dir) ){

						$data = $provider->get_data($file_row);
						log_message('debug',__METHOD__.'@'.__LINE__.', write into path '.$file_storage_path);
						write_file($file_storage_path, $data);
					}
				}
			}
        }else{
			log_message('debug',__METHOD__.'@'.__LINE__.', file exist at '.$file_storage_path);
        }

		return read_file($file_storage_path);
	}

	/**
	 * Get file data
	 * 
	 * @param  [type] $file_id [description]
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	public function get_file($file_id, $options = null)
	{
		if(empty($file_id)){
	    	return $this->error(ERROR_INVALID_DATA, 'FileIdRequired');
		}

		$file_row = $this->file_model->read(array('id'=>$file_id));
		if(empty($file_row['id']) || $file_id != $file_row['id']){
	    	return $this->error(self::STATUS_ERROR_OBJECT_NOT_EXIST, 'FileServiceFileRecordNotFound');
		}

		// Prepare default local path
        $file_row['local_prvdata_path'] = $this->get_path($file_row['folder'],$file_row['sys_name']);

        $file_storage_path = $this->get_private_storage_directory($file_row['local_prvdata_path']);

        if (!file_exists($file_storage_path)){
        	if(!empty($file_row['bucket_source'])){

				$provider = $this->get_bucket_provider($file_row['bucket_source']);
				if(!empty($provider)){

					$file_storage_dir = dirname( $file_storage_path);
					if(!is_dir($file_storage_dir) ){
						@mkdir($file_storage_dir, 0777, true);
					}

					if(!is_dir($file_storage_dir) ){

						$data = $provider->get_data($file_row);
						log_message('debug',__METHOD__.'@'.__LINE__.', write into path '.$file_storage_path);

						write_file($file_storage_path, $data);
					}
				}
			}
        }else{
			log_message('debug',__METHOD__.'@'.__LINE__.', file exist at '.$file_storage_path);
        }

		

		return $file_row;
	}

	/**
	 * Check if the file id and it's path exist on cloud storage
	 * 
	 * @param  string $file_id [description]
	 * @return bool            [description]
	 */
	public function is_file_path_exist ($file_id)
	{
		$file_row = $this->get_file($file_id);
		if(!empty($file_row['prvdata_path']) && file_exists($file_row['prvdata_path']) && is_file($file_row['prvdata_path'])){
			return true;
		}

		return false;
	}

	/**
	 * Remove a file
	 * 
	 * @param  string $file_id [description]
	 * @return [type]          [description]
	 */
	public function remove_file($file_id)
	{
		$file_row = $this->get_file($file_id);

		if(!empty($file_row['id'])){
		    $this->file_model->remove(array('id'=>$file_row['id']));

		    $this->after_file_record_remove($file_row);
		}
	    return $file_row;
	}

	protected function after_file_record_remove($file_row)
	{
		if(!empty($file_row['bucket_source'])){
			$provider = $this->get_bucket_provider($file_row['bucket_source']);

			if(!empty($provider)){
				$provider->remove_data($file_row);
			}
		}

		$resources = $this->file_resource_model->find(['file_id'=>$file_row['id']]);

		if(is_array($resources) && !empty($resources)){
			foreach($resources as $file_resource_row){

				if(!empty($file_row['bucket_source'])){
					$provider = $this->get_bucket_provider($file_row['bucket_source']);

					if(!empty($provider)){
						$provider->remove_resource($file_row, $file_resource_row);
					}
				}else{
					$bucket_path = $file_resource_row['bucket_path'];

					// Get the storage path
					$target_file_path = $this->get_public_storage_directory($bucket_path);

					if(file_exists($target_file_path) && is_file($target_file_path)){
						@unlink($target_file_path);
					}
				}
			}

			$this->file_resource_model->remove(['file_id'=>$file_row['id']]);
		}

	}

	public function build_resource_path($file_row, $file_resource_row)
	{

		$directory = '';
		$file_name = '';

		// Get the path for public area
		if(!empty($file_resource_row['path'])){

			$directory .= $file_resource_row['path'];
		}


		// Get the file name for public area

		if(!empty($file_resource_row['sys_name'])){

			$file_name = $file_resource_row['sys_name'];
		}else{
			if(!empty($file_resource_row['filename_prefix'])){

				$file_name .=  $file_resource_row['filename_prefix'];
			}

			$pathinfo = pathinfo($file_row['sys_name']);

			$file_name .= $pathinfo['filename'];

			if(!empty($file_resource_row['filename_suffix'])){

				$file_name .=  $file_resource_row['filename_suffix'];
			}


			if(!empty($file_resource_row['file_ext'])){
				$file_name .= '.'.$file_resource_row['file_ext'];
			}elseif(!empty($file_row['file_ext'])){
				$file_name .= '.'.$file_row['file_ext'];
			}
		}

		$path = $this->get_path($directory, $file_name);

		return compact('directory','file_name','path');
	}

	public function get_resource($file_id, $options = null)
	{

		$queries = ['file_id' => $file_id];

		$file_row = $this->file_model->read(['id'=>$file_id]);
		if(empty($file_row['id'])){
			return $this->error(ERROR_INVALID_DATA, 'FileNotFound');
		}

		// Prepare location of temp folder
		$tmp_file_path = TMP_DIR. time().'_'.$file_row['sys_name'];


		// Get the path for public area
		if(!empty($options['path'])){
			$queries['path'] = $options['path'];
		}


		if(!empty($options['sys_name'])){
			$queries['sys_name'] = $options['sys_name'];
		}else{

			if(!empty($options['filename_prefix'])){
				$queries['filename_prefix'] = $options['filename_prefix'];
			}

			if(!empty($options['filename_suffix'])){
				$queries['filename_suffix'] = $options['filename_suffix'];
			}

			if(!empty($options['file_ext'])){
				$queries['file_ext'] = $options['file_ext'];
			}
		}

		$is_rebuild = data('rebuild', $options, false);

		$is_insert = false;
		$file_resource_row = $this->file_resource_model->read($queries);

		if(empty($file_resource_row['id'])){
			$is_insert = true;
			$file_resource_row = $queries;
		}

		$provider = $this->get_bucket_provider($file_row['bucket_source']);

		$file_resource_info = $this->build_resource_path($file_row, $file_resource_row);

		$is_resource_exist = false;

		$res_url = null;

		if(!empty($provider)){

			log_message('debug',__METHOD__.'@'.__LINE__.', use provider');

			$bucket_path = $file_resource_row['bucket_path'] = $file_resource_info['path'];

			$is_resource_exist = $provider->is_resource_exist($file_row, $file_resource_row);

			if($is_resource_exist){
				$res_url = $provider->get_resource_url($file_row, $file_resource_row);
			}

		}else{

			log_message('debug',__METHOD__.'@'.__LINE__.', use local storage');

			$bucket_path = $file_resource_row['bucket_path'] = $file_resource_info['path'];

			// Get the storage path
			$target_file_path = $this->get_public_storage_directory($bucket_path);

			if(file_exists($target_file_path)){
				$is_resource_exist = true;

				$res_url = $this->get_public_storage_url($bucket_path);
					log_message('debug', __METHOD__.'@'.__LINE__.', FileExist, data='.print_r(compact('bucket_path','target_file_path','res_url'),true));
			}else{

				// Pre-process, create target folder before action
				$target_file_directory = dirname($target_file_path);
				if(!is_dir($target_file_path)){
					log_message('debug', __METHOD__.'@'.__LINE__.', CreateDirectory, path='.$target_file_directory);
					@mkdir($target_file_directory, 0777, true);
				}

				if(!is_dir($target_file_directory)){
					log_message('error', __METHOD__.'@'.__LINE__.', FileServiceTargetDirectoryNotExist, path='.$target_file_directory);
					return $this->error(ERROR_INVALID_DATA, 'FileServiceTargetDirectoryNotExist');
				}

				if(!is_writable($target_file_directory)){
					log_message('error', __METHOD__.'@'.__LINE__.', FileServiceTargetDirectoryNotWritable, path='.$target_file_directory);
					return $this->error(ERROR_INVALID_DATA, 'FileServiceTargetDirectoryNotWritable');
				}

			}
		}

		log_message('debug',__METHOD__.'@'.__LINE__.'# data: '.print_r(compact('is_resource_exist','is_rebuild','queries','file_resource_info'), true));

		// If resource does not exist, we create it first
		if(!$is_resource_exist || $is_rebuild){

			// Prepare data from local storage
			$source_file_path = $this->get_private_storage_directory($this->get_path($file_row['folder'],$file_row['sys_name']));

			if(!empty($provider)){

				// Getting final data from recorded resource
				$source_data = $provider->get_data($file_row);

				$source_file_dir = dirname($source_file_path);

				if(!is_dir($source_file_dir)){
					@mkdir($source_file_dir, 0777, true);
				}

				// Write into local storage for temporary content creation
				write_file($source_file_path, $source_data);

				if(!file_exists($source_file_path)){

				log_message('error',__METHOD__.'@'.__LINE__.', get_data from provider into '.$source_file_path);
				}else{

				log_message('debug',__METHOD__.'@'.__LINE__.', get_data from provider into '.$source_file_path);
				}

			}else{

			}


			// If there any callable function, run it for new content
			if(!empty($options['content_callback'])){
				$file_resource_row = call_user_func($options['content_callback'], $file_row, $file_resource_row, $source_file_path, $tmp_file_path);

				if(file_exists($tmp_file_path)){
					log_message('debug',__METHOD__.'@'.__LINE__.', reading file at '.$tmp_file_path);
					$new_data = read_file($tmp_file_path);
				}else{
					log_message('error',__METHOD__.'@'.__LINE__.', temporary file not created');
				}
			}else{
				$new_data = $source_data;
			}


			if(!empty($new_data)){

				if(!empty($provider)){
					log_message('debug',__METHOD__.'@'.__LINE__.', put file resource.'.print_r($file_resource_row,true));
					$res_url = $provider->put_resource_data($file_row, $file_resource_row, $new_data);
				}else{

					// Get the storage path
					$target_file_path = $this->get_public_storage_directory($bucket_path);

					log_message('debug',__METHOD__.'@'.__LINE__.', writing file at '.$target_file_path);

					// Write into local public storage
					write_file($target_file_path, $new_data);

					if(!file_exists($target_file_path)){
						return $this->error(ERROR_INVALID_DATA, 'FileNotCreated');
					}

					$res_url = $this->get_public_storage_url($bucket_path);


					log_message('debug',__METHOD__.'@'.__LINE__.', store file into local storage.'.print_r(compact('target_file_path','res_url','bucket_path'),true));
				}

				if($is_insert){
					$this->file_resource_model->save($file_resource_row);
				}
			}
		}

		if(file_exists($tmp_file_path) && !data('keep_local_copy', $options, false)){
			@unlink($tmp_file_path);
		}

		return [
			'url'=>$res_url,
			'file_name'=>$file_resource_info['file_name'],
			'file_path'=>$tmp_file_path,
			'parameters'=>data('parameters',$file_resource_row),
			'directory'=>$file_resource_info['directory'],
			'requested_options'=>[
				'path'=>data('path',$options),
				'filename_prefix'=>data('filename_prefix',$options),
				'filename_suffix'=>data('filename_suffix',$options),
				'file_ext'=>data('file_ext',$options),
				'sys_name'=>data('sys_name',$options),
			],
		];

	}
}