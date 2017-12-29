<?php 
namespace Dynamotor\Controllers\Portal;

// MY_Controller should be defined in apps/core/MY_Controller.php
use \MY_Controller;
use \Dynamotor\Helpers\ResourceHelper;

// Base Controller class for PostHelper
// contains default behaviour of 
class CustomRouteController extends MY_Controller
{
	public function _remap()
	{
		return $this->do_custom_route();
	}
}
