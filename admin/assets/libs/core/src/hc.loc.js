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