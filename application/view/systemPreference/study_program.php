<div class="main-content">
  <div class="main-content-inner">
    <div class="breadcrumbs ace-save-state" id="breadcrumbs">

    </div>

    <div class="page-content">
      <div class="row">
        <div class="col-xs-12">
          <!-- PAGE CONTENT BEGINS -->

          <div class="row">

<?php $this->renderFeedbackMessages(); ?>

            <div class="col-xs-12 col-sm-12">
              <form method="post"  action="<?php echo Config::get('URL') . 'systemPreference/insertStudyProgram/'; ?>">
                <div class="panel panel-primary">
                  <div class="panel-heading">
                    <h3 class="panel-title">
                      Tambah Program Studi
                    </h3>
                  </div>
                  <table class="table table-striped table-bordered table-hover">
                    <tr>
                      <td>
                        <input type="text" class="form-control" name="study_name" placeholder="Nama Program Studi">
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="number" class="form-control" name="quota_student" placeholder="Jumlah Kuota Reguler">
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="number" class="form-control" name="quota_scholarship" placeholder="Jumlah Kuota Cadangan">
                      </td>
                    </tr>
					<tr>
                      <td>
                        <input type="text" class="form-control" name="fakultas" placeholder="Fakultas">
                      </td>
                    </tr>
					<tr>
                      <td>
                        <input type="text" class="form-control" name="gelar" placeholder="Gelar">
                      </td>
                    </tr>
                  </table>

                  <div class="panel-footer">
                    <button class="btn btn-sm btn-primary">
                      <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true" aria-label="save"></span>
                      Save
                    </button>
                  </div>
                </div>
              </form>
            </div><!-- /.col-sm-6 -->
          </div><!-- ./row -->
          <div class="hr hr10 hr-double"></div>
          <div class="row">


            <div class="col-xs-12 col-sm-12">
              <div class="table-responsive">
                <table class="table table-condensed table-striped table-hover table-bordered">
                  <thead>
                    <tr>
                      <th colspan="6" class="text-center">Daftar Program Studi</th>
                    </tr>
                    <tr>
                      <th>#</th>
                      <th>Nama</th>
                      <th>Kuota Reguler</th>
                      <th>Kuota Cadangan</th>
					   <th>Fakultas</th>
					   <th>Gelar</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $no = 1;
                    foreach($this->study_program as $value) {
                      if (isset($_GET['edit_uid']) AND $_GET['edit_uid'] == $value->uid) {
                        echo '<tr class="info" autofocus>';
                        echo '<td><form method="post"  action="' . Config::get('URL') . 'systemPreference/updateStudyProgram/">' . $no . '</td>';
                        echo '<td><input type="text" class="form-control" name="study_name" value="' . $value->study_name . '"></td>';
                        echo '<td class="text-right"><input type="text" class="form-control" name="quota_student" value="' . $value->quota_student . '"></td>';
                        echo '<td class="text-right"><input type="text" class="form-control" name="quota_scholarship" value="' . $value->quota_scholarship . '"><input type="hidden" name="uid" value="' . $value->uid . '"></td>';
						echo '<td><input type="text" class="form-control" name="fakultas" value="' . $value->fakultas . '"></td>';
						echo '<td><input type="text" class="form-control" name="gelar" value="' . $value->gelar . '"></td>';
                        echo '<td>
                          <div class="btn-group btn-corner" role="group">
                            <button class="btn btn-sm btn-success">
                              <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true" aria-label="save"></span>
                              Save
                            </button>
                          </div>
                          </form>
                          </td>
                          </tr>';
                      } else {
                    ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo $value->study_name; ?></td>
                      <td class="text-right"><?php echo $value->quota_student; ?></td>
                      <td class="text-right"><?php echo $value->quota_scholarship; ?></td>
					  <td class="text-right"><?php echo $value->fakultas; ?></td>
					  <td class="text-right"><?php echo $value->gelar; ?></td>
                      <td class="text-right">
                        <?php 
                          if ($value->is_active == 1) {
                            echo 'Aktif';
                          } else {
                            echo 'not active';
                          }
                        ?>  
                          </td>
                      <td>
                        <div class="btn-group btn-corner" role="group" aria-label="...">
                          <a href="<?php echo Config::get('URL') . 'systemPreference/studyProgram/?edit_uid=' . $value->uid;?>" class="btn btn-info btn-xs">
                            Edit
                          </a>
                          <?php if ($value->is_active == 1) { ?>
                            <a href="<?php echo Config::get('URL') . 'systemPreference/unactivateProdi/' . $value->uid;?>" class="btn btn-warning btn-xs">
                            &nbsp; &nbsp; Tutup&nbsp; &nbsp;
                          </a>
                          <?php } else { ?>
                            <a href="<?php echo Config::get('URL') . 'systemPreference/activateProdi/' . $value->uid;?>" class="btn btn-success btn-xs">
                            &nbsp; &nbsp; Buka &nbsp; &nbsp;
                          </a>
                           <?php } ?>
                          <a href="<?php echo Config::get('URL') . 'delete/remove/study_program/uid/' . $value->uid . '/?forward=' . $_SERVER['REQUEST_URI'];?>" class="btn btn-danger btn-xs" onclick="return confirmation('Are you sure to delete?');">
                            Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                    <?php } $no++;} ?>
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