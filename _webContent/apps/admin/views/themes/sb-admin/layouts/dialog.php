<?php


require_once dirname(__FILE__).DS.'helper.inc.php';

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
$this-> asset ->js_import(theme_url('js/main.min.js'));

// Date Time Plugin
$this-> asset ->js_import(base_url('assets/js/moment.min.js'));

// Date Picker Plugin
$this-> asset ->css(base_url('assets/libs/bootstrap-datepicker/css/datepicker3.css'));
$this-> asset ->js_import(base_url('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.js'));

// Date Time Picker Plugin
$this-> asset ->css(base_url('assets/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$this-> asset ->js_import(base_url('assets/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js'));

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


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
<meta charset="utf-8" />
<title><?php echo implode(" > ", $this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<script type="text/javascript">
var config = <?php print json_encode(array(
	'uri_string'=>uri_string(),
	'path_string'=>isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : uri_string(),
	'site_url'=>site_url(),
	'base_url'=>base_url(),
	'asset_url'=>asset_url(),
	'web_url'=>web_url(),
	'lang_code'=>$this->lang->lang(),
	'locale_code'=>$this->lang->locale(),
	'country_code'=>$this->lang->country(),
))?>;
</script>
<?php $this-> asset ->print_tags('head');?>

</head>
<body class="<?php echo $this-> asset ->get_data('body_css_class','string', ' ');?>">

	
<div class="modal iframe-dialog">
<?php echo $_main_content?>
</div>


<div class="clearfix"></div>

<?php $this-> asset ->print_tags('body_foot');?>
</body>
</html>