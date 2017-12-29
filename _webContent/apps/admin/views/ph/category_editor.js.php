;(function($){

$(function(){
	
	
	var $editor = $('#page-wrapper,.iframe-dialog');
	$editor.on('response', function(evt, rst){
		if(rst.id){
			$target.find('[re-href=preview_url]').prop('href',rst.preview_url);
			$target.find('[re-href=live_url]').prop('href',rst.live_url);
			if(typeof rst.data != 'undefined')
				for(var key in rst.data)
					$target.find('[name='+key+']').val(rst.data[key]);
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
		apiPathPrefix:'<?php echo site_url('s/'.$section.'/category')?>',
		id: <?php echo isset($id) ? "'".strip_tags($id)."'" : 'null'?>
	});

<?php if ($this->ph->is_locale_enabled) {?>
	$('body').on('click', 'a[re-action=change-locale]',function(evt){
		evt.preventDefault();
		var loc = $(this).attr('re-locale');
		var locName = $(this).attr('re-locale-name');
		$('.btn-change-locale span.loc-name').text( locName );
		var $old = $('.tab-pane.active.in');
		$old.removeClass('in');
		var $tar = $('.tab-pane[re-locale='+loc+']');
		var deactiveOld = function(){ $old.removeClass('active'); $tar.addClass('active');  };
		var activeNew = function(){ $tar.addClass('in'); }

		if($old.length){
			setTimeout(deactiveOld,500);
			setTimeout(activeNew,600);
		}
		else{
			setTimeout(activeNew,100);
		}
	});
<?php } ?>
});
	
})(jQuery);
