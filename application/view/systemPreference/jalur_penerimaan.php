<div class="main-content">
                <div class="main-content-inner">
                    <div class="breadcrumbs ace-save-state" id="breadcrumbs">
                        
                    </div>

                    <div class="page-content">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->
                                <?php $this->renderFeedbackMessages(); // Render message success or not?>

                                <h3>Tambah Jalur Penerimaan</h3>

<form method="post" class="form-horizontal"  action="<?php echo Config::get('URL') . 'systemPreference/insertJalurPenerimaan/'; ?>">
  <div class="form-group">
    <label class="col-sm-2 control-label">Nama Penerimaan</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="item_name" name="item_name">
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label">Keterangan</label>
    <div class="col-sm-10">
      <textarea class="form-control" rows="3" name="note"></textarea>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Save</button>
    </div>
  </div>
</form>


<div class="hr hr10 hr-double"></div>

                  <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                            <div class="table-responsive">
                                                <table class="table table-condensed table-striped table-hover table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="4" class="text-center">Daftar Jalur Penerimaan Yang Ada</th>
                                                            </tr>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Nama</th>
                                                                <th>Keterangan</th>
                                                                <th>Delete</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                                $no = 1;
                                                                foreach($this->data as $value) { ?> 
                                                            <tr>
                                                                <td><?php echo $no; ?></td>
                                                                <td><?php echo $value->item_name; ?></td>
                                                                <td><?php echo $value->note; ?></td>
                                                                <td>
                                                                    <div class="btn-group" role="group" aria-label="...">
                                                                        <a href="<?php echo Config::get('URL') . 'delete/remove/system_preference/uid/' . $value->uid . '/?forward=' . $_SERVER['REQUEST_URI'];?>" class="btn btn-danger btn-xs" onclick="return confirmation('Are you sure to delete?');">
                                                                        delete
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php $no++;} ?>
                                                        </tbody>
                                                </table>
                                            </div>
                                    </div><!-- /.col -->
                                </div><!-- /.row -->

<!-- PAGE CONTENT ENDS -->
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.page-content -->
        </div>
      </div><!-- /.main-content -->