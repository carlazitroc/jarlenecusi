<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = "welcome";
$route['404_override'] = 'welcome/page_not_found';

$mm_rule = '([a-z]{2}-[a-z]{2}|[a-z]{2})\/';
$route[$mm_rule.'s\/([a-zA-Z]+)\/(.+)'] = 'ph/$3';
$route[$mm_rule.'s\/([a-zA-Z]+)\/?'] = 'ph/welcome';
$route[$mm_rule.'(.+)'] = '$2';
$route[$mm_rule.'{0,1}'] = 'welcome';
$route['s\/([a-zA-Z]+)\/(.+)'] = 'ph/$2';
$route['s\/([a-zA-Z]+)\/?'] = 'ph/welcome';



/* End of file routes.php */
/* Location: ./application/config/routes.php */