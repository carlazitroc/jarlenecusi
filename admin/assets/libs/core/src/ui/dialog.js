
/* Create Dialog based on Bootstrap Modal component */
(function(exports, $){
var DialogOptions = {
	show: true,
	removeWhenHidden: true,
	template: '<div class="modal"><div class="modal-dialog"><div class="modal-content"></div></div></div>',
	buttonTemplate: '<button type="button" class="btn" />',
	fade: true,
	header: '<div class="modal-header" />',
	footer: '<div class="modal-footer" />',
	body: '<div class="modal-body" />',
	title: null,
	message: null,
	buttons: null
};

var Dialog = function(options){
	var self = this;
	this.$elm = null;
	this.options  = options;

	var $header, $body,$footer;
	var isLocked = false;

	var $ui = null;
	var $overlay = $('<div class="modal-overlay" style="position:absolute; left:0; top:0; width:100%; height:100%; z-index:1000; background:white; opacity:0.1;" />')
	function lock(){
		$ui = self.$elm.find('.btn,.form-control,select');
		$ui.addClass('disabled');
		$body.append($overlay);
	}

	function unlock(){
		$ui.removeClass('disabled');
		$overlay.remove();
	}

	function install(){
		if(self.$elm != null){
			return;
		}
		var c, $c;
		if(typeof options.template == 'function'){
			c = options.template.apply(self,null);
		}else{
			c = options.template;
		}
		var $elm = $(c);
		self.$elm = $elm;

		if(typeof options.header == 'function'){
			c = options.header.apply(self,null);
		}else{
			c = options.header;
		}
		$header = $c = $(c);
		$elm.find('.modal-content').append($c );


		if(typeof options.body == 'function'){
			c = options.body.apply(self,null);
		}else{
			c = options.body;
		}
		$body = $c = $(c);
		$elm.find('.modal-content').append($c );


		if(typeof options.footer == 'function'){
			c = options.footer.apply(self,null);
		}else{
			c = options.footer;
		}
		$footer = $c = $(c);
		$elm.find('.modal-content').append($c );


		if(options.title != null){
			$header.append( typeof options.title == 'function' ? options.title.apply(self, null) : options.title );
		}

		if(options.message != null){
			$body.append( typeof options.message == 'function' ? options.message.apply(self, null) : options.message );
		}

		if(options.buttons != null){
			for(var buttonName in options.buttons){
				var buttonCfg = options.buttons[buttonName];

				var $b;
				if(buttonCfg.template){
					if(typeof buttonCfg.template == 'function')
						$b = $( buttonCfg.template.apply(self, [buttonCfg]) );
					else 
						$b = $( buttonCfg.template);

				}else{
					if(typeof options.buttonTemplate == 'function')
						$b = $( options.buttonTemplate.apply(self, [buttonCfg]) );
					else 
						$b = $( options.buttonTemplate);

				}

				if(buttonCfg.type){

					if(typeof buttonCfg.type == 'function')
						$b.attr('type', buttonCfg.type.apply(self, [buttonCfg]) );
					else 
						$b.attr('type', buttonCfg.type);
				}

				if(buttonCfg.label){

					if(typeof buttonCfg.label == 'function')
						$b.append( buttonCfg.label.apply(self, [buttonCfg]) );
					else 
						$b.append( buttonCfg.label);
				}else{
					$b.append(buttonName);
				}
				if(buttonCfg.class){

					if(typeof buttonCfg.class == 'function')
						$b.addClass( buttonCfg.class.apply(self, [buttonCfg]) );
					else 
						$b.addClass( buttonCfg.class);
				}
				if(buttonCfg.events){
					$(buttonCfg.events).each(function(idx, opts){
						$b.on(opts.type, function(){
							opts.callback.apply(self, arguments);
						});

					});
				}

				$b.appendTo($footer);
			}
		}

		$elm.appendTo('body').modal({show:false});
		$elm.on('show.bs.modal', function(){
			if(typeof options.onShow == 'function'){
				options.onShow.apply(self,null);
			}
		});
		$elm.on('shown.bs.modal', function(){
			if(typeof options.onShown == 'function'){
				options.onShown.apply(self,null);
			}
		});
		$elm.on('hide.bs.modal', function(){
			if(typeof options.onHide == 'function'){
				options.onHide.apply(self,null);
			}
		});
		$elm.on('hidden.bs.modal', function(){
			if(typeof options.onHidden == 'function'){
				options.onHidden.apply(self,null);
			}
			if(options.removeWhenHidden){
				destroy();
			}
		});

		if(options.fade){
			$elm.addClass('fade');
		}

	}

	function show(){
		if(self.$elm == null){
			install();
		}
		if(self.$elm !=null){
			self.$elm.modal('show');
		}
	}

	function hide(){

		if(self.$elm !=null){
			self.$elm.modal('hide');
		}
	}

	function destroy(){
		if(self.$elm)
			self.$elm.remove();
		self.$elm = null;
	}

	this.show = show;
	this.hide = hide;
	this.install = install;
	this.destroy = destroy;
	this.lock = lock;
	this.unlock = unlock;

	if(typeof options.onInit == 'function'){
		options.onInit.apply(self,null);
	}

	if(options.show){
		show();
	}

};
Dialog.defaults = DialogOptions;
exports.Dialog = Dialog;
exports.createDialog = function(opts){
	return new Dialog($.extend({},DialogOptions, opts));
};

})(hc.ui, jQuery);