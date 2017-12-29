

$(function(){


<?php if($this->acl->has_permission('FULL_MANAGE')):?>

$('.btn-sys-cache').on('click', function(evt){
	evt.preventDefault();

	if(!confirm('Removing system cache will reduce loading performance, sure?')) return;

	hc.ui.showLoaderAnimation();

	$.getJSON('<?php echo site_url('misc/cacheClear.json')?>', function(rst){
		hc.ui.hideLoaderAnimation();
		if(rst.success){
			hc.ui.showMessage('System cache removed.', 'success',10000)
		}else{

			hc.ui.showMessage('Error when removing system cache', 'error',10000)
		}
	}).error(function(){
		hc.ui.showMessage('Connection problem, please try again.', 'error',10000)
		hc.ui.hideLoaderAnimation();
	});
});

<?php endif;?>

});