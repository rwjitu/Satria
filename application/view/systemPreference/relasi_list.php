<div class="main-content">
        <div class="main-content-inner">
        <!-- #section:basics/content.breadcrumbs -->
<?php
 $this->renderFeedbackMessages(); ?>
          <div class="table-responsive">
          <table class="table table-striped table-bordered table-hover ExcelTable2007">
          <thead>
          <tr>
          <th class="text-center">No</th>
          <th class="text-center">Nama</th>
          <th class="text-center">No. SK</th>
          <th class="text-center">Alamat</th>
          <th class="text-center">Delete</th>
          </tr>
          </thead>

          <tbody>
          <?php
          $no = 1;
          foreach($this->relation_person as $key => $value) {
          echo "<tr>";
          echo '<td class="text-right">' . $no . '</td>';
          echo '<td>' . ucwords($value->relation_name) . '</td>';
          echo '<td>' . $value->credential_number . '</td>';
          echo '<td>' . $value->address . '</td>';
          echo '<td><div class="btn-group btn-corner" role="group">
                        <a href="' . Config::get('URL') . 'delete/remove/relation_person/uid/' . $value->uid . '?forward=' . $_SERVER['REQUEST_URI'] . '" class="btn btn-danger btn-xs" onclick="return confirmation(\'Are you sure to delete?\')">
                        delete
                        </a>
                    </div></td>';
          echo "</tr>";
          $no++;
          }
          ?>
         
          </tbody>
          </table>
          </div><!-- /.table-responsive -->
        </div><!-- /.main-content-inner -->
      </div><!-- /.main-content -->