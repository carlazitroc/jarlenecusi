
/* Load an iframe instance based on Bootstrap3 Modal, All contents and buttons should be should within the iframe */
(function(exports, $){

if(top.hc && top.hc.ui && top.hc.ui.openModal) {exports.openModal = top.hc.ui.openModal;return;}
if(parent.hc && parent.hc.ui && parent.hc.ui.openModal) {exports.openModal = parent.hc.ui.openModal;return;}
	
var openModal = function(url,options){
	if(typeof url == 'undefined') return false;
	if(!options) options = {};
	options = $.extend({} ,options, openModal.defaults);
	
	var key = 'ins_'+ (new Date()).getTime()+'_'+ ( Math.random()*1000 << 0);
	
	var queryStr = url.indexOf('?') != -1 ? url.substr( url.indexOf('?') ) : '';
	url+= (queryStr == '') ? '?':'&';
	url+= '_modalIns='+key;
	if(typeof options.urlAppendStr != 'undefined' && options.urlAppendStr!=''){
		url+= '&'+options.urlAppendStr;
	}
	
	
	
	var $elm = $('<div class="modal"></div>'), $root = $elm, $parent = $elm;
	openModal.instances[ key ] = $elm;
	
	var headerElm = null;
	if(typeof options.header =='function'){
		headerElm = options.header();
	}else if(typeof options.header == 'string' && options.header.length>0){
		headerElm = $(options.header);
	}else if(typeof options.title == 'string' && options.title.length>0){
		headerElm = $('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>'+options.title+'</h3></div>');
	}
	
	var footerElm = null;
	if(typeof options.footer =='function'){
		footerElm = options.footer();
	}else if(typeof options.footer == 'string' && options.header.length>0){
		footerElm = $(options.footer);
	}
	$root = $('<div class="modal-dialog" />');
	if(options.size == 'large')
		$root.addClass('modal-lg');
	if(options.size == 'fluid')
		$root.addClass('modal-fluid');
	$root.appendTo($elm);
	$parent = $root;
	
	//if(headerElm || footerElm){
		$parent = $('<div class="modal-content" />');
		$parent.appendTo($root);
	//}
	
	if(headerElm){
		$parent.append(headerElm);
	}
	
	var $content = $('<iframe class="modal-iframe" />').width(options.width).height(options.height).prop('src',url);
	$content.appendTo($parent);
	
	if(footerElm) $parent.append(footerElm);
	
	if(headerElm || footerElm){
		//$content.addClass('modal-body');
	}
	
	$elm.on('hidden.bs.modal', function(evt){
		
		if(typeof options.srcElement != 'undefined' && options.srcElement){
			$(options.srcElement).trigger('sb.hidden',evt);
		}
		if(typeof options.onHidden != 'undefined' && options.onHidden){
			options.onHidden.apply($elm, []);
		}
		
		openModal.instances[ key ] = null;
		$elm.remove();
		$elm = null;
		options = null;
		
	}).on('shown.bs.modal', function(evt){
		
		if(typeof options.srcElement != 'undefined' && options.srcElement){
			$(options.srcElement).trigger('sb.shown',evt);
		}
		if(typeof options.onShown != 'undefined' && options.onShown){
			options.onShown.apply($elm, []);
		}
	});
	
	return $elm.modal(options);
};
openModal.instances = {};

openModal.defaults = {
	width:'100%',
	height:500,
	urlAppendStr: 'dialog=yes',
	size: 'fluid',
	header:'',
	footer:'',
	title:'',
	removeWhenClose:true
};

exports.openModal = openModal;

})(hc.ui, top.jQuery || parent.jQuery || self.jQuery);