<?php

/**
 * Package: Dynamotor\Modules
 */

// Define package namespace
namespace Dynamotor\Modules;

use \Dynamotor\Modules\FileModule;

interface IFileBucketProvider 
{
	public function get_bucket_source();

	public function set_parent(FileModule $file_module);
	public function put_data(&$file_record, $file_data);
	public function put_file(&$file_record, $file_path);
	public function get_data($file_record);
	public function remove_data($file_record);

	public function is_resource_exist($file_record, $file_resource);
	public function get_resource_url($file_record, $file_resource);
	public function put_resource_data($file_record, &$file_resource, $new_data = null);
	public function put_resource_file($file_record, &$file_resource, $new_file_path = null);
	public function remove_resource($file_record, $file_resource);
}
