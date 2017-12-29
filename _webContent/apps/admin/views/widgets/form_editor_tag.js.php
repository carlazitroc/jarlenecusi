<?php

$this->asset->css(base_url('assets/libs/bootstrap-tagsinput/bootstrap-tagsinput.css'));
$this->asset->js_import(base_url('assets/libs/bootstrap-tagsinput/bootstrap-tagsinput.min.js'));
$this->asset->js_import(base_url('assets/libs/typeahead.js/typeahead.min.js'));
?>
;(function(sel, list_url, search_url, save_url){

$(function(){
var $items = $(sel);

var idStr = $items.val();
var ids = idStr.split(',');
$items.tagsinput({
	itemValue: 'value',
	itemText: 'label'
});
$items.data('tagsinput').$container.addClass('form-control');
var $itemInput = $items.tagsinput('input');
$itemInput.on('typeahead:initialized',function(){
	if(ids.length>0){
		var _url = list_url;
		if(_url.indexOf('?') < 1) 
			_url+='?'
		else
			_url+='&';
		$.getJSON(_url+'ids='+ids.join(','), function(rst){

			// if server require to login
			if(hc.auth.isResultRestrict(rst)){
				return hc.auth.handleLogin(function(){
					$itemInput.trigger('typeahead:initialized');
				});
			}
			
			$(rst).each(function(idx,row){
				$items.tagsinput('add', row);
			});
		});
	}
}).on('typeahead:selected', $.proxy(function (obj, datum) {
	$items.tagsinput('add', datum);
	$itemInput.typeahead('setQuery', '');
}, $items)).on('keydown', function(evt){

	// press enter will send to save
	if(evt.keyCode == 13){
		var val = $(this).val();

		var saveNewItem = function(val,callback){
			$.post(save_url,{
				title: val,
				status: 1,
				parent_id:0
			}, callback,'json');
		}

		var onSaveItem = function(rst){

			if(hc.auth.isResultRestrict(rst)){
				return hc.auth.handleLogin(function(){
					saveNewItem( val, onSaveItem);
				});
			}
			$items.tagsinput('add', {value: rst.id, label: val});
		}

		saveNewItem( val, onSaveItem);

		$itemInput.typeahead('setQuery', '');
		evt.preventDefault();
	}
});

$itemInput.typeahead({
	template: function(datum){
		return datum.label;
	},
	remote: search_url
});
})
;
})('<?php echo $selector?>','<?php echo $list_url?>','<?php echo $search_url?>','<?php echo $save_url?>');