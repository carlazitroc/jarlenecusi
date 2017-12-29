<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Parent class is defined at /vendor/dynamotor-ci/ci-package/classes/Dynamotor/Controllers/Admin/PreferenceController.php
// Please update configuration at /packages/corecms/config/dynamotor.php for the details

use Dynamotor\Controllers\Admin\CoreController;

class Misc extends CoreController
{

	public function cacheClear()
	{
		if($this->_restrict('SYSTEM_CACHE_CLEAR')) return;

		cache_remove_all();

		return $this->_api(array('success'=>true));
	}

	public function cacheInfo(){

		if($this->_restrict('SYSTEM_CACHE_INFO')) return;
		var_dump(cache_get_instance()->cache_info());

	}
}