<div class="main-content">
                <div class="main-content-inner">
                    <div class="breadcrumbs ace-save-state" id="breadcrumbs">
                        
                    </div>

                    <div class="page-content">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->
                                <h3>SETUP PROFIL UNIVERSITAS</h3>
<div class="hr hr10 hr-double"></div>
<form method="post" class="form-horizontal"  action="<?php echo Config::get('URL') . 'systemPreference/saveCompanyProfile/'; ?>">
<?php
$counter = 1;
foreach ($this->company as $key => $value) { ?>
    <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label"><?php echo $value->item_name; ?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="<?php echo 'value_' . $counter; ?>" value="<?php echo $value->value; ?>">
      <input type="hidden" name="<?php echo 'item_' . $counter; ?>" value="<?php echo $value->item_name; ?>">
    </div>
  </div>

<?php $counter++;} ?>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>
</form>


<!-- PAGE CONTENT ENDS -->
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.page-content -->
        </div>
      </div><!-- /.main-content -->