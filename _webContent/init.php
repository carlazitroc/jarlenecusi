<?php

// Set system paths for loading core libraries, helpers, and also Codeigniter System.
$site_dir = (dirname(dirname(__FILE__))).''; // root directory of all resource.
$webcontent_dir = $site_dir.'/'.basename(dirname(__FILE__)); // leave it NULL if using default location
$private_data_dir = NULL; // leave it NULL if using default location
$public_data_dir = NULL; // leave it NULL if using default location
$system_dir = NULL; // Codeigniter's system folder

// if config.php is available under this directory. it will be loaded 
if(file_exists(dirname(__FILE__)."/config.php"))
	require_once dirname(__FILE__)."/config.php";

/*****************************************************************/
// Set system default timezone.
if(function_exists('date_default_timezone_set')){
	date_default_timezone_set('GMT');	
}
/*****************************************************************/