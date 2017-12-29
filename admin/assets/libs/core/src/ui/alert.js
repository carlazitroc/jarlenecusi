/*
* HDV Components
*/
;(function(exports){

exports.showMessage = function(text, type, timeout, layout){
	var cfg = {
	    'text': '-', layout:'topCenter','type':type
	};
	if(typeof text == 'object'){
		cfg = text;
	}else{
		cfg.text = text;
	}
	if(!cfg.type) cfg.type = 'default';
	if(layout) cfg.layout = layout;
	if(timeout) cfg.timeout = timeout;


	return noty(cfg);
}

})(hc.ui);