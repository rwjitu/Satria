<div class="main-content">
                <div class="main-content-inner">
                    <div class="breadcrumbs ace-save-state" id="breadcrumbs">
                        
                    </div>

                    <div class="page-content">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->
                                <?php $this->renderFeedbackMessages();?>
                                <h3>Tambah Daftar Kategori Produk Jadi</h3>

<form method="post" class="form-horizontal"  action="<?php echo Config::get('URL') . 'systemPreference/changeSalary/'; ?>">
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-3 control-label">Uang Transport Harian</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" name="uang_transport" value="<?php echo $this->uang_transport->value; ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword3" class="col-sm-3 control-label">Denda Keterlambatan</label>
    <div class="col-sm-8">
      <input type="text" class="form-control"  name="attendance_late_fine" placeholder="biaya denda keterlambatan datang" value="<?php echo $this->attendance_late_fine->value; ?>">
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword3" class="col-sm-3 control-label">Bonus Bulanan Jika Tidak Pernah Telat</label>
    <div class="col-sm-8">
      <input type="text" class="form-control"  name="never_late_per_month_reward" placeholder="Bonus jika pegawai tidak pernah telat sama sekali dalam satu bulan" value="<?php echo $this->never_late_per_month_reward->value; ?>">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-default">Save</button>
    </div>
  </div>
</form>



<!-- PAGE CONTENT ENDS -->
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.page-content -->
        </div>
      </div><!-- /.main-content -->