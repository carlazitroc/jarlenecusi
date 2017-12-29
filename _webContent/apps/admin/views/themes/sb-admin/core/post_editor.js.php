<?php

if(isset($_REQUEST['debug']) && defined('PROJECT_DEBUG_KEY') && $_REQUEST['debug'] == PROJECT_DEBUG_KEY){
	$this->asset->compress_js = false;
}
?>
;(function($){
$(function(){

	var endpointUrl = '<?php echo $endpoint_url_prefix?>';

	var $editor = $('#page-wrapper,.iframe-dialog');
	var $form = $editor.find('form');

	hc.editor = new hc.ui.RecordEditor($editor,{
		apiPathPrefix: endpointUrl+'',
		id: <?php echo isset($id) ? "'" . strip_tags($id) . "'" : 'null'?>
	});

	$form.on('response', function(evt, rst){


		if(hc.ui && hc.ui.hideLoaderAnimation) hc.ui.hideLoaderAnimation();

		$editor.find('.alert').remove();

		if(rst.data){
			for(var key in rst.data){
				var val = rst.data[key];
				$form.find('[name="'+key+'"]').val( val ).trigger('change');
				$form.find('div[re-text="'+key+'"]').text( val ).trigger('change');
				$form.find('div[re-html="'+key+'"]').html( val ).trigger('change');
			}
		}
		if(rst.loc){
			for(var locale in rst.loc){
				var loc_data = rst.loc[locale];

				for(var key in loc_data){
					var val = loc_data[key];
					$form.find('[name="loc['+locale+']['+key+']"]').val( val ).trigger('change');
					$form.find('div[re-text="'+key+'"]').text( val ).trigger('change');
					$form.find('div[re-html="'+key+'"]').html( val ).trigger('change');
				}
			}
		}
		if(rst.error){
			if(rst.detail && rst.detail.validate){
				for(var key in rst.detail.validate.fields){
					var $target = $form.find('[name="'+key+'"]');
					var msg = rst.detail.validate.fields[key];
					if(msg && $target.length){
						$(msg).insertBefore ( $target);
					}
				}
			}
		}
	}).on('submitting', function(){
		
		if(hc.ui && hc.ui.showLoaderAnimation) {
			hc.ui.showLoaderAnimation();
		}
	}).on('error', function(){

		if(hc.ui && hc.ui.hideLoaderAnimation){
			hc.ui.hideLoaderAnimation();
		}
	}).on('timeout', function(){
		
		if(hc.ui && hc.ui.hideLoaderAnimation){
			hc.ui.hideLoaderAnimation();
		}
	});


<?php if($clone_enabled):?>
	$('body').on('click', '.btn-clone', function(evt){
		evt.preventDefault();

		location.href = endpointUrl +'/' + hc.editor.id +'/clone'
	})
<?php endif; ?>	
});

})(jQuery);