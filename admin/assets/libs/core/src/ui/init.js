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