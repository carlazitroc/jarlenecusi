<?php

?>
;(function($){
var ns = 'dynamotor-rich-editor';

var defaultSettings= {
	
	selector: '[dynamotor-rich-editor]',
	theme: 'modern',

	skin: 'lightgray',
	
    plugins : 'advlist autolink link contextmenu paste image media table pagebreak textcolor wordcount visualchars visualblocks anchor charmap code',

    toolbar : 'formatselect styleselect removeformat | fontselect fontsizeselect | forecolor backcolor | bold underline italic'
    +' link unlink image hr | bullist numlist outdent indent | subscript superscript charmap | table | code',
	image_advtab : true,

	menubar: false,
	 
	convert_urls: false,
	relative_urls : false, // Default value
	force_p_newlines : true,
	forced_root_block : false,

    height: 300,

    visualblocks_default_state: true,
    end_container_on_empty_block: true,
    
	
	file_picker_callback: function(callback, value, meta) {
        // Provide file and text for the link dialog
        var sel = new Selector();
        sel.callbackID = 'tfc_'+(new Date).getTime();
		sel.onClose = function(){
			
			if( sel.$modal){
				sel.$modal.modal('hide');
				sel.$modal = null;
			}
		};
		sel.onUpdate = function(){
			sel.updateField();

			if(sel.ids.length>0){
				var fileID = sel.ids[0];

				$.getJSON(hc.net.site_url('file/'+fileID+'.json?need-pub-url=yes'), function(row){
					if(row.id && row.pub_url){
						callback(row.pub_url);
					}
				});
			}
		};
		
		var url = hc.net.site_url('file');
		url+= '?dialog=yes&callback=top.'+sel.callbackID+'.didResponse';
		url+= '&type='+meta.filetype;
		
		top.window[sel.callbackID] = sel;
		sel.$modal = hc.ui.openModal(url);
		sel.$modal.css('z-index',65536);
    }
};

var instanceCounter = 0;
var RichEditor = function(scope){
	var self = this;
	var $elm = $(scope);
	var isntanceId = instanceCounter ++;
	var elmConfig = $elm.data('config');
	if(!elmConfig) elmConfig = {};

	var $input = $(scope).find('textarea');

	var element_id = $elm.attr(ns);
	if(!element_id || element_id == '')
		element_id = 'dynamotorRichEditor_' + isntanceId;

	$elm.attr(ns, element_id);
	$input.attr(ns,element_id)

	if(typeof elmConfig == 'string')
		elmConfig = JSON.parse(elmConfig);
	if(typeof elmConfig == 'undefined')
		elmConfig = {};
	var _editor = null;
	var options = {};
	$.extend(options, defaultSettings, elmConfig);
	options.selector = 'textarea['+ns+'='+element_id+']';

	$input.on('change', function(){
		_editor.setContent( $input.val(), {format: 'raw'});
	})

	options.setup = function(editor) {
		_editor = editor;
        editor.on('change', function(e) {
        	var str = editor.getContent();
        	$input.val( str );

        	console.log('RichEditor',editor,'changed:',str);

            $elm.trigger('change');
            //$input.trigger('change');
        	
        });
    };

	this.options = options;

	tinymce.init(options);
};

$.dynamotorRichEditor = {};
$.dynamotorRichEditor.defaultSettings = defaultSettings;

$.fn.dynamotorRichEditor = function(options){
	return $(this).each(function(){
		var instance = $(this).data(ns);
		if(!instance){
			instance = new RichEditor(this);
			$(this).data(ns, instance);
		}
	});
};

$(function(){
	$('['+ns+']').dynamotorRichEditor();
});

})(jQuery);