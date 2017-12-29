<?php

$acl_lock = true;// false;

$_main_content = '';
if(isset($view_path) && !empty($view_path)) {
	$_main_content = $this->view($view_path,NULL,TRUE);
}elseif(isset($view) && !empty($view)) {
	$_main_content = $this->view($view,NULL,TRUE);
}else{
	$view = 'blank';
}


require_once dirname(__FILE__).DS.'helper.inc.php';

$this-> asset ->css(base_url('assets/libs/bootstrap/css/bootstrap.min.css'));
$this-> asset ->css(base_url('assets/libs/font-awesome/css/font-awesome.min.css'));
$this-> asset ->css(theme_url('css/sb-admin.min.css'));
$this-> asset ->css(theme_url('css/custom.min.css'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
<meta charset="utf-8" />
<title><?php echo lang('platform_name')?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<script type="text/javascript">
var config = <?php print json_encode(array(
	'uri_string'=>uri_string(),
	'path_string'=>isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : uri_string(),
	'site_url'=>site_url(),
	'base_url'=>base_url(),
	'asset_url'=>asset_url(),
	'lang_code'=>$this->lang->lang(),
	'locale_code'=>$this->lang->locale(),
	'country_code'=>$this->lang->country(),
))?>;
</script>

<?php $this-> asset ->print_tags('head');?>


</head>
<body class="<?php echo $this-> asset -> get_data('body_css_class');?>">
<?php $this-> asset ->print_tags('body_head');?>
	
<div class="container">

<?php echo $_main_content?>

</div>

<div class="clearfix"></div>
	
<?php $this-> asset ->print_tags('body_foot');?>

</body>
</html>