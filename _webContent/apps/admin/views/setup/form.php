<?php 

$this->asset->js_import(base_url('assets/js/jquery-1.10.2.min.js'));
?>

<h1>Setup</h1>
<h2>Step 2 - Admin user configuration</h2>
<hr />
<?php echo form_open('setup/process',array('action'=>'post','class'=>'form form-horizontal'));?>

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
	<?php echo form_input(array('type'=>'text','name'=>'installation_key','value'=>data('installation_key',$data),'class'=>'form-control','required'=>'','maxlength'=>100));?>
	</div>
</div>

<hr />

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Name</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_input(array('type'=>'text','name'=>'name','value'=>data('name',$data),'class'=>'form-control','required'=>'','maxlength'=>100));?>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Login ID</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_input(array('type'=>'text','name'=>'login_name','value'=>data('login_name',$data),'class'=>'form-control','required'=>'','maxlength'=>24));?>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Login Password</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_input(array('type'=>'password','name'=>'login_pass','value'=>data('login_pass',$data),'class'=>'form-control','required'=>'','maxlength'=>32));?>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-md-3 col-sm-4">Email</label>
	<div class="col-md-9 col-sm-8">
	<?php echo form_input(array('type'=>'email','name'=>'email','value'=>data('email',$data),'class'=>'form-control','required'=>'','maxlength'=>200));?>
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


<script>
$(function(){

	$('form').on('submit', function(evt){
		var frm = this;
		evt.preventDefault();

		var data = $(this).serialize();

		var $elms = $('button',frm);

		$elms.prop('disabled',true);

		$.post('<?php echo site_url('setup/process.json')?>', data, function(rst){
			if(rst.done){
				location.href = '<?php echo site_url('setup/completed')?>';
				return;
			}

			$elms.prop('disabled',true);
			if(rst.message){
				$('#message_viewer').text( rst.message.join('\r\n') );
			}
		},'json');

	});
})
</script>
