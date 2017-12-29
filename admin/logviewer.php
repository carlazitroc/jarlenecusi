<?php

$app_ids = array();

$apps_path = dirname(dirname(__FILE__)).'/_webContent/apps';

if(!file_exists($apps_path))
	die("Application folder does not exist.");

// scanning application
$op = opendir($apps_path);
while($file = readdir($op)){
	if(substr($file,0,1) == '.') continue;
	if(is_file($file)) continue;

	$app_id = $file;
	$app_path = $apps_path.'/'.$app_id;
	if(!is_file($app_path.'/boot.php')){
		continue;
	}
	$app_ids[] = $file;
}
closedir($op);
if(empty($app_ids))
	die("No application installed.");

if(isset($_REQUEST['api']) && $_REQUEST['api'] == 'yes'){

	header("Content-type: text/plain");

	$app_id = isset($_REQUEST['app']) ? $_REQUEST['app'] : $app_ids[0];

	// if passed app id does not match
	if(!in_array($app_id, $app_ids)){
		$app_id = $app_ids[0];
	}

	$app_path = $apps_path.'/'.$app_id;

	if(!is_dir($app_path)){
		die("Application folder does not exist.");
	}
	if(!is_file($app_path.'/boot.php')){
		die("Application boot file does not exist.");
	}

	include_once $app_path.'/boot.php';

	if(!defined('LOG_DIR')){
		die("Log directory does not defined.");
	}

	$date = isset($_REQUEST['date']) && !empty($_REQUEST['date']) ? $_REQUEST['date'] : FALSE;
	$clear = isset($_REQUEST['clear']) && !empty($_REQUEST['clear']) && $_REQUEST['clear'] == 'yes' ? TRUE : FALSE;

	//if(!$this->user_auth->isLogin()) show_404();
	if(!$date) $date = date("Y-m-d");
		$path = LOG_DIR.'log-'.$date.'.php';

	if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'error'){
		$path = LOG_DIR.'error-'.$date.'.log';
	}

	if(!file_exists($path)) die('Log file not found at '.$path);



	//print 'Log : '.$date."\n".str_pad("\n",40,"=",STR_PAD_LEFT);
	if($clear){

		unlink($path);
		print 'Log file has been erased.';
		exit;
	}
	$content = implode("",file($path));
	$content = strip_tags($content);
	print $content;
	exit;

	exit;
}
?><html>
<head>
<title>Log Viewer</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="assets/js/jquery-1.10.2.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">

<!-- Optional theme -->
<link href="assets/libs/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">

<!-- Latest compiled and minified JavaScript -->
<script src="assets/libs/bootstrap/js/bootstrap.min.js"></script>

<script>
;(function($){

var api_path ='<?php echo dirname($_SERVER['PHP_SELF']);?>';
var api_file ='<?php echo basename($_SERVER['PHP_SELF']);?>';
var date = '';
var app_id = 'portal';
var view_type = 'sys';
var reload = function(q){
	var data = typeof q == 'object' ? q : {};
	var _date = null;
	var _app = app_id;
	var _view = '';
	var p_url = api_path+ '/' +api_file+'?app='+app_id;
	data.api = 'yes';
	data.app = _app;
	if(date !=''){
		_date = date;
		data.date = date;
		p_url+= '&date='+date;
	}
	if(view_type !=''){
		_view = view_type;
		data.type = view_type;
		p_url+= '&type='+_view;
	}
	var $content = $('#log-content');
	var $date = $('#log-date');
	$('.view-type ul li.active').removeClass('active');
	$('.app-type ul li.active').removeClass('active');
	
	if(_app!=''){$('.app-type-'+_app,'.app-type ul').addClass('active');}
	if(_view!=''){$('.view-type-'+_view,'.view-type ul').addClass('active');}
	if(_date != null) {
		$date.text(_date);
		p_url+'&date='+_date;
		$('header input[name=date]').val( _date);
	}else{
		$date.text('now');
		$('header input[name=date]').val('');
	}
	$content.text('Loading...');
	
	$.ajax({
		url: api_file,
		data: data,
		dataType: 'text',
		success: function(txt){$content.text(txt);$('body').scrollTop($content.height())},
		error: function(){$content.text('Error, cannot access server log content.');}
	});
	try{ window.history.pushState(document.title,null,p_url)}catch(err){}
}
var clear = function(){return reload({clear:'yes'});}
var switch_mode = function(field, value){
	if(field == 'app') app_id = value;
	if(field == 'view') view_type = value;
	reload();
}
$('body').ready(function(){
	
	$(this).on('click','.btn-action',function(evt){
		evt.preventDefault();
		var action = $(this).data('action');
		var field = $(this).data('field');
		var value = $(this).data('value');
		
		if(action == 'refresh'){
			return reload();
		}
		if(action == 'clear'){
			return clear();
		}
		if(action == 'switch-mode'){
			return switch_mode(field, value);
		}
	});
<?php if(!empty($_REQUEST['date'])) echo "\tdate='".strip_tags($_REQUEST['date'])."';\n"?>
<?php if(!empty($_REQUEST['app'])) echo "\tapp_id='".strip_tags($_REQUEST['app'])."';\n"?>
<?php if(!empty($_REQUEST['type'])) echo "\tview_type='".strip_tags($_REQUEST['type'])."';\n"?>
	reload();
});

})(jQuery);
</script>
</head>
<body>
<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" id="top" role="banner">
<div class="container-fluid">
<div class="navbar-header">
<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
<col-xs- class="sr-only">Toggle navigation</col-xs->
<col-xs- class="icon-bar"></col-xs->
<col-xs- class="icon-bar"></col-xs->
<col-xs- class="icon-bar"></col-xs->
</button>
<a href="#" class="navbar-brand">Log Viewer</a>
</div>
<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
<ul class="nav navbar-nav">
<li class="app-type dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">App Type <b class="caret"></b></a>
<ul class="dropdown-menu">
<?php foreach($app_ids as $app_id):?>
<li class="app-type-<?php echo $app_id?>">
<a href="#" class="btn-action" data-action="switch-mode" data-field="app" data-value="<?php echo $app_id?>"><?php echo $app_id?></a>
</li>
<?php endforeach;?>
</ul>
</li>
<li class="view-type dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">View Type <b class="caret"></b></a>
<ul class="dropdown-menu">
<li class="view-type-sys">
<a href="#" class="btn-action" data-action="switch-mode" data-field="view" data-value="sys">System</a>
</li>
<li class="view-type-error">
<a href="#" class="btn-action" data-action="switch-mode" data-field="view" data-value="error">Error</a>
</li>
</ul>
</li>
<li>
<a href="#" class="btn-action" data-action="refresh"><i class="glyphicon glyphicon-refresh"></i> <col-xs- class="sr-only">Refresh</col-xs-></a>
</li>
<li>
<a href="#" class="btn-action" data-action="clear"><i class="glyphicon glyphicon-trash"></i> <col-xs- class="sr-only">Clear</col-xs-></a>
</li>
</ul>
<form class="navbar-form navbar-right" method="get" role="search">
<div class="form-group">
<input type="text" name="date" class="form-control" placeholder="YYYY-mm-dd" value="" />
</div>
<button type="submit" class="btn btn-primary">Submit</button>
<div class="clearfix"></div>
</form>
</nav>
</div>
</header>
<div style="padding-top:50px;" class="container-fluid">
<div class="page-header">
<h4>Date: <col-xs- id="log-date"><?php echo date("Y-m-d")?></col-xs-></h4>
</div>
<pre id="log-content"></pre>
</div>
</body>
</html>