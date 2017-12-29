
    <div class="row">
<div class="col-md-offset-4 col-md-4 col-sm-offset-2 col-sm-8">

            <h3><?php echo lang('sign_in_heading')?></h3>
<?php echo form_open('auth/signin', array('role'=>'form'));?>
    <input type="hidden" name="do" value="login" />
    <input type="hidden" name="forward" value="<?php echo forward_url()?>" />
<?php if(isset($errors['loginid_notfound'])){ ?>
        
        <div class="alert alert-danger">
            <strong><?php echo lang('error')?></strong><br /><?php echo lang('signin_account_not_match')?>
        </div>
<?php }?>

<?php if(isset($errors['password_incorrect'])){ ?>
        
        <div class="alert alert-danger">
            <strong><?php echo lang('error')?></strong><br /><?php echo lang('signin_password_incorrect')?>
        </div>
<?php }?>
        <div class="form-group">
            <input class="form-control" placeholder="<?php echo lang('field_login_name')?>" name="login_name" type="text" value="<?php echo data('login_name',$data) ?>" autofocus />
        </div>
        <div class="form-group">
            <input class="form-control" placeholder="<?php echo lang('field_password')?>" name="login_pass" type="password" value="" />
        </div>


        <!-- Change this to a button or input when using this as a form -->
        <button type="submit" class="btn btn-success btn-block"><?php echo lang('sign_in')?></button>
</form>
        <hr />
        <p><a href="<?php echo site_url('auth/forgetPassword')?>"><?php echo lang('forget_password')?>?</a></p>
        </div>
    </div>
