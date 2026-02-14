				<div class="row">
				 <?php $this->renderFeedbackMessages(); ?>

                <div class="col-sm-6">
                  <section class="panel panel-default">
                    <header class="panel-heading font-bold">Form Ganti Photo</header>
                    <div class="panel-body">
                    <div class="box">
				
                    <form action="<?php echo Config::get('URL') . 'user/updateAvatarMhs/' . $this->user_id; ?>" method="post" enctype="multipart/form-data">
                        <label for="avatar_file">Pilih foto yang akan diupload (Ukuran 4x6 hitam putih maksimal 2 MB)</label>
                        <input name="avatar_file" type="file" class="filestyle" data-icon="false" data-classButton="btn btn-default" data-classInput="form-control inline v-middle input-s" required />
						
                        <!-- max size 5 MB (as many people directly upload high res pictures from their digital cameras) -->
                        <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
                        <br>
                        <input type="submit" value="Upload" class="btn btn-info" />
                    </form>
					</div>
                      </form>
                    </div>
                  </section>
                </div>
				<div class="col-sm-6">
                  <section class="panel panel-default">
                    <header class="panel-heading font-bold">Delete Photo</header>
                    <div class="panel-body">
                      <form class="bs-example form-horizontal">
                        <div class="form-group">
						<label class="col-lg-4 control-label">Hapus Photo</label>
                          <span class="help-block m-b-none"><a href="<?php echo Config::get('URL'); ?>user/deleteAvatarMhs_action" class="btn btn-sm btn-danger">Delete Photo</a></span>
                        </div>
                      </form>
                    </div>
                  </section>
                </div>
            

<script src="<?php echo Config::get('URL'); ?>flat/js/file-input/bootstrap-filestyle.min.js"></script>