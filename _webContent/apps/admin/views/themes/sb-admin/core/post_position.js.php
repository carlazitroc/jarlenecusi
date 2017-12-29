<?php $this->asset->compress_js = false; ?>
;(function($){
$(function(){

/* var apiURLPrefix = '<?php echo $endpoint_url_prefix;?>';
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
			url: '<?php echo $endpoint_url_prefix;?>/search.json?limit=10000&sort=priority&direction=asc',
			data:'' ,
			dataType: 'JSON',
			success: function(res){
				$.each( res.data, function(idx, post_row){
					var priority 		= idx + 1;
					if (typeof(post_row['cover'])!='undefined' && (post_row['cover']['url'] != null)){
						var cover			= post_row['cover']['url'];
						if (typeof(post_row['title'])!='undefined' && (post_row['title'] != null)){
							var title 			= post_row['title'];
						}
						
						var list_html =      '<li style="float:left; list-style-type:none; position:relative; padding: 5px; margin:10px; " class="ui-state-default">'+
												'<p class="title" style="font-weight: bold">'+title+'</p>'+
												'<img src="'+cover+'" />'+
												'<p class="priority" style="position:absolute; right: 5px; top: 0px; padding: 0 9px;font-size: 20px; color: white; background: #666; display: block;">'+priority+'</p>'+
												'<input name="items['+post_row['id']+']" value="'+priority+'" hidden />'+
											 '</li>';	
					}else{
						var cover			= post_row['media_url'];
						var title 			= post_row['profile_fullname'];
						var list_html =      '<li style="float:left; list-style-type:none; position:relative; padding: 5px; margin:10px; " class="ui-state-default">'+
												'<p class="title" style="font-weight: bold">'+title+'</p>'+
												'<img src="'+cover+'" />'+
												'<p class="priority" style="position:absolute; right: 5px; top: 0px; padding: 0 9px;font-size: 20px; color: white; background: #666; display: block;">'+priority+'</p>'+
												'<input name="items['+post_row['id']+']" value="'+priority+'" hidden />'+
											 '</li>';	
					}
						
					
					
					var $list = $(list_html);

					
					$list.appendTo('.list_template');
				});
			}
		
		});
		
		
		$('#btn_update').on('click',function(){
			$.ajax({
				type: "POST",
				url: "<?php echo $endpoint_url_prefix;?>/priority",
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
