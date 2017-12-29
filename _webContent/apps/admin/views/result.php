<?php

$callback = $this->input->get_post('jscallback');
if(!$callback) $callback = '_'.md5($_SERVER['REMOTE_ADDR'].'.'. date('YmdHis'));
if(!isset($success))$success= false;
?>
<html><head>
<script type="text/javascript">
window.onload = function(){try{
var a = window.parent['<?php echo $callback?>'];
if(a)a(<?php echo json_encode($output)?>);}catch(e){}}
</script></head><body></body></html>