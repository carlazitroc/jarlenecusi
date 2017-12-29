<?php 

$this->asset
	-> js_embed('auth/forget_password.js.php', NULL, NULL, 'body_foot')
	-> js_import(base_url('assets/js/jquery-1.10.2.min.js'));
;
?>

	<div class="container">
<div class="row">
<div class="col-md-offset-4 col-md-4 col-sm-offset-2 col-sm-8">

<h3>Forget Password</h3>
	
<div data-toggle="success" class="alert alert-success  hidden">
  <p>We sent you an email about how to reset your password. </p>

	<p><a href="<?php echo site_url('auth/signin')?>"><i class="fa fa-angle-left"></i> <?php echo lang('sign_in')?></a></p>
</div>
	
<div data-toggle="error" class="alert alert-danger  hidden">
  <div class="text"></div>
</div>

<form name="forget_password" action="<?php echo site_url('auth/forgetPassword/submit/'.$request_id)?>" method="post" class="form">
	<input type="hidden" name="do" value="request" />
	<div class="form-group">
		<label class="control-label">Login ID</label>
			<div class="">
			<input class="form-control" type="text" name="login_name" maxlength="100" />
			<div class="help-block">
				Your Login ID.
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label"><?php echo lang('field_email')?></label>
			<div class="">
			<input class="form-control" type="text" name="email" maxlength="100" />
			<div class="help-block">
				Your registered email address
			</div>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label">Captcha Code</label>
		<div class="">
		<input class="form-control" type="text" name="captcha_answer" placeholder="Enter the value shown in the below" maxlength="12" />
		<div class="help-block">
			<a href="<?php echo site_url('auth/forgetPassword/captcha/'.$request_id)?>" class="captch-code"><img src="<?php echo site_url('auth/forgetPassword/captcha/'.$request_id)?>" /></a><br />
			<small>(Click the image to refresh)</small>
		</div>
		</div>
	</div>

	<div class="form-group">
			<button type="submit" class="btn btn-success btn-lg "><?php echo lang('submit')?></button>
	</div>
	<p><a href="<?php echo site_url('auth/signin')?>"><i class="fa fa-angle-left"></i> <?php echo lang('sign_in')?></a></p>
</form>
	</div>


				</div><!--/login-box-->		
		</div><!--/row-->		
		
	</div><!--/container-->
