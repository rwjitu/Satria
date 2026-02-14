<div class="main-content">
                <div class="main-content-inner">
                    <div class="page-content">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->


<h3>SETING GELOMBANG PENDAFTARAN</h3>
<?php $this->renderFeedbackMessages(); // Render message success or not?>

<div class="hr hr10 hr-double"></div>
<form method="post" class="form-horizontal"  action="<?php echo Config::get('URL') . 'systemPreference/insertGelombangPendaftaran/'; ?>">

    <div class="form-group">
      <label class="col-sm-3 control-label">Nama Gelombang Pendaftaran</label>
      <div class="col-sm-9">
        <input type="text" class="form-control" name="item_name">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Tangal Mulai</label>
      <div class="col-sm-9">
        <input type="date" class="form-control datepicker" data-date-format="yyyy-mm-dd" name="date_start">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Tangal Berakhir</label>
      <div class="col-sm-9">
        <input type="date" class="form-control datepicker" data-date-format="yyyy-mm-dd" name="date_end">
      </div>
    </div>

  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <button type="submit" class="btn btn-primary">Save</button>
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
                                                                <th colspan="6" class="text-center">Daftar Gelombang Pendaftaran</th>
                                                            </tr>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Nama Gelombang</th>
                                                                <th>Tanggal Mulai</th>
                                                                <th>Tanggal Berakhir</th>
                                                                <th>Status</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                                $no = 1;
                                                                foreach ($this->data as $key => $value) {
                                                             ?> 
                                                            <tr>
                                                                <td><?php echo $no; ?></td>
                                                                <td><?php echo $value->item_name; ?></td>
                                                                <td><?php echo $value->date_start; ?></td>
                                                                <td><?php echo $value->date_end; ?></td>
                                                                <td><?php 
                                                                if ($value->is_active == 1) {
                                                                  echo 'Aktiv';
                                                                }
                                                                 ?></td>
                                                                <td>
                                                                    <div class="btn-group btn-corner" role="group" aria-label="...">
                                                                        <a href="<?php echo Config::get('URL') . 'delete/remove/gelombang_pendaftaran/uid/' . $value->uid . '/?forward=' . $_SERVER['REQUEST_URI'];?>" class="btn btn-danger btn-xs" onclick="return confirmation('Are you sure to delete?');">
                                                                        delete
                                                                        </a>
                                                                        <a href="<?php echo Config::get('URL') . 'systemPreference/activateGelombangPendaftaran/' . $value->uid;?>" class="btn btn-info btn-xs" onclick="return confirmation('Are you sure to activate gelombang pendaftaran ini?');">
                                                                        Aktivate
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