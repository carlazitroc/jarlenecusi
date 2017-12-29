
;(function($){
$(function(){
	var selector = new Selector('[hc-elm=<?php echo $element_id?>] input');
	selector.root = '[hc-elm=<?php echo $element_id?>]'; 
	selector.caller = 'selector_<?php echo $element_id?>';
	selector.preview = $('.preview-box',selector.root);
	selector.getValue = function(){
		return $(this.target).val();
	}
	var $btnEdit = $('.btn-edit',selector.root);
	var $btnSelect = $('.btn-select',selector.root);
	var $btnCancel = $('.btn-cancel',selector.root);
	var $btnCreate = $('.btn-create',selector.root);
	selector.onUpdate = function(){
		this.preview.addClass('empty').css('background-image','none').find('.body');
		this.updateField();
		var val = this.getValue();
		if(val && val.length > 0){
			$btnEdit.removeClass('hidden');
			$btnCancel.removeClass('hidden');
			$btnCreate.addClass('hidden');
			this.preview.css('background-image','url(<?php echo site_url('album')?>/'+val+'/picture?size=medium)').removeClass('empty').find('.body');
		}else{
			$btnEdit.addClass('hidden');
			$btnCancel.addClass('hidden');
			$btnCreate.removeClass('hidden');
		}
	}
	selector.onUpdate();
	top.window[selector.caller] = selector;
	
	$btnCancel.click(function(){
		$(selector.target).val('');
		selector.ids.length = 0;
		selector.onUpdate();
	});
	$btnEdit.click(function(){
		var url = '<?php echo site_url('album/add')?>';
		var val = selector.getValue();
		if(val.length > 0){
			url = '<?php echo site_url('album')?>/'+val+'/edit';
		}
		url+='?dialog=yes&callback='+selector.caller+'.didResponse';
		hc.ui.openModal(url, {size:'fluid',onHidden: function(){
			selector.onUpdate();
		}});
		return false;
	});
	$btnCreate.click(function(){
		var url = '<?php echo site_url('album/add')?>';
		url+='?dialog=yes&callback=top.'+selector.caller+'.didResponse';
		hc.ui.openModal(url, {size:'fluid',onHidden: function(){
			selector.onUpdate();
		}});
		return false;
	});
	
	$btnSelect.click(function(){
		var url = '<?php echo site_url('album')?>';
		url+='?dialog=yes&callback=top.'+selector.caller+'.didResponse';
		hc.ui.openModal(url, {size:'fluid',onHidden: function(){
			selector.onUpdate();
		}});
		return false;
	});
});
})(jQuery);