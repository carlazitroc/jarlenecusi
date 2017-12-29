;(function($){
$(function(){
	var apiPathPrefix = '<?php echo site_url('album')?>';
	var record_id = '<?php echo data('id',$data)?>';

	var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
	var bridge = null;
	if(queries && queries.callback){
		bridge = new SelectorBridge();
		bridge.multiSelect = queries.multiple == 'yes';
		bridge.getIds = function(){
			if(record_id)
				return [record_id];
			return [];
		}
		bridge.onSelectTooMuch = function(){
			alert('You can select single item only');
		}
		bridge.onUpdate = function(){
		}
		bridge.onReload = function(){
			bridge.ids = bridge.getIds();
		}
		try{
			bridge.connect(queries.callback);
		}catch(exp){
			$('[re-action=done]').addClass('hidden');
		}
	}else{
		$('[re-action=cancel]').addClass('hidden');
	}

	$('#file-list li.cell').remove();
	
	var _cell_ids = [];
	var _cell_new_ids = {};
	var _cell_data = {};
	var _cell_parameters = {};
	
	var _object_counter = 0;
	var createElement = function(row){
		$('#file-list').sortable('disable');
		
		row._object_id = '_'+(new Date()).getTime()+'_'+(_object_counter++);
		if(!row.id){
			_cell_new_ids[ row._object_id ] = true;
		}
		
		_cell_data [ row._object_id ] = row;
		_cell_parameters [ row._object_id ] = typeof row.parameters != 'undefined' ? row.parameters : {};
		_cell_ids.push( row._object_id );
		
		var $clone = $('li.template').clone();
		$clone.removeClass('template').addClass('cell');
		$clone.insertBefore('#file-list .last');
		$clone.data('object_id', row._object_id);
		$clone.data('data_row', row);
		$clone.find('.thumbnail').css('background-image','url('+ row.image.thumbnail +')');
		
		$('#file-list').sortable('enable');
		
		fileListUpdateSequence();
	}
	
	var doSave = function(){
		
		var data = [];
		
		if(record_id){
			data.push('id='+escape(record_id));
		}
		var total = 0;
		$('#file-list .cell').each(function(idx){
			var $cell = $(this);
			cell_row = $cell.data('data_row');
			var relationship_id = cell_row.id;
			var object_id = cell_row._object_id;

			var param = typeof cell_row.parameters != 'undefined' ? cell_row.parameters : null;
			data.push('cell_ids['+idx+']='+ escape(relationship_id));
			data.push('cell_object_ids['+idx+']='+escape(object_id));
			data.push('cell_files['+idx+']='+ escape(cell_row.main_file_id));
			if(param){
				data.push('cell_params['+idx+']='+escape(JSON.stringify(param)));
			}
			total ++;
		});
		
		if(total < 1){
			noty({
			    text: 'Please select or upload any photo before save an album.', layout:'topCenter','type':'error'
			});
			return false;
		}
		
		$('#file-list').sortable('disable');
		$('.btn').attr('disabled',true);
		
		$.ajax({
			url: apiPathPrefix+'/save.json',
			data: data.join('&'),
			dataType:'jsonp',
			type:'post',
			success: function(rst){
				if(hc.auth.isResultRestrict(rst)){
					return hc.auth.handleLogin(function(){
						$(frm).submit();
					});
				}
				$('#file-list').sortable('enable');
				$('.btn').attr('disabled',false);
				if(rst.id){
					hc.ui.showMessage(hc.loc.getText('record_saved'),'success',5000);
					record_id = rst.id;
					
					if(rst.cell_ids){
						$('.cell').each(function(idx){
							var $cell = $(this);
							var object_id = $cell.data('object_id');
							var _cell_row = _cell_data[ object_id ];
							if(_cell_row.id != rst.cell_ids[ idx] ){
								// remark the new photo is already saved in album
								_cell_new_ids[ object_id ] = false;
								
								// assign new photo relationship id
								_cell_row.id = rst.cell_ids[ idx];
							}
						});
					}
					
					var url = '<?php echo site_url('album')?>/'+record_id+'/edit';
					if(location.search.length>0){
						url+=location.search;
					}
					window.history.pushState(null,document.title, url);
				}
				if(rst.error){
					hc.ui.showMessage(rst.error.message,'error',5000);
				}
			},error: function(){
				$('#file-list').sortable('enable');
				$('.btn').attr('disabled',false);
				hc.ui.showMessage('Cannot submit to server. Please try again later.','error',5000);
			}
		})
	};
	
	if(record_id){
		$.getJSON('<?php echo site_url('album')?>/'+record_id+'/get.json', function(rst){
			if(rst.id){
				$(rst.photos).each(function(idx, row){
					createElement(row);
				});
			}
		});
	}
	var $remove_target = null;
    
    var fileListUpdateSequence = function(){
    	$('#file-list .cell').each(function(idx){ 
    		$(this).find('.sequence > span').text(idx+1);
    	});
    };
    
    var loadFile = function(file_id){
	
		$.getJSON('<?php echo site_url('file')?>/'+file_id+'.json',function(row){
			if(row.id){
				row.main_file_id = file_id;
				row.id = null;
				createElement(row);
			}
		});
    }
    
	$('#file-list').sortable({
		placeholder:"placeholder" , 
		items:'> li.cell',
		handle: ".handle",
		update: function( event, ui ) {
			fileListUpdateSequence();
		}
	});

	$('[re-action=save]').on('click', function(){
		doSave();
	});
	
	$('[re-action=done]').on('click', function(){
		if(!record_id){
			hc.ui.showMessage('Please save the record first.', 'error');
			return false;
		}
		if(bridge){
			if(bridge.send())
				window.close();
		}else{
			window.close();
		}
	})
	
	$('[re-action=cancel]').on('click', function(){
		window.close();
	});
	
	var fileSelector = new Selector();
	fileSelector.caller = 'fileSelector_<?php echo time()?>';
	fileSelector.onUpdate = function(){
		$(this.ids).each(function(idx, file_id){
			loadFile(file_id);
		});
		this.ids = [];
		this.reload();
	}
	top.window[fileSelector.caller] = fileSelector;
	$('.btn-photo-select').on('click', function(){
		hc.ui.openModal('<?php echo site_url('file/selector');?>?dialog=yes&multiple=yes&type=image&callback='+fileSelector.caller+'.didResponse');
	});
    
	$('#photo-detail').on('click','.btn-save',function(){
		
		var object_id = $('#photo-detail').data('id');
		_cell_parameters[ object_id ].name = $('#photo-detail input[data-field=name]').val();
		_cell_parameters[ object_id ].link = $('#photo-detail input[data-field=link]').val();
		_cell_parameters[ object_id ].content = $('#photo-detail textarea[data-field=content]').val();
		
		$('#photo-detail').modal('hide');
	}).on('show.bs.modal',function(){
		
		$('#photo-detail input[data-field=name]').val('');
		$('#photo-detail input[data-field=link]').val('');
		$('#photo-detail textarea[data-field=content]').val('');
		
		var object_id = $('#photo-detail').data('id');
		
		var params = _cell_parameters[ object_id ];
		if(params && typeof params.name != 'undefined')
			$('#photo-detail input[data-field=name]').val(params.name);
		if(params && typeof params.link != 'undefined')
			$('#photo-detail input[data-field=link]').val(params.link);
		if(params && typeof params.content != 'undefined')
			$('#photo-detail textarea[data-field=content]').val(params.content);
	})
	
	$('#photo-remove').on('click','.btn-photo-remove',function(){
		$remove_target.remove();
		$('#photo-remove').modal('hide');
	})

	$('#file-list').on('click','.cell .btn-delete', function(){
		var $elm = $(this);
		var $ctr = $elm.parents('.cell');
		$remove_target = $ctr;
		$('#photo-remove').modal('show');
	}).on('click','.cell .btn-detail', function(){
		
		var $cell = $(this).parents('.cell');
		$('#photo-detail').data('id',$cell.data('id') ).modal('show');
	});
	
	// select file button
	$('form.ifrm input[type=file]').change(function(){
		if(this.value != ''){
			$(this).parents('form.ifrm').submit();
		}
	});
	
	// handle file uploaded by button
	$('form.ifrm').ifrm({
		onSubmit:function(){
			if($('input[type=file]',this).val() == ''){
				return false;
			}
			//alert('Upload File Now: '+$('form.ifrm input[type=file]').val());
			return true;
		},
		onSend: function(){
			$('input[type=file]',this).prop('disabled',true);
		},
		onResponse:function(rst){
			$('input[type=file]',this).prop('disabled',false).val('');
			
			if(rst.id){
				loadFile(rst.id);
			}
			if(rst.error){
				if(rst.error.message){
					noty({text: rst.error.message, type:'error', layout:'topCenter'});
				}
			}
		},onTimeout:function(){
			$('input[type=file]',this).prop('disabled',false).val('');
			noty({text:'Timeout. Please try again later.', type:'error', layout:'topCenter'});
		}
	});
	
	// handle file drop into area
	$('.filedrop').filedrop({
	    url: '<?php echo site_url('file/upload.json')?>',              // upload handler, handles each file separately, can also be a function taking the file and returning a url
	    paramname: 'new_file',          // POST parameter name used on serverside to reference file, can also be a function taking the filename and returning the paramname
	    withCredentials: true,          // make a cross-origin request with cookies
	    data: {
	    },
	    headers: {          // Send additional request headers
	    },
	    error: function(err, file) {
	            	console.log(err);
	        switch(err) {
	            case 'BrowserNotSupported':
	                noty({text:'browser does not support HTML5 drag and drop', type:'error', layout:'topCenter'});
	                break;
	            case 'TooManyFiles':
	                noty({text:'user uploaded more than \'maxfiles\'', type:'error', layout:'topCenter'});
	                // user uploaded more than 'maxfiles'
	                break;
	            case 'FileTooLarge':
	                noty({text:'Your file\'s size too large.', type:'error', layout:'topCenter'});
	                // program encountered a file whose size is greater than 'maxfilesize'
	                // FileTooLarge also has access to the file which was too large
	                // use file.name to reference the filename of the culprit file
	                break;
	            case 'FileTypeNotAllowed':
	                noty({text:'The file type is not accepted.', type:'error', layout:'topCenter'});
	                // The file type is not in the specified list 'allowedfiletypes'
	                break;
	            case 'FileExtensionNotAllowed':
	                noty({text:'The file extension is not accepted.', type:'error', layout:'topCenter'});
	                // The file extension is not in the specified list 'allowedfileextensions'
	                break;
	            default:
	                break;
	        }
	    },
	    allowedfiletypes: ['image/jpeg','image/png','image/gif'],   // filetypes allowed by Content-Type.  Empty array means no restrictions
	    allowedfileextensions: ['.jpg','.jpeg','.png','.gif'], // file extensions allowed. Empty array means no restrictions
	    maxfiles: 50,
	    maxfilesize: 8,    // max file size in MBs
	    drop: function() {
	        // user drops file
	        console.log('drop',arguments);
	    },
	    uploadStarted: function(i, file, len){
	        // a file began uploading
	        // i = index => 0, 1, 2, 3, 4 etc
	        // file is the actual file of the index
	        // len = total files user dropped
	        console.log('uploadStarted',arguments);
	    },
	    uploadFinished: function(i, file, rst, time) {
	        console.log('uploadFinished',arguments);
	        // response is the data you got back from server in JSON format.
			if(rst.id){
				loadFile(rst.id);
			}
			if(rst.error){
				if(rst.error.message){
					noty({text: rst.error.message, type:'error', layout:'topCenter'});
				}
			}
	    },
	    progressUpdated: function(i, file, progress) {
	        console.log('progressUpdated',arguments);
	        // this function is used for large files and updates intermittently
	        // progress is the integer value of file being uploaded percentage to completion
	    },
	    globalProgressUpdated: function(progress) {
	        console.log('globalProgressUpdated',arguments);
	        // progress for all the files uploaded on the current instance (percentage)
	        // ex: $('#progress div').width(progress+"%");
	    },
	    beforeSend: function(file, i, done) {
	        console.log('beforeSend',arguments);
	        // file is a file object
	        // i is the file index
	        // call done() to start the upload
	    },
	    afterAll: function(){
	        console.log('afterAll',arguments);
	    	noty({text: 'The file(s) have been uploaded successfully!', type:'success', layout:'topCenter'});

	    }
	});
	
	fileListUpdateSequence();
	
	$('#photo-detail').appendTo('body');
	$('#photo-remove').appendTo('body');
})
})(jQuery);
