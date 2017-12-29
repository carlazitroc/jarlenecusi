<?php


require_once dirname(__FILE__).DS.'helper.inc.php';

$_main_content = '';
if(isset($view_path) && !empty($view_path)) {
	$_main_content = $this->view($view_path,NULL,TRUE);
}elseif(isset($view) && !empty($view)) {
	$_main_content = $this->view($view,NULL,TRUE);
}else{
	$view = 'blank';
}

print $_main_content;