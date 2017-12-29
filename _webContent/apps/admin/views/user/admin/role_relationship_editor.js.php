;(function($, selector){
$(function(){

	var dataRowClass = 'rr-row';
	var rrType = 'role';

	var $root = $(selector);

	var $listgroup = $root.find('.table tbody');
	var roleTemplateStr = $root.find('script[data-template=row]').text();

	$listgroup.sortable({
		axis:'y',
		update: function(){
			reload();
		}
	})

	var selectRoleForm = function(data){
		var sourceUrl = '<?php echo site_url('user/admin/role')?>';
		var table = data.refTable;
		var modal = null;

		var selector = new Selector();
		selector.multiple = true;
		selector.onUpdate = function(){


			$listgroup.sortable('disable');

			$.getJSON(sourceUrl+'/search.json?id='+this.ids.join(','), function(rst){
				if(rst.data){
					$(rst.data).each(function(idx, _row){
						$empty.remove();
						var row = {};
						row.start_date = null;
						row.end_date = null;
						row.status = 1;
						row.role_id = _row.id;
						row.role_name = _row.name;

						createElement(row);
					});
				}

				reload();
			});

			this.ids.length = 0;

			modal.modal('hide');
		}
		var selectorName = 'selector_'+(new Date).getTime();
		window.top[selectorName] = selector;

		var url = sourceUrl +'/selector?dialog=yes&multiple=yes&callback=top.'+selectorName+'.didResponse' ;
		modal = hc.ui.openModal( url);
	};

	var editRoleForm = function(formData, callback){
		if(!formData) return;

		var $elm = $root.find('[hc-dialog-template="role-form"]').clone();
		var dialog = hc.ui.createDialog({
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
					modal.$elm.find('[hc-locale-value]').each(function(){
						var name = $(this).attr('hc-locale-value');
						var locale = $(this).attr('hc-locale');
						if(!formData [ name ] ) formData [ name ]  = {}
						formData [ name ] [ locale ] = $(this).val();
					});
					modal.$elm.find('[hc-value]').each(function(){
						var name = $(this).attr('hc-value');
						if(!formData [ name ] ) formData [ name ]  = null;
						formData [ name ] = $(this).val();
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

				hc.ui.initUIElement(this.$elm);

				if($.fn.dynamotorUploader)
					this.$elm.find('[dynamotor-uploader]').dynamotorUploader();
			}
		});

	}

	var $btnAdd = $root.find('.list-group-add');


	$btnAdd.on('click', function(evt){
		evt.preventDefault();
		var self = this;
		var data = $(this).data();

		selectRoleForm(data );

	});


	var $empty = $listgroup.find('.empty').removeClass('hidden');
	var $loading = $listgroup.find('.loading').removeClass('hidden');
	$empty.remove();$loading.remove();

	var fetchedData;

	var fetch = function(){

		if(hc.editor.id){
			$empty.remove();
			$listgroup.prepend($loading);

			$.getJSON('<?php echo $endpoint_url_prefix?>/'+hc.editor.id+'/role/search.json', function(rst){
				fetchedData = 	rst.data;

				reloadFetchedData();
				reload();
			});
		}
	};

	var reloadFetchedData = function(){
		$listgroup.empty();
		$loading.remove();

		if(fetchedData && fetchedData.length>0){
			$.each(fetchedData, function(idx, row){
				createElement(row);
			});
		}else{
			$listgroup.append($empty);
		}
	}

	var reload = function(){

		var $cells = $listgroup.find('.'+dataRowClass);

		var items = $listgroup.find('.'+dataRowClass).map(function(){
			var ins = $(this).data(dataRowClass);
			var data = ins.data;
			return {
				_obj_id: ins._obj_id,
				id: data.id,
				role_id: data.role_id,
				status: data.status,
				start_date: data.start_date,
				end_date: data.end_date,
			};
		}).toArray();

		fetchedData = $listgroup.find('.'+dataRowClass).map(function(){
			var ins = $(this).data(dataRowClass);
			var data = ins.data;
			return data;
		}).toArray();

		reloadFetchedData();
				
				

		var str = JSON.stringify(items);

		$root.find('[hc-elm-input]').val( str );
	}

	var createElement = function (dataRow){
		$empty.remove();

		var obj_id = rrType+'_'+(new Date()).getTime()+'_'+((Math.random()*100000 ) <<0);

		var $elm = $(roleTemplateStr);
		$elm.addClass(dataRowClass);
		$elm.appendTo($listgroup);

		var cell = {
			data: dataRow,
			obj_id: obj_id,
			set: function(name,value){
				dataRow [name] = value;

				cell.reload();
			},
			reload: function(){
				$elm.attr('rr-db-id',dataRow.id);
				$elm.attr('rr-obj-id',obj_id);

				hc.ui.ElementParser($elm, dataRow,'rr-');
			}
		}

		$elm.data(dataRowClass, cell);
		cell.reload();
	}

	$listgroup.on('click','.'+dataRowClass+' [rr-action=remove]', function(evt){
		evt.preventDefault();
		var $li = $(this).parents('.'+dataRowClass);
		//$listgroup.sortable('disable');
		$li.remove();
		//$listgroup.sortable('enable');
		reload();

	}).on('click','.'+dataRowClass+' [rr-action=edit]',function(evt){
		evt.preventDefault();

		var $elm = $(this).parents('.'+dataRowClass);
		var ins = $elm.data(dataRowClass);

		var afterSave = function(){

			ins.reload();
			reload();
		}

		editRoleForm( ins.data, afterSave );
	});

	fetch();

	hc.editor.$form.on('submit', function(){
		fetch();
	})

});

})(jQuery,'[hc-elm=<?php echo $element_id?>]');