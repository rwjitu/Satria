<div class="main-content">
        <div class="main-content-inner">
        <!-- #section:basics/content.breadcrumbs -->

<?php $this->renderFeedbackMessages(); ?>
          <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover ExcelTable2007">
          <thead>
          <tr>
          <th class="text-center">No</th>
          <th class="text-center">Program Studi</th>
          <th class="text-center">Kuota Mahasiswa</th>
          <th class="text-center">Kuota Beasiswa</th>
          </tr>
          </thead>

          <tbody>
          <?php
          $no = 1;
          foreach($this->study_program as $key => $value) {
          echo "<tr>";
          echo '<td>' . $no . '</td>';
          echo '<td>' . $value->study_name . '</td>';
          echo '<td><input type="number" name="quota_student_' . $no . '"></td>';
          echo '<td><input type="number" name="quota_scholarship_' . $no . '">
                  <input type="hidden" name="study_name_' . $no . '"></td>';
          echo "</tr>";
          $no++;
          }
          ?>
         <tr>
  <td colspan="2"><a href="javascript: history.go(-1)" class="btn btn-sm btn-danger" style="width: 100%;">Batal</a></td>
  <td colspan="2"><button type="submit" id="save-button"  class="btn btn-sm btn-primary" style="width: 100%;">Simpan</button></td>
</tr>
          </tbody>
          </table>
          </div><!-- /.table-responsive -->
        </div><!-- /.main-content-inner -->
      </div><!-- /.main-content -->
