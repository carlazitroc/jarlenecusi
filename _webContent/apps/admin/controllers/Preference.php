<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Parent class is defined at /vendor/dynamotor-ci/ci-package/classes/Dynamotor/Controllers/Admin/PreferenceController.php
// Please update configuration at /packages/corecms/config/dynamotor.php for the details

use \Dynamotor\Controllers\Admin\PreferenceController;

class Preference extends PreferenceController
{
	var $scope = 'default';
	var $config_key = 'pref_sections';



	protected function after_save(){
		parent::after_save();

		$lines = array();

		$timezone = $this->pref_model->item('timezone');
		if(!empty($timezone)){
			$lines[] = "if(function_exists('date_default_timezone_set')){date_default_timezone_set('".$timezone."');}";
		}

		$content = '<'.'?php '. "\r\n// This is system generated content.\r\n// Do not modify this file and it will be replaced by system. \r\n\r\n".implode("\r\n", $lines);

		write_file(TMP_DIR.'/system_preconfigured.php', $content);
	}
}