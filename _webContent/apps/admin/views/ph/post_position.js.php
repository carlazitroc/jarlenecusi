<?php $this->asset->compress_js = false; ?>
;(function($){
$(function(){

/* var apiURLPrefix = '<?php echo site_url('s/'.$section.'/post');?>';
var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
var docTitle = document.title; */

$( "#sortable" ).sortable({
		  placeholder: "ui-state-highlight"
		});
		$( "#sortable" ).disableSelection();
		/*Original
		
		$( "#sortable" ).find('li').each(function(idx, liElem){				
			$(liElem).find('input').val(idx+1);
		});*/
			
		/*Updated priority*/	
		$( "#sortable" ).on("sortupdate", function(){
			$( "#sortable" ).find('li').each(function(idx, liElem){
				$(liElem).attr('data-index',idx+1);
				$(liElem).find('input').val(idx+1);
			});
		});
		
		
					
		$.ajax({
			type: "POST",
			url: '<?php echo site_url('s/'.$section.'/post');?>/search.json?limit=10000&sort=priority&direction=asc',
			data:'' ,
			dataType: 'JSON',
			success: function(res){
				console.log(res);
				$.each( res.data, function(idx, post_row){
					var title 			= post_row['loc_title'];
					var cover			= post_row['cover_url'];
					var priority 		= idx + 1;
					
					var list_html =      '<li style="float:left; list-style-type:none; position:relative; padding: 5px; margin:10px; " class="ui-state-default">'+
											'<p class="title" style="font-weight: bold">'+title+'</p>'+
											'<img src="'+cover+'" />'+
											'<p class="priority" style="position:absolute; right: 5px; top: 0px; padding: 0 9px;font-size: 20px; color: white; background: #666; display: block;">'+priority+'</p>'+
											'<input name="items['+post_row['id']+']" value="'+priority+'" hidden />'+
								  		 '</li>';	
					
					var $list = $(list_html);

					
					$list.appendTo('.list_template');
				});
			}
		
		});
		
		
		$('#btn_update').on('click',function(){
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('s/'.$section.'/post');?>/submit",
				data: $('#priority_form').serialize(),
				dataType: 'JSON',
				success: function(data){					
					if (data.result){
						hc.ui.showMessage('Priority updatad','success',5000);
					} else {
						hc.ui.showMessage('Fail to update priority','error',5000);
					}
					
				}
			});	
				

			
		})
		
		
	
});
})(jQuery);
