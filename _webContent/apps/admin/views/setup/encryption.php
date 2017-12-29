
<?php 

$this->asset->js_import(base_url('assets/js/jquery-1.10.2.min.js'));
?>
<h1>Setup</h1>
<h2>Ecnryption Tools</h2>
<hr />
<?php echo form_open('setup/encryption',array('action'=>'post','class'=>'form form-horizontal'));?>

<?php if(!empty($errors)):?>
	<div class="alert alert-danger">
	<b>Error!</b>
	<pre><?php print_r($errors);?></pre>
	</div>
<?php endif; ?>

<div class="row">

<div class="col-md-8">

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Installation Key</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_input(array('type'=>'text','name'=>'installation_key','value'=>'','class'=>'form-control','required'=>'','maxlength'=>100));?>
	</div>
</div>

<hr />

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Source</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_textarea('source','',' class="form-control"')?>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Action</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_dropdown('action',array('encode'=>'Encode','decode'=>'Decode'),'', ' class="form-control"');?>
	</div>
</div>

<div class="form-group">
	<div class="col-md-offset-3 col-sm-offset-4 col-md-9 col-sm-8">
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
</div>

</div>
</div>

<?php echo form_close();?>

<hr />
<div  id="message_viewer" class="well" style="height:250px; overflow: auto;">
<pre style="border: none; background: none;">Loading result...</pre>
</div>

<script>
$(function(){

	
	function recheck(evt){
		evt.preventDefault();
		$('button').prop('disabled', true);

		$('#message_viewer pre').text( 'Loading result...');
		
			$.post('<?php echo site_url('setup/encryption.json')?>', $('form').serialize(), function(rst){
				$('[type=submit]').prop('disabled',false);
				if(rst.answer){
					$('#message_viewer pre').text('Result value:\n'+rst.answer);
					$('#message_viewer').scrollTop( $('#message_viewer pre').height()  );
				}
			},'json').error(function(){

				$('[type=submit]').prop('disabled',false);
				$('#message_viewer pre').text( 'Cannot load result, please try again.');
			});
	}

	$('[type=submit]').on('click', recheck);
})
</script>
