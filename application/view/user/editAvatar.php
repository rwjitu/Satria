 <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                       
                        <!-- end page title -->
						<div class="row">
                            <div class="col-lg-9">
                                <div class="card">
                                    <div class="card-body">
									<?php $this->renderFeedbackMessages();?>
                                        <h4 class="card-title mb-4">Edit Avatar Image</h4>
                                        <form action="<?php echo Config::get('URL') . 'user/updateAvatar/' . $this->user_id; ?>" method="post" enctype="multipart/form-data">
                                            <div data-repeater-list="outer-group" class="outer">
                                                <div data-repeater-item class="outer">
                                                    <div class="inner-repeater mb-4">
                                                        <div data-repeater-list="inner-group" class="inner form-group mb-0 row">
                                                            <label class="col-form-label col-lg-3">Pilih Avatar Image</label>
                                                            <div  data-repeater-item class="inner col-lg-6 ms-md-auto">
                                                                <div class="mb-3 row align-items-center">                                                                    
                                                                    <div class="col-md-12">
                                                                        <div class="mt-4 mt-md-0">
                                                                            <input name="avatar_file" class="form-control" type="file">
																			<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
                                                                        </div>
                                                                    </div>
                                                                   
                                                                    
                                                                </div>
																
                                                            </div>
                                                        </div>
                                                       
                                                    </div>
                                                   
                                                </div>
                                            </div>
                                        
                                        <div class="row justify-content-end">
                                            <div class="col-lg-10">
                                                <button type="submit" value="Upload" class="btn btn-primary">Unggah</button>
                                            </div>
                                        </div>
									</form>
                                    </div>
                                </div>
                            </div>
							
							 <div class="col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Preview Image</h4>
							 <img src="<?php echo Config::get('URL') . 'avatars/' . $this->user_id . '_medium.jpg'; ?>" alt="hgfh">
							 </div>
							  </div>
							   </div>
							
                        </div>
 </div>
  </div>
 </div>

