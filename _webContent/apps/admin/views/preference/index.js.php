;(function($){

$(function(){
	$('[hc-form=setting]').on('submit',function(evt){
		evt.preventDefault();
		var frm = this;
	
		hc.ui.showLoaderAnimation();

		$.post(frm.action+'.json', $(frm).serialize(), function(rst){
	
			hc.ui.hideLoaderAnimation();

			if(!rst.error){
				hc.ui.showMessage( hc.loc.getText('record_saved'),'success',5000);
			}else{
				hc.ui.showMessage( rst.error.message, 'error',50000);
			}
			//console.log(rst);
		},'json').error(function(){
			hc.ui.hideLoaderAnimation();
			hc.ui.showMessage( hc.loc.getText('error_connection'), 'error',50000);
		});
	})


	$('body').on('click', 'a[re-action=change-locale]',function(evt){
		evt.preventDefault();
		var loc = $(this).attr('re-locale');
		var locName = $(this).attr('re-locale-name');
		$('.btn-change-locale span.loc-name').text( locName );
		$('.localized-pane').removeClass('active in');
		$('.localized-pane[re-locale='+loc+']').addClass('active in');
	});


});

})(jQuery);