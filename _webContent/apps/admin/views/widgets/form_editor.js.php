;(function($){
$(function(){

<?php if($has_localized):?>

	var locales = <?php echo json_encode($this->lang->get_available_locale_keys());?>;
	$('body').on('click', 'a[re-action=change-locale]',function(evt){
		evt.preventDefault();
		var loc = $(this).attr('re-locale');
		var locName = $(this).attr('re-locale-name');
		$('.btn-change-locale span.loc-name').text( locName );
		$('.localized-pane').removeClass('active in');
		$('.localized-pane[re-locale='+loc+']').addClass('active in');
	}).on('click', '[data-toggle=copy-value]', function(evt){
		evt.preventDefault();
		var name = $(this).data('name');
		var field = $(this).data('field');
		var loc = $(this).data('locale');
		var val = $('[name="'+field+'"]').val();

		if( val || val == ''){
			$('[re-field='+ name +']').each(function(){
				var $input = $(this);

				console.log($input);
				$input.val( val ).trigger('change');

			})
		}
	
	});
<?php endif; ?>

}); // end of DOM Ready
})(jQuery);