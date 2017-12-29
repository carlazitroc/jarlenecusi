<?php



//$this->asset->compress_js = false;
?>
;(function($){
$(function(){

	var apiPathPrefix = '<?php echo site_url('s/' . $section . '')?>';
	var $editor = $('#page-wrapper,.iframe-dialog');
	$editor.on('response', function(evt, rst){
		if(rst.id){
			$editor.find('[re-href=preview_url]').prop('href',rst.preview_url);
			$editor.find('[re-href=live_url]').prop('href',rst.live_url);
			if(typeof rst.data != 'undefined')
				for(var key in rst.data)
					$editor.find('[name='+key+']').val(rst.data[key]);
		}
	});

	$editor.on('response.ifrm', function(evt, rst){
		if(rst.error && rst.detail && rst.detail.fields){
			for(var key in rst.detail.fields){
				var $input = $editor.find('[name='+key+']');
				$('<div class="alert alert-danger" />').html( rst.detail.fields[key] ).insertAfter($input);
			}
		}
	});
	hc.editor = new hc.ui.RecordEditor($editor,{
		apiPathPrefix: apiPathPrefix+'/post',
		id: <?php echo isset($id) ? "'" . strip_tags($id) . "'" : 'null'?>
	});


});
})(jQuery);
