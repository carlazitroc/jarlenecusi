/*
* HDV Components
*/
var Selector = function(target){};
Selector.prototype.ids = [];
Selector.prototype.multiSelect = true;
Selector.prototype.target = null;
Selector.prototype.reload = function(){};
Selector.prototype.didResponse = function(connector){};
Selector.prototype.didUpdate = function(){};
Selector.prototype.getIds = function(){};
Selector.prototype.tempAdd = function(id){};
Selector.prototype.tempUpdate = function(id){};
Selector.prototype.tempRemove = function(id){};
Selector.prototype.tempClear = function(){};
Selector.prototype.update = function(){};
Selector.prototype.updateField = function(){};
Selector.prototype.onUpdate = function(){};

var SelectorBridge = function(){};
SelectorBridge.prototype.ids = [];
SelectorBridge.prototype.multiSelect = true;
SelectorBridge.prototype.connect = function(callback){};
SelectorBridge.prototype.disconnect = function(){};
SelectorBridge.prototype.send = function(){};
SelectorBridge.prototype.onReload = function(){};
SelectorBridge.prototype.onUpdate = function(ids){};
SelectorBridge.prototype.onTempAdd = function(id){};
SelectorBridge.prototype.onTempRemove = function(id){};
SelectorBridge.prototype.onTempUpdate = function(ids){};
SelectorBridge.prototype.onSelectTooMuch = function(){};;
SelectorBridge.prototype.getIds = function(){};

SelectorBridge.queryToParams = function(str){}

;(function($){
	
	Selector = function (target){
		var _connector = null;
		var _self = this;
		
		_self.ids = [];
		_self.target = target;
		
		var _tmpIds = null;
		function getIds(){
			var $target = $(target);
			if($target.length > 1){
				return $target.map(function(){
					return $(this).val();
				});
					
			}else{
				var str = $(target).val();
				if(!str || str.length < 1)return [];
				var ary = _self.multiSelect == false || str.indexOf(',') >0 ? str.split(',') : [str];
				
				return ary;
				
			}
		}
		
		function reload (){
			_self.ids = getIds();
		}
		
		function didResponse(connector){
			_connector = connector;
			_connector.selector = _self;
			reload();
			
			_connector.onSelect = function(ids){
				return update(ids);
			};
			_connector.onDisconnect = function(){
				_self.onClose();
			};
			_connector.tempAdd = tempAdd;
			_connector.tempRemove = tempRemove;
			_connector.tempUpdate = tempUpdate;
			_connector.tempClear = tempClear;
			_connector.bridge.ids = _tmpIds? _tmpIds : _self.ids ;
			
			_connector.onConnect();
		}
		function tempAdd(id){
			
			var found = 0;
			if(_connector && ! _connector.multiSelect ){
				_tmpIds = null;
			}else{
				
				if(_tmpIds){
					for(var j=0; j< _tmpIds.length; j++){
						if(_tmpIds[j] == id)
							found ++;
					}
				}
			}
			if(found<1){
				if(!_tmpIds) _tmpIds = [];
				_tmpIds.push(id);
			}
			if(_connector)
				_connector.bridge.ids = _tmpIds? _tmpIds : _self.ids ;
		}
		
		function tempUpdate(ids){
			
		}
		
		function tempRemove(id){
		
			var found = -1;
			if(_tmpIds){
				for(var j=0; j< _tmpIds.length; j++){
					if(_tmpIds[j] == id)
						found = j;
				}
				if(found>=0){
					_tmpIds.splice(found,1);
				}
			}
			if(_connector)
				_connector.bridge.ids = _tmpIds? _tmpIds : _self.ids ;
		}
		
		function tempClear (){
			_tmpIds = null;
			if(_connector)
				_connector.bridge.ids =  _self.ids ;
		}
		
		function didUpdate( ids ){
			if(!ids)return false;
			
			_self.ids = ids;
			_self.onUpdate();
			return true;
		}
		
		function update (ids){
			if(!_connector)return false;
			_tmpIds = null;
			return didUpdate(ids);
		}
		
		function updateField(){
			if( ! _self.multiSelect ){
				if(_self.ids.length > 0)
					$(target).val( _self.ids[0]);
				else
					$(target).val('');
				return;
			}
			$(target).val( _self.ids.join(',') );
		}
	
		_self.reload = reload;
		_self.didResponse = didResponse;
		_self.didUpdate = didUpdate;
		_self.getIds = getIds;
		_self.update = update;
		_self.updateField = updateField;
		_self.onClose = function(){
			// default action
			try{
				$.colorbox.close();
			}catch(err){}
		};
		_self.onUpdate = function(){
			_self.ids = getIds();
			updateField();
		};
		_self.ids = getIds();
		_self.tempAdd = tempAdd;
		_self.tempRemove = tempRemove;
		_self.tempUpdate = tempUpdate;
		_self.tempClear = tempClear;
		
	};
	
	
	SelectorBridge = function(){
		
		var _self = this;
		var par = '';
		var tar = '';
		var func = '';
		
		this.ids = [];
		this.getIds = function(){
			return _self.ids;
		};
		
		var connector = {};
		connector.selector = null;
		connector.bridge = this;
		
		connector.multiSelect = _self.multiSelect;
		// to be renewed by Selector
		connector.tempUpdate = function(ids){};
		connector.tempAdd = function(id){};
		connector.tempRemove = function(id){};
		connector.tempClear = function(id){};
		connector.onSelect = function(){};
		connector.onDisconnect = function(){};
		connector.update = function(){
			_self.onUpdate();
			_self.onReload();
		};
		connector.onConnect = function(){
			connector.multiSelect = _self.multiSelect;
			connector.update();
		};
		connector.getIds = function(){
			return _self.ids;
		};
		_self.onSelectTooMuch = function(){};
		_self.onUpdate = function(){
			
			// default behavior
			/*
			// you should implement by yourself
			
			$elms = $('input[name^=recordId]');
			$elms.attr('checked',false);
			$(bridge.ids).each(function(idx,val){
				for(var i =0; i< $elms.length; i++){
					if($elms[i].value == val)
						$($elms[i]).attr('checked',true);
				}
			});
			
			//*/
		};
		_self.onReload = function(){
			
			// default behavior
			/*
			// you should implement by yourself
			
			$('#btn-select').html(_self.ids.length> 0 ? 'Select ('+_self.ids.length+')':'Cancel Select');
			//*/
		};
		_self.onTempAdd = function(id){
			connector.tempAdd (id);
			
			_self.onUpdate();
		};
		_self.onTempClear = function(){
			connector.tempClear();
		};
		_self.onTempRemove = function(id){
			connector.tempRemove (id);
		};
		_self.onTempUpdate = function(ids){
			connector.tempUpdate (id);
		};
		_self.onDisconnect = function(){
			
			if(par == 'opener'){
				window.close();
			}
		};
		_self.disconnect = function(){
			connector.onDisconnect();
			_self.onDisconnect();
		};
		_self.connect = function(callback){
			var segs = callback.split('.');
			var objName = null;
			var funcName = null;
			if(segs.length >=3 ){
				var offset = 0;
				if(segs[0] == 'window'){
					offset = 1;
				}
				par = segs[offset+0];
				objName = segs[offset+1];
				funcName = segs[offset+2];
			
			}else if(segs.length == 2){
				par = window.opener ? 'opener' : 'parent';
				objName = segs[0];
				funcName = segs[1];
			}else if(segs.length == 1){
				par = window.opener ? 'opener' : 'parent';
				funcName = segs[1];
			}
			
			if(funcName == null)return false;
			
			if(objName != null){
				tar = window[par][objName ];
				func = tar[ funcName ];
			}else{
				func = window[par][ funcName ];
			}
			
			connector.multiSelect = _self.multiSelect;
			
			if(func){
				func(connector);
				return true;
			}else{
				$.error('Bridge Callback not found');
				return false;
			}
		};
		_self.send = function(){
			var ids = _self.getIds();
			if(!_self.multiSelect){
				if(ids.length > 1){
					_self.onSelectTooMuch();
					return false;
				}
			}
			return connector.onSelect(ids);
		};
	};
	SelectorBridge.queryToParams = function(str){
		if(str.substr(0,1) == '?')
			str = str.substr(1);
		var obj = {};
		var segs = str.split('&');
		for(var i = 0 ; i< segs.length; i ++){
			var parts = segs[i].split('=',2);
			if(parts.length == 2){
				obj[ parts[0] ] = parts[1];
			}else if(parts.length == 1){
				obj[ parts[0] ] = null;
			}
		}
		return obj;
	};
	
})(jQuery);

