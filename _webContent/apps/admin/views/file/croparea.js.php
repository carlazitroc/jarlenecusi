
$(function(){
	
	
	$('.cropbox').each(function(){
		var data = $(this).data();
		var $cropImg = $('img',this);
		var opts = {};
		if(typeof data.width != 'undefined') opts.width = parseFloat(data.width);
		if(typeof data.height != 'undefined') opts.height = parseFloat(data.height);
		if(typeof data.zoom != 'undefined') opts.zoom = parseFloat(data.zoom);
		if(typeof data.maxZoom != 'undefined') opts.maxZoom = parseFloat(data.maxZoom);
		$cropImg.cropbox(opts);
		var cropIns = $cropImg.data('cropbox');
		if(typeof data.cropArea != 'undefined'){
			var p = data.cropArea.split(',');
			if(p && p.length==4){
				var r = {cropX: parseInt(p[0]), cropY:parseInt(p[1]), cropW: parseInt(p[2]), cropH: parseInt(p[3])};
				cropIns.setCrop(r);
				setTimeout(function(){
					cropIns.setCrop(r);
				},500);
			}
		}
	});
	
	
	
	$('body').on('click','.btn.btn-primary',function(){
		var callback = $('.cropbox').data('callback');
		var cropArea = $('.cropbox img').data('cropbox').result;
		if(typeof window.top[callback] == 'function')
			window.top[callback]([cropArea.cropX, cropArea.cropY, cropArea.cropW, cropArea.cropH].join(','));
		window.close();
	}).on('click','.btn.btn-cancel',function(){
		window.close();
	});
});