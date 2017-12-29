<?php 

echo "Reset file/directory permission\r\n";

echo str_repeat("#",50)."\r\n";

define('WEBCONTENT_DIR',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
echo "Web Content Directory: ".WEBCONTENT_DIR."\r\n";

define('SITE_DIR',dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);
echo "Site Directory: ".SITE_DIR."\r\n";

echo "\r\n";

function reset_folder_permission($path){
	if(!is_dir($path)) {
		@mkdir($path, 0777, true);
	}

	$site_path = substr($path, strlen(SITE_DIR));
	if(!is_dir($path)) {
		echo "$site_path ... is not a directory, and can not be created. \r\n";
		return;
	}

	
	@chmod($path, 0777);
	echo "$site_path ... is a directory, ";
	if(!is_writable($path)) {
		echo "$site_path ... but can not be writable. \r\n";
		return;
	}
	if(!is_executable($path)) {
		echo "$site_path ... but can not be executable. \r\n";
		return;
	}
	echo "listing: \r\n";

	$op = opendir($path);
	if(!$op) return;
	while($file = readdir($op)){
		$file_path = $path.DIRECTORY_SEPARATOR. $file;
		if(substr($file,0,1) == '.') {continue;}

		$site_path = substr($file_path, strlen(SITE_DIR));
		echo "$site_path ...";

		$change_needed = false;

		$val = 0666;
		if(is_dir($file_path)) {
			$val = 0777;
			echo "is a directory,";

			// Should be executable to write file into directory
			if(!is_executable($file_path)) $change_needed = true;
		}else{
			echo "is a file,";
		}

		// Should be writable
		if(!is_writable($file_path)) $change_needed = true;

		if(!$change_needed){
			echo " no change needed.";
		}elseif(!chmod( $file_path, $val)){
			echo " completed.";
		}else{
			echo " not permitted.";
		}
		echo "\r\n";
		if(is_dir($file_path)) {
			reset_folder_permission($file_path.DIRECTORY_SEPARATOR);
		}
	}
	closedir($op);
}

echo str_repeat("-",50)."\r\n";
foreach(array(
	WEBCONTENT_DIR.'tmp',
	WEBCONTENT_DIR.'tmp/cache',
	WEBCONTENT_DIR.'tmp/session',
	WEBCONTENT_DIR.'prvdata',
	WEBCONTENT_DIR.'logs',
	SITE_DIR.'pub',
) as $path){
	reset_folder_permission($path);	
}

echo str_repeat("-",50)."\r\n";
echo "DONE\r\n";
