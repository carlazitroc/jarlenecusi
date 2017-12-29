/*
* HDV Components
*/
;(function($){

var RecordEditor = function(scope, options){
	var self = this;
	var $scope = $(scope);
	var $form = $scope.find('form.ifrm');
	self.options = options;
	self.$scope = $scope;
	self.$form = $form;


	if($scope.length < 1){
		console.error('No element found by passed scope:', scope);
	}
	$scope.addClass('record-editor');

	if(options.apiPathPrefix) self.apiPathPrefix = options.apiPathPrefix;
	if(options.id) self.id = options.id;
	if(options.richEditorConfig) self.richEditorConfig = options.richEditorConfig;
	if(options.text) self.text = options.text;

	$(window).on('keyup',function(evt){
		if(!evt.altKey && !evt.shiftKey && (evt.ctrlKey || evt.commandKey) && (String.fromCharCode(evt.keyCode).toLowerCase() == 's')){
			$form.submit();
			evt.preventDefault();
		}
	});
	
	$scope.on('click', '[re-action]', function(evt){
		evt.preventDefault();
		var action = $(this).attr('re-action');


		if( action == 'close' ) {
		window.close();
			return;
		}

		if( action == 'save' || action == 'submit' ) {
			self.submit();
			return;
		}

		if( action == 'publish' ) {
			self.publish();
			return;
		}

		if(typeof options.onExecuteAction == 'function')
			options.onExecuteAction(action,this);

	});


	if(typeof self.initialize == 'function' ){
		self.initialize();
	}
	if(typeof options.initialize == 'function' ){
		options.initialize(self);
	}
	this.handleArgument = function(method){
		if(arguments.length < 1) return self;
		var args = []; for(var i = 1; i < arguments.length; i ++) args.push(arguments[i]);
		if(method && typeof self[method] == 'function'){
			return self[method].apply(self,args);
		}
	};
}

var editorPrototype = {
	apiPathPrefix: '',
	id: null,
	bridge: null,

	richEditorConfig: null,

	initialize: function(){
		var self  = this;
		var $scope = self.$scope;
		var $form = self.$form;
		
		self.updateStatus();

		hc.ui.initUIElement($scope);

		if(typeof window.tinyMCE != 'undefined'){
			var tinyMCEConfig = self.richEditorConfig || {};
			$scope.find('.rich-editor').each(function(){
				tinymce.init(tinyMCEConfig);
			});
		}

		var queries = Fragment.parseQuery(location.search);

		bridge = new SelectorBridge();
		this.bridge = bridge;
		bridge.multiSelect = queries.multiple == 'yes';
		bridge.getIds = function(){
			if(self.id)
				return [self.id];
			return [];
		}
		bridge.onSelectTooMuch = function(){
			hc.ui.showMessage( hc.loc.getText('error_select_too_much') , 'error');
		}
		bridge.onUpdate = function(){
		}
		bridge.onReload = function(){
			bridge.ids = bridge.getIds();
		}
		if(queries && queries.callback)
			bridge.connect(queries.callback);

		/* ---------- Form Submission ---------- */


		var processing = false;
		var formConfig = {
			onSubmit:function(){
				
				$scope.find('.form-group').removeClass('has-error').removeClass('has-warning');


				if(self.options.iframe){
					if(this.action.substr( this.action.length -5, 5 )!='.html')
						this.action +='.html';
				}

				if(processing){
					hc.ui.showMessage( hc.loc.getText('error_processing'),'error');
					return false;
				}
				
				var success = true;
				var error_required_empty = [];
				$scope.find('[re-required]').each(function(){
					var val = $(this).val();
					if(!val || val.length < 1){

						success = false;

						error_required_empty.push(this);
					}
				});

				$scope.find('[re-error]').each(function(){
					success = false;
					var val = $(this).attr('re-error');
					if(val && val.length >0){

						hc.ui.showMessage( val,'error');
					}
				});

				if(!success){
					if(error_required_empty.length> 0){

						$(error_required_empty).each(function(){
							$(this).parents('.form-group').addClass('has-error');
						});

						hc.ui.showMessage( hc.loc.getText('error_required_empty'),'error');
						$form.trigger('error', []);
					}
				}else{
					processing = true;
				}

				if(success)
					$form.trigger('submitting', []);

				return success;
			},onResponse:function(rst){
				processing =false;
				var frm = this;


				if(rst.id){
					
					hc.ui.showMessage( hc.loc.getText('record_saved') ,'success',5000 );

					if(!self.id){
						self.id = rst.id;
						var url = self.apiPathPrefix +'/'+self.id+'/edit';
						
						$scope.find('input[name=id]',frm).val( self.id);
						
						self.updateStatus();

						History.pushState( null,null, url);
					}
				}
				
				if(rst.error && rst.error.message){	
					hc.ui.showMessage( rst.error.message,'error' );
				}

				$form.trigger('response', [rst]);

				if(hc.auth.isResultRestrict(rst)){

					return hc.auth.handleLogin(function(){
						self.submit();
					});
				}


			},onError:function(){
				processing =false;
				hc.ui.showMessage( hc.loc.getText('error_cannot_save'),'error' );
				$form.trigger('error', []);
			},onTimeout : function(){

				processing =false;
				hc.ui.showMessage( hc.loc.getText('error_timeout'),'error' );
				$form.trigger('error', []);
			}
		};

		if(this.options.iframe){
			$form.ifrm(formConfig);
		} else{

			$form.on('submit', function(evt){
				evt.preventDefault();
				var success = formConfig.onSubmit.apply( $form.get(0) ,[]);
				if(success){
					$.ajax({
						url: self.getURL(), 
						data: self.getData(), 
						type: 'post',
						dataType: 'json',
						success: function(rst){
							formConfig.onResponse.apply( $form.get(0), [rst] );
						},
						error : function(){
							formConfig.onError.apply( $form.get(0),[]);
						}
					});
				}
			});

		}

	},

	getURL: function(){
		var self = this;
		var $form = self.$form;
		var out = $form.prop('action')
		if(!this.options.iframe){
			out +='.json';
		}

		if(self.options.getURL) out = self.options.getURL.apply(self, [self.$form]);

		return out;
	},

	getData: function(){
		var self = this;
		var $form = self.$form;
		var out = $form.serialize();
		if(self.options.getData) out = self.options.getData.apply(self, [self.$form]);

		return $form.serialize();
	},

	submit: function(){
		var self = this;
		if(typeof window.tinyMCE != 'undefined')
			tinyMCE.triggerSave();
		self.$form.submit();
	},

	publish : function(){
		var self = this;
		if(self.id){
			$.post(self.apiPathPrefix +'/batch/publish','ids='+self.id, function(rst){
				if(rst.data){
					hc.ui.showMessage( hc.loc.getText('record_published') ,'success',5000 );
				}
			},'json');
		}
	},

	updateStatus: function(){
		var self = this;
		var $scope = self.$scope;
		if(self.id){
			$scope.find('.record-editor [re-visible=edit]').removeClass('hidden');
			$scope.find('.record-editor [re-hidden=edit]').addClass('hidden');
			$scope.find('.visible-edit').removeClass('hidden');
			$scope.find('.hidden-edit').addClass('hidden');
		}else{
			$scope.find('.record-editor [re-visible=edit]').addClass('hidden');
			$scope.find('.record-editor [re-hidden=edit]').removeClass('hidden');
			$scope.find('.visible-edit').addClass('hidden');
			$scope.find('.hidden-edit').removeClass('hidden');
		}
	}
};
RecordEditor.prototype = editorPrototype;
RecordEditor.defaults = {
	text:null,
	iframe: false,
	getURL: null,
	getData: null
};


if(typeof window.hc == 'undefined') window.hc = {};
if(typeof window.hc.ui == 'undefined') window.hc.ui = {};
hc.ui.RecordEditor = RecordEditor;
//window.hc.editor = editor;


$.fn.recordEditor = function(options){
	var args = arguments;
	var c = 'each';
	var dataKey = 'record-editor';

	if(args.length > 0 && typeof args[0] != 'object' || args.length == 0){
		var $elm = $(this).eq(0);
		if($elm.length){
			var editor = $elm.data(dataKey);
			if(editor) {
				return editor.handleArgument.apply(this, args);
			}
		}
	}

	return $(this)[c](function(){
		var elm = this;
		var $elm = $(elm);

		var editor = $elm.data(dataKey);

		if(editor){
			return editor.handleArgument.apply(this, args);
		}
		editor = new RecordEditor($elm, $.extend({}, options, RecordEditor.defaults));

		$elm.data(dataKey, editor);
	});
};


})(jQuery);