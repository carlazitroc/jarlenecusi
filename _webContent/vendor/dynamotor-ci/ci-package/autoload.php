<?php

//use \Exception;
//use \CI_Exceptions;

spl_autoload_register(function ($class_name) {

	//print "spl_autoload_register($class_name)<br />\r\n";

	$package_dir = defined('PACKAGE_DIR') ? PACKAGE_DIR : dirname( dirname(__FILE__));
	$my_dir = ( dirname(__FILE__));


	$class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) .'.php';

	$target_dirs = array();
	if(substr($class_name,0,3) == 'CI_'){
		$target_dirs[] = realpath(CI_SYSTEM_DIR).DIRECTORY_SEPARATOR;

		$class_path = substr($class_path,3);
	}
	if(defined('APP_DIR')){
		$target_dirs[] = realpath(APP_DIR).DIRECTORY_SEPARATOR;
	}

	// include my classes
	$target_dirs[] = realpath($my_dir).DIRECTORY_SEPARATOR;


	if(!isset($GLOBALS['__spl_dynamotor_package_folders'])){
		$GLOBALS['__spl_dynamotor_package_folders'] = array();
		$op = opendir($package_dir);

		if($op){
			while($file = readdir($op)){
				if($file == '.' || $file == '..') continue;
				if(!is_dir($package_dir.$file)) continue;
				$GLOBALS['__spl_dynamotor_package_folders'][] = $package_dir.$file.DIRECTORY_SEPARATOR;
			}
			closedir($op);
		}
	}
	if(!empty($GLOBALS['__spl_dynamotor_package_folders']) && is_array($GLOBALS['__spl_dynamotor_package_folders'])){

		foreach($GLOBALS['__spl_dynamotor_package_folders'] as $_package_dir){
			$target_dirs[] = $_package_dir;
		}
	}

	$subpaths = array('classes','core','libraries','models');

	foreach($target_dirs as $target_dir){
		foreach($subpaths as $subpath){
			$file_path = $target_dir.$subpath.DIRECTORY_SEPARATOR.$class_path;
			//print "$class_name: $file_path <br />\r\n";
			if(file_exists($file_path)){
			    require_once $file_path;
			    return ;
			}
			$file_path = $target_dir.$subpath.DIRECTORY_SEPARATOR.strtolower($class_path);
		//	print "$class_name: $file_path <br />\r\n";
			if(file_exists($file_path)){
			    require_once $file_path;
			    return ;
			}
		}
	}
});

// Hack of CI's load_class
function &load_class($class, $directory = 'libraries', $param = NULL)
{
	static $_classes = array();

	// Does the class exist? If so, we're done...
	if (isset($_classes[$class]))
	{
		return $_classes[$class];
	}

	$name = FALSE;

	// Look for the class first in the local application/libraries folder
	// then in the native system/libraries folder
	foreach (array(APPPATH, BASEPATH) as $path)
	{
		if (file_exists($path.$directory.'/'.$class.'.php'))
		{
			$name = 'CI_'.$class;
			// Do not include until the class loaded by spl_autoload handler

			if (class_exists($name, FALSE) === FALSE)
			{

				//require_once($path.$directory.'/'.$class.'.php');
			}

			break;
		}
	}

	// Is the request a class extension? If so we load it too
	if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
	{
		$name = config_item('subclass_prefix').$class;
		//print "$name<br />\r\n";

		if (class_exists($name, FALSE) === FALSE)
		{
			// Do not include until the class loaded by spl_autoload handler
			//require_once(APPPATH.$directory.'/'.$name.'.php');
		}
	}

	// Did we find the class?
	if ($name === FALSE)
	{
		// Note: We use exit() rather than show_error() in order to avoid a
		// self-referencing loop with the Exceptions class
		set_status_header(503);
		echo 'SPL: Unable to locate the specified class: '.$class.'.php';
		print "<pre>";
		var_dump(debug_backtrace());
		print "</pre>";
		exit(5); // EXIT_UNK_CLASS
	}

	// Keep track of what we just loaded
	is_loaded($class);

	if(!class_exists($name)){
		print "SPL: Unable to locate the specified class: ".$name."<br />";

		print "<pre>";
		var_dump(debug_backtrace());
		print "</pre>";
		exit(5);
	}
	
	$_classes[$class] = isset($param)
		? new $name($param)
		: new $name();

	return $_classes[$class];
}


