<?php

$this->asset->css(base_url('assets/libs/jquery-chosen/chosen.css'));
$this->asset->css(base_url('assets/libs/jquery-chosen/bootstrap.css'));
$this->asset->js_import(base_url('assets/libs/jquery-chosen/chosen.jquery.min.js'));


if(empty($row_parent)) $row_parent = 'parent_id';
if(empty($row_value)) $row_value = 'id';
if(empty($row_label)) $row_label = 'title';
?>
(function(){
	var $input = $('select[hc-elm=<?php echo $element_id?>]');
	$input.chosen();
	$input.each(function(){

		var $elm = $(this);

		var build_nodes = function(data, level){

			if(!level) level = 0;
			//var prefix = ''; for(var i = 0; i< level; i ++) prefix+='&nbsp;&nbsp;&nbsp;';
			$(data).each(function(idx, row){
				var $li = $('<option/>');
				$li.css('padding-left', (1+level) * 15);
				$li.prop('value', row['<?php echo $row_value?>'] ).html( row['<?php echo $row_label?>'] );
				$elm.append($li);
			});
		}

		var tree = function(data, parent_id, level){
			if(!parent_id) parent_id = 0;
			if(!level) level = 0;

			var child = [];

			$(data).each(function(idx, row){
				if((typeof row.<?php echo $row_parent?> =='undefined' && parent_id == 0) || (typeof row.<?php echo $row_parent?> !='undefined' && row.<?php echo $row_parent?> == parent_id)) {
					// build current node
					build_nodes(row, level);
					// start next level
					if(parent_id != row['<?php echo $row_value?>'])
						tree(data, row['<?php echo $row_value?>'], level+1);
				}
			});
		};

		var getSelected = function(){
			return $elm.find('option:selected').map(function(){
				return this.value;
			}).toArray();
		};

		var setSelected = function(selected){
			$(selected).each(function(idx,val){
				$elm.find('option[value='+val+']').prop('selected',true);
			});
		};

		var reload = function(){
			var url = $elm.attr('hc-remote');
			var selected = $elm.attr('hc-selected');
			if(selected) selected = selected.split(',');

			$elm.prop('readonly',true);
			$.getJSON(url, function(rst){
				$elm.empty();
				
				tree(rst.data);

				if(selected){
					$(selected).each(function(idx,val){
						$elm.find('option[value='+val+']').prop('selected',true);
					});
				}
				$elm.prop('readonly',false);

				$elm.trigger('reloaded');
			});
		};

		var api = {
			reload: reload,
			setSelected: setSelected,
			getSelected: getSelected
		};

		$elm.data('rs', api);

		reload();
		return this;
	}).on('reloaded', function(){
		$(this).trigger("chosen:updated");
	});
})();

<?php 

unset($_hash); unset($value_field); unset($label_field);