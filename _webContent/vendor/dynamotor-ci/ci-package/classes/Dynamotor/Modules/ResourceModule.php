<?php
/** 
* Resource module for CodeIgniter
* @author      leman 
* @copyright   Copyright (c) 2015, LMSWork. 
* @link        http://lmswork.com 
* @since       Version 1.0 
*  
*/

namespace Dynamotor\Modules;

use \Dynamotor\Helpers\PictureRenderer;

class ResourceModule extends \Dynamotor\Core\HC_Module
{

    public function __construct(){
        parent::__construct();
    }

    protected function _init_models(){

        // load cache helper if it does not exist
        $CI = &get_instance();
        if(!function_exists('cache_get')){
            $this->load->helper('cache');
        }

        if(!isset($CI->file)){
            try{
                $CI->file = $CI->singleton('Dynamotor.Modules.FileModule');
            }catch(Exception $exp){}
        }


        // load File model if it does not exist
        if(!class_exists('Album_model')){
            $this->load->model(array('album_model','album_photo_model'));
        }
    }

    public function file_path($file_row){

        $url = $this->uploaded_file($file_row,$this->is_refresh,'files');

        return $url;
    }

    public function file_url($file_row, $options = NULL){

        $url = $this->uploaded_file_url($file_row,$this->is_refresh,'files','files',$options);

        return $url;
    }

    public function uploaded_file($file,$rebuild=false,$source_path='files'){

        $file_row = $this->file->get_file($file['id']);

        $options = [
            'path'=>'',
            'keep_local_copy'=>true,
            'content_callback' => function($file_row, $file_resource_row, $source_file_path, $tmp_file_path) {

                if(!file_exists($tmp_file_path))
                    @copy($source_file_path, $tmp_file_path);

                log_message('debug', __METHOD__.'@'.__LINE__.', file_row='.print_r($file_row, true));

                // Copy mime-type for copied file
                $mime_type = get_mime_by_extension($file_row['file_ext']);


                if(!empty($mime_type))
                    $file_resource_row['parameters']['mime_type'] = $mime_type;

                return $file_resource_row;
            }
        ];
        $resource_info = $this->file->get_resource($file_row['id'], $options);

        return $resource_info['file_path'];

    }

    public function public_uploaded_file($file,$rebuild=false,$source_path='files', $dest_path = 'files'){
	
		if(is_string($file))
       		$file_row = $this->file->get_file($file);
		elseif(isset($file['id']))
       		$file_row = $this->file->get_file($file['id']);
		if(empty($file_row['id']))
			return NULL;

        $options = [
            'path'=>$dest_path,
            'content_callback' => function($file_row, $file_resource_row, $source_file_path, $tmp_file_path) {

                if(!file_exists($tmp_file_path))
                    @copy($source_file_path, $tmp_file_path);

                log_message('debug', __METHOD__.'@'.__LINE__.', file_row='.print_r($file_row, true));

                // Copy mime-type for copied file
                $mime_type = get_mime_by_extension($file_row['file_ext']);

                if(!empty($mime_type))
                    $file_resource_row['parameters']['mime_type'] = $mime_type;

                return $file_resource_row;
            }
        ];
        $resource_info = $this->file->get_resource($file_row['id'], $options);

        return $resource_info;

    }

    public function public_uploaded_file_url($file,$rebuild=false,$source_path='files', $dest_path = 'files'){
        
        $resource_info = $this->public_uploaded_file($file, $rebuild, $source_path, $dest_path);

        if(!empty($resource_info['url'])){
            return $resource_info['url'];
        }
        return null;
    }

    public function uploaded_file_url($file,$rebuild=false,$source_path='files', $dest_path = 'files'){
        
        $resource_info = $this->public_uploaded_file($file, $rebuild, $source_path, $dest_path);

        if(!empty($resource_info['url'])){
            return $resource_info['url'];
        }
        return null;
    }

    public function picture_mapping($file_row,$group='file',$size='thumb',$subpath = false,$options = false,&$image_info=false){
        if(is_array($file_row) && !isset($file_row['id'])){
            $outputs = array();
            foreach($file_row as $file){
                $_image_info = false;
                $image_output = $this->picture_mapping( $file, $group,$size,$subpath,$options,$_image_info);
                if(!empty($image_output)){
                    $outputs[] = $image_output;
                    $image_info[] = $_image_info;
                }
            }
            return $outputs;
        }
        $dest_path = '';
        $dest_path_seg = array();
        
        if(!empty($subpath)){
            if(is_array($subpath)){
                $dest_path = implode('/', $subpath);
                $dest_path_seg = $subpath;
            }else{
                $dest_path = $subpath;
                $dest_path_seg = explode('/', $dest_path);
            }
        }

        if(!$image_info) 
            $image_info = array();

        $croparea = NULL;
        if(isset($options['croparea']))
        	$croparea = $options['croparea'];

        $this->_init_models();

        $src_path = 'files';
        if(isset($options['src_path'])) $src_path = $options['src_path'];

        $config = [
            'file'=>$file_row,
            'size_group'=>$group, 
            'size_name'=>$size, 
            'rebuild'=> $this->config->item('is_refresh'), 
            'src_folder'=>$src_path, 
            'dest_folder'=>$dest_path_seg,
            'croparea'=>$croparea
        ];

        $resource_info = $this->picture($config);

        log_message('debug',__METHOD__.'@'.__LINE__.', result: '.print_r($resource_info, true).' config '.print_r($config,true));
        if(empty($resource_info['url'])){
            return NULL;
        }

        

        $output = array();
        if(!empty($resource_info['url'])){
            $output['url'] = $resource_info['url'];
            if(! preg_match('#^https?\:\/\/#',  $output['url'] ) ) 
                 $output['url'] = base_url( $output['url'] );
        }

        if(!empty($resource_info['parameters']['image_info'])){
            $image_info = $resource_info['parameters']['image_info'];
            $output['width'] = $image_info['width'];
            $output['height'] = $image_info['height'];
        }
        return $output;
    }

    public function picture($config, $options = NULL)
    {
        $id = NULL;
        $file_row = NULL;
        $_cfg = NULL;
        if(isset($config['file']) ){

            if(is_array($config['file']) && isset($config['file']['sys_name']))
                $file_row = $config['file'];
            elseif(is_string($config['file']))
                $file_row = $this->file->get_file($config['file']);

        }elseif(is_array($config) && isset($config['sys_name'])){

            $file_row = $config;

        }elseif(is_array($config) && isset($config['id'])){
            $id = $config['id'];
            $_cfg = $config;

            $file_row = $this->get_file($id,!$rebuild);

        }elseif(is_string($config)){
            $id = $config;
            $file_row = $this->get_file($id,!$rebuild);
        }
        
            
        if(empty($file_row['sys_name'])){
            log_message('error',__METHOD__.'#record-not-found, passing='.print_r($config,true));
            return NULL;
        }
        
        $size_group = 'default';
        $size_name = 'default';
        $src_folder = 'files';
        $dest_folder = 'pictures';
        $croparea = null;
        $rebuild = false;

        $image_info = [];
        
        if(isset($config['size_group'])) $size_group = $config['size_group'];
        if(isset($config['size_name'])) $size_name = $config['size_name'];
        if(isset($config['rebuild'])) $rebuild = $config['rebuild'] == true;
        if(isset($config['src_folder'])) $src_folder = $config['src_folder'];
        if(isset($config['dest_folder'])) $dest_folder = $config['dest_folder'];
        if(isset($config['croparea'])) $croparea = $config['croparea'];
        
        if(empty($size_group)) $size_group ='default';
        if(empty($size_name)) $size_name ='default';
        if(empty($src_folder)) $src_folder ='files';
        if(empty($dest_folder)) $dest_folder ='pictures';

        if(empty($file_row['sys_name']))
           $file_row = $this->file->get_file($file_row['id']);

        log_message('debug',__METHOD__.'@'.__LINE__.', config:'.print_r(compact('config','options'),true));

        $size_info = PictureRenderer::get_size_info($file_row['sys_name'], $size_group, $size_name);

        if(empty($size_info)){
            log_message('debug',__METHOD__.'@'.__LINE__.', size_info '.print_r($size_info, true));
        }


        $options = [
            'filename_prefix'=>data('prefix',$size_info),
            'filename_suffix'=>data('suffix',$size_info),
            'file_ext'=>data('file_ext',$size_info),
            'path'=>$dest_folder,
            'rebuild'=>$rebuild,
            'content_callback' => function($file_row, $file_resource_row, $source_file_path, $tmp_file_path) use($rebuild, $croparea, $size_group, $size_name, $size_info) {

                $opts = array(
                    'src_path'=>$source_file_path,
                    'dest_path'=>$tmp_file_path,
                    'rebuild'=>$rebuild,
                    'full'=>true,
                );
                
                if(!empty($croparea))
                    $opts['croparea'] = $croparea;

                log_message('debug',__METHOD__.'@'.__LINE__.', gen content for opts '.print_r($opts, true));

                $result = PictureRenderer::make($file_row, $size_group,  $size_name, $opts);


                if(!file_exists($tmp_file_path)){

                    log_message('error',__METHOD__.'@'.__LINE__.', error after gen content for opts '.print_r(compact('result','tmp_file_path','file_resource_row'), true));

                }else{
                    $_image_info = getimagesize($tmp_file_path);
                    
                    $image_info = array(
                        'width'=>$_image_info[0],
                        'height'=>$_image_info[1],
                        'type'=>$_image_info[2],
                        'mime_type'=>$_image_info['mime'],
                        'bits'=>$_image_info['bits'],
                        'file_size'=>filesize($tmp_file_path),
                        //'dir'=>$tmp_file_path,
                    );

                    $file_resource_row['parameters']['mime_type'] = $_image_info['mime'];
                    $file_resource_row['parameters']['image_info'] = $image_info;


                    log_message('debug',__METHOD__.'@'.__LINE__.', details after gen content for opts '.print_r(compact('result','tmp_file_path','file_resource_row','image_info'), true));
                }



                return $file_resource_row;
            }
        ];
        $resource_info = $this->file->get_resource($file_row['id'], $options);

        if(isset($resource_info['parameters']['image_info']))
            $image_info = $resource_info['parameters']['image_info'];

        return $resource_info;
    }

    public function picture_url($file, $size_group='default', $size_name='default', $rebuild=false, &$image_info=false,$src_folder='files',$dest_folder='pictures',$croparea=NULL, $options = NULL){
        
        $config = [
            'file'=>$file,
            'size_group'=>$size_group, 
            'size_name'=>$size_name, 
            'rebuild'=> $rebuild, 
            'src_folder'=>$src_folder, 
            'dest_folder'=>$dest_folder,
            'croparea'=>$croparea
        ];

        $resource_info = $this->picture($config, $options);

        if(!empty($resource_info['image_info'])){
            $image_info = $resource_info['image_info'];
        }

        if(!empty($resource_info['url'])){
            return $resource_info['url'];
        }

        return;
    }

    public function get_file($file_id, $options = NULL, $cache_ttl = 3600){
        return $this->file->get_file($file_id);
    }

    public function reset_file_cache($r){

    }


    public function get_album($album_id, $options = NULL, $cache_ttl = 3600){

        if(is_array($album_id) && isset($album_id['id'])){
            $options = $album_id;
            $album_id = $album_id['id'];
            unset($options['id']);
        }
        if(!isset($options['is_live'])) $options['is_live'] = '1';
        $options['_order_by'] = array('sequence'=>'asc');
        $options['album_id'] = $album_id;

        // load cache helper if it does not exist
        $CI = &get_instance();

        $cache_key = 'res/album/'.$album_id.'/is_live_'.$options['is_live'];

        $locale_code = '';

        if( $this->lang->locale() !='' ){
            $locale_code = $this->lang->locale();
            $cache_key .='/'.$locale_code;
        }

        if(!function_exists('cache_get')){
            $this->load->helper('cache');
        }

        $cache_allowed = false;
        if( is_bool($cache_ttl)){
            $cache_allowed = $load_cache;
            $cache_ttl = 3600;
        } else{
            $cache_allowed = true;
        }

        $r = cache_get($cache_key);

        if(!$cache_allowed || empty($r)){

            $this->_init_models();
            
            $query = array('id'=>$album_id,'is_live'=>$options['is_live']);
            $r = $this ->album_model->read($query);
            if(empty($r['id'])){
                return NULL;
            }
            $photo_files = $this ->album_photo_model->find( $options );
            
            $r['photos'] = array();
            if(is_array($photo_files)){
                foreach($photo_files as $photo_info){
                    $file_id = $photo_info['main_file_id'];

                    // override file id for multiple language
                    if( $this->lang->locale() !='' ){
                        if(!empty($photo_info['parameters']['loc'][ $locale_code  ]['file_id'])){
                            $file_id = $photo_info['parameters']['loc'][ $locale_code  ]['file_id'];
                        }
                    }

                    $file = $this->get_file($file_id, NULL, $cache_ttl);

                    $file['default_file_id'] = $photo_info['main_file_id'];
                    $file['parameters'] = $photo_info['parameters'];
                    if(isset($photo_info['is_live']))
                        $file['is_live'] = $photo_info['is_live'];
                    $file['relation_id'] = $photo_info['id'];

                    $r['photos'] [] = $file;
                }
            }
            if($cache_ttl>0){
                cache_set($cache_key, $r,$cache_ttl);
                log_message('debug', __METHOD__.', saving cache for album '.$album_id.' by key '.$cache_key);
            }
        }else{
            log_message('debug', __METHOD__.', getting cache for album '.$album_id.' by key '.$cache_key);
        }
        return $r;
    }

    public function reset_album_cache($r){
        $cache_key = 'res/album/'.$r['id'].'';

        // load cache helper if it does not exist
        $CI = &get_instance();
        if(!function_exists('cache_get')){
            $this->load->helper('cache');
        }

        log_message('debug', __METHOD__.', removing cache for album '.$r['id'].' by key '.$cache_key);
        cache_remove($cache_key);

    }
}
