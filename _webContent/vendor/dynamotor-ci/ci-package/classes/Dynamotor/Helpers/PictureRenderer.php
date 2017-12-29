<?php

namespace Dynamotor\Helpers;

use \Dynamotor\Modules\PictureRendererModule;

class PictureRenderer
{

	static protected function get_renderer()
	{
		$CI = &get_instance();
		return $CI->load->singleton('Dynamotor.Modules.PictureRendererModule', null, 'pictureRenderer');
	}

	static function resize($src_filename, $dst_filename, $options=null)
	{
		$renderer = self::get_renderer();

		return $renderer->get_name($src_filename, $dst_filename, $options);
	}

	static function get_size_info($file, $size_group=null, $size_name=null)
	{
		$renderer = self::get_renderer();

		return $renderer->get_size_info($file, $size_group, $size_name);
	}
	
	static function make($file=null,$size_group=null,$size_name=null,$options=null)
	{
		$renderer = self::get_renderer();

		return $renderer->make($file, $size_group, $size_name, $options);
	}	
}
