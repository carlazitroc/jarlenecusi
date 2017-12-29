
if($.fn.datepicker && $.fn.datepicker.defaults){
	$.fn.datepicker.defaults.format = "yyyy-mm-dd";
}else if($.fn.datepicker && $.datePicker.defaults){
	$.datePicker.defaults.dateFormat = 'yyyy-mm-dd';
}

hc.ui.modules.push(function(scope){

	var $scope = $(scope);

	if($.fn.tab){
		$scope.tab();
	}

	if($.fn.datepicker){
		$('.datepicker,.date-picker',scope).each(function(){
			$(this).datepicker( $(this).data() );
		});
	}else if($.fn.datePicker){
		$('.datepicker,.date-picker',scope).each(function(){
			$(this).datePicker( $(this).data() );
		});
	}

	if($.fn.datetimepicker){
		$('.datetimepicker,.date-time-picker',scope).each(function(){
			$(this).datetimepicker( $(this).data() );
		});
	}

	if($.fn.timePicker){
		$('.timepicker,.time-picker',scope).timePicker();
	}

	if($.fn.colorPicker){
		$('.colorpicker,.color-picker',scope).colorPicker();
	}
	
	$scope.on('click','a.popup-modal-iframe', function(evt){
		if(exports.openModal){
			evt.preventDefault();
			exports.openModal($(this).attr('href'), {srcElement: this});
		}
	}).on('click','button.popup-modal-iframe, input.popup-modal-iframe', function(evt){
		if(exports.openModal){
			evt.preventDefault();
			exports.openModal($(this).data('href'), {srcElement: this});
		}
	}).on('click','.btn-window-close', function(){
		window.close();
	});
})