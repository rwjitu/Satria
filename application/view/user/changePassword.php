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
                                        <h4 class="card-title mb-4">Ubah Password</h4>
                                       <form method="post" action="<?php echo Config::get('URL'); ?>user/updatePassword" name="new_password_form">
                                            <div data-repeater-list="outer-group" class="outer">
                                                <div data-repeater-item class="outer">
                                                    <div class="form-group row mb-4">
                                                        <label for="taskname" class="col-form-label col-lg-2">Password sekarang</label>
                                                        <div class="col-lg-4">
                                                            <input id="change_input_password_current" class="reset_input form-control" type="password"
															name='user_password_current' pattern=".{6,}" required autocomplete="off"  />															
                                                        </div>
														
                                                    </div>
													<div class="form-group row mb-4">
                                                        <label for="taskname" class="col-form-label col-lg-2">Password Baru</label>
                                                        <div class="col-lg-4">
                                                            <input id="change_input_password_new" class="reset_input form-control" type="password"
															name="user_password_new" pattern=".{6,}" required autocomplete="off" />
                                                        </div>
														
                                                    </div>
													<div class="form-group row mb-4">
                                                        <label for="taskname" class="col-form-label col-lg-2">Ulangi Password</label>
                                                        <div class="col-lg-4">
                                                            <input id="change_input_password_repeat" class="reset_input form-control" type="password"
															name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />
                                                        </div>
														
                                                    </div>
                                            </div>
                                        
                                        <div class="row justify-content-end">
                                            <div class="col-lg-10">
                                                <button type="submit" name="submit_new_password" value="Submit new password" class="btn btn-primary">Update</button>
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


