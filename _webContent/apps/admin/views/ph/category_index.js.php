;(function($){
$(function(){

var apiURLPrefix = '<?php echo site_url('s/'.$section.'/category');?>';
var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
	if(!queries.path) queries.path = '/';

var $list = $('.record-list');
$list.recordList({
	queries: queries,
	selectorCallback: queries.callback,
	selectorMultiple: queries.multiple == 'yes' || queries.multiple == 'true',
	acceptingParams: [
		'offset','limit','direction','q','status','sort-field','sort-field-by','path','_modalIns','dialog','callback'
	],
	queryParams:[
		'offset','limit','direction','q','status','sort-field','sort-field-by','path'
	],
	searchOption: function(queries){
		return {
			url:apiURLPrefix + '/search.json',
			data: queries,
			dataType:'json'
		}
	},

	onParse: function (url,queries, rst){
		console.log(rst);
		if(rst.breadcrumb){
			updateBreadcrumb(rst.breadcrumb);
		}
	},
	queryUpdate: function(queries){
		History.pushState(null,null,apiURLPrefix + '?'+Fragment.buildQuery(queries));
	},
	afterSearch:function(url,queries){

		if(typeof queries.q !='undefined'){
			$list.find('.searchbar input[name=q]').val(queries.q );
		}
		if(typeof queries.direction !='undefined'){
			$list.find('.searchbar .search-order input').prop('checked',false);
			$list.find('.searchbar .search-order label.active').removeClass('active');
			$list.find('.searchbar .search-order input[value='+queries.direction+']')
				.prop('checked',true)
				.parents('label').addClass('active');
		}

	},
	onBatch: function(action, ids){
		var list = this;
		if( action == 'remove'){
			if( ids.length < 1 ) return false;

			hc.ui.createDialog({
				title: '<?php echo lang('remove');?>',
				message: '<?php echo lang('remove_message');?>',
				buttons: {
					'<?php echo lang('yes')?>': {
						'class':'btn-danger',
						'events':[{
							type:'click',
							callback: function onConfrim(evt){
								var dialog = this;
								dialog.lock();

								$.post(apiURLPrefix+'/remove.json',{
									ids:ids.join(',')
								}, function(rst){
									if(!rst.error){
										dialog.hide();
										list.deselect(ids).reload();
									}else{
										dialog.unlock();
										alert(rst.error.message);
									}
								},'json');
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
		}else{
			if( ids.length < 1 ) return false;
			
			$.post(apiURLPrefix+'/batch/'+action+'.json',{
				ids:ids.join(',')
			}, function(rst){
				if(!rst.error){
					list.reload();
				}else{
					alert(rst.error.message);
				}
			},'json');	
		}
		return true;
	},
	onRowAction: function(action, elm){
		var list = this;
		// popage('open',{url:elm.href+'?dialog=yes',useScrollbar:true,boundToWindow:true});
		if( action == 'modal')
			hc.ui.openModal(elm.href,{onHide: function(){
				$list.recordList('reload');

			}});
		return true;
	}
});

	
function queryToHref(q){
	var str = apiURLPrefix;
	var newQ = $.extend({}, queries);
	for(var key in q){
		newQ[key] = q[key]
	}
	return str+'?'+Fragment.buildQuery(newQ);
}
window.queryToHref = queryToHref;
function updateBreadcrumb(breadcrumb){
	console.log(breadcrumb);
	var $breadcrumb = $('.breadcrumb');
	$breadcrumb.find('li:not(:first-child)').remove();
	for(var i =0 ; i < breadcrumb.length; i++){
		var item = breadcrumb[i];
		
		var $li = $('<li><a class="listing-query" /></li>');
		$li.find('a').attr('href',apiURLPrefix+'?path='+escape(item.path)).text(item.title)
		$breadcrumb.append($li);
	}
}

$('body').on('click','.listing-query',function(evt){
	evt.preventDefault();
	
	var $elm = $(this);
	var href = $elm.prop('href');
	var frag = new Fragment(href);
	
	for(var key in frag.queries){
		queries[key ] = frag.queries[key];
	}
	var url = apiURLPrefix;
	var q = Fragment.buildQuery(queries);
	if(q != '') url+='?'+q;
	History.pushState(null,null, url);
	//$('.record-list').recordList('setQuery',queries).recordList('reload');
});


History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
    var State = History.getState(); // Note: We are using History.getState() instead of event.state

	queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
	if(typeof queries.direction !='undefined'){
		$('.record-list .searchbar .search-order input').prop('checked',false);
		$('.record-list .searchbar .search-order label.active').removeClass('active');
		$('.record-list .searchbar .search-order input[value='+queries.direction+']')
			.prop('checked',true)
			.parents('label').addClass('active');
	}

	// must pass it if no path in the parameter list.
	if(!queries.path) queries.path = '/';

	$('.record-list').data('record-list').setQuery(queries);	
	$('.record-list').recordList('reload');
});


});
})(jQuery);
