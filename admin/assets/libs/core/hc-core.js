/*
* HDV Components
*/
;(function(){
var hc = {};

var _config = {};

hc.config = {
	set: function(key,val){
		if(typeof arguments[0] == 'object'){
			var dict = arguments[0];
			for(var dictKey in dict){
				_config[dictKey] = dict[dictKey];
			}
			return;
		}
		_config[key] = val;
	},
	get: function(key,def){
		if(!def) def = null;
		if(!key) return _config;
		if(typeof _config[key] == 'undefined') return def;
		return _config[key];
	}
};

window.hc = hc;
})();
;(function(exports){

var loc = {
	code :null,

	text:{
	},

	setText : function(key,str){
		if(typeof key == 'object'){
			for(var _key in key)
				loc.setText(_key, key[_key]);
			return ;
		}
		loc.text[key] = str;
	},

	getText: function(key,params){
		var str = key;
		var text_lib = loc.text;
		var loc_code = loc.code;
		if(typeof text_lib == 'function'){
			str = text_lib(key);
		}else if(loc_code !=null && typeof text_lib[loc_code][key] != 'undefined'){
			str = text_lib[loc_code][key];
		}else if(typeof text_lib[key] != 'undefined'){
			str = text_lib[key];
		}
		if(params ){
			for(var key in params){
				str = str.replace('{'+key+'}',params[key]);
			}
		}
		return str;
	}
};

exports.loc = loc;
})(hc);
;(function(exports){

var net = {};
net.url = function(val,prefix){
	var str = prefix;
	if(prefix && prefix.substr(prefix.length-1,1) != '/'){
		if(val && val.substr(0,1)!='/')
			str += '/';
	}
	return str + val;
};
net.site_url = function(val){
	return net.url(val, exports.config.get('site_url',''));
};
net.asset_url = function(val){
	return net.url(val, exports.config.get('asset_url',''));
};
exports.net = net;
})(hc);
;(function(exports,$){

var initUIElement = function(scope){
	var $scope = $(scope);
    $(ui.modules).each(function(idx, callback){
    	callback($scope);
    });
};

var ui = {
	modules: [],
	initUIElement: initUIElement
};

exports.ui = ui;

})(hc,jQuery)
;(function(exports,$){
if(!$) {
	console.error('ElementParser require jQuery');
	return;
}

// private variables
var _registeredDirectives = {}; // only registered directives will be fired on ElementParser
var _directiveKeys = [];

// Define default directives;
var defaultDirectives = {
	'text': function($elm, str, val, prefix){
		if(typeof moment != 'undefined' && $elm.attr(''+prefix+'date-format') == 'ago')
			val = moment(val).fromNow();
		$elm.text( val );
	},
	'href': function($elm, str, val, prefix){
		$elm.prop('href', val );
	},
	'target': function($elm, str, val, prefix){
		$elm.prop('target', val );
	},
	'placeholder': function($elm, str, val, prefix){
		$elm.prop('placeholder', val );
	},
	'data': function($elm, str, val, prefix, key, parser, data){
		val = defaultContentParser(data, '(function(){return '+str+'})()');
		if(typeof val == 'object'){
			for(var key in val)
				$elm.data(key, val[key] );
		}
	},
	'attr': function($elm, str, val, prefix, key, parser, data){
		val = defaultContentParser(data,  '(function(){return '+str+'})()');
		if(typeof val == 'object'){
			for(var key in val)
				$elm.attr(key, val[key] );
		}
	},
	'id': function($elm, str, val, prefix){
		$elm.prop('id', val );
	},
	'name': function($elm, str, val, prefix){
		$elm.prop('name', val );
	},
	'class': function($elm, str, val, prefix){
		$elm.attr('class', val );
	},
	'src': function($elm, str, val, prefix){
		$elm.prop('src', val );
	},
	'value': function($elm, str, val, prefix){
		$elm.val(val );
	},
	'title': function($elm, str, val, prefix){
		$elm.prop('title', val );
	},
	'html': function($elm, str, val, prefix){
		$elm.html(val);
	},
	'alt': function($elm, str, val, prefix){
		$elm.prop('alt', val );
	},
	'width': function($elm, str, val, prefix){
		$elm.width( val );
	},
	'height': function($elm, str, val, prefix){
		$elm.height( val );
	},
	'background-image': function($elm, str, val, prefix){
		$elm.css('background-image', val ? 'url('+ val+')' : '' );
	},
	'if': function($elm, str, val, prefix){
		if(!val){
			$elm.remove();
		}
	},
	'not-if': function($elm, str, val, prefix){
		if(val){
			$elm.remove();
		}
	},
	'loop': function($elm, str, val, prefix, key, parser, data){
		if(str.indexOf('..')==-1) return;
		var parts = str.split('..');
		var ary = parser(parts[1]);
		var $parent = $elm.parent();
		$elm.remove();
		$elm.attr(prefix+key,null);
		if($.isArray(ary)){
			$.each(ary, function(i, val){
				var _scope = {};
				_scope[ parts[0] ] = val;

				var $child = $elm.clone();
				$parent.append($child);

				hc.ui.ElementParser($child, _scope, prefix);
			});
		}
	}
};
defaultDirectives['data'].isInternalParser = true;
defaultDirectives['attr'].isInternalParser = true;
defaultDirectives['loop'].isInternalParser = true;


// Helper functions
var addDirective = function(key, callback, pos){
	if(!pos ) pos = _directiveKeys.length;

	if(typeof callback != 'function'){
		console.error('ElementParser.addDirective must pass function at 2th parameter');
		return false;
	}
	
	_registeredDirectives [key] = callback;
	if(pos>= _directiveKeys.length)
		_directiveKeys.push(key);
	else
		_directiveKeys.splice(pos, 0, key);
	return true;
};
var getDirectiveAt = function(pos){
	if(pos < 0 || pos >= _directiveKeys.length) return null;
	return _directiveKeys[pos];
};
var getDirective = function(key){
	if(typeof _registeredDirectives[key] != 'undefined')
		return _registeredDirectives[key];
};
var replaceDirective = function(key, callback){
	if(typeof _registeredDirectives[key] != 'undefined')
		_registeredDirectives[key] = callback;
};
var removeDirective = function(key){
	var pos = -1;
	var _newKeys = [];
	for(var i = 0 ; i < _directiveKeys.length; i ++)
	{
		if(_directiveKeys[i] != key)
		{
			_newKeys.push(_directiveKeys[i]);
		}
	}

	if(typeof _registeredDirectives[key] !='undefined') {
		_registeredDirectives[key] = null;
		delete _registeredDirectives[key];
	}

	_directiveKeys = _newKeys;
};
var defaultContentParser = function(row, field){
	if( typeof field == 'undefined') return;
	if(typeof row[field] != 'undefined'){
		return row[field];
	}
	try{
		with(row){
			return eval(field);
		}
	}catch(err){}
	return '';
};

for(var key in defaultDirectives)
	addDirective(key, defaultDirectives[key]);

// ElementParser
var ElementParser = function($rowElm, data, prefix){
	if(!prefix) prefix = 'rc-';
	var contentParser = arguments[1];
	if(typeof contentParser != 'function') contentParser = function(exp){return ElementParser.contentParser(data,exp);};
	if(typeof contentParser != 'function') contentParser = function(exp){return defaultContentParser(data,exp);};

	$.each(_directiveKeys,function(i, _directiveKey){
		var _directiveCallback = _registeredDirectives[_directiveKey];
		var _parser = typeof _directiveCallback.parser == 'function' ? _directiveCallback.parser : contentParser;
		var _isInternalParser = typeof _directiveCallback.isInternalParser != 'undefined' && _directiveCallback.isInternalParser ? _directiveCallback.isInternalParser : false;

		var attrName = prefix+_directiveKey;

		var $list = $rowElm.find('['+attrName+']');
		//console.log(_directiveKey, $list);
		$list.each(function(){
			var $elm = $(this);
			var exp = $elm.attr(attrName);
			var val = _isInternalParser ? null : _parser(exp);
			_directiveCallback.call($rowElm, $elm, exp, val, prefix, _directiveKey, _parser, data);
		});
	});
};

ElementParser.defaultDirectives = defaultDirectives;
ElementParser.contentParser = defaultContentParser;
ElementParser.addDirective = addDirective;
ElementParser.getDirectiveAt = getDirectiveAt;
ElementParser.getDirective = getDirective;
ElementParser.removeDirective = removeDirective;
ElementParser.replaceDirective = replaceDirective;
exports.ElementParser = ElementParser;
})(hc.ui,jQuery);