<?php
?>

;(function($,scope,field){
var ns = 'dynamotor-gallery-editor';
var locs = <?php echo json_encode( $this->lang->get_available_locale_keys() ); ?>;


var openGalleryEditor = function(scope, cell,callback){
	var loc;
	var self = this;
	var $elm = null;

	var reload = function(){
		var row = {};
		if(typeof cell.dataRow['parameters'] == 'undefined' || !cell.dataRow['parameters']){
			cell.dataRow['parameters'] = {};
		}
		var p = cell.dataRow['parameters'];
		for(var i = 0; i< locs.length; i ++){
			var loc = locs[i];
			if(typeof p['loc'][loc] == 'undefined'  || $.isArray(p['loc'][loc]) || !p['loc'][loc]){
				p['loc'][loc] = {};
			}
		}
		loadData();
	};

	var loadData = function(){
		$elm.find('[hc-value]').each(function(evt){
			var field = $(this).attr('hc-value');

			if(typeof cell.dataRow[field] != 'undefined' ){
				$(this).val( cell.dataRow[field] ).trigger('change');
			}else{
				$(this).val( '' ).trigger('change');
			}
		});
		$elm.find('[hgc-value]').each(function(evt){
			var field = $(this).attr('hgc-value');
			var loc = $(this).attr('hgc-locale');
			var p = cell.dataRow['parameters'];
			if(loc && loc !=''){

				if(typeof p['loc'][loc] != 'undefined' && typeof p['loc'][loc][field] != 'undefined'){
					$(this).val( p['loc'][loc][field] ).trigger('change');
				}else{
					$(this).val( '' ).trigger('change');
				}
			}
			else{
				if(typeof p[field] != 'undefined' ){
					$(this).val( p[field] ).trigger('change');
				}else{
					$(this).val( '' ).trigger('change');
				}

			}
		});
	};

	var saveData = function(){
		$elm.find('[hc-value]').each(function(evt){
			var field = $(this).attr('hc-value');
			 cell.dataRow[field] = $(this).val();
		});

		$elm.find('[hgc-value]').each(function(evt){
			var field = $(this).attr('hgc-value');
			var loc = $(this).attr('hgc-locale');

			if(loc && loc !=''){
				cell.dataRow['parameters']['loc'][loc][field] = $(this).val();
			}
			else{
				cell.dataRow['parameters'][field] = $(this).val();
			}
		});
	};

	hc.ui.createDialog({
		template: function(){
			return $elm;
		},
		header: function(){
			return $elm.find('.modal-header');
		},
		body: function(){
			return $elm.find('.modal-body');
		},
		footer: function(){
			return $elm.find('.modal-footer');
		},
		onHidden: callback,
		onInit: function(){
			$elm = $('[hc-modal=slide-editor]',scope).clone();
			$elm.appendTo('body');

			$elm.on('click', 'a[re-action=change-locale]',function(evt){
				evt.preventDefault();
				var loc = $(this).attr('re-locale');
				var locName = $(this).attr('re-locale-name');
				$elm.find('.btn-change-locale span.loc-name').text( locName );
				$elm.find('.tab-pane').removeClass('active in');
				$elm.find('.tab-pane[re-locale='+loc+']').addClass('active in');
			});

			var dialog = this;


			
			reload();
			$elm.find('[dynamotor-uploader]').dynamotorUploader();

			$elm.on('click','[hgc-action]',function(evt){
				evt.preventDefault();
				var action=  $(this).attr('hgc-action');

				if(action == 'cancel'){
					dialog.hide();
				}else if(action == 'done'){

					saveData();
					dialog.hide();
				}
			})
		}
	});
}

var GalleryCell = function(dataRow, scope){
	var self = this;
	var $src = $('[hc-template]',scope);
	var str = $src.html();
	var $elm = $(str);

	this.dataRow = dataRow;
	this.$elm = $elm;

	if(!dataRow.parameters){
		dataRow.parameters = {};
	}else if(dataRow.parameters == 'N;'){
		dataRow.parameters = {};
	}
	if(!dataRow.parameters.loc){
		dataRow.parameters.loc = {};
		for(var i = 0; i < locs.length; i ++){
			dataRow.parameters.loc[ locs[i] ] = {};
		}
	}

	this.install = function(root){
		root.append(self.$elm);
	};
	this.setDataRow = function(newRow){
		dataRow = self.dataRow = newRow;
		self.reload();
	}
	this.reload = function(){
		var row = self.dataRow;
		hc.ui.ElementParser($elm,function(exp, targetElm){
			if(typeof row[exp] != 'undefined'){
				return row[exp];
			}else{
				try{
					var o = eval(exp);
					return o;
				}catch(e){}
				return '';
			}
		});
	};

	this.remove = function(){
		self.$elm.remove();
	}

	this.selected = false;
	this.setSelected = function(newVal){
		self.selected = newVal;
		if(self.selected){
			$elm.addClass('active');
		}else{
			$elm.removeClass('active');
		}

		self.$elm.trigger('select', self);
	}

	$elm.data(GalleryCell.ns, this);
	this.reload();
};
GalleryCell.ns = 'hc-gallery-cell';

var instance_counter = 0;
var GalleryEditor = function(scope){
	var self = this;
	var instance_id = instance_counter ++;
	
	var $scope = $(scope);
	var element_id = $scope.attr(ns);

	// if no element_id applied, we create one
	if(!element_id){
		element_id = 'hcgalleryeditor_'+instance_id;
		$scope.attr(ns, element_id);
	}

	var $panel = $scope.find('.panel');
	var $list = $scope.find('.cell-list');
	var $field = $scope.find('[dynamotor-gallery-editor-input]');
	$field.attr(ns, element_id);
	
	var album_id = $field.val();
	var data = [], initial_data = [];
	var changed = false;
	var setIsChanged = function(val){
		changed = val;

		if(val){
 			$field.attr('re-error','Gallery changes must be confirmed before save.');
		}else{
 			$field.attr('re-error',null);
		}

		$('.visible-changed').toggleClass('hidden', !changed);
		$('.visible-unchanged').toggleClass('hidden', changed);
		$panel.toggleClass('panel-default', !changed);
		if($panel.hasClass('panel-danger'))
			$panel.toggleClass('panel-danger', changed);
		else
			$panel.toggleClass('panel-warning', changed);
	}

	var clear = function(){
		$list.sortable('disable');
		album_id = '';
		$list.empty();
		$field.val('');
		data = [];
		initial_data = [];
		$list.sortable('enable');

		setIsChanged(false);
	}

	var save = function(){
		var post_data = {};

		if(album_id != '' && album_id!= null)
			post_data['id'] = album_id;

		$(data).each(function(sequence, dataRow){
			post_data['cell_ids['+sequence+']'] = dataRow.id;
			post_data['cell_files['+sequence+']'] = dataRow.file_id;
			post_data['cell_params['+sequence+']'] = JSON.stringify(dataRow.parameters);
		});

		hc.ui.showLoaderAnimation();

		$.post('<?php echo site_url('album/save.json')?>',post_data, function(rst){
			if(rst.id){
				initial_data = data;
				album_id = rst.id;
				$field.val(album_id);
				setIsChanged(false);

				$field.trigger('updated.dynamotor');
				hc.ui.hideLoaderAnimation();
			}
		},'json').error(function(){
			hc.ui.hideLoaderAnimation();
			noty({'text':'<?php echo lang('error_connection')?>','modal':true,'type':'error',force:true, killer: true,layout:'center'})
		});
	};

	function reset(){
		$list.sortable('disable');
		data.length = 0;
		for(var i = 0; i < initial_data.length; i ++)
			data.push(initial_data[i]);
		//$list.empty();
		setIsChanged(false);
		$list.sortable('enable');

		reload();
	}

	function fetch(){
		if(!album_id) return;
		$.getJSON('<?php echo site_url('album')?>/'+album_id+'/get.json', function(rst){
			console.log(rst);
			if(rst.id){
				initial_data = data = rst.photos;
				reload();
			}
		});
	}

	function createElement(dataRow){
		var cell = new GalleryCell(dataRow, scope);
		cell.install($list);
	}
	function reload(){
		$list.sortable('disable');

		var $child = $list.find('.cell.item');

		$(data).each(function(idx,dataRow){
			if($child.length > idx){
				$child.eq(idx).data(GalleryCell.ns).setDataRow( dataRow);
			}else{
				createElement(dataRow);
			}
		});

		$child = $list.find('.cell.item');
		for(var i = data.length; i < $child.length; i ++ )
			$child.eq(i).remove();

		$list.sortable('enable');
	}

	function updateValue (){

		data = $list.find('.cell.item').map(function(){
			return $(this).data(GalleryCell.ns).dataRow
		}).toArray();
	}

	var importCounter = 0;
	function importFile(fileId){
		createElement({id:'', file_id: fileId, object_id: element_id+'_cell_'+(importCounter++)});
	}

	var selector_counter = 0;
	$scope.on('click','[hc-gallery-action]',function(evt){
		evt.preventDefault();
		var action = $(this).attr('hc-gallery-action');
		if(action == 'import'){

			var n = selector_counter++;

			var sel = new Selector();
			sel.caller = element_id+'_sel_'+n;
			sel.multiple = true;
			sel.getValue = function(){
				return this.ids;
			}
			sel.onUpdate = function(){
				var val = this.getValue();
				if(val && val.length > 0){
					for(var i= 0; i< val.length; i ++)
						importFile(val[i])
					setIsChanged(true);
					updateValue();
				}
			}
			sel.onUpdate();
			window.top[sel.caller] = sel;

			hc.ui.openModal(hc.net.site_url('file/selector?type=image&multiple=yes&callback=top.'+sel.caller+'.didResponse'));
		}else if( action == 'save'){
			save();
		}else if( action == 'reset'){
			reset();
		}
	});

	$list.on('click','.cell.item [hc-action]',function(evt){
	//	evt.preventDefault();

		var $self = $(this);
		var $cell = $self.parents('.cell');

		if($self.attr('hc-action') == 'remove'){
			$list.sortable('disable');
			
			$cell.remove();
			
			setIsChanged(true);
			updateValue();

			$list.sortable('enable');
		}else  if($self.attr('hc-action') == 'edit'){
			var cell = $cell.data(GalleryCell.ns);
			if(cell){
				openGalleryEditor(scope, cell, function(){
					setIsChanged(true);
					updateValue();
				});
			}
		}
	})

	$list.sortable({
    	placeholder: "cell ui-state-highlight",
    	items: ".cell.item",
    	update: function(){

			setIsChanged(true);
			updateValue();
    	}
 	});

 	$list.parents('form').on('submit', function(evt){
		$panel.toggleClass('panel-default', !changed);
		$panel.toggleClass('panel-warning', false);
		$panel.toggleClass('panel-danger', changed);
 	});

	// handle file drop into area
	$panel.filedrop({
	    url: '<?php echo site_url('file/upload.json')?>',              // upload handler, handles each file separately, can also be a function taking the file and returning a url
	    paramname: 'new_file',          // POST parameter name used on serverside to reference file, can also be a function taking the filename and returning the paramname
	    withCredentials: true,          // make a cross-origin request with cookies
	    error: function(err, file) {
	        switch(err) {
	            case 'BrowserNotSupported':
	                hc.ui.showMessage('browser does not support HTML5 drag and drop','error');
	                break;
	            case 'TooManyFiles':
	                hc.ui.showMessage('user uploaded more than \'maxfiles\'','error');
	                // user uploaded more than 'maxfiles'
	                break;
	            case 'FileTooLarge':
	                hc.ui.showMessage('Your file\'s size too large.','error');
	                // program encountered a file whose size is greater than 'maxfilesize'
	                // FileTooLarge also has access to the file which was too large
	                // use file.name to reference the filename of the culprit file
	                break;
	            case 'FileTypeNotAllowed':
	                hc.ui.showMessage('The file type is not accepted.','error');
	                // The file type is not in the specified list 'allowedfiletypes'
	                break;
	            case 'FileExtensionNotAllowed':
	                hc.ui.showMessage('The file extension is not accepted.','error');
	                // The file extension is not in the specified list 'allowedfileextensions'
	                break;
	            default:
	                break;
	        }
	    },
	    allowedfiletypes: ['image/jpeg','image/png','image/gif'],   // filetypes allowed by Content-Type.  Empty array means no restrictions
	    allowedfileextensions: ['.jpg','.jpeg','.png','.gif'], // file extensions allowed. Empty array means no restrictions
	    maxfiles: 50,
	    maxfilesize: 5,    // max file size in MBs
	    drop: function(){
	    	$panel.removeClass('filedrop-dnd');
	    },
	    dragOver: function(){
	    	$panel.addClass('filedrop-dnd');
	    },
	    dragLeave: function(){
	    	$panel.removeClass('filedrop-dnd');
	    },
	    uploadStarted: function(){
	    	$panel.find('['+ns+'-elm="upload-message"]').toggleClass('hidden',true);
	    },
	    uploadFinished: function(i, file, rst, time) {
	        // response is the data you got back from server in JSON format.
			if(rst.id){
				setIsChanged(true);
				importFile(rst.id);
			}
			if(rst.error){
				if(rst.error.message){
					hc.ui.showMessage(rst.error.message,'error');
				}
			}
	    },
	    afterAll: function(){
	    	$panel.find('['+ns+'-elm="upload-message"]').toggleClass('hidden',false);
	    	hc.ui.showMessage('The file(s) have been uploaded successfully!', 'success',5000);
	    }
	});

	var api = self;
	api.reload = reload;
	api.fetch = fetch;
	api.save = save;
	api.clear = clear;

	setIsChanged(false);
 	fetch();
}

$.fn.dynamotorGalleryEditor = function(options){
	return $(this).each(function(){
		var instance = $(this).data(ns);
		if(!instance){
			instance = new GalleryEditor(this);
			$(this).data(ns, instance);
		}
	});
}

$(function(){
	$('['+ns+']').dynamotorGalleryEditor();
});

})(jQuery);