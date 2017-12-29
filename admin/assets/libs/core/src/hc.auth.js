;(function(exports){

exports.auth = {
	isResultRestrict: function(rst){
		if(rst && rst.error && rst.error.code == 120){
			return true;
		}
		return false;
	},
	handleLogin: function(callback){
		window.authSuccessCallback = function(){
			callback();
		};
		exports.ui.openModal(exports.net.site_url('/auth/popup?callback=parent.authSuccessCallback'));
	}
};

})(hc);