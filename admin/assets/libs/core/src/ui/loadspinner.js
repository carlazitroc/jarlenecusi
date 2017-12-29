/*
* HDV Components
*/
;(function(exports){

exports.showLoaderAnimation = function(){
	$('<div class="global-loader"><div class="global-loader-wrap"><div class="loader"></div></div></div>').appendTo('body');
}

exports.hideLoaderAnimation = function(){
	$('.global-loader').remove();
}

})(hc.ui);