<?php 

$this -> asset -> css(base_url('assets/css/ifuploader.css'));
$this -> asset -> css(base_url('assets/libs/jasny/css/jasny-bootstrap.min.css'));
$this -> asset -> js_import(base_url("assets/js/ifuploader.js"));
$this -> asset -> js_import(base_url("assets/js/jquery.filedrop.js"));
$this -> asset -> js_import(base_url("assets/libs/jasny/js/jasny-bootstrap.min.js"));

?>
;(function($){
$(function(){

var docTitle = document.title;
var apiURLPrefix = '<?php echo site_url('file');?>';
var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
var initialType  = '';

var $list = $('.record-list');
$list.recordList({
	selectorCallback: queries.callback,
	selectorMultiple: queries.multiple == 'yes' || queries.multiple == 'true',
	queries: queries,
	containerSelector: '.cell-list.uploaded-list',
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

		var $searchType = $list.find('.searchbar .search-type');
		$searchType.find('.active').removeClass('active');

		var $selectedType = null;
		if(queries.type && queries.type !=''){
			$selectedType = $searchType.find('li a.type-'+queries.type);
		}

		if($selectedType && $selectedType.length>0){
			$selectedType.parents('li').addClass('active');
		}else{
			$selectedType = $searchType.find('li a.type-all');
		}
		if($selectedType && $selectedType.length>0){
			$searchType.find('> .btn > .text').text($selectedType.text());
		}

	},
	searchOption: function(queries){


		return {
			url:apiURLPrefix + '/search.json',
			data: queries,
			dataType:'json'
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
		if( action == 'modal'){
			hc.ui.openModal(elm.href,{onHide: function(){
				list.reload();
			}});
		}
		return true;
	}
});


History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
    var State = History.getState(); // Note: We are using History.getState() instead of event.state

	queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
	
	$list.recordList('setQuery', queries);
	$list.recordList('reload');
});

$list.on('click','.search-type li a',function(evt){
	evt.preventDefault();
	queries.type = $(this).data('value');
	queries.offset = 0;

	var  str = ''+docTitle;
	if(queries.q && queries.q.length>0){
		str += ', Search Keyword "'+queries.q+'"';
	}

	History.pushState(null,str,apiURLPrefix + '?'+Fragment.buildQuery(queries));
});

function handleUploadedResult(rst){

	if(hc.auth.isResultRestrict(rst)){
		return hc.auth.handleLogin(reload);
	}
	
	$('input[type=file]',this).prop('disabled',false).val('');
	var msg = [];
	if(rst.id){
		$.getJSON(config.site_url+'file/'+rst.id+'.json',function(row){
			if(row.id){
				var api = $('.record-list').data('record-list');
				api.createElement(row);
			}
		});
	}
	if(rst.error){
		if(rst.error.message){
			hc.ui.showMessage( 'Error: '+rst.error.message, 'error');
		}
	}
}

$('.upload form.ifrm').ifrm({
	onSubmit:function(){
		if($('input[type=file]',this).val() == ''){
			return false;
		}
	    $(this).addClass('is-processing');
		//alert('Upload File Now: '+$('form.ifrm input[type=file]').val());
		return true;
	},
	onSend: function(){
		$('input[type=file]',this).prop('disabled',true);
	},
	onResponse:function(rst){
	    $(this).removeClass('is-processing');
		return handleUploadedResult(rst);
	},onTimeout:function(){
	    $(this).removeClass('is-processing');
		$('input[type=file]',this).prop('disabled',false).val('');
	}
});

$('.filedrop').each(function(){
	var $self = $(this);
	$self.filedrop({
		maxfiles: 20 ,
		//fallback_id: 'btn-file-upload',   // an identifier of a standard file input element, becomes the target of "click" events on the dropzone
	    url: config.site_url+'file/upload',              // upload handler, handles each file separately, can also be a function taking the file and returning a url
	    paramname: 'new_file',          // POST parameter name used on serverside to reference file, can also be a function taking the filename and returning the paramname
	    withCredentials: true ,         // make a cross-origin request with cookies
	    dragOver: function() {
	        // user dragging files over #dropzone
	        $self.addClass('has-drag');
	    },
	    dragLeave: function() {
	        // user dragging files out of #dropzone
	        $self.removeClass('has-drag');
	    },
	    drop: function() {
	        // user drops file
	        $self.removeClass('has-drag');
	    },
	    uploadStarted: function(i, file, len){
	        // a file began uploading
	        // i = index => 0, 1, 2, 3, 4 etc
	        // file is the actual file of the index
	        // len = total files user dropped
	        $self.addClass('is-processing');
	    },
	    uploadFinished: function(i, file, rst, time) {
	        // response is the data you got back from server in JSON format.

	        //$self.removeClass('is-processing');
			return handleUploadedResult(rst);
	    },
	    afterAll: function() {
	        // runs after all files have been uploaded or otherwise dealt with
	        $self.removeClass('is-processing');
	    }
	});
});

$('.upload form.ifrm input[type=file]').change(function(){
	if(this.value != ''){
		$(this).parents('form.ifrm').submit();
	}
});

});
})(jQuery);
