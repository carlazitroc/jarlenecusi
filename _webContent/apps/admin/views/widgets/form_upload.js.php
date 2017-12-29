;(function($){
var ns = 'dynamotor-uploader';

var defaultSettings = {
	params: null,
	selector: 'image',
	image: false,
	crop:false,
	cropFieldName: '',
	cropWidth: 0,
	cropHeight: 0
};

var Uploader = function(scope){

	var self = this;
	
	var $target = $(scope);
	var elmData = $target.data();

	var $input = $target.find('[hc-uploader-value]');

	var options = {};
	$.extend(options, defaultSettings, elmData);

	this.options = options;

	var ifuploaderConfig = {
		target: '[hc-uploader-value]',
		url: hc.net.site_url('file/upload.html'),
		crop_field_name: options.cropFieldName,
		croparea_url: hc.net.site_url('file/croparea/{val}'),
		crop_width: options.cropWidth,
		crop_height: options.cropHeight,
		selector_url: hc.net.site_url('file/selector/'+options.selector),
		params: options.params,
		openDialog: function(url){
			var self = this;
			var opts = {
				size:'fluid', 
				onHidden: function(){
					self.$dialog = null;
				}
			};
			self.$dialog = hc.ui.openModal(url,opts);
		},
		closeDialog: function(){
			var self = this;
			if(self.$dialog !=null){
				self.$dialog.modal('hide');
			}
			self.$dialog = unll;
		}
	};

	if( options.image){
		ifuploaderConfig.preview_url = function(type, value){
			if(!value){
				return null;
			}
			var appendStr = '';

			if(options.crop){
				appendStr+= '&croparea='+this.getCropAreaValue();
				appendStr+= '&crop=yes&scale=fill&width='+this.settings.crop_width+'&height='+this.settings.crop_height;
			}
			if(type == 'enlarge')
				return hc.net.site_url('file/'+value+'/picture?size=source'+appendStr);
			
			return hc.net.site_url('file/'+value+'/picture?size=thumb'+appendStr);
		}
	}else{
		ifuploaderConfig.previewer = function(value){

			var $previewer = $(this);
			if(value){
				$previewer.addClass('empty').addClass('loading');
				$.getJSON( hc.net.site_url('file/'+value+'.json'), function(rst){
					$previewer.removeClass('loading');
					if(rst.id){
						$previewer.removeClass('empty').find('.body').html('<?php echo lang('selected_file')?>: <a target="_blank" href="'+rst.download_url+'">'+rst.file_name +'</a>');
					}
				});
			}else{
				$previewer.addClass('empty').removeClass('loading');
			}
		}
	}

	$target.ifuploader(ifuploaderConfig);

	$input.on('change', function(){
		var api = $target.data('ifuploader');
		var newVal = $input.val(); 
		
		api.setValue ( newVal );
	})

	// dragndrop file upload
	if(options.image){
		
		// handle file drop into area
		var $filedrop = $target.find('.filedrop');
		$filedrop.filedrop({
		    url: '<?php echo site_url('file/upload.json')?>',              // upload handler, handles each file separately, can also be a function taking the file and returning a url
		    paramname: 'new_file',          // POST parameter name used on serverside to reference file, can also be a function taking the filename and returning the paramname
		    withCredentials: true,          // make a cross-origin request with cookies
		    data: {
		    },
		    headers: {          // Send additional request headers
		    },
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
		    maxfiles: 1,
		    maxfilesize: 8,    // max file size in MBs
		    drop: function(){
		    	$filedrop.removeClass('filedrop-dnd');
		    },
		    dragOver: function(){
		    	$filedrop.addClass('filedrop-dnd');
		    },
		    dragLeave: function(){
		    	$filedrop.removeClass('filedrop-dnd');
		    },
		    uploadStarted: function(){
		    	$target.find('['+ns+'-elm=upload-message]').toggleClass('hidden', !1);
		    },
		    uploadFinished: function(i, file, rst, time) {
		    	$target.find('['+ns+'-elm=upload-message]').toggleClass('hidden', !0);
		        // response is the data you got back from server in JSON format.
				if(rst.id){
					$target.ifuploader('setValue', rst.id);
				}
				if(rst.error){
					if(rst.error.message){
						hc.ui.showMessage(rst.error.message,'error');
					}
				}
		    }
		});
	}
};

$.dynamotorUploader = {};
$.dynamotorUploader.defaultSettings = defaultSettings;

$.fn.dynamotorUploader = function(options){
	return $(this).each(function(){
		var instance = $(this).data(ns);
		if(!instance){
			instance = new Uploader(this);
			$(this).data(ns, instance);
		}
	});
};

$(function(){
	$('['+ns+']').dynamotorUploader();
});

})(jQuery);