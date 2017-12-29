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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta charset="UTF-8">
<title><?php if(is_array($page_title)) echo implode(' / ', array_reverse($page_title));?></title>
<?php $this->asset->print_tags('head');?>
</head>

<body>

<?php $this->widget('ga');?>
<?php $this->asset->print_tags('body_head');?>

<?php if(defined('PREVIEW_MODE') && PREVIEW_MODE):?>

<div style="position: static; z-index:100000; font-size:10px; font-family:arial; text-align:center; padding:5px 10px; background:red; color:white; font-weight:bold;">
	PREVIEW MODE (<a style="color:white;" href="<?php echo site_url('../')?>">BACK TO ADMIN</a>)
</div>
<?php endif;?>

<div class="container">
<?php print $_main_content ?>
</div>

<?php $this->asset->print_tags('foot');?>
</body>
</html>
