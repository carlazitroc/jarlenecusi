<?php 

namespace Dynamotor\Modules;

use \Dynamotor\Core\HC_Module;

class PictureRendererModule extends HC_Module
{
	var $default_folder = 'picture';

	var $default_size_groups = [];

	public function __construct()
	{
		parent::__construct();

		$this->load->config('picture');
		$this->default_size_groups = $this->config->item('picture_sizes');
	}

    public $max_resize_width = 5000;
    public $max_resize_height = 5000;

	public function is_enable_logging (){ return true;}

	public function get_memory_size($width, $height )
	{

		// For true color, 5 bytes for pixel
		$amount = $width * $height * 5 * 8;

		// Normalized into M
		$amount_normalized = round( $amount / 1024 / 1024 );
		if($amount_normalized < 64) $amount_normalized = 64;

		return $amount_normalized;
	}

	public function set_max_memory_size()
	{
		$memory_size = $this->get_memory_size($this->max_resize_width, $this->max_resize_height).'M';
		log_message('error',__METHOD__.'@'.__LINE__.'# request memory size limit to '.$memory_size);
		ini_set('memory_limit', $memory_size );

		return TRUE;
	}

	public function set_memory_size($src_path, $options = null)
	{
		$this->set_max_memory_size();

		return TRUE;
	}
	
	public function resize($src_path, $dst_path, $options=null)
	{
		
		if(!file_exists($src_path)){
			log_message('error',__METHOD__.'@'.__LINE__.'# file not found: '.$src_path.' ');
		
			return FALSE;	
		}
		$info = @getimagesize($src_path);
		//log_message('error','PictureRenderer::resize, source image size:'.print_r($info,true));
		
		if(!isset($info[0]) || !isset($info[1])){
			log_message('error',__METHOD__.'@'.__LINE__.'# no size info for src_path '.$src_path.': '.json_encode($info));
			return FALSE;
		}
            
        // Check file resolution before resize
        if($info[0] <= 0 || $info[1] <= 0){
            log_message('error',__METHOD__.'@'.__LINE__.'#size-is-zero, src_path at '.$src_path);
            return null;
        }

        if($info[0] > $this->max_resize_width || $info[1] > $this->max_resize_height){
            log_message('error',__METHOD__.'@'.__LINE__.'#size-too-large, Src path at '.$src_path.' resolution['.$info[0].'x'.$info[1].']');
            return null;
        }
		
		if(!file_exists(dirname($dst_path))){
			log_message('error',__METHOD__.'@'.__LINE__.'# destination folder does not exist: '.dirname($dst_path).' ');
			return FALSE;
		}
		
		if(!is_writable(dirname($dst_path))){
			log_message('error',__METHOD__.'@'.__LINE__.'# destination folder does not writable: '.dirname($dst_path).' ');
			return FALSE;
		}
		
		if($this->is_enable_logging()){
			log_message('debug',__METHOD__.'@'.__LINE__.'# for '.$src_path.' to '.$dst_path.'  with options'.json_encode($options));
		}

		if(!$this->set_memory_size($src_path, $options)){
			log_message('error', __METHOD__.'@'.__LINE__.'# unable to set enough memory for image processing for src_path at '.$src_path);
			return FALSE;
		}
		
		$src_w = $info[0];
		$src_h = $info[1];
		
		$has_tar_w = false;
		$has_tar_h = false;
		$tar_w = $src_w;
		$tar_h = $src_h;
		
		if(isset($options ['width'])){
			$tar_w = $options ['width'];
			$has_tar_w = true;
		}
		if(isset($options ['height'])){
			$tar_h = $options ['height'];
			$has_tar_h = true;
		}
		
		$CI = &get_instance();
		$CI->load->library('image_lib');
		
		
		$quality = 100;
		if(isset($options['quality'])){
			$quality = $options['quality'];
		}
		
		if(isset($options['crop_x']) && isset($options['crop_y']) && isset($options['crop_width']) && isset($options['crop_height'])){
			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# [crop] before crop size ('.$info[0].'w x '.$info[1].'h)');
			}
			$CI->image_lib->clear();
			
			$cfg = array(
				'source_image'	=> $src_path,
				'new_image'		=> $dst_path,
				'maintain_ratio'=> FALSE,
				'x_axis'		=> $options['crop_x'],
				'y_axis'		=> $options['crop_y'],
				'width'			=> $options['crop_width'],
				'height'		=> $options['crop_height'],
				'quality'		=> $quality,
			);

			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# [crop] cfg ('.json_encode($cfg).')');
			}
			$CI->image_lib->initialize($cfg);

			// Bug fix: Image_lib does not load destination folder correctly in CI3.0 branch
			$CI->image_lib->dest_folder = dirname($dst_path);
			$CI->image_lib->dest_image = basename($dst_path);
			$CI->image_lib->full_dst_path = $dst_path;
			
			if($CI->image_lib->crop()){
				if(file_exists($dst_path)){
					$info = @getimagesize($dst_path);
					if($this->is_enable_logging()){
						log_message('debug',__METHOD__.'@'.__LINE__.'# [crop] after crop size ('.$info[0].'w x '.$info[1].'h)');
					}
					
					if($has_tar_w && $has_tar_h){
						
						$CI->image_lib->clear();
						$cfg = array(
							'source_image'	=> $dst_path,
							'new_image'		=> $dst_path,
							'width'			=> $tar_w,
							'height'		=> $tar_h,
							'maintain_ratio'=> FALSE,
							'quality'		=> $quality,
						);
						$CI->image_lib->initialize($cfg);

						// Bug fix: Image_lib does not load destination folder correctly in CI3.0 branch
						$CI->image_lib->dest_folder = dirname($dst_path);
						$CI->image_lib->dest_image = basename($dst_path);
						$CI->image_lib->full_dst_path = $dst_path;
							
						if(!$CI->image_lib->resize()){
							log_message('error',__METHOD__.'@'.__LINE__.'#[croparea] image_lib cannot perform with this setting:'.json_encode($cfg));
							return FALSE;
						}
						$info = @getimagesize($dst_path);

						if($this->is_enable_logging()){
							log_message('debug',__METHOD__.'@'.__LINE__.'# [crop] after resize ('.$info[0].'w x '.$info[1].'h)');
						}
					}
				}else{
					log_message('error',__METHOD__.'@'.__LINE__.'# [crop] file does not created after crop');
				}
				
				return TRUE;	
			}
			log_message('error',__METHOD__.'@'.__LINE__.'# [crop] cannot crop image by giving crop area setting');
			
		
			return FALSE;
		}
		
		
		$crop_x = 0;
		$crop_y = 0;
		
		$type = isset($options ['type']) ? ($options ['type']) : '';
		$crop = isset($options ['crop']) && $options ['crop'] === TRUE ? $options ['crop'] : false;
		
		if($type =='fill'){
			
			// scale to fill the boundary
			$scale = 1 ;
			if( $has_tar_w ){
				$scale = $tar_w / $src_w;;
				if($this->is_enable_logging()){
					log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by width | scale='.$scale);
				}
				if($has_tar_h && $scale * $src_h < $tar_h){
					$scale = $tar_h / $src_h;
					if($this->is_enable_logging()){
						log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by height | scale='.$scale);
					}
				}
			}elseif( $has_tar_h ){
				$scale = $tar_h / $src_h;
				if($this->is_enable_logging()){
					log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by height | scale='.$scale);
				}
			}
			if($scale > 1) $scale = 1;
		}else{
			
			// scale to fit the boundary
			$scale = 1;
			if($has_tar_w && $scale * $src_w > $tar_w)
				$scale = $tar_w / $src_w;

			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by width | scale='.$scale);
			}
			if($has_tar_h && $scale * $src_h > $tar_h){
				$scale = $tar_h / $src_h;
			}
			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by height | scale='.$scale);
			}
			if($scale > 1) $scale = 1;
		}
			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after scaled by fixed max scale | scale='.$scale);
			}
		
		$to_w = ceil($src_w * $scale);
		$to_h = ceil($src_h * $scale);
		$to_x = floor($tar_w * 0.5 - $to_w  * 0.5);
		$to_y = floor($tar_h * 0.5 - $to_h  * 0.5);
		
		if($this->is_enable_logging()){
			log_message('debug',__METHOD__.'@'.__LINE__.'# information of '.basename($src_path).': '.json_encode(compact(
				'crop','type','to_x','to_y','to_w','to_h','scale','src_w','src_h','has_tar_w','tar_w','has_tar_h','tar_h'
				)));
		}
	
		if($crop ){
			
			
			
			// step 1: scale image to target size
			$CI->image_lib->clear();
			$cfg = array(
				'source_image'	=> $src_path,
				'new_image'		=> $dst_path,
				'width'			=> $to_w,
				'height'		=> $to_h,
				'maintain_ratio'=> FALSE,
				'quality'		=> $quality,
			);
			$CI->image_lib->initialize($cfg);

			// Bug fix: Image_lib does not load destination folder correctly in CI3.0 branch
			$CI->image_lib->dest_folder = dirname($dst_path);
			$CI->image_lib->dest_image = basename($dst_path);
			$CI->image_lib->full_dst_path = $dst_path;
			
			if(!$CI->image_lib->resize()){
				log_message('error',__METHOD__.'@'.__LINE__.'[crop_1], image_lib cannot perform with this setting:'.json_encode($cfg));
				return FALSE;
			}
			
			if(!file_exists($dst_path)){
				log_message('error',__METHOD__.'@'.__LINE__.'# file does not created after resize');
				return FALSE;
			}
			$info = @getimagesize($dst_path);


			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after resize ('.$info[0].'w x '.$info[1].'h)');
			}
			
			
			// step 2: crop in center
			$CI->image_lib->clear();
			$cfg = array(
				'source_image'	=> $dst_path,
				'new_image'		=> $dst_path,
				'x_axis'		=> $to_x < 0 ? - $to_x : 0,
				'y_axis'		=> $to_y < 0 ? - $to_y : 0,
				'width'			=> $to_w < $tar_w ? ceil($to_w) : $tar_w,
				'height'		=> $to_h < $tar_h ? ceil($to_h) : $tar_h,
				'maintain_ratio'=> FALSE,
				'quality'		=> $quality,
			);
			$CI->image_lib->initialize($cfg);

			// Bug fix: Image_lib does not load destination folder correctly in CI3.0 branch
			$CI->image_lib->dest_folder = dirname($dst_path);
			$CI->image_lib->dest_image = basename($dst_path);
			$CI->image_lib->full_dst_path = $dst_path;
			
			if(!$CI->image_lib->crop()){
				log_message('error',__METHOD__.'@'.__LINE__.'[crop_2], image_lib cannot perform with this setting:'.json_encode($cfg));
				return FALSE;
			}
			$CI->image_lib->clear();
			
			if(!file_exists($dst_path)){
				log_message('error',__METHOD__.'@'.__LINE__.'# file does not created after crop');
				return FALSE;
			}
			$info = @getimagesize($dst_path);


			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after crop size ('.$info[0].'w x '.$info[1].'h)');
			}
			
			return TRUE;	
			
		}else{
				
			$CI->image_lib->clear();
			$cfg = array(
				'source_image'	=> $src_path,
				'new_image'		=> $dst_path,
				'width'			=> $to_w,
				'height'		=> $to_h,
				'maintain_ratio'=> FALSE,
			); 
			$CI->image_lib->initialize($cfg);

			// Bug fix: Image_lib does not load destination folder correctly in CI3.0 branch
			$CI->image_lib->dest_folder = dirname($dst_path);
			$CI->image_lib->dest_image = basename($dst_path);
			$CI->image_lib->full_dst_path = $dst_path;
			
			if(!$CI->image_lib->resize()){
				log_message('error',__METHOD__.'@'.__LINE__.'[resize_only], image_lib cannot perform with this setting:'.json_encode($cfg));
				return FALSE;
			}
			$CI->image_lib->clear();
			
			if(!file_exists($dst_path)){
				log_message('error',__METHOD__.'@'.__LINE__.'# file does not created after resize');
				return FALSE;
			}
			
   			
			$info = @getimagesize($dst_path);

			if($this->is_enable_logging()){
				log_message('debug',__METHOD__.'@'.__LINE__.'# after resize ('.$info[0].'w x '.$info[1].'h)');
			}
			
			return TRUE;
	
		}
	}

	public function get_size_info($file, $size_group='default',$size_name='default')
	{
		$pathinfo = is_string($file) ? pathinfo($file): pathinfo($file['sys_name']);
		
		$file_name = $pathinfo['filename'];
		$file_ext = $pathinfo['extension'];

		$_size_groups = $this->default_size_groups;
		$_size_group = null;
		$size_info = NULL;
		
		if(is_string($size_group)){
			if(isset($_size_groups[$size_group])){
				$_size_group = $_size_groups[$size_group];
			}
		}elseif(is_array($size_group)){
			$_size_group = $size_group;
		}
		
		if(!$_size_group && isset($size_groups['default'])){
			// default group must always available
			$_size_group = $size_groups['default'];
		}
		
		if(is_string($size_name)){
			if(isset($_size_group[$size_name])){
				$size_info = $_size_group[$size_name];
			}
		}
		
		if(!$size_info && isset($_size_group['default'])){
			// default group must always available
			$size_info = $_size_group['default'];
		}

		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		if(!isset($size_info) || empty($size_info)) {
			log_message('error',__METHOD__.'@'.__LINE__.'# empty size_info. config='.print_r(compact('_size_groups','size_group','size_name'),true));
			return NULL;
		}
		
		if(!empty($size_info['format'])){
			if(is_array($size_info['format']) && count($size_info['format']) > 0){
				if(!in_array($file_ext,$size_info['format'])){
					$file_ext = $size_info['format'][0];
				}
			}elseif(is_string($size_info['format'])){
				$file_ext = $size_info['format'];
			}
		}

		$file_ext = strtolower($file_ext);

		$size_info['file_ext'] = $file_ext;

		return $size_info;
	}
	
	public function make($file=false,$size_group='default',$size_name='default',$options=false)
	{
		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		$is_rebuild = isset($options['rebuild'] ) && $options['rebuild'] == true;
		$is_overwrite = isset($options['overwrite'] ) && $options['overwrite'] == true;
		$src_dir = isset($options['src'] ) ? $options['src'] : PRV_DATA_DIR.DS.$this->default_folder;
		$dest_dir = isset($options['dest'] ) ? $options['dest'] : $src_dir;
		
		$pathinfo = pathinfo($file['sys_name']);
		
		$file_name = $pathinfo['filename'];
		$file_ext = $pathinfo['extension'];


		log_message('debug',__METHOD__.'@'.__LINE__.'# parameters='.print_r(compact('file','file_name','file_ext','size_group','size_name','options','size_info','src_path','dest_path','is_rebuild','is_overwrite'), true));

		// if the it given sizetype group name,
		// then we try to load the picture sizetypes group
		$this->load->helper('data');
		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		$this->load->config('picture');
		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		
		$size_info = $this->get_size_info($file, $size_group, $size_name);
		
		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		if(!isset($size_info) || empty($size_info)) {
			log_message('error',__METHOD__.'@'.__LINE__.'# empty size_info. config='.print_r(compact('size_group','size_name'),true));
			return NULL;
		}
		
		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		$_options =  array();
		
		$dest_name = $file_name;
		$filename_prefix = '';
		$filename_suffix = '';

		if(isset($size_info['prefix'])) {
			$filename_prefix = $size_info['prefix'];
			$dest_name = $size_info['prefix']. $dest_name;
		}
		if(isset($size_info['suffix'])){
			$filename_suffix = $size_info['suffix'];
			$dest_name = $dest_name. $size_info['suffix'];
		}
		
		if(!empty($options['src_path'])){
			$src_path = $options['src_path'];
		}else{

			if(substr($src_dir,-1,1) != DIRECTORY_SEPARATOR){
				$src_dir .= DIRECTORY_SEPARATOR;
			}
			
			$src_path = $src_dir.$file['sys_name'];
		}
		
		if(!empty($options['dest_path'])){
			$dest_path = $options['dest_path'];
		}else{
			$dest_file = $dest_name.strtolower($file_ext);
			
			if(substr($dest_dir,-1,1) != DIRECTORY_SEPARATOR){
				$dest_dir .= DIRECTORY_SEPARATOR;
			}
			
			$dest_path = $dest_dir.$dest_file;

			if(!file_exists($dest_dir)){
				@mkdir($dest_dir,0777);
			}
			if(!is_writable($dest_dir)){
				log_message('error',__METHOD__.'@'.__LINE__.'#, '.$dest_dir.' not writable');
				return NULL;
			}
		}

		//log_message('debug','PictureRenderer::make, LINE '.__LINE__.': info='.json_encode(compact('size_info','src_path','dest_path','is_rebuild','is_overwrite')));
		
		if(!file_exists($dest_path) || $is_rebuild || $is_overwrite){
			
			$_options['type'] = 'fit';
			if( isset($size_info['width'])){
				$_options['width'] = $size_info['width'];
			}
			if( isset($size_info['height'])){
				$_options['height'] = $size_info['height'];
			}
			
			if(isset($size_info['crop']) && $size_info['crop']) 
				$_options['crop'] = true;
			if(isset($size_info['scale'] ) ){
				if( $size_info['scale'] == 'fit-fill' || $size_info['scale']  == 'fill'){
					$_options['type'] = 'fill';
				}
			}
			if(isset($size_info['quality'])) 
				$_options['quality'] = $size_info['quality'];
			
			if(!empty($options['croparea'])){
				
				if($this->is_enable_logging()){
					log_message('debug',__METHOD__.'@'.__LINE__.'# [croparea],'.$options['croparea']);
				}
				try{
					$croparea = NULL;
					if(is_array($options['croparea'])){
						$croparea = $options['croparea'];
					}elseif(is_string($options['croparea'])){
						if(substr($options['croparea'],0,1)=='{'){
							$croparea = json_decode($options['croparea'],true);
						}else{
							list($cx,$cy,$cw,$ch) = explode(',',$options['croparea'],5);
							$croparea['x'] = $cx;
							$croparea['y'] = $cy;
							$croparea['width'] = $cw;
							$croparea['height'] = $ch;
						}
						
					}

					if(isset($croparea[$size_name])){
						
						if(isset($croparea[$size_name]['width'])){
							$_options['crop_x'] = 0;
							$_options['crop_width'] = ($croparea[$size_name]['width']);
						}
						if(isset($croparea[$size_name]['height'])){
							$_options['crop_y'] = 0;
							$_options['crop_height'] = ($croparea[$size_name]['height']);
						}
						if(isset($croparea[$size_name]['x'])){
							$_options['crop_x'] = ($croparea[$size_name]['x']);
						}
						if(isset($croparea[$size_name]['y'])){
							$_options['crop_y'] = ($croparea[$size_name]['y']);
						}
					}elseif(isset($croparea['x']) && isset($croparea['y']) && isset($croparea['width']) && isset($croparea['height'])){
						$_options['crop_x'] = $croparea['x'];
						$_options['crop_y'] = $croparea['y'];
						$_options['crop_width'] = $croparea['width'];
						$_options['crop_height'] = $croparea['height'];
					}
				}catch(Exception $exp){
					
				}
			}

			if(isset($options['crop_x']) && isset($options['crop_y']) && isset($options['crop_width']) && isset($options['crop_height'])){
				$_options['crop_x'] = $options['crop_x'];
				$_options['crop_y'] = $options['crop_y'];
				$_options['crop_width'] = $options['crop_width'];
				$_options['crop_height'] = $options['crop_height'];
			}
			
			$this->resize( $src_path, $dest_path, $_options);
		}
		
		if(!file_exists($dest_path)){
			log_message('error',__METHOD__.'@'.__LINE__.'#no_image_created='.$dest_path);
			return NULL;
		}
		if(isset($options['full'])){
			return compact('dest_path','dest_name','filename_prefix','filename_suffix');
		}
		return $dest_file;
	}	
}