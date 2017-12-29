
;(function($){

$(function(){
	<?php foreach($source_types as $type_name => $type_info){
		$source_types[$type_name]['label'] = lang($source_types[$type_name]['label']);
	}?>
	var source_types = <?php echo json_encode($source_types);?>;
	
	var $editor = $('#page-wrapper,.iframe-dialog');
	var $form = $editor.find('form');

	hc.editor = new hc.ui.RecordEditor($editor,{
		apiPathPrefix:'<?php echo $endpoint_url_prefix?>',
		id: <?php echo isset($id) ? "'".strip_tags($id)."'" : 'null'?>
	});

	$('body').on('click', 'a[re-action=change-locale]',function(evt){
		evt.preventDefault();
		var loc = $(this).attr('re-locale');
		var locName = $(this).attr('re-locale-name');
		$('.btn-change-locale span.loc-name').text( locName );
		$('.localized').addClass('hidden');
		$('.localized[re-locale='+loc+']').removeClass('hidden');
		$('.tab-pane').removeClass('active in');
		$('.tab-pane[re-locale='+loc+']').addClass('active in');
	}).on('click', '[data-toggle=copy-value]', function(evt){
		evt.preventDefault();
		var $form = $(this).parents('form')
		var $group = $(this).parents('.form-group')
		var name = $(this).data('name');
		var group = $(this).data('group');
		var loc = $(this).data('locale');
		var val = $group.find('[hc-value][hc-locale="'+loc+'"]').val();

		if( val || val == ''){
			$form.find('[hc-value-group='+group+'] [hc-value]').each(function(){

				var locale = $(this).attr('hc-locale');
				if(locale == loc) return;

				$(this).val( val ).trigger('change');

				if( $(this).is('[dynamotor-uploader]') ){
					$(this).ifuploader('setValue', val);
				}
				if( $(this).parents('[dynamotor-uploader]').length ){
					$(this).parents('[dynamotor-uploader]').ifuploader('setValue', val);
				}


			})
		}
	
	});

	/* ---------- item ids ---------- */
	(function(){
		var $root = $('#listgroup-items');

		var $listgroup = $root.find('.list-group');
		var templateStr = $root.find('script').text();

		$listgroup.sortable({
			axis:'y',
			update: function(){

				reload();
			}
		})

		var createDBForm = function(sourceUrl, data){
			var table = data.refTable;
			var modal = null;

			var selector = new Selector();
			selector.multiple = true;
			selector.onUpdate = function(){


				$listgroup.sortable('disable');

				$.getJSON(sourceUrl +'/search.json?ids='+this.ids.join(','), function(rst){
					if(rst.data){
						$(rst.data).each(function(idx, row){
							$empty.remove();
							row.type = 'db';
							row.parameters = {};
							row.ref_table = table;
							row.ref_table_str = source_types[table].label;
							row.ref_id = row.id;
							delete row.id;

							createElement(row);
						});
					}
				});

				this.ids.length = 0;

				modal.modal('hide');
				$listgroup.sortable('enable');
			}
			var selectorName = 'selector_'+(new Date).getTime();
			window.top[selectorName] = selector;

			var url = sourceUrl +'/selector?dialog=yes&multiple=yes&callback=top.'+selectorName+'.didResponse' ;
			modal = hc.ui.openModal( url);
		};

		var createLinkForm = function(formData, callback){

			var $elm = $('[hc-dialog-template="type-link"]').clone();
			hc.ui.createDialog({
				template: function(){
					return $elm;
				},
				header: function(){
					return $elm.find('.modal-header');
				},
				footer: function(){
					return $elm.find('.modal-footer');
				},
				body: function(){
					return $elm.find('.modal-body');
				},
				onShow: function(){
					var modal = this;

					this.$elm.find('[hc-action=save]').on('click', function(){
						modal.$elm.find('[hc-value]').each(function(){
							var name = $(this).attr('hc-value');
							var locale = $(this).attr('hc-locale');
							if(!formData [ name ] ) formData [ name ]  = {}
							formData [ name ] [ locale ] = $(this).val();
						});

						formData.description = formData.href;

						if(callback)
						callback(formData);

						modal.hide();
					});

					modal.$elm.find('[hc-value-group] [hc-locale]').each(function(){
						var locale = $(this).attr('hc-locale');
						var name = $(this).attr('hc-value');
						var val = formData [name] && formData [name] [locale] ? formData [name] [locale] : ''
						$(this).val( val ).change()
					})

					this.$elm.find('[dynamotor-uploader]').dynamotorUploader();
				}
			});
		};

		var createDBCustomForm = function(formData, callback){
			if(!formData) return;

			var $elm = $('[hc-dialog-template="type-db-custom"]').clone();
			hc.ui.createDialog({
				template: function(){
					return $elm;
				},
				header: function(){
					return $elm.find('.modal-header');
				},
				footer: function(){
					return $elm.find('.modal-footer');
				},
				body: function(){
					return $elm.find('.modal-body');
				},
				onShow: function(){
					var modal = this;
					this.$elm.find('[hc-action=save]').on('click', function(){
						modal.$elm.find('[hc-value]').each(function(){
							var name = $(this).attr('hc-value');
							var locale = $(this).attr('hc-locale');
							if(!formData [ name ] ) formData [ name ]  = {}
							formData [ name ] [ locale ] = $(this).val();
						});

						if(callback)
						callback(formData);

						modal.hide();
					});

					modal.$elm.find('[hc-value-group] [hc-locale]').each(function(){
						var locale = $(this).attr('hc-locale');
						var name = $(this).attr('hc-value');
						var val = formData [name] && formData [name] [locale] ? formData [name] [locale] : ''
						$(this).val( val ).change()
					})
					this.$elm.find('[dynamotor-uploader]').dynamotorUploader();
				}
			});
		}

		var $btnAdd = $root.find('.list-group-add');


		$btnAdd.on('click', '.dropdown-menu a', function(evt){
			evt.preventDefault();
			var self = this;
			var data = $(this).data();

			if(data.type == 'db')
				createDBForm(self.href, data );
			if(data.type == 'link')
				createLinkForm({type:'link','ref_table':'','ref_table_str':'Link', 'ref_id':''} , createElement );

		});


		var $empty = $listgroup.find('.empty').removeClass('hidden');
		var $loading = $listgroup.find('.loading').removeClass('hidden');
		$empty.remove();$loading.remove();

		var fetch = function(){
			$listgroup.sortable('disable');
			if(hc.editor.id){
				$empty.remove();
				$listgroup.prepend($loading);

				$.getJSON('<?php echo $endpoint_url_prefix?>/'+hc.editor.id+'/item/search.json', function(rst){
					$loading.remove();
					if(rst.data && rst.data.length>0){
						$(rst.data).each(function(idx, row){
							createElement(row);
						});
					}else{
						$listgroup.append($empty);
					}
					$listgroup.sortable('enable');

					reload();
				});
			}
		};

		var reload = function(){
			var items = $listgroup.find('.cell').map(function(){
				return $(this).data('record-row')
			}).toArray();

			$('[name=items_payload]').val( JSON.stringify(items) );
		}

		var createElement = function (dataRow){
			$empty.remove();

			dataRow._obj_id = 'obj_'+(new Date()).getTime()+'_'+((Math.random()*100000 ) <<0);

			var $elm = $(templateStr);
			$elm.data('record-row', dataRow);
			var api = {
				set: function(name,value){
					dataRow [name] = value;
					api.reload();
				},
				reload: function(){
					$elm.attr('rr-db-id',dataRow.id);
					$elm.attr('rr-obj-id',dataRow._obj_id);
					hc.ui.ElementParser($elm, dataRow,'rr-');
				}
			}
			api.reload();
			$elm.data('cell', api);
			$elm.appendTo($listgroup);
			reload();
		}

		$listgroup.on('click','.cell [rr-action=remove]', function(){
			var $li = $(this).parents('.cell');
			$listgroup.sortable('disable');
			$li.remove();
			$listgroup.sortable('enable');
			reload();
		}).on('click','.cell [rr-action=edit]',function(){
			var $elm = $(this).parents('.cell');
			var dataRow = $elm.data('record-row');

			var afterSave = function(){

				$elm.data('cell').reload();
				reload();
			}

			if(dataRow.type == 'db')
				createDBCustomForm( dataRow, afterSave );
			if(dataRow.type == 'link')
				createLinkForm( dataRow, afterSave );
		});

		fetch();

		$form.on('response', function(evt, rst){

			if(rst.item_objects){
				for(var item_obj_id in rst.item_objects){
					var $li = $listgroup.find('[rr-obj-id='+rst.item_object_id+']');
					var cell = $li.data('cell');
					if(cell)
					cell.set('id',rst.item_objects[ item_obj_id ] );
				}
			}
		});

	})();

});
	
})(jQuery);