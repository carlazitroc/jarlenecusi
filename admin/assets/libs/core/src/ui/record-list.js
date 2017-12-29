/*
* HDV Components
*/
;(function(hc, $){


var RecordRow = function(elm, rrId, dataRow, options){

	var $elm = $(elm);
	var self = this;
	this.rrId = rrId;
	this.elm = elm;
	this.$elm = $elm;
	this.options = options;
	
	var $rowElm = $elm;
	
	var _checked = false;

	function getData(field){


		if( typeof field == 'undefined') return dataRow;
		//console.log(dataRow, field);
		var row = dataRow;
		if(typeof dataRow[field] != 'undefined'){
			return dataRow[field];
		}

		if(field!= ''){
			try{
				return eval(field);
			}catch(err){}
			return null;
		}
		return null;
	}

	function setData(newData){
		dataRow = newData;
		update();

		return self;
	}


	function update(){

		if(typeof options.beforeUpdate == 'function'){
			options.beforeUpdate.apply(self, [$rowElm, dataRow])
		}
		hc.ui.ElementParser($rowElm, getData);
		if(typeof options.afterUpdate == 'function'){
			options.afterUpdate.apply(self, [$rowElm, dataRow])
		}

		return self;
	}

	$rowElm.prop('id', 'rr-'+rrId).data('rr-id', rrId).on('click','td,th',function(evt){

		if($rowElm.hasClass('disabled')){
			return;
		}

		var $tar = $(evt.target);
		if($tar.is('.btn,.btn-link,a,.label,.dropdown')) return;
		if($tar.parents('.btn,a,.label,.dropdown').length>0) return;
		evt.preventDefault();
		
		self.toggleChecked();
	});
	$rowElm.find('input[type=checkbox]').val(rrId);
	if(dataRow.is_image){
		$rowElm.find('.thumbnail').css('background-image','url('+ dataRow.image.thumbnail +')');
	}

	if(typeof options.parseRow == 'function'){
		options.parseRow.apply(self, [$rowElm, dataRow])
	}

	
	var $btnSelect = $rowElm.find('.btn-select');
	var $icon = $rowElm.find('.btn-select i');

	this.getDisabled = function(){
		return $rowElm.hasClass('disabled');
	};

	this.setDisabled = function(newVal){
		if(newVal){
			$rowElm.addClass('disabled');
		}else{
			$rowElm.removeClass('disabled');
		}

		if(typeof options.onDisabledStateChange == 'function'){
			options.onDisabledStateChange.apply(this,null);
		}
		
		return self;
	};

	this.getChecked= function(){
		return _checked;
	};
	
	this.setChecked= function(newVal){
		_checked = newVal;
		$icon.removeClass().addClass('fa');
		if(newVal){	
			$rowElm.addClass('active');
			$icon.addClass($icon.data('active'));
			$btnSelect.addClass('btn-success');
		}else{
			$rowElm.removeClass('active');
			$icon.addClass($icon.data('deactive'));
			$btnSelect.removeClass('btn-success');
		}
		if(typeof options.onCheckedStateChange == 'function'){
			options.onCheckedStateChange.apply(self,null);
		}
		return self;
	};


	this.toggleChecked= function(){
		self.setChecked ( !_checked );
		
		return self;
	};
	this.getData = getData;
	this.update = update;
	this.setData = setData;

	update();
	$rowElm.data('record-row', self);
};
	
var _rr_counter = 0;
RecordRow.createID = function(){
	var rrId = 'RR'+(_rr_counter++);
	return rrId;
};

var RecordList = function(elm, options ){
	var self = this;
	self.options=  options;

	var $elm = $(elm);
	self.$elm = $elm;
	
	var bridge = null;
	
	var limit = options.limit;
	var offset = options.offset;
	var total = 0;
	var queries = !options.queries ? {} : options.queries;

	var _selector_select_all_button = '.btn-toggle-select-all:not(.disabled),[rl-toggle-select-all]:not(.disabled)';
	
	var _rrIds = [];
	var _rr_new_ids = {};
	var _rr_mapping = {};
	var _rr_data = {};
	var _rr_data_map = {};
	var _rr_elm_map = {};
	var _rr_parameters = {};
	var _rr_selected_ids = [];
	
	var _selected_counter = 0;
	var _selected_all_current = false;


	var _enable_multiple = false;

	function _error (){
		if(typeof options.onError == 'function'){
			options.onError.apply(self, arguments);
		}else if(arguments.length>0 ){

			alert('Error: '+ arguments[0]);
		}else{
			alert('Unknown error.');
		}
	}

	function updateSelectAllButton(){

		$elm.find(_selector_select_all_button).each(function(){
			var $btn = $(this);
			var $icon = $btn.find('> i');
			if(!_selected_all_current){
				$btn.removeClass('active');
				$icon.removeClass( $icon.data('active') ).addClass( $icon.data('deactive')  );
			}else{
				$btn.addClass('active');
				$icon.removeClass( $icon.data('deactive') ).addClass( $icon.data('active')  );
			}

		});
	}

	function selectCurrent(){
		_selected_all_current = true;
		for(var i=0; i< _rrIds.length; i ++){
			var rr_id = _rrIds[i];

			var row = getRowElmByRRID( rr_id );
			if(row){
				row.setChecked ( true);
			}
		}
		updateSelectAllButton();
	}

	function deselectCurrent(){
		_selected_all_current = false;
		for(var i=0; i< _rrIds.length; i ++){
			var rr_id = _rrIds[i];
			var row = getRowElmByRRID( rr_id );
			if(row){
				row.setChecked ( false);
			}
		}
		updateSelectAllButton();
	}

	function getRowElmByDataID (dataId){

		var _rrId = _rr_data_map [ dataId ];
		return typeof _rr_elm_map [ _rrId ] != 'undefined' ? _rr_elm_map [ _rrId ] : null;

	}

	function updateRowElmByDataID(dataId, newData){
		if(typeof _rr_data_map[dataId] == 'undefined') return;
		var rrId = _rr_data_map[dataId];
		var rowElm = getRowElmByRRID( rrId );
		if(rowElm){
			if(newData){
				newData._rrId = rrId;
				_rr_data_map[dataId] = newData;
				rowElm.setData(newData);
			}else{
				rowElm.update();
			}
		}
	}

	function getRowElmByRRID(rrId){
		if(typeof _rr_elm_map[rrId] != 'undefined')
			return  _rr_elm_map[rrId ];
		return null;
	}

	function getRowByDataID(dataId){
		if(typeof _rr_data_map[dataId] != 'undefined'){
			var rrId = _rr_data_map[dataId];
			return _rr_data[ rrId ];
		}
		return null;
	}

	function getRows(){
		return _rr_data;
	}

	function doBatch(action, data, ids){
		if(!ids) ids = _rr_selected_ids;
		var _ids = [];
		for(var i = 0 ; i < ids.length; i ++){
			/*
			var _rrId = ids[i];
			if(typeof _rr_data[ _rrId ] != 'undefined'){
				_ids.push( _rr_data[_rrId].id );
			}
			//*/
			_ids.push(ids[i]);
		}

		if( typeof options.onBatch == 'function'){
			return options.onBatch.apply(self, [action, _ids, data]);
		}
		return false;
	}

	function doRowAction(action, elm, rrId){

		var row_id = null;
		if(typeof _rr_data[ rrId ] != 'undefined'){
			row_id = _rr_data[rrId].id;
		}
		

		if( typeof options.onRowAction == 'function'){
			return options.onRowAction.apply(self, [action, elm, row_id]);
		}
		return false;
	}
	
	var $template = $(options.containerSelector+ ' .template',$elm);
	var $empty = $(options.containerSelector+ ' .empty',$elm);

	$template.remove();
	if($empty.length)
		$empty.remove();


	function updateAfterSelectedItemChange (){

		if( _selected_counter <1 ){

			if(options.selectorSend!=null){
				$(options.selectorSend).addClass('disabled').prop('disabled',true);
			}
			if($elm.attr('rl-select-button') != '' && typeof $elm.attr('rl-select-button') != 'undefined'){
				$(  $elm.attr('rl-select-button')).addClass('disabled').prop('disabled',true);
			}
	
			$('.btn-rr-select').addClass('seletcted').prop('disabled',true);
		}else{

			if(options.selectorSend!=null){
				$(options.selectorSend).removeClass('disabled').prop('disabled',false);
			}
			if($elm.attr('rl-select-button') != '' && typeof $elm.attr('rl-select-button') != 'undefined'){
				$(  $elm.attr('rl-select-button')).removeClass('disabled').prop('disabled',false);
			}
	
			$('.btn-rr-select').removeClass('seletcted').prop('disabled',false);
		}
		//console.log('Total :'+_selected_counter, _rr_selected_ids);
	}

	function addSelectedDataId( dataId ){
		if(!dataId) return;
		var offset = $.inArray(dataId, _rr_selected_ids);
		if(offset<0){
			_rr_selected_ids.push( dataId );
			_selected_counter  ++;
		}
		updateAfterSelectedItemChange();
		//console.log(dataId, _rr_selected_ids);
	}

	function removeSelectedDataId( dataId ){
		if(!dataId) return;
		var offset = $.inArray(dataId, _rr_selected_ids);
		if(offset>=0){
			_selected_counter  --;
			_rr_selected_ids.splice(offset,1);
		}
		updateAfterSelectedItemChange();
		//console.log(dataId, _rr_selected_ids);
	}

	function getSelectedIds(){
		return _rr_selected_ids;
	}

	function createElement(dataRow){

		if($template.length<1)
			throw new Error('$template is empty.');
		
		var rrId = null;
		if(!dataRow.id || !_rr_mapping[ dataRow.id ]){
			rrId = RecordRow.createID();
		}
		if(!dataRow.id){
			_rr_new_ids[ rrId ] = true;
		}else if(!_rr_mapping[ dataRow.id ]){
			_rr_mapping[ dataRow.id ] = rrId;
		}else{
			rrId = _rr_mapping[ dataRow.id ];
		}
		dataRow._rrId = rrId;
		
		if(dataRow.id)
			_rr_data_map [ dataRow.id ] = rrId;
		_rr_data [ rrId ] = dataRow;
		_rr_parameters [ rrId ] = typeof dataRow.parameters != 'undefined' ? dataRow.parameters : {};
		_rrIds.push( rrId );

		var $rowElm = $template.eq(0).clone();
		$rowElm.removeClass('template').addClass('rr');

		// append to container
		$elm.find(options.containerSelector).append($rowElm);

		var rrObj = new RecordRow($rowElm, rrId, dataRow, {
			onCheckedStateChange: function(){
				var data = this.getData();

				if(this.getChecked()){

					addSelectedDataId( data.id) ;
				}else{
					removeSelectedDataId( data.id );
					if(_selected_all_current){
						_selected_all_current = false;
						updateSelectAllButton();
					}
				}
			},
			beforeUpdate: options.onRowBeforeUpdate,
			afterUpdate: options.onRowAfterUpdate
		});

		if( $.inArray( dataRow.id, _rr_selected_ids) >= 0) {
			rrObj.setChecked(true);
		}

		if( typeof options.onRowCreate == 'function'){
			options.onRowCreate.apply( self, [rrObj, dataRow]);
		}

		_rr_elm_map[ rrId ] = rrObj;

		return rrObj;
	}

	function select(ids){
		//console.log(self, 'select',ids);

		$(ids).each(function(idx, id){

			addSelectedDataId(id);
			var rr = getRowElmByDataID(id);
			if( rr ) rr.setChecked(true);
		});
		//console.log(self, 'deselect',_rr_selected_ids);
		return self;
	}

	function deselect(ids){
		//console.log(self, 'deselect',ids);

		$(ids).each(function(idx, id){
			removeSelectedDataId(id);
			var rr = getRowElmByDataID(id);
			if( rr ) rr.setChecked(false);
		});

		//console.log(self, 'deselect',_rr_selected_ids);
		return self;
	}
	
	function updatePaging(){
		
		if(offset <= 0 ){
			$elm.find('.pager .previous, .pagination .previous').addClass('disabled');
		}else{
			$elm.find('.pager .previous, .pagination .previous').removeClass('disabled');
		}
		var nextIndex = offset + limit;
		if( nextIndex >= total ){
			$elm.find('.pager .next, .pagination .next').addClass('disabled');
		}else{
			$elm.find('.pager .next, .pagination .next').removeClass('disabled');
		}
		
		$elm.find('.pagination .paging-total').text( total );
		$elm.find('.pagination .paging-offset-start').text( Math.min ( total, offset + 1));
		$elm.find('.pagination .paging-offset-end').text( Math.min ( total,nextIndex));


		if(typeof options.onUpdatePaging == 'function'){
			options.onUpdatePaging.apply(self, [offset, total]);
		}
	}
	
	function updateToolbar(){
		if(bridge) bridge.onReload();
		var noRecord = _selected_counter < 1;
		$elm.find('.btn-rr-select,.btn-selected-action')
			.prop('disabled',noRecord)
			.button(noRecord ? 'disable':'enable' );

		if(typeof options.onUpdateToolbar == 'function'){
			options.onUpdateToolbar.apply(self, [_selected_counter]);
		}
	}

	function handleAfterReload(rst){
		$elm.find('[rl-text]').each(function(){
			var $elm = $(this);
			var q = $(this).attr('rl-text');
			var val = null;
			if(q && q.length>0){
				try{
					val = eval(q);
				}catch(exp){}
			}
			if(val){
				$elm.text(val);
			}
		}).find('[rl-html]').each(function(){
			var $elm = $(this);
			var q = $(this).attr('rl-html');
			var val = null;
			if(q && q.length>0){
				try{
					val = eval(q);
				}catch(exp){}
			}
			if(val){
				$elm.html(val);
			}
		}).find('[rl-value]').each(function(){
			var $elm = $(this);
			var q = $(this).attr('rl-value');
			var val = null;
			if(q && q.length>0){
				try{
					val = eval(q);
				}catch(exp){}
			}
			if(val){
				$elm.val(val);
			}
		});
	}

	function parse(rst){

		if(hc.auth.isResultRestrict(rst)){
			return hc.auth.handleLogin(reload);
		}
		clearRows();


		if(typeof options.afterSearch == 'function'){
			options.afterSearch.apply(self, [lastQueryURL,lastQueryData,rst]);
		}
		
		updateToolbar();


		var newRst = rst;
		if(typeof options.onParse == 'function'){
			newRst = options.onParse.apply(self, [lastQueryURL,lastQueryData,rst]);
			
		}

		if(newRst) 
			rst = newRst;
		
		if(rst.data){

			if(typeof rst.paging != 'undefined'){
				offset = parseInt(rst.paging.offset);
				limit = parseInt(rst.paging.limit);
				total = parseInt(rst.paging.total);
				
				updatePaging();
			}

			handleAfterReload(rst);

			if($empty.length)
				$empty.remove();
			
			var counter = 0;
			for(var i = 0 ; i < rst.data.length; i ++){
				var rrObj = createElement( rst.data[i]);
				if(rrObj.getChecked()){
					counter ++;
				}
			}

			// if empty row is available
			if(rst.data.length < 1 && $empty.length){
				$elm.find(options.containerSelector).append($empty);
			}

			if( counter >= limit){
				_selected_all_current = true;
				updateSelectAllButton();
			}

		}else if(rst.error && rst.error.message){
			_error(rst.error.message);
		}

	}

	function clearRows(){
		$elm.find('.rr').remove();

		_selected_all_current = false;
		updateSelectAllButton();
		_rrIds.length = 0;
		_rr_data_map = {};
		_rr_elm_map = {};
	}

	var $lockedUI = null;
	var lastQueryURL = null;
	var lastQueryData = null;

	var loader = null;
	function reload(){

		if(loader) {
			loader.abort();
			loader = null;
		}

		var acceptingParams = options.acceptingParams;
		var queryParams = options.queryParams;
		var _queries = {};
		var _urlParams = {};

		if(!queryParams) queryParams = acceptingParams;

		
		for(var i = 0 ; i < acceptingParams.length; i ++)
			if(typeof queries[ acceptingParams[i] ] != 'undefined')
				_urlParams[ acceptingParams[i] ] = queries[ acceptingParams[i] ];

		for(var i = 0 ; i < queryParams.length; i ++)
			if(typeof queries[ queryParams[i] ] != 'undefined')
				_queries[ queryParams[i] ] = queries[ queryParams[i] ];
			
		if(!_queries.offset || _queries.offset < 0)
			_queries.offset = 0;
		_queries.offset = parseInt(_queries.offset);
		if(!_queries.limit || _queries.limit < 1)
			_queries.limit = 25;
		_queries.limit = parseInt(_queries.limit);
		
		var $lockedUI = $elm.find('.btn:not(.disabled), .pagination li:not(.disabled) a, .pager li:not(.disabled) a');
		$lockedUI.addClass('disabled');

		lastQueryURL = options.searchURL;
		lastQueryData = _urlParams;

		var cfg = typeof options.searchOption == 'function' ? options.searchOption.apply(self, [_queries]) : {url: options.searchURL, data: _queries, dataType:'json'};
		cfg.error = function(){
			$elm.addClass('rl-loading');
			$elm.removeClass('rl-error');
			loader = null;

			$lockedUI.removeClass('disabled');
			if(typeof options.afterSearch == 'function'){
				options.afterSearch.apply(self, [lastQueryURL,lastQueryData,null]);
			}

			if($empty.length)
				$elm.find(options.containerSelector).append($empty);

		};
		cfg.success = function(rst){
			$elm.removeClass('rl-error');
			$elm.removeClass('rl-loading');
			loader = null;
			$lockedUI.removeClass('disabled');
			parse(rst);
		};

		if(typeof options.beforeSearch == 'function'){
			options.beforeSearch.apply(self, [lastQueryURL,lastQueryData, cfg]);
		}
		$elm.removeClass('rl-error');
		$elm.addClass('rl-loading');
		loader = $.ajax(cfg);
	}
	
	function send(){
		if(bridge){
			if(bridge.send()){
				window.close();
			}
		}
	}

	var initialType = null;

	if(options.selectorSend!=null){
		$(options.selectorSend).on('click', function(){
			send();
		});
	}
	if($elm.attr('rl-select-button') != '' && typeof $elm.attr('rl-select-button') != 'undefined'){
		$(  $elm.attr('rl-select-button')).on('click', function(){
			send();
		});
	}
	
	$('body').on('click','.btn-rr-select,[rr-select]', function(evt){
		evt.preventDefault();
		send();
	});
	$elm.on('click','.btn-reload,[rl-reload]:not(.disabled)',function(){
		reload();
	}).on('click',_selector_select_all_button,function(){
		if( !_selected_all_current ){
			selectCurrent();
		}else{
			deselectCurrent();
		}
	}).on('click','.btn-rr-select:not(.disabled),[rl-send]:not(.disabled)',function(){
		send();
	}).on('click','.btn-selected-action a:not(.disabled)', function(){
		var data = $(this).data();
		var action = data.action;

		doBatch(action, data);
	}).on('change','.searchbar .search-order input',function(){

		queries.direction = $(this).val();
		
		if(typeof options.queryUpdate == 'function') options.queryUpdate.apply(self, [queries]);
		else reload();
	}).on('submit','.searchbar', function(evt){
		evt.preventDefault();
		
		var keywordStr = $elm.find('.searchbar input[name=q]',this).val();
		if(keywordStr && keywordStr.length>0){
			queries.q = keywordStr;
		}else{
			queries.q = null;
		}
		queries.offset=0;
		
		if(typeof options.queryUpdate == 'function') options.queryUpdate.apply(self, [queries]);
		else reload();
	}).on('click','.pager .previous:not(.disabled) a, .pagination .previous:not(.disabled) a',function(evt){
			evt.preventDefault();
		offset -= limit;
		if(offset < 0 )
			offset = 0;
		queries.offset = offset;

		if(typeof options.queryUpdate == 'function') options.queryUpdate.apply(self, [queries]);
		else reload();
	}).on('click','.pager .next:not(.disabled) a, .pagination .next:not(.disabled) a',function(evt){
		evt.preventDefault();
		if(offset + limit < total){
			offset += limit;
			queries.offset = offset;

			if(typeof options.queryUpdate == 'function') options.queryUpdate.apply(self, [queries]);
			else reload();
		}
	}).on('click',options.containerSelector +' .rr .btn-select:not(.disabled)', function(evt){
		var $btn = $(this);
		var $rowElm = $btn.parents('.rr');
		
		var rrObject = $rowElm.data('record-row');
		var rrId = $rowElm.data('rr-id');
		
		rrObject.toggleChecked();
		
		updateToolbar();
			evt.preventDefault();
	}).on('click', options.containerSelector +' .rr *[rc-action]:not(.disabled)', function(evt){
		var $btn = $(this);
		var data = $btn.data();
		var action = $btn.attr('rc-action');
		var $rowElm = $btn.parents('.rr');
		
		var rrObject = $rowElm.data('record-row');
		var rrId = $rowElm.data('rr-id');
		
		if(doRowAction(action, this, rrId)){
			evt.preventDefault();
		}
	}).on('change', '[name=sort]', function(evt){
		evt.preventDefault();
		var $select = $(this);

		queries.sort = $select.val();
		if(typeof options.queryUpdate == 'function') options.queryUpdate.apply(self, [queries]);
		else reload();
	});
	
	$elm.on('click',options.containerSelector +' .rr .rc-hide', function(){
		var $btn = $(this);
		var $rowElm = $(this).parents('.rr');
		$rowElm.remove();
	});

	this.createElement = createElement;
	this.updatePaging = updatePaging;
	this.updateToolbar = updateToolbar;
	this.reload = reload;
	this.select = select;
	this.deselect = deselect;
	this.getRows = getRows;
	this.getRowByDataID = getRowByDataID;
	this.getRowElmByDataID = getRowElmByDataID;
	this.getRowElmByRRID = getRowElmByRRID;
	this.updateRowElmByDataID =  updateRowElmByDataID;

	this.selectCurrent = selectCurrent;
	this.deselectCurrent = deselectCurrent;
	this.getSelectedIds = getSelectedIds;
	this.getQuery = function(){
		return queries;
	}
	this.setQuery = function(newVal){
		queries = newVal;
	}
	this.getBridge = function(){
		return bridge;
	};
	this.handleArgument = function(method){
		if(arguments.length < 1) return self;
		var args = []; for(var i = 1; i < arguments.length; i ++) args.push(arguments[i]);
		if(method && typeof self[method] == 'function'){
			return self[method].apply(self,args);
		}
	};
	this.setTemplate = function(c){
		$template = $(c);
		return self;
	}
	this.setEmpty = function(c){
		$empty = $(c);
		return self;
	}
	this.addQueryField = function(name){
		if($.isArray(name)){
			$(name).each(function(idx, val){ self.addQueryField(val); });
			return self;
		}
		options.acceptingParams.push(name);
		options.queryParams.push(name);
		return self;
	};

	bridge = new SelectorBridge();
	_enable_multiple = bridge.multiSelect = options.selectorMultiple || options.selectorMultiple == 'yes';
	bridge.getIds = function(){
		return _rr_selected_ids;
	}
	bridge.onSelectTooMuch = function(){
		hc.ui.showMessage( hc.loc.getText('error_select_too_much') , 'error');
	}
	bridge.onUpdate = function(){
		$elms = $elm.find('.rr');
		$elms.each(function(idx,$elm){
			var checked = $.inArray($elm.recordRow('getData').id , bridge.ids) >= 0;
			$elm.recordRow('setChecked',checked);
		});
	}
	bridge.onReload = function(){
		bridge.ids = bridge.getIds();
	}

	if(options.selectorValue){
		var _sv = $.isArray(options.selectorValue) ? options.selectorValue : options.selectorValue.replace(/[\s\t\r\n]*/,'').split(',');
		$(_sv).each(function(idx, id){
			addSelectedDataId( id);
		});
	}

	if(options.extraParams){
		this.addQueryField(options.extraParams);
	}

	if(options.selectorCallback){
		bridge.connect(options.selectorCallback);
	}

	var reloadFirst = true;
	if(typeof options.onInit == 'function'){
		reloadFirst = options.onInit.apply(self,null);
	}

	updateAfterSelectedItemChange();
	if(reloadFirst)
		reload();

};


$.recordList = {
	defaults: {
		selectorCallback: null,
		selectorValue: null,
		selectorMultiple: null,
		selectorValue: null,
		selectorSend: null,

		dataRowId: 'id',
		dataRowTitle: 'title',

		containerSelector: '.table tbody',

		onInit: null,
		onRowCreate: null,
		onRowAction: null,
		onRowBeforeUpdate: null,
		onRowAfterUpdate: null,
		onBatch: null,

		remote:true,
		searchURL: '',
		searchOption: null, // callback function
		beforeSearch: null, // callback function
		afterSearch: null, // callback function

		queryUpdate:null, // callback function

		offset: 0,
		limit: 25,
		queries: null,

		acceptingParams: [
			'offset','limit','sort','direction','q','status','type','_modalIns','dialog','callback'
		],

		queryParams: [
			'offset','limit','sort','direction','q','status','type'
		]
	}
};
$.recordRow = {
	defaults: {
		beforeUpdate: null,
		afterUpdate:null,
		onCheckedStateChange:null
	}
};
$.fn.recordList = function(options){
	var args = arguments;
	var c = 'each';
	var dataKey = 'record-list';

	if(args.length > 0 && typeof args[0] != 'object' || args.length == 0){
		var $elm = $(this).eq(0);
		if($elm.length){
			var r = $elm.data(dataKey);
			if(r) {
				return r.handleArgument.apply(this, args);
			}
		}
	}
	return $(this)[c](function(){
		var elm = this;
		var $elm = $(elm);

		var list = $elm.data(dataKey);

		if(list){
			return list.handleArgument.apply(this, args);
		}
		list = new RecordList($elm, $.extend({}, $.recordList.defaults, options));

		$elm.data(dataKey, list);
	});
};

$.fn.recordRow = function(options){
	var args = arguments;
	var c = 'each';
	var dataKey = 'record-row';

	if(args.length > 0 && typeof args[0] != 'object' || args.length == 0){
		var $elm = $(this).eq(0);
		if($elm.length){
			var r = $elm.data(dataKey);
			if(r) {
				return r.handleArgument.apply(this, args);
			}
		}
	}
	return $(this)[c](function(){
		var elm = this;
		var $elm = $(elm);

		var r = $elm.data(dataKey);

		if(r){
			return r.handleArgument.apply(this, args);
		}
		r = new RecordRow($elm, $.extend({}, $.recordRow.defaults, options));

		$elm.data(dataKey, r);
	});
};

})(hc, jQuery);