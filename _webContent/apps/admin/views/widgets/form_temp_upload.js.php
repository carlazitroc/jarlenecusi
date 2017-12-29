;(function($){

$(function(){
	var $root = $('[hc-elm=<?php echo $element_id?>]');
	var $elm = $('input[type=file]',$root);
	$elm.fileupload({
		url: '<?php if(isset($url)) echo $url?>'
	}).on('fileuploaddone', function(evt, api){
		var resultStr = api.response().result;
		var result = JSON.parse(resultStr);
		
		$root.trigger('tempuploadcomplete', result );
<?php if(isset($callback) && !empty($callback)):?>
		var callback = <?php echo $callback?>;
		if(typeof callback) callback( result );
<?php endif;?>
	}).on('fileuploaderror',function(evt, api){

		$root.trigger('tempuploaderror', evt );
		console.log(evt);
	});
});

})(jQuery);