<?php
// Application-scope configuration
// This file will be loaded by logviewer.php and application/index.php 

// Load the init file to load shared config.
require_once dirname(dirname(dirname(__FILE__))).'/init.php';

// set applocation paths
$application_id = basename(dirname(__FILE__));
$application_dir = $webcontent_dir.'/apps/'.$application_id;

/*****************************************************************/
// Environment
//
// create an file called "env" under this directory and present  
// environment mode. default value is "production"
// possible values are "development", "testing" and "production"
if(!defined('ENVIRONMENT')){
	$env = 'production';
	$env_path = dirname(__FILE__).'/env';
	if(file_exists($env_path) && is_file($env_path)){
		$_file_content = file_get_contents($env_path);
		if(!empty($_file_content) && preg_match('/^[\w]+$/', $_file_content)){
			$env = $_file_content;
		}
	}
	define('ENVIRONMENT', $env);
}
	

/*****************************************************************/
// Other core constants
if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
if(!defined('APP_DIR')) define('APP_DIR',dirname(__FILE__).DS);
if(!defined('WEBCONTENT_DIR')) define('WEBCONTENT_DIR',isset($webcontent_dir) &&!empty($webcontent_dir) ? realpath($webcontent_dir).DS : APP_DIR.DS); // directory of web applications (Limited access for this area)
if(!defined('SITE_DIR')) define('SITE_DIR',isset($site_dir) &&!empty($site_dir) ? realpath($site_dir).DS : APP_DIR.DS); // public directory of accessing scripts
if(!defined('PRV_DATA_DIR')) define('PRV_DATA_DIR', isset($private_data_dir) &&!empty($private_data_dir) ? realpath($private_data_dir) : WEBCONTENT_DIR .'prvdata'.DS ); // directory of storing private data (cache, certificates, originial files...)
if(!defined('VENDOR_DIR')) define('VENDOR_DIR', isset($vendor_dir) && !empty($vendor_dir) ? realpath($vendor_dir) : WEBCONTENT_DIR.'vendor'.DS); // directory of tmp files contains cache, session...
if(!defined('TMP_DIR')) define('TMP_DIR', isset($tmp_dir) && !empty($tmp_dir) ? realpath($tmp_dir) : WEBCONTENT_DIR.'tmp'.DS); // directory of tmp files contains cache, session...
if(!defined('CACHE_DIR')) define('CACHE_DIR', isset($cache_dir) && !empty($cache_dir) ? realpath($cache_dir) : TMP_DIR.'cache'.DS); // directory of cache files
if(!defined('SESSION_DIR')) define('SESSION_DIR', isset($session_dir) && !empty($session_dir) ? realpath($session_dir) : TMP_DIR.'session'.DS); // directory of cache files
if(!defined('PUB_DIR')) define('PUB_DIR', isset($pub_dir) && !empty($pub_dir) ? realpath($pub_dir) : SITE_DIR.'pub'.DS); // directory of public files (pre-generated image thumbnail, files...)
if(!defined('PACKAGE_DIR')) define('PACKAGE_DIR', isset($package_dir) && !empty($package_dir) ? realpath($package_dir) : WEBCONTENT_DIR.'packages'.DS);
if(!defined('LOG_DIR')) define('LOG_DIR', isset($log_dir) && !empty($log_dir) ? realpath($log_dir) : WEBCONTENT_DIR.'logs'.DS.$application_id.DS);
if(!defined('VIEWPATH')) define('VIEWPATH',APP_DIR.'views'.DS);
