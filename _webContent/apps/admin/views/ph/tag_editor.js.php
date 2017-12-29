;(function($){

$(function(){
	
	var $editor = $('#page-wrapper,.iframe-dialog');
	hc.editor = new hc.ui.RecordEditor($editor,{
		apiPathPrefix:'<?php echo site_url('s/'.$section.'/tag')?>',
		id: <?php echo isset($id) ? "'".strip_tags($id)."'" : 'null'?>
	});

	$editor.on('response.ifrm', function(evt, rst){
		if(rst.error && rst.detail && rst.detail.fields){
			for(var key in rst.detail.fields){
				var $input = $editor.find('[name='+key+']');
				$('<div class="alert alert-danger" />').html( rst.detail.fields[key] ).insertAfter($input);
			}
		}
	});

<?php if ($this->ph->is_locale_enabled) {?>
	$('body').on('click', 'a[re-action=change-locale]',function(evt){
		evt.preventDefault();
		var loc = $(this).attr('re-locale');
		var locName = $(this).attr('re-locale-name');
		$('.btn-change-locale span.loc-name').text( locName );
		$('.tab-pane').removeClass('active in');
		$('.tab-pane[re-locale='+loc+']').addClass('active in');
	});
<?php } ?>

});
	
})(jQuery);
