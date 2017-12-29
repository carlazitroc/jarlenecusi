/*
* HDV Components
*/
;(function($){

	var random_string = function(length){
		if( length < 1) return '';
		var code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefgjhiklmnopqrstuvwxyz0123456789';
		return random_string(length - 1) + code[ Math.floor(code.length * Math.random()) ] ;
	};
	
	var IFrameUploader = function(elm, settings){
		var self = this;
		
		this.elm = elm;
		this.settings = settings;
		
		var _target = typeof settings.target == 'function' ? settings.target() : settings.target;
		
		var $elm = $(elm);
		var $previewer = $elm.find('.preview-box');
		var $targetField = $elm.find(_target);
		if($targetField.length < 1){
			$targetField = $elm.find('input[name='+ _target+']');
		}
		var $cropareaField = $elm.find('input[name='+ settings.crop_field_name+']');
		
		var elmHash = random_string(16);

		var $selectButton = $elm.find('button[data-action=selector]');
		var $cropareaButton = $elm.find('button[data-action=croparea]');
		var $uploadButton = $elm.find('button[data-action=upload]');
		var $cancelButton = $elm.find('button[data-action=cancel]');
		var $selectFileButton = $elm.find('button[data-action=select-file]');
		var $uploadField = $elm.find('input[type=file]');
		var selector = null;
		var selectorCallback = 'selector_'+elmHash;
		var cropareaCallback = 'croparea_'+elmHash;

		var uploadFile = function(){
			
			if($('.iframeuploader').length < 1){
				$('<div style="position:absolute; left: -120px; top:-100px; width:100px; height:1px;" class="iframeuploader"/>').appendTo('body');
			}
			
			function callback(rst){
				$ifrm.remove();
				$form.remove();
				window[handlerCallback] = null;
				delete window[handlerCallback];
				
				if(rst && rst.result && rst.result.id){
					
					self.setValue(rst.result.id);
					if($.overlay) $.overlay.hide();
				}else if(rst && rst.id){
					
					self.setValue(rst.id);
					if($.overlay) $.overlay.hide();
				}else if(rst && rst.messages){
					alert(rst.messages.join(',\n'));
				}
				
				$elm.trigger('ifupr_respond', rst);
			}
			
			var handlerCallback = 'ifuprc_'+random_string(8);
			var ifrmName = 'ifuprf_'+random_string(8);
			
			var $form = $('<form action="'+settings.url+'" method="post" enctype="multipart/form-data"/>');
			var $newUploadField = $uploadField.clone();
			$newUploadField.insertAfter($uploadField);
			$form.append('<input type="hidden" name="jscallback" value="parent.'+handlerCallback+'" />');
			$form.append('<input type="hidden" name="do" value="save" />');
			
			for(var key in settings.params){
				$form.append('<input type="hidden" name="'+key+'" value="'+settings.params[key]+'" />');
			}
			
			$uploadField.attr('name',settings.field_name).appendTo($form);
			$form.attr('target', ifrmName);
			var $ifrm = $('<iframe name="'+ifrmName+'" />');
			
			
			window[handlerCallback] = callback;
			
			$form.appendTo('.iframeuploader');
			$ifrm.appendTo('.iframeuploader');
			
			$form.submit();
			
			$uploadButton.button('disable');
		};
		
		this.buttonReload = function(){
			
			var val = $uploadField.val();
			var lbl = settings.text_select_file;
			var has_file = false;
			if(val && val.length > 0){
				var fileName = val.split(/[\\\/]/);
				if(fileName.length > 1){
					fileName = fileName[ fileName.length - 1];
					lbl = settings.text_selected_file +' '+fileName;
					has_file = true;
				}
			}
			//$selectFileButton.button('option','label',lbl); 
			$uploadButton.button(!has_file ? 'disable' : 'enable');
		};
		
		this.enlarge = function(){
			var url = settings.preview_url.apply(this,['enlarge', $targetField.val() ]);
			if(!url || url.length < 1) return;
			
			if($.fn.colorbox){
				$.colorbox({href:url,photo:true,'maxWidth':'90%',maxHeight:'90%'});
			}else{
				window.open(url);
			}
		};
		this.previewerReload = function(){
			var val = '';
			
			if($targetField.length > 0 && $targetField.val().length > 0){ 
				val = $targetField.val();
			}
			var has_val = val && val!='';
			if(has_val){
				$cancelButton.show();
			}else{
				$cancelButton.hide();
			}

			if(typeof settings.previewer != 'undefined'){
				settings.previewer.apply( $previewer, [val]);
				
			}else if($previewer.length > 0 && ($previewer.data('type') == 'image' || $previewer.data('type') == null) ){
				$previewer.addClass('empty').removeClass('loading');
				if(has_val){
					var url = settings.preview_url.apply(this,['preview', val ]);
					
					$previewer.removeClass('empty').find('.body').css('background-image','url('+url+')');
				}else{

					$previewer.addClass('empty').find('.body').css('background-image','none');
				}
			}
		};
		this.setCropAreaValue = function(newVal){
			
			if($cropareaField.length > 0 ){
				console.log(self, 'change');
				$cropareaField.val(newVal);
			}
			return this;
		};
		this.getCropAreaValue = function(){
			
			if($cropareaField.length > 0 ){
				return $cropareaField.val();
			}
			return null;
		};
		this.getValue = function(){
			if($targetField.length > 0 ){
				return $targetField.val();
			}
			return null;
		};
		this.setValue = function(val, dispatchEvent){
			// check does it really changed

			if(!dispatchEvent)
				dispatchEvent = (val != $targetField.val());

			$targetField.val(val);
			
			$uploadField.val('');

			self.previewerReload();
			self.buttonReload();

			if(dispatchEvent)
				$targetField.trigger('change');
		};
		this.clear = function(){
			
			$targetField.val('');
			
			$uploadField.val('');

			self.previewerReload();
			self.buttonReload();

			$uploadField.trigger('change');
			
		};
		this.reset = function(){
			$targetField.val('');
			
			$uploadField.val('');

			self.previewerReload();
			self.buttonReload();

			$targetField.trigger('change');
		};
		
		$previewer.find('.body').click(function(){
			self.enlarge();
		});
		
		//$selectFileButton.button('option','icons',{primary:'ui-icon-folder-open'});
		
		$uploadField
			.change(function(){
				self.buttonReload();
				if($uploadField.data('upload') == 'instant'){
					uploadFile();
				}
			})
			.mousedown(function(){
				$selectFileButton.mousedown();
			}).mouseup(function(){
				$selectFileButton.mouseup();
			}).mouseenter(function(){
				$selectFileButton.mouseenter();
			}).mouseleave(function(){
				$selectFileButton.mouseleave();
			});
		
		$uploadButton.on('click',uploadFile);

		if($cancelButton.length){
			$cancelButton.on('click', function(){
				selector.ids = [];

				self.setValue('');
			});
		}
		
		if($selectButton.length ){
			selector = new Selector($targetField);
			self.selector = selector;
			
			selector.onClose = function(){
				
				if( selector.$modal){
					selector.$modal.modal('hide');
					selector.$modal = null;
				}
				if( selector.$dialog){
					selector.$dialog.dialog('close');
					selector.$dialog = null;
				}
				if( $.fn.colorbox){
					$.colorbox.close();
				}
			};
			selector.$previewer = $previewer;
			selector.onUpdate = function(){

				self.setValue ( selector.ids.length ? selector.ids[0] :'' , true);
			};
			
			$selectButton.on('click',function(){
				var url = settings.selector_url;
			url+= url.indexOf('?') >=0 ? '&':'?';
			url+= 'dialog=yes&callback=top.'+selectorCallback+'.didResponse';
				openWin(url);
			});

			top.window[selectorCallback] = selector;
			selector.onUpdate();
			
		}else{
			self.previewerReload();
		}
		
		var openWin = function(url){
			var horizontalPadding = 30;
			var verticalPadding = 30;
			
				
			
			if( hc && hc.ui){
				selector.$modal = hc.ui.openModal(url);
			}else{
				window.open(url,'','width='+settings.dialog_settings.width+',height='+settings.dialog_settings.height+',resizalbe=yes,scrollbars=yes');
			}
		}
		
		if($cropareaButton.length == 1){
			window.top[cropareaCallback] = function(val){
				$cropareaField.val(val);
				
				self.previewerReload();
				return true;
			};
			
			$cropareaButton.click(function(){
				
				if(!($targetField.val()!= 'undefined' && $targetField.val().length>0))return;
				
				var url = settings.croparea_url.replace('{val}',$targetField.val());
				url+= url.indexOf('?') >=0 ? '&':'?';
				url+= 'dialog=yes&width='+settings.crop_width+'&height='+settings.crop_height+'&val='+$cropareaField.val()+'&callback='+cropareaCallback+'';
				openWin(url)

			});
			selector.onUpdate();
			
		}
		
		self.buttonReload();
	};
	
	var apiName = 'ifuploader';
	$.ifuploader = {
		defaultSettings: {
			url:'',
			selector_url:'',
			croparea_url:'',
			field_name: 'new_file',
			crop_field_name: 'new_file_croparea',
			crop_width: 200,
			crop_height: 200,
			callback_name: 'jscallback',
			target: '',
			preview_url: function(type, val){return '';},
			dialog_settings: { 
				title: 'Select Uploaded', 
				autoOpen: true, 
				width: 800, 
				height: 500, 
				modal: true, 
				resizable: true, 
				autoResize: true, 
				overlay: {opacity: 0.5,background: "black"}
			},
			text_selected_file: 'Selected File:',
			text_select_file: 'Select File'
		}
	};
	
	$.fn.ifuploader = function(settings){
		
        var args = $(arguments).toArray();
        if(args.length>0) args.splice(0,1);
		
        if(typeof settings == 'string'){
        	var api = $(this).data(apiName);
        	if(!api) return;
        	
            if(settings == 'option' && typeof args[0] == 'string' ){
            	if( args.length > 1)
                    api.settings[args[0]] = args[1];
                else{
                	return api.settings[ args[0]];
                }
            }else if(api[settings] && typeof api[settings] == 'function')
                api[settings].apply(api, args);
            
            return;
        }
		
			
		return $(this).each(function(){
            var $elm = $(this);
            var api = $elm.data(apiName);
            if(api)return api;
            
            var _settings = $.extend({},$.ifuploader.defaultSettings, settings);
            api = new IFrameUploader( $elm, _settings );
            $elm.data(apiName,api);
			
			return api;
		});
	
	};
		
})(jQuery);