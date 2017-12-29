;(function($){

$(function(){

$('body').on('click', 'a.captch-code', function(evt){
	evt.preventDefault();

	var $elm = $(this);
	var $img = $elm.find('img');
	var url = $elm.prop('href');

	url += url.indexOf('?') > 0 ? '&':'?';

	$img.prop('src', url +'_t='+(new Date()).getTime() );
}).on('submit', '[name=forget_password]', function(evt){
	evt.preventDefault();

	var $form = $(this);
	$('[data-toggle=success]').addClass('hidden');
	$('[data-toggle=error]').addClass('hidden');
	$.post($form.prop('action') + '.json', $form.serialize(), function(rst){
		if(rst.done){
			$form.hide();
			$('[data-toggle=success]').removeClass('hidden');
		}else if(rst.error && rst.error.message){

			$('a.captch-code').trigger('click');
			$('[name=captcha_answer]').val('');
			$('[data-toggle=error]').removeClass('hidden').find('.text').html('<b>Error code: '+rst.error.code+'</b><br />' +rst.error.message)
		}
	},'json').error(function(xhr, type){
		if(type == 'abort') return;
		$('[data-toggle=error]').removeClass('hidden').find('.text').text( 'Connection problem, please try again.');
	});

});

});

})(jQuery);