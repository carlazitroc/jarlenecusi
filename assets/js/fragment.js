var Fragment = function(options){
	if(typeof options == 'string'){
		options = {value: options};
	}
	this.options = options;
	if(options && options.value)
		this.parse( options.value );
};
Fragment.parseQuery = function(str){
	var params = {};
	if(str.substr(0,1)=='?')
		str = str.substr(1);
	if(str.length > 0){
		var groups = str.split('&');
		for(var i = 0; i < groups.length; i++){
			var group = groups[i];
			var parts = group.split('=',2);
			if(parts.length == 2)
				params[ decodeURI(parts[0]) ] = decodeURI(parts[1]);
			if (parts.length == 1)
				params[ decodeURI(parts[0]) ] = null;
		}
	}
	return params;
};
Fragment.buildQuery = function(params){
	var groups = [];
	for(var key in params){
		if(params[key] == null)
			continue;
		groups.push(key+'='+encodeURI (params[key]));
	}
	return groups.join('&');
};
	
Fragment.prototype = {
	nodes : null,
	path : '',
	value : '',
	queryString: '',
	queries: {},
	parse : function( value ){
		var str = String( value );
		
		if(str.indexOf('?')>=0){
			var pos = str.indexOf('?');
			this.setQueryString( str.substr( pos+1, str.length - pos) );
			str = str.substr(0,pos);
			
		}
		
		if(str.substr(0,1) == '/') str = str.substr(1, str.length-1);
		if(str.substr(-1,1)== '/') str = str.substr(0, str.length-1);
		
		this.nodes = str.length > 0 ? str.split('/') : [''];
		this.path = str;
		this.value = this.val();
	},
	setQueryString: function(str){
		if(str.substr(0,1) == '?') str = str.substr(1, str.length-1);
		this.queryString = str;
		this.queries = Fragment.parseQuery(str);
		this.value = this.val();
	},
	parseQuery: function(str){
		this.queries = Fragment.parseQuery(str);
	},
	at : function(index){
		if(!this.nodes) return null;
		var nodes = this.nodes;
		return index >=0 && index < nodes.length ? nodes[index] : null;
	},
	equalAt : function(index, val){
		return ( this.at(index) === val);
	},
	param: function(key){
		if( this.queries[key] )
			return this.queries[key];
		return null;
	},
	val: function(){
		var str = this.path;
		var qs = Fragment.buildQuery( this.queries );
		if(qs.length > 0) str += '?' +qs;
		return str;
	}
};
