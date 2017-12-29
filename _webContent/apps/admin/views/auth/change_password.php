
<?php if($is_dialog){ ?>
	<div class="modal-header">
		<h4><?php echo lang('change_password');?></h4>
	</div>

	<div class="modal-footer float">
		<button type="button" class="btn btn-default btn-window-close right" re-action="close"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
		<button type="button" class="btn btn-item-select" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
	</div>
	<div class="modal-body">
		<?php } ?>


		<div class="row">
			<?php if(!$is_dialog){ ?>
			<div class="col-xs-12">
				<div class="page-header">
					<h2><?php echo lang('change_password');?></h2>
				</div>
			</div>
			<?php }?>


<div class="col-xs-12">

<?php if(isset($result) && $result == 'saved'){?>
<div class="alert alert-success alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<strong>Well done!</strong> Your password has been changed.
</div>
<?php }?>

<?php if(isset($errors['old_password_incorrect'])){?>

<div class="alert alert-danger">
<strong>Error!</strong> You entered password does not match with database. 
</div>
<?php } ?>
<?php if(isset($errors['same_password'])){?>

<div class="alert alert-warning">
	<strong>Warning!</strong> You entered password is same as database. 
</div>
<?php } ?>
<?php if(isset($errors['new_password_invalid'])){?>
	
	<div class="alert alert-warning">
		<strong>Warning!</strong> You entered password is invalid.
	</div>
	<?php } ?>
	<?php if(isset($errors['retype_password_incorrect'])){?>
		
		<div class="alert alert-danger">
			<strong>Error!</strong> Re-type password does not match with your new password.
		</div>
		<?php } ?>
		<form action="" method="post" class="form">
			<input type="hidden" name="do" value="change" />
			<div class="form-group">
				<label class="control-label col-sm-2">Current Password</label>
				<div class="col-sm-10">
					<input class="form-control" type="password" name="old_password" />
					<div class="help-block">
						Please enter your valid password to make changes.
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">New Password</label>
				<div class="col-sm-10">
					<input class="form-control" type="password" name="new_password" />
					<div class="help-block">
						Enter your new password. Accept any letter and numeric characters.
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2">Re-type Password</label>
				<div class="col-sm-10">
					<input class="form-control" type="password" name="retype_new_password" />
					<div class="help-block">
						Re-enter your new password to confirm you entered correct password.
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-success btn-lg "><?php echo lang('change')?></button>
				</div>
			</div>
		</form>
	</div>

</div>
<?php if($is_dialog){?>
</div>
<?php } ?>