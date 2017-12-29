<?php
$_main_content = '';

// Insert theme based JS by using $this->asset->js_import or CSS by $this->asset->css_import

$this->asset->set_meta_content('viewport','width=device-width, initial-scale=1.0');



if(isset($view_path))
	$_main_content = $this->view($view_path,NULL,true);
elseif(isset($view))
	$_main_content = $this->view($view,NULL,true);

$page_title = $this->asset->get_data('page_title','array');

?>
<?php print $_main_content ?>
