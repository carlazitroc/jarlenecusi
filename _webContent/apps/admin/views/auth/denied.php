<?php $this->asset
  ->css_code('
    body {
      background-color: #f5f5f5;
    }
    .container, #page-wrapper{

      padding-top: 40px;
      padding-bottom: 40px;
    }');
    ?>
    <div class="row">
<div class="col-md-offset-4 col-md-4 col-sm-offset-2 col-sm-8">
        <div class="panel panel-default">
          <div class="panel-body">
            <h2 class="form-signin-heading">Sorry</h2>
            <p>Your session does not have enough permission or deneid by system setting.</p>
            <?php if(defined('PROJECT_DEBUG_KEY') && $this->input->get('debug') == PROJECT_DEBUG_KEY):?>
            <p>&nbsp;</p>
            <p><b>Debug message:</b><br />
              <pre>Requring scopes...
<?php print_r($scopes);?>
              </pre>
            </p>
            <?php endif; ?>
            
          </div>
        </div>
      </div>
    </div>
  </div> <!-- /container -->
