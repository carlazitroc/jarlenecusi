<?php

if(isset($_REQUEST['debug']) && defined('PROJECT_DEBUG_KEY') && $_REQUEST['debug'] == PROJECT_DEBUG_KEY){
	$this->asset->compress_js = false;
}
?>;(function($){
$(function(){

var endpointUrl = '<?php echo $endpoint_url_prefix;?>';
var queries = null;

var docTitle = document.title;
var $list = $('.record-list');

var columns = <?php echo json_encode($columns); ?>;
var columns_info = <?php echo json_encode($columns_info); ?>;

var $table = $list.find('.table');
var total_record = 0;

var selectedIds = [];

var listing_column_actions = <?php echo json_encode($listing_column_actions);?>;
var listing_row_actions = <?php echo json_encode($listing_row_actions);?>;


if($list.find('.listing-filter').length)
	hc.ui.initUIElement( $list.find('.listing-filter') );

$list.find('[hc-table-actions]').on('click','a', function(evt){
	evt.preventDefault();
	var data = $(this).data();
	var text = $(this).text();

	if(data.action != ''){
		hc.ui.createDialog({
			title: '<?php echo lang('confirm');?>',
			message: 'Confirm to perform action &quot;'+text+'&quot; for the following records?<br />IDs: '+selectedIds.join(', '),
			buttons: {
				'<?php echo lang('yes')?>': {
					'class':'btn-danger',
					'events':[{
						type:'click',
						callback: function onConfrim(evt){
							var dialog = this;
							dialog.lock();

							hc.ui.showLoaderAnimation();

							$.post(endpointUrl+'/batch/'+data.action+'.json',{
								ids: selectedIds.join(',')
							}, function(rst){
								hc.ui.hideLoaderAnimation();
								if(!rst.error){
									selectedIds = [];

									$table.bootgrid('deselect');
									noty({'text':'<?php echo lang('completed')?>','modal':true,'type':'success',force:true, killer: true,layout:'center'})
									dialog.hide();
									// reload list
									$table.bootgrid('reload');
								}else{
									dialog.unlock();
									noty({'text':rst.error.message,'modal':true,'type':'error',force:true, killer: true,layout:'center'})
								}
								updateSelectorUI()
							},'json').error(function(){
									dialog.unlock();
								hc.ui.hideLoaderAnimation();
								noty({'text':'<?php echo lang('error_connection')?>','modal':true,'type':'error',force:true, killer: true,layout:'center'})

							});	
						}
					}]
				},
				'<?php echo lang('no')?>': {
					'class':'btn-default',
					'events':[{
						type: 'click',
						callback: function onCancel(evt){
							this.hide();
						}
					}]
				}
			}
		});

	}
});
if(typeof window.History != 'undefined'){
	var currentState = History.getState(); // Note: We are using History.getState() instead of event.state
	var currentUrl = currentState.url;

	History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        var state = History.getState(); // Note: We are using History.getState() instead of event.state

        if(state.url != currentUrl){
        	currentState = state;
        	currentUrl = currentState.url;

        	queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);

        	$table.bootgrid('reload');
        }

    });
}




var exportSupportedFormats = {'xlsx':'Excel 2007', 'xls':'Excel 97-2003'};


var bootgrid_fired = false;
$('body').on('click','a.btn-export', function(evt){
	evt.preventDefault();

	hc.ui.createDialog({
		title: '<h4><?php echo lang('export');?></h4>',
		message: function(){
			var html = '';
			var limit = 2000;

			html+='<div class="panel panel-default"><ul class="list-group">';
			for (var i = 0; i<= total_record; i+= limit){
				html+='<li class="list-group-item">'
				html+='<div class="row">';
				html+= '<div class="pull-right col-sm-6 text-right"><i class="fa fa-download"></i> ';
				html+='<div class="btn-group">';
				for(var formatKey in exportSupportedFormats){
					var _query = $.extend({}, queries, {sort:'<?php echo !empty($this->sorting_fields[0]) ? $this->sorting_fields[0] : ''?>', direction:'asc', format:formatKey, offset:i, limit: limit});
					if(_query.page) delete _query.page;
					var queryStr = Fragment.buildQuery(_query);
					html+='<a target="_blank" href="'+endpointUrl+'/export?'+queryStr+'" class="btn btn-link btn-sm dropdown-toggle" type="button">'+exportSupportedFormats[formatKey]+'</a>';
				}
				
				html+='</div>';
				html+='</div>';

				html+='<div class="col-sm-6">'
				html+='<p><b class="list-group-item-heading">'+(i+1)+' - '+Math.min(i+limit, total_record)+'</b></p>';
				html+='</div>';
				html+='</div>';
				html+='</li>';
			}
			html+='</ul></div>';

			return html;
		},
		buttons: {
			'<?php echo lang('button_close')?>': {
				'class':'btn-default btn-success',
				'events':[{
					type: 'click',
					callback: function onCancel(evt){
						this.hide();
					}
				}]
			}
		}
	});

});
$table.bootgrid({
	ajax: true,
	url: '<?php echo $endpoint_url_prefix?>/search.json',
	selection: true,
	multiSelect: true,
	rowSelect: true,
	rowCount:[50,100,250,500,-1],
	keepSelection: true,
	css:{
		'dropDownMenuItems':'dropdown-menu',
		'header':'bootgrid-header',
		'footer':'bootgrid-footer'
	},
	responseHandler: function(res){

		queries.page = res.paging.page;
		queries.limit = res.paging.limit;
		total_record = res.paging.total;
		if(!queries.q) delete queries.q;


		if(!bootgrid_fired){
			setTimeout(function(){
				console.log('list for bootgrid')
				if(!bootgrid_fired)
					$table.trigger('loaded.rs.jquery.bootgrid');
			},500);
		}

		return {
			current: res.paging.page,
			total: res.paging.total,
			rows: res.data,
			rowCount: res.paging.limit
		};
	},
	requestHandler: function(req){
		//debugger;
		//console.log('requestHandler',req,queries);
		if(!queries || typeof queries !='object') {
			queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
		}else{
			if(typeof req.current != 'undefined') queries.page = req.current;
			if(typeof req.rowCount != 'undefined') queries.limit = req.rowCount;
			if(typeof req.searchPhrase != 'undefined') queries.q = req.searchPhrase;
			if(typeof req.sort != 'undefined') {
				delete queries.sorts;
				for(var key in req.sort)
					queries['sorts['+key+']'] = req.sort[key];
			}
		}

		if(queries.callback) delete queries.callback;

		return queries;
	},
	formatters: {
		data: function(column, row){

			var $root = $('<div class="cell-content"></div>');
			if(typeof row[ column.id ] !='undefined'){

				var output = row[ column.id ]; //default output;
				var val = output;


				if(typeof columns_info[ column.id] != 'undefined'){
					var column_info = columns_info[column.id];
					if(column_info.control == 'bool'){
						if( row[ column.id ] == '0') output = '<?php echo lang('No')?>';
						if( row[ column.id ] == '1') output = '<?php echo lang('Yes')?>';
					}
					if(column_info.control == 'select' && column_info.control_type == 'file'){
							output = '';
						if(row[column.id] && row[column.id].length){
							if(column_info.is_image){
								var file_path = typeof column_info.file_path != 'undefined' ? column_info.file_path : 'file';
								output = $('<a href="'+hc.net.site_url(file_path+'/'+row[ column.id ]+'/picture?size=source')+'" target="_blank"><img src="'+hc.net.site_url(file_path+'/'+row[ column.id ]+'/picture')+'" width="50" alt=""/></a>');
							}else{
								var file_path = typeof column_info.file_path != 'undefined' ? column_info.file_path : 'file';
								output = $('<a href="'+hc.net.site_url(file_path+'/'+row[ column.id ]+'/download')+'" target="_blank"><?php echo lang('button_view')?></a>');
							}
						}
					}
					if(column_info.listing_type == 'link'){

						if(typeof row[ column.id ] != 'undefined'){
							output = $('<a href="'+row[ column.id ]+'" target="_blank"><?php echo lang('button_view')?></a>');
						}
					}
					if(column_info.listing_type == 'modal'){

						if(typeof row[ column.id ] != 'undefined'){
							output = $('<a href="'+row[ column.id ]+'" onclick="hc.ui.openModal(\'<?php echo site_url('tools/QRCode/')?>?t=\'+escape(this.href),{size:\'medium\'});return false;" target="_blank"><?php echo lang('button_view')?></a>');
						}
					}
					if(column_info.options){

						if(typeof column_info.options[ row[ column.id ] ] != 'undefined')
							output = column_info.options[ row[ column.id ] ]
					}
				}

				$root.append(output);

				var $subcontent = $('<div />');
				$subcontent.appendTo($root);

				if(listing_column_actions && typeof listing_column_actions [ column.id ] != 'undefined'){
					$.each(listing_column_actions[ column.id ], function(i, action_info){
						if(action_info.action == 'popup'){
							var label = action_info.label;
							var href = action_info.href;
							var parameters = {
								site_url: hc.net.site_url(),
								asset_url: hc.net.asset_url(), 
								web_url: hc.net.url('',hc.config.get('web_url')), 
								base_url: hc.net.url('',hc.config.get('base_url')), 
								val: val, 
								value:val,
								locale_code: hc.config.get('locale_code'),
								lang_code: hc.config.get('lang_code'),
								country_code: hc.config.get('country_code')
							};
							label = pstr(label, parameters);
							label = pstr(label, row);
							href = pstr (href, parameters);
							href = pstr(href, row);


							var $elm = $('<a target="_blank"></a>');
							$elm.text(label);
							$elm.prop('href', href);
							if(action_info.class)
								$elm.attr('class', action_info.class);

							$subcontent.append($elm);
						}
					});
				}
			}
			return $root.html();
		},
		commands: function(column, row){
			var $root = $('<div />');
			var $group = $('<div class="btn-group" />');
			$group.appendTo($root);

			if( typeof listing_row_actions != 'undefined'){
				if(listing_row_actions.length > 1){

					//$('<button class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">Actions <span class="caret"></span></button>').appendTo($group);

					var $menu = $('<div />').appendTo($group);
					$.each(listing_row_actions, function(i, action_info){
						if(action_info.action == 'link' || action_info.action == 'popup'){
							var label = action_info.label;
							var href = action_info.href;
							var parameters = {
								site_url: hc.net.url('',hc.config.get('site_url')), 
								web_url: hc.net.url('',hc.config.get('web_url')), 
								base_url: hc.net.url('',hc.config.get('base_url')), 
								locale_code: hc.config.get('locale_code'),
								lang_code: hc.config.get('lang_code'),
								country_code: hc.config.get('country_code')
							};
							label = pstr(label, parameters);
							label = pstr(label, row);

							href = pstr (href, parameters);
							href = pstr(href, row);


							var $elm = $('<a></a>');
							$elm.text(label);
							$elm.prop('href', href);
							if(action_info.class)
								$elm.attr('class', action_info.class);
							else 
								$elm.attr('class', 'btn btn-default btn-xs');

							if(action_info.action == 'popup')
								$elm.attr('target', '_blank');
							if(action_info.target)
								$elm.attr('target', action_info.target);
							$elm.appendTo($menu);
							//$('<li/>').append($elm).appendTo($menu);
						}
					});
				}else{
					$.each(listing_row_actions, function(i, action_info){
						if(action_info.action == 'link' || action_info.action == 'popup'){
							var label = action_info.label;
							var href = action_info.href;
							var parameters = {
								site_url: hc.net.url('',hc.config.get('site_url')), 
								web_url: hc.net.url('',hc.config.get('web_url')), 
								base_url: hc.net.url('',hc.config.get('base_url')), 
								locale_code: hc.config.get('locale_code'),
								lang_code: hc.config.get('lang_code'),
								country_code: hc.config.get('country_code')
							};
							label = pstr(label, parameters);
							label = pstr(label, row);

							href = pstr (href, parameters);
							href = pstr(href, row);


							var $elm = $('<a></a>');
							$elm.text(label);
							$elm.prop('href', href);
							if(action_info.class)
								$elm.attr('class', action_info.class);
							else 
								$elm.attr('class','btn btn-default btn-xs');
							if(action_info.action == 'popup')
								$elm.attr('target', '_blank');
							if(action_info.target)
								$elm.attr('target', action_info.target);

							$group.append($elm);
						}
					});
				}
			}
			return $root.html();
		}
	}
}).on('loaded.rs.jquery.bootgrid',function(evt){


	var newUrl = location.protocol + '//'+ location.host + location.pathname + '?'+Fragment.buildQuery(queries);

    if(newUrl != currentUrl){

    	currentUrl = newUrl;
		if(typeof window.History != 'undefined'){
			History.pushState(docTitle, null, newUrl);
			return;
		}
	}

	$(this).find('.dropdown-toggle').dropdown();
	$(this).find('.dropdown-toggle').parents('td').css('overflow','visible');

	$(this).find('[hc-action]').on('click', function(evt){
		evt.preventDefault();

		var data = $(this).data();
		var action = $(this).attr('hc-action');

		if(action == 'modal'){
			hc.ui.openModal(this.href);
		}
	});

	bootgrid_fired = true;

}).on('selected.rs.jquery.bootgrid', function(evt, rows){
	for(var i = 0; i < rows.length; i ++){
		if($.inArray( rows[i].id, selectedIds ) < 0)
			selectedIds.push( rows[i].id )
	}
	updateSelectorUI()

}).on('deselected.rs.jquery.bootgrid', function(evt, rows){
	var deleteIds = [];
	for(var i = 0; i < rows.length; i ++){
		if($.inArray( rows[i].id, deleteIds ) < 0)
			deleteIds.push( rows[i].id )
	}
	var newIds = [];

	for(var i = 0; i < selectedIds.length; i ++){
		if($.inArray( selectedIds[i], deleteIds ) < 0)
			newIds.push( selectedIds[i] )
	}

	selectedIds = newIds;
	updateSelectorUI()
});



// when the page in dialog mode, we should add support for sending selected values to parent.
var $selectBtn = $('.btn-rr-select');
if($selectBtn.length){

	var selectorBridge = new SelectorBridge();
	selectorBridge.onSelectTooMuch = function(){
		alert('Select too much.');
	}
	selectorBridge.onUpdate = function(){
		console.log(this,arguments);
	}
	selectorBridge.onReload = function(){
		console.log(this,arguments);
	}
	
	if(selectorBridge.connect('<?php echo $this->input->get('callback')?>')){

		$selectBtn.on('click', function(evt){
			evt.preventDefault();


			selectorBridge.ids = selectedIds;
			if(selectorBridge.send()){
				// hide the dialog
				window.close();
			}
		});
	}else{
		$.error('SelectorBridge connect failure.');
	}
}
var pstr = function(str, parameters){
	for(var key in parameters){
		str = str.replace('{'+key+'}', parameters[key]);
	}
	return str;
}

function updateSelectorUI(){

	$('[hc-table-actions] .btn').prop('disabled', selectedIds.length < 1);
	$('[hc-table-actions] [data-num]').text( ''+ selectedIds.length);

	$selectBtn.prop('disabled', selectedIds.length <= 0 )
}

updateSelectorUI();


});
})(jQuery);