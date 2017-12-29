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