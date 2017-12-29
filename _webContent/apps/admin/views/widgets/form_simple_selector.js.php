
;(function($){

var ns = 'dmr-selector';
var clsName = 'dmrSelector';

var defaultSettings= {
	'field_name':'',
	'url':'',
	'row_url':'',
	'row_label':'',

	onChange: null
};

var instanceCounter = 0;
var ParameterParser = function(str, params, prefix, suffix){
	if(!prefix) {prefix = '{'; suffix = '}';}
	if(!suffix) {suffix = '}';}

	for(var key in params)
		str = str.replace(prefix+key+suffix, params[key]);
	return str;
}
var DmrSimpleSelector = function(elm, options){
	var self = this;
	var $elm = $(elm);

	var isntanceId = instanceCounter ++;
	var elmConfig = {
		'url': $elm.attr(ns+'-url'),
		'row_url': $elm.attr(ns+'-row-url'),
		'row_label': $elm.attr(ns+'-row-label'),
	}
	if(!options) options = {};

	options = $.extend({},elmConfig,options);
	options = $.extend({},defaultSettings,options);

	this.options = options;

	var $input = $elm.find('['+ns+'-input]');

	var selectButtonStr = '['+ns+'-btn="select"]';
	var $btnSelect = $elm.find(selectButtonStr);

	var clearButtonStr = '['+ns+'-btn="clear"]';
	var $btnClear = $elm.find(clearButtonStr);

	var $label = $elm.find('['+ns+'-label]');

	var element_id = $elm.attr(ns);
	if(!element_id || element_id == '')
		element_id = clsName+ isntanceId;




	var selector = new Selector( $input );
	selector.root = $elm; 
	selector.caller = clsName+'Callback_'+isntanceId;
	selector.getValue = function(){
		return $(this.target).val();
	}
	selector.onUpdate = function(){
		this.updateField();
		var self = this;
		var val = this.getValue();

		$btnClear.prop('disabled', true );


		if(val && val.length > 0 && val !='0' && val !=''){
			$label.html('<?php echo lang('error_processing')?>');
			var url = ParameterParser( options.row_url,{
				'val':val,
				'base_url':hc.net.url('',hc.config.get('base_url')),
				'site_url':hc.net.url('',hc.config.get('site_url')),
				'locale': hc.config.get('locale_code')
				});
			
			$btnClear.prop('disabled', false );

			$.getJSON(url, function(rst){
				if(rst.id){
					var str = ParameterParser( options.row_label, rst);
					$label.html(str);
				}else{
					$label.html('<?php echo lang('not_selected')?>');
				}
			}).error(function(){
					$label.html('<?php echo lang('error_connection')?>');
			});
		}else{
			$label.html('<?php echo lang('not_selected')?>');
		}
	}
	selector.onUpdate();
	
	window[selector.caller] = selector;

	this.update = function(){
		selector.onUpdate();
	};

	this.select = function(){
		
		var url = options.url;
		url+='?dialog=yes&callback=parent.'+selector.caller+'.didResponse';

		hc.ui.openModal(url, {size:'',onHidden: function(){
			selector.onUpdate();
		}});
	}

	this.getValue = function(){
		selector.getValue();
	};

	this.attach = function(){

		$elm.on('click', selectButtonStr, function(evt){
			evt.preventDefault();
			
			self.select();
		});

		$elm.on('click', clearButtonStr, function(evt){
			evt.preventDefault();
			
			selector.setValue('');
			selector.onUpdate();
		})
	};

	this.detach = function(){

		$elm.off('click', selectButtonStr);
		$elm.off('click', clearButtonStr);
	};

	this.destroy = function(){
		this.detach();
		$elm.data(ns, null);
	};

	this.getRawSelector = function(){
		return selector;
	}

	this.attach();
	$elm.data(ns, this);
};

$.fn[clsName] = function(options){
	return $(this).each(function(){
		var instance = $(this).data(ns);
		if(!instance){
			instance = new DmrSimpleSelector(this,options);
		}else if(arguments.length> 1 && typeof arguments[0] == 'string' && typeof instance[ arguments[0] ] != 'undefined'){
			return instance[ arguments[0] ].apply(instance, [arguments[1]] );
		}
	});
};

$(function(){
	$('['+ns+']')[clsName]();
});

})(jQuery);