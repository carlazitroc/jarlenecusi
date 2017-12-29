<?php

use Dynamotor\Helpers\Numbertowords;

$this->title = array();
$this->title[] = lang('platform_name');

// Core Required Library
$this -> asset -> js_import(base_url('assets/js/jquery-1.10.2.min.js'));
$this -> asset -> css(base_url('assets/css/jquery-ui-1.10.3.custom.min.css'));
$this -> asset -> js_import(base_url("assets/js/jquery-ui-1.10.3.min.js"));

// FontAwesome Icon
$this-> asset ->css(base_url('assets/libs/font-awesome/css/font-awesome.min.css'));

$this -> asset -> css(base_url('assets/libs/jasny/css/jasny-bootstrap.min.css'));
$this -> asset -> js_import(base_url('assets/libs/jasny/js/jasny-bootstrap.min.js'),null,'body_foot');

// Theme based CSS
$this-> asset ->css(base_url('assets/libs/bootstrap/css/bootstrap.min.css'));

$this-> asset ->css(theme_url('css/sb-admin.min.css'));
$this-> asset ->css(base_url('assets/css/ui.min.css'));
$this-> asset ->css(theme_url('css/custom.min.css'));

// Theme based Plugin
$this-> asset ->js_import(base_url('assets/libs/bootstrap/js/bootstrap.min.js'));
$this-> asset ->js_import(base_url('assets/libs/metisMenu/jquery.metisMenu.js'));
$this-> asset ->js_import(theme_url('js/main.min.js'));

//Colorpicker
$this-> asset ->js_import(base_url('assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js'));
$this-> asset ->css(base_url('assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.css'));


// Date Time Plugin
$this-> asset ->js_import(base_url('assets/js/moment.min.js'));

// Date Picker Plugin
$this-> asset ->css(base_url('assets/libs/bootstrap-datepicker/css/datepicker3.css'));
$this-> asset ->js_import(base_url('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.js'));

// Date Time Picker Plugin
$this-> asset ->css(base_url('assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$this-> asset ->js_import(base_url('assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));

// Placeholder Plugin
$this-> asset ->js_import(base_url('assets/js/jquery.placeholder.min.js'));

// Prompt Plugin
$this-> asset ->js_import(base_url('assets/js/jquery.noty.min.js'));
$this-> asset ->js_import(base_url('assets/js/jquery.noty.bootstrap.js'));

// HTML4/5 History API
$this-> asset ->js_import(base_url('assets/js/history.min.js'));

//// Dynamotor Required JS 
/*
// Passing data for cross-window
$this-> asset ->js_import(base_url('assets/libs/core/selector.min.js'));

// Submit Form to iframe (ajax upload supported for old browser)
$this-> asset ->js_import(base_url('assets/libs/core/ajaxform.min.js'));

// Handle URL Segment/Fragment
$this-> asset ->js_import(base_url('assets/libs/core/fragment.min.js'));

// Dynamotor Required JS Class / Library (hc.* classes)
$this-> asset ->js_import(base_url('assets/libs/core/core.min.js'));
$this-> asset ->js_import(base_url('assets/libs/core/core.list.min.js'));
$this-> asset ->js_import(base_url('assets/libs/core/core.editor.min.js'));
//*/
// Combined version
$this -> asset -> js_import(base_url("assets/libs/core/hc-full.min.js"));
//*/

$this-> asset ->js_code('var config='.json_encode(array(
	'uri_string'=>uri_string(),
	'path_string'=>isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : uri_string(),
	'site_url'=>site_url(),
	'base_url'=>base_url(),
	'asset_url'=>asset_url(),
	'web_url'=>web_url(),
	'lang_code'=>$this->lang->lang(),
	'locale_code'=>$this->lang->locale(),
	'country_code'=>$this->lang->country(),
)).';hc.config.set(config);');

// Load default script
$this -> asset -> js_embed($theme_path.'layouts/default.js.php');

if($this->lang->extra('momentjs') != NULL){

	$this-> asset ->js_code('if(typeof moment != \'undefined\')moment.locale('.json_encode($this->lang->extra('momentjs')).')');
}

$this -> asset -> js_code('hc.loc.setText('.json_encode(array(
	'record_saved'=>lang('record_saved'),
	'record_published'=>lang('record_published'),
	'error_timeout'=>lang('record_submit_error'),
	'error_required_empty'=>lang('record_empty_error'),
	'error_processing'=>lang('error_processing'),
)).')');

$_main_content = '';
if(isset($view_path) && !empty($view_path)) {
	$_main_content = $this->view($view_path,NULL,TRUE);
}elseif(isset($view) && !empty($view)) {
	$_main_content = $this->view($view,NULL,TRUE);
}else{
	$view = 'blank';
}


require_once dirname(__FILE__).DS.'helper.inc.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
<meta charset="utf-8" />
<title><?php echo implode(" > ", $this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

<?php $this-> asset ->print_tags('head');?>

</head>
<body>

<div id="wrapper">
<header>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="<?php echo site_url()?>"><?php echo lang('platform_name')?></a>
    </div>
	<ul class="nav navbar-top-links navbar-right">
<?php if($this->lang->has_available_locale()){ ?>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-globe"></i></a>
			<ul class="dropdown-menu">
<?php foreach($this->lang->get_available_locale_keys() as $locale_key) {
$locale_info = $this->lang->get_locale_info($locale_key);
$url = site_url($locale_key.'/'.uri_string().uri_query());

 ?>
<?php if($locale_key == $this->config->item('language')): ?>
				<li class="active"><a href="<?php echo $url?>"><i class="icon-check-sign"></i> <?php echo lang('lang_'.$locale_key)?></a></li>
<?php else: ?>
				<li><a href="<?php echo $url?>"> <?php echo lang('lang_'.$locale_key)?></a></li>
<?php endif;?>
<?php } ?>
			</ul>
		</li>
<?php }?>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user fa-white"></i> <?php echo $this->config->item('account_name')?></a>
			<ul class="dropdown-menu">
				<li><a target="_blank" href="<?php echo web_url($this->lang->locale())?>"><i class="fa fa-jump"></i> <?php echo lang('button_live')?></a></li>
				<li><a target="_blank" href="<?php echo base_url('preview/'.$this->lang->locale())?>"><i class="fa fa-jump"></i> <?php echo lang('preview')?></a></li>
				<li class="divider"></li>
				<li><a href="<?php echo site_url('preference')?>"><i class="fa fa-cogs"></i> <?php echo lang('preference_heading')?></a></li>
				<li class="divider"></li>
				<li><a href="<?php echo site_url('auth/changePassword')?>"><i class="fa fa-key"></i> <?php echo lang('change_password')?></a></li>
				<li><a href="<?php echo site_url('auth/signout')?>"><i class="fa fa-power-off"></i> <?php echo lang('sign_out')?></a></li>
			</ul>
		</li>
	</ul>
</nav>
	</header>
	<div class="navbar-default navbar-side navbar-fixed-side" role="navigation">
		<div class="sidebar-collapse">
			<ul id="side-menu" class="nav">
<?php


$layout_menus = $this->config->item('layout_menu');
print main_menu_create($layout_menus,4);
?>
		</ul>
		</div>
	</div>

<div id="page-wrapper">

<?php echo $_main_content; ?>

</div>
</div>
<div id="overlay"></div>

<?php $this-> asset ->print_tags('body_foot');?>
<script>
	$('.colorpicker').colorpicker(
		{
		 format: "hex"    
		}
	);
</script>
</body>
</html>
