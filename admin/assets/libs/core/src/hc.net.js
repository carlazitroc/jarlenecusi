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