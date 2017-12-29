<?php 

$this->asset->js_import(base_url('assets/js/jquery-1.10.2.min.js'));
?>
<h1>Setup</h1>
<h2>Step 1 - Basic checking</h2>
<hr />
<div  id="message_viewer" class="well" style="height:250px; overflow: auto;">
<pre style="border: none; background: none;">Loading result...</pre>
</div>

<div class="center">
	<button type="button" id="recheck_btn" class="btn btn-default">Recheck</button>
	<button id="continue_btn" type="button" class="btn  btn-success pull-right">Continue</button>
</div>

<script>
$(function(){
		$('button').prop('disabled', true);
	
	function recheck(){
		$('button').prop('disabled', true);

		$('#message_viewer pre').text( 'Loading result...');
		
		setTimeout(function(){
			$.getJSON('<?php echo site_url('setup/checker.json?check_setup_already=yes')?>', function(rst){
				$('#recheck_btn').prop('disabled',false);
				if(rst.done && rst.setup_enabled){
					$('#continue_btn').prop('disabled',false);
				}
				if(rst.message){
					$('#message_viewer pre').text( rst.message.join('\r\n') );
					$('#message_viewer').scrollTop( $('#message_viewer pre').height()  );
				}
			}).error(function(){

				$('#recheck_btn').prop('disabled',false);
				$('#message_viewer pre').text( 'Cannot load result, please try again.');
			});
		},150);
	}

	setTimeout(recheck,500);

	$('#recheck_btn').on('click', recheck);
	$('#continue_btn').on('click', function(){
		location.href = '<?php echo site_url('setup/form')?>';
	})
})
</script>