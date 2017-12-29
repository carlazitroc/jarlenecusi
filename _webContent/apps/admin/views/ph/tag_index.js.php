;(function($){
$(function(){

var apiURLPrefix = '<?php echo site_url('s/'.$section.'/tag');?>';
var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);

var docTitle = document.title;
var $list = $('.record-list');
$list.recordList({
	selectorCallback: queries.callback,
	selectorMultiple: queries.multiple == 'yes' || queries.multiple == 'true',
	queries: queries,
	searchOption: function(queries){
		return {
			url:apiURLPrefix + '/search.json',
			data: queries,
			dataType:'json'
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
				list.reload();

			}});
		return true;
	}
});


History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
    var State = History.getState(); // Note: We are using History.getState() instead of event.state

	queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
	
	if(typeof queries.q !='undefined'){
		$list.find('.searchbar input[name=q]').val(queries.q );
	}
	if(typeof queries.type !='undefined' && queries.type !=''){
		if(!initialType) initialType = queries.type;
		$list.find('.searchbar .search-type li a.type-'+queries.type).click();
	}
	if(typeof queries.direction !='undefined'){
		$list.find('.searchbar .search-order input').prop('checked',false);
		$list.find('.searchbar .search-order label.active').removeClass('active');
		$list.find('.searchbar .search-order input[value='+queries.direction+']')
			.prop('checked',true)
			.parents('label').addClass('active');
	}
	$list.recordList('reload');
});


});
})(jQuery);
