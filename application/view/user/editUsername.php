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
                                        <h4 class="card-title mb-4">Ubah Username</h4>
                                       <form action="<?php echo Config::get('URL'); ?>user/editUserName_action" method="post">
                                            <div data-repeater-list="outer-group" class="outer">
                                                <div data-repeater-item class="outer">
                                                    <div class="form-group row mb-4">
                                                        <label for="taskname" class="col-form-label col-lg-2">Full Name</label>
                                                        <div class="col-lg-4">
                                                            <input type="text" class="form-control" value="<?php echo $this->full_name; ?>" disabled>
															
                                                        </div>
														
                                                    </div>
													<div class="form-group row mb-4">
                                                        <label for="taskname" class="col-form-label col-lg-2">Username</label>
                                                        <div class="col-lg-4">
                                                            <input name="user_name" type="text" class="form-control" value="<?php echo $this->user_name; ?>">
															<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                                                        </div>
														
                                                    </div>
                                            </div>
                                        
                                        <div class="row justify-content-end">
                                            <div class="col-lg-10">
                                                <button type="submit" value="Submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
									</form>
                                    </div>
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
