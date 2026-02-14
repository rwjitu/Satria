<div class="main-content">
        <div class="main-content-inner">
        <!-- #section:basics/content.breadcrumbs -->
<?php $this->renderFeedbackMessages(); // Render message success or not?>
<form method="post" class="form-horizontal"  action="<?php echo Config::get('URL') . 'systemPreference/insertAddRelasi/'; ?>">
<div class="table-responsive">
<table class="table table-striped table-bordered table-hover ExcelTable2007">
<tr>
  <th class="heading">No</th>
  <th>Nama Relasi</th>
  <th>No. SK </th>
  <th>Alamat </th>
</tr>
<?php
                for ($i=1; $i <=20; $i++) { ?>
<tr>
  <td align="left" valign="bottom"><?php echo $i; ?></td>
  <td><input type="text" name="relation_name_<?php echo $i; ?>"></td>
  <td><input type="text" name="credential_number_<?php echo $i; ?>"></td>
  <td><input type="text" name="address_<?php echo $i; ?>"></td>
</tr>
<?php } ?>
<tr>
  <td colspan="2"><a href="javascript: history.go(-1)" class="btn btn-sm btn-danger" style="width: 100%;">Cancel</a></td>
  <td colspan="2"><button type="submit" class="btn btn-primary" style="width: 100%;">Save</button></td>
</tr>
</table>
</div><!-- /.table-responsive -->
</form>
        </div><!-- /.main-content-inner -->
      </div><!-- /.main-content -->

<script type="text/javascript">

function finishAndSave()
{
  //Make string from purchased product table
  var cell = document.getElementsByClassName("saved-data");
  var i = 0;
  var product_string = '';
  while(cell[i] != undefined) {
    if (cell[i].tagName === 'SELECT' || cell[i].tagName === 'INPUT') { // because we take value on dropdown value select (number three in fields)
      product_string += cell[i].value + ' --- ';
    } else if (cell[i].innerHTML == 'endRow separator') {
      product_string += ' ___ ';
    } else  {
        product_string += cell[i].innerHTML + ' --- ';
     }
    i++;
  }//end while
  //Send the string to server
  var http = new XMLHttpRequest();
  var url = "<?php echo Config::get('URL'); ?>systemPreference/saveRelasi";
  var params = "transaction_list=" + product_string;
  http.open("POST", url, true);
console.log(params);
  //Send the proper header information along with the request
  http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  http.onreadystatechange = function() {//Call a function when the state changes.
      if(http.readyState === XMLHttpRequest.DONE && http.status == 200) {
          alert(http.responseText);
          resetPage();
      }
  }
  http.send(params);
  //window.print();
  
}
function resetPage() {
    window.location.reload(false); 
}
</script>