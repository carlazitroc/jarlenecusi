<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/*
|--------------------------------------------------------------------------
| Error code for response
|--------------------------------------------------------------------------
|
| These codes will be used to response data request
|
*/

define('ERROR_INVALID_SESSION',120);
define('ERROR_INVALID_SESSION_MSG','error_require_session');
define('ERROR_MISSING_PERMISSION',121);
define('ERROR_MISSING_PERMISSION_MSG','error_require_permission');
define('ERROR_GAPI_CONFIG_INCORRECT',135);
define('ERROR_NO_RECORD_LOADED',150);
define('ERROR_INVALID_DATA',200);
define('ERROR_RECORD_SAVE_ERROR',221);
define('ERROR_RECORD_VALIDATION',222);
define('ERROR_RECORD_VALIDATION_MSG','error_form_validation');
define('ERROR_RECORD_PROCESSING',311);
define('ERROR_EDM_NOT_FOUND',331);
define('ERROR_EDM_CONTENT_PROCESSING',332);
define('ERROR_EDM_CONTENT_DRAFT_MODE',333);
define('ERROR_EDM_CONTENT_NO_RECIPIENT',334);
define('ERROR_FILE_NOT_IMAGE',501);
define('ERROR_FILE_CANNOT_BUILD_CACHE',502);
define('ERROR_FILE_SYSTEM_FILE_NOT_EXIST',503);
define('ERROR_FILE_SOURCE_NOT_EXIST',504);


/* End of file constants.php */
/* Location: ./application/config/constants.php */