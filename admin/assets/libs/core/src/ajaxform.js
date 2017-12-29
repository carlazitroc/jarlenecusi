/*
* HDV Components
*/;
(function($){
	
	var counter = 0;
	
	var $requestWrapper ;
	
	
	
	function init(){
		
		
		if(!$requestWrapper){	
			$requestWrapper = $('<div style="position:absolute;bottom:0;" />');
			$requestWrapper.hide();
			$requestWrapper.appendTo('body');
		}
		
	}
	
	$.ifrm = {};
	$.ifrm.defOptions = {};
	$.ifrm.defOptions.onSubmit = function(){};
	$.ifrm.defOptions.onSend = function(){};
	$.ifrm.defOptions.onError = function(){};
	$.ifrm.defOptions.onTimeout = function(){};
	$.ifrm.defOptions.onResponse = function(rst){};
	$.ifrm.defOptions.timeoutDuration = 30;
	$.ifrm.defOptions.callbackName = 'jscallback';
	$.ifrm.defOptions.embedTimestamp = true;
	$.ifrm.defOptions.useOverlay = false;
	
	var counter = 0;
	
	var methods = {
		init : function (options){
			init();
			
			// Create some defaults, extending them with any options that were provided
			var settings = $.extend( $.ifrm.defOptions , options);
			
			return this.each(function() {
					
				var _enabled = true;
				var self = this;
				var $self = $(this),
					data = $self.data('ifrm');
				if ( ! data ) {
					
					var timeoutHandler;
					
					
					
					var onSubmit = function(){
						
						// skip handler if disabled
						if(!_enabled)return true;
						
						var frm = self;
						var id = counter++;
						var $hiddenCtr;
						
						var elmName = 'ifrm_'+id;
						var callbackName = 'ifrm_callback_'+id;
						var $ifrm;
						
						
						if(settings.onSubmit){
							var val = settings.onSubmit.apply( self, arguments);
							if(val ===false){
								// stop here if false returned by onSubmit handler
								return false;
							}
						}
						
						// create handler for public listener object
						window[callbackName] = function(rst){
							clearTimeout(timeoutHandler);
							
							delete window[callbackName];
						
							// remove handler iframe
							if($.ifrm.defOptions.useOverlay && $.overlay) $.overlay.hide();
							
							$ifrm.remove();
							
							if(settings.onResponse){
								settings.onResponse.apply( self, arguments);
								$ifrm.trigger('af_response', arguments);
							}
						};
						
						if($('#'+elmName).length>0){
							$('#'+elmName).remove();
						}
						
						frm.target = elmName;
						
						$ifrm = $('<iframe/>');
						$ifrm.error(function(){
							clearTimeout(timeoutHandler);
							
							if(settings.onError){
								settings.onError.apply(self,arguments);
							}
						});
						$ifrm.attr('name',elmName);
						$ifrm.appendTo($requestWrapper);
						
						if($(frm).find('input[name='+settings.callbackName+']').length < 1){
							$('<input name="'+settings.callbackName+'" type="hidden" />').appendTo(frm);
						}
						$(frm).find('input[name='+settings.callbackName+']').val('parent.'+callbackName);
						
						if(settings.embedTimestamp){
							if($(frm).find('input[name=_t_]').length < 1){
								$('<input name="_t_" type="hidden" />').appendTo(frm);
							}
							$(frm).find('input[name=_t_]').val(new Date().getTime());
						}
						
						if($.ifrm.defOptions.useOverlay){
							$.overlay.show();
						}
						
						timeoutHandler = setTimeout(function(){
							
							if($.ifrm.defOptions.useOverlay){
								$.overlay.hide();
							}
							
							if(settings.onTimeout){
								settings.onTimeout.apply(self,arguments);
							}
						},settings.timeoutDuration * 1000);
						
						frm.submit();
						
						if(settings.onSending){
							settings.onSending.apply(self,arguments);
						}
					};
					
					$(this).data('ifrm',{
						settings: settings,
						submit: function(){
							$self.trigger('submit');
						},
						enable: function(){
							_enabled = true;
						},
						disable: function(){
							_enabled = false;
						},
						cancel: function(){
							clearTimeout(timeoutHandler);
							$ifrm.remove();
						}
					});
					
					$self.on('submit.ifrm',onSubmit);
				}
			});
		},
		destroy : function( ) {
			return this.each(function(){
			
				var $this = $(this),
					api = $this.data('ifrm');
     		    $this.removeData('ifrm');
				$this.off('.ifrm');
			});
		},
		submit : function(){
			return this.each(function() {
				
				var $this = $(this),
					api = $this.data('ifrm');
				
				if(api){
					api.submit();
				}
		
			});
		},
		enable : function(){
			
			return this.each(function() {
				
				var $this = $(this),
					data = $this.data('ifrm');
				
				if(data){
					data.enable();
				}
		
			});
		},
		disable : function(){
			
			return this.each(function() {
				
				var $this = $(this),
					data = $this.data('ifrm');
				
				if(data){
					data.disable();
				}
		
			});
		},
		cancel: function(){
			
			return this.each(function() {
				
				var $this = $(this),
					data = $this.data('ifrm');
				
				if(data){
					data.cancel();
				}
		
			});
		}
	};
	
	$.fn.ifrm = function(method){
		
		if ( methods[ method ] ) {
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
		  return methods.init.apply( this, arguments );
		} else {
		  $.error( 'Method ' +  method + ' does not exist on jQuery.ifrm' );
		}
	};
	
})(jQuery);