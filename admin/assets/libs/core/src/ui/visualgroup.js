;(function(exports,$){
var VGCore = function(options){
	this.visible = false;
	this.initialize.apply(this,arguments);
};
VGCore.prototype.el = null;
VGCore.prototype.options = null;
VGCore.prototype.visible = false;
VGCore.prototype.initialize = function(opts){
	this.el = opts.el;
	this.options = opts;
	this.target = opts.target;
	this.scope = opts.scope;
	this.bindElm();
	this.update();
};
VGCore.prototype.bindElm = function(){
	var self = this;
	var $target = self.options.scope ? $(this.target,self.options.scope) : $(this.target);
	self.$target = $target;
	$target.on('change',function(){
		self.update();
	});
};
VGCore.prototype.update = function(){};

var VGSelect = function(opts){
	this.type = 'select';
	this.value = opts.value;
	this.initialize.apply(this,arguments);
};	
VGSelect.prototype =$.extend({}, VGCore.prototype);
VGSelect.prototype.update = function(){
	var self = this;
	this.visible = self.$target.find('option[value='+this.value+']').is(':selected');
	if(typeof this.options.onUpdate == 'function'){
		this.options.onUpdate.apply(this, []);
	}
};

var VGCheckbox = function(opts){
	this.type = 'checkbox';
	this.target = opts.target;
	this.initialize.apply(this,arguments);
};
VGCheckbox.prototype =$.extend({}, VGCore.prototype);
VGCheckbox.prototype.initialize = function(opts){
	this.el = opts.el;
	this.options = opts;
	this.target = opts.target;
	this.scope = opts.scope;
	this.bindElm();
	this.update();
};
VGCore.prototype.bindElm = function(){
	var self = this;
	var $target = self.options.scope ? $(this.target,self.options.scope) : $(this.target);
	self.$target = $target;
	$target.on('change',function(){
		self.update();
	});
};
VGCheckbox.prototype.update = function(){
	var $target = self.options.scope ? $(this.target,self.options.scope) : $(this.target);
	this.visible = $target .is(':checked');
	if(typeof this.options.onUpdate == 'function'){
		this.options.onUpdate.apply(this, []);
	}
};

exports.VGCore = VGCore;
exports.VGCheckbox = VGCheckbox;
exports.VGSelect = VGSelect;

exports.modules.push(function($scope){

	$scope.find('[vg-select],[vg-checked]').each(function(){
		var $el = $(this);
		var list = [];
		var onUpdate = function(){
			var counter = 0;
			
			$(list).each(function(idx, checker){
				if(checker.visible) counter++;
			});
			
			if( counter >= list.length){
				$el.show();
			}else{
				$el.hide();
			}
		};
		if($el.attr('vg-select')!=null){
			list.push(new VGSelect({scope: $scope, el: $el,target: $el.attr('vg-select') ,value:$el.attr('vg-select-value'),onUpdate: onUpdate}));
		}
		if($el.attr('vg-checked')!=null){
			list.push(new VGCheckbox({scope: $scope, el: $el,target: $el.attr('vg-checked'),onUpdate: onUpdate}));
		}
		onUpdate();
	});
});


})(hc.ui,jQuery);