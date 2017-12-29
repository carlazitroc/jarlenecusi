<?php
// Load the boot.php for app start.
require_once dirname(__FILE__)."/boot.php";

if(empty($system_dir)){
	$system_dir = VENDOR_DIR . 'codeigniter/framework/system/';
}
if(!defined('CI_SYSTEM_DIR')) define('CI_SYSTEM_DIR',$system_dir);


/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

// Set the current directory correctly for CLI requests
if (defined('STDIN'))
{
	chdir(dirname(__FILE__));
}

if (realpath($system_dir) !== FALSE)
{
	$system_dir = realpath($system_dir).'/';
}

// ensure there's a trailing slash
$system_dir = rtrim($system_dir, '/').'/';

// Is the system path correct?
if ( ! is_dir($system_dir))
{
	exit("Your system folder path does not appear to be set correctly ($system_dir).");
}

/*
* -------------------------------------------------------------------
*  Now that we know the path, set the main path constants
* -------------------------------------------------------------------
*/
// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// The PHP file extension
// this global constant is deprecated.
define('EXT', '.php');

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_dir));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

// The path to the "application" folder
if (is_dir($application_dir))
{
	define('APPPATH', $application_dir.'/');
}
else
{
	if ( ! is_dir(BASEPATH.$application_dir.'/'))
	{
		exit("Your application folder path does not appear to be set correctly.");
	}

	define('APPPATH', BASEPATH.$application_dir.'/');
}

/*
* --------------------------------------------------------------------
* LOAD THE BOOTSTRAP FILE
* --------------------------------------------------------------------
*
* And away we go...
*
*/
if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
		case 'testing':
			ini_set('display_errors','yes');
			error_reporting(E_ALL);
			define('SHOW_DEBUG_BACKTRACE', TRUE);
		break;
	
		case 'staging':
		case 'production':
			ini_set('display_errors','no');
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

// create cache diectory path
if(defined('CACHE_DIR') && !is_dir(CACHE_DIR)){
	@mkdir(CACHE_DIR,0777,true);
}

// create log diectory path
if(defined('LOG_DIR') && !is_dir(LOG_DIR)){
	@mkdir(LOG_DIR,0777,true);
}

// present system error path
if(is_dir(LOG_DIR)){
	$date = date("Y-m-d");
	$path = LOG_DIR.DS.'error-'.$date.'.log';
	ini_set("log_errors", 1);
	ini_set('error_log',$path);
}

// present system session directory. we will not put the data in shared session pool.
if(!is_dir(SESSION_DIR)){
	@mkdir(SESSION_DIR,0777,true);

	if(is_dir(SESSION_DIR) && is_writable(SESSION_DIR))
		session_save_path(SESSION_DIR);
}

// load composer autoloader
if(is_file(VENDOR_DIR.'autoload.php')){
	require_once VENDOR_DIR.'autoload.php';
}

// load dynamotor-shared core library
if(is_file(VENDOR_DIR.'/dynamotor-ci/ci-package/autoload.php')){
	require_once VENDOR_DIR.'/dynamotor-ci/ci-package/autoload.php';
}

require_once BASEPATH.'core/CodeIgniter.php';
