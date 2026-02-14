<?php

/**
 * RegisterController
 * Register new user
 */
class DaftarController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class. The parent::__construct thing is necessary to
     * put checkAuthentication in here to make an entire controller only usable for logged-in users (for sure not
     * needed in the RegisterController).
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Register page
     * Show the register form, but redirect to main-page if user is already logged-in
     */
   
    /**
     * Register page action
     * POST-request after form submit
     */
    public function TambahPegawaiBaru()
    {
      $registration_successful = DaftarModel::TambahPegawaiBaru();

        if ($registration_successful) {
            Redirect::to('pegawai/dataPegawai');
        } else {
            Redirect::to('pegawai/dataPegawai');
        }		
    }

	public function importpegawai() {
		
        $this->View->render('pegawai/import', array(
                'title' => 'Import ',
                'activelink1' => 'Pegawai',
                'activelink2' => 'Import'
				
        ));
        
    }
	
	public function importData()
    {
        $import_successful = ImportModel::importData();

        if ($import_successful) {
            Redirect::to('daftar/importpegawai');
        } else {
            Redirect::to('daftar/importpegawai');
        }
    }	
    
}
