<?php


class SystemPreferenceController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // this entire controller should only be visible/usable by logged in users, so we put authentication-check here
        Auth::checkAuthentication();
    }

    public function companyProfile() {
        $category = "SELECT * FROM `system_preference` WHERE `category` = 'company_identification' ORDER BY `uid` ASC";
        $this->View->render('systemPreference/companyProfile',
            array(
                'title' => 'Profil Universitas',
                'activelink1' => 'System Preference',
                'activelink2' => 'company profile',
                'company' => GenericModel::rawSelect($category),
                )
        );
    }

    public function saveCompanyProfile()
    {
        $totalPost = count($_POST);
        for ($i=1; $i <= $totalPost; $i++) { 
            if (!empty(Request::post('item_' . $i))) {
                    $update = array(
                        'value'    => Request::post('value_' . $i)
                        );
                    $condition = "`item_name` = '" . Request::post('item_' . $i) . "'";
                    GenericModel::update('system_preference', $update, $condition);
                }
        }

        Redirect::to('systemPreference/companyProfile/');
    }


    public function studyProgram() {
        $study_program = "SELECT * FROM `study_program` WHERE 1 ORDER BY `uid` ASC";
        $this->View->render('systemPreference/study_program',
            array(
                'title' => 'Seting Program Studi',
                'activelink1' => 'System Preference',
                'activelink2' => 'faculty',
                'study_program' => GenericModel::rawSelect($study_program),
                )
        );
    }

    public function insertStudyProgram()
    {
        if (!empty(Request::post('study_name'))) {
                $insert = array(
                        'uid'    => GenericModel::guid(),
                        'study_name' => Request::post('study_name'),
                        'quota_student' => Request::post('quota_student'),
                        'quota_scholarship' => Request::post('quota_scholarship'),
						'fakultas' => Request::post('fakultas'),
						'gelar' => Request::post('gelar'),
                        );
                GenericModel::insert('study_program', $insert);
            }
        
        Redirect::to('systemPreference/studyProgram/');
    }

    public function updateStudyProgram()
    {
        if (!empty(Request::post('study_name'))) {
                $update = array(
                        'study_name' => Request::post('study_name'),
                        'quota_student' => Request::post('quota_student'),
                        'quota_scholarship' => Request::post('quota_scholarship'),
						'fakultas' => Request::post('fakultas'),
						'gelar' => Request::post('gelar'),
                        );
                $uid = Request::post('uid');
                GenericModel::update('study_program', $update, "`uid` = '{$uid}'");
            }
        
        Redirect::to('systemPreference/studyProgram/');
    }


    public function kuota() {
        $header_script = '<link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/excel-2007.css" media="screen"/>';
        $this->View->render('systemPreference/kuota',
            array(
                'header_script' => $header_script,
                'title' => 'Seting Kuota',
                'activelink1' => 'System Preference',
                'activelink2' => 'kuota',
                'study_program' => GenericModel::getAll('study_program'),
                )
        );
    }

    public function relasiList() {
        $header_script = '<link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/excel-2007.css" media="screen"/>';
        $this->View->render('systemPreference/relasi_list',
            array(
                'header_script' => $header_script,
                'title' => 'Daftar Relasi',
                'activelink1' => 'System Preference',
                'activelink2' => 'relasi',
                'activelink3' => 'relasi list',
               'relation_person' => GenericModel::getAll('relation_person')
                )
        );
    }
    

    public function addRelasi() {
        $header_script = '<link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/excel-2007.css" media="screen"/>';
        $this->View->render('systemPreference/add_relasi',
            array(
                'header_script' => $header_script,
                'title' => 'Tambah Relasi',
                'activelink1' => 'System Preference',
                'activelink2' => 'relasi',
                'activelink3' => 'tambah relasi',
                
                )
        );
    }

    public function insertAddRelasi() {
        for ($i=1; $i <= 20 ; $i++) {

            if (!empty(Request::post('relation_name_' . $i))) {
                $insert=array(
                    'uid'    => GenericModel::guid(),
                    'relation_name'    => Request::post('relation_name_' . $i),
                    'credential_number'    => Request::post('credential_number_' . $i),
                    'address'    => Request::post('address_' . $i),
                    );
                GenericModel::insert('relation_person', $insert);
            }
        }
        Redirect::to('systemPreference/relasiList/');
    }

    public function gelombangPendaftaran() {
        $data = "SELECT * FROM `gelombang_pendaftaran` WHERE 1 ORDER BY `date_start` DESC";

        $header_script = '<link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/excel-2007.css" media="screen"/>
            <link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/datepicker.css" media="screen"/>
        ';
        $footer_script = '<script src="' . Config::get('URL') . 'bootstrap-3.3.7/js/bootstrap-datepicker.js"></script>
        <script>
        $(".datepicker").datepicker(); //date picker
        
        
        </script>';

        $this->View->render('systemPreference/gelombang_pendaftaran',
            array(
                'header_script' => $header_script,
                'footer_script' => $footer_script,
                'title' => 'Seting Gelombang Pendaftaran',
                'activelink1' => 'System Preference',
                'activelink2' => 'gelombang pendaftaran',
                'data' => GenericModel::rawSelect($data),
                )
        );
    }

    public function insertGelombangPendaftaran()
    {
        if (!empty(Request::post('item_name'))) {
                $insert = array(
                        'uid'    => GenericModel::guid(),
                        'item_name' => Request::post('item_name'),
                        'date_start' => date("Y-m-d", strtotime(Request::post('date_start'))),
                        'date_end' => date("Y-m-d", strtotime(Request::post('date_end'))),
                        );
                GenericModel::insert('gelombang_pendaftaran', $insert);
            }
        
        Redirect::to('systemPreference/gelombangPendaftaran/');
    }

    
    public function activateGelombangPendaftaran($uid)
    {
        $update = array(
                        'is_active'    => 1,
                        );

        GenericModel::update('gelombang_pendaftaran', $update, "`uid` = '{$uid}'");
        $update = array(
                        'is_active'    => 0,
                        );

        GenericModel::update('gelombang_pendaftaran', $update, "`uid` != '{$uid}'");

        Redirect::to('systemPreference/gelombangPendaftaran/');
    }

    public function jalurPenerimaan() {
        $data = "SELECT * FROM `system_preference` WHERE `category` = 'jalur_penerimaan'";
        $this->View->render('systemPreference/jalur_penerimaan',
            array(
                'title' => 'Seting Jalur Penerimaan',
                'activelink1' => 'System Preference',
                'activelink2' => 'Jalur Penerimaan',
                'data' => GenericModel::rawSelect($data),
                )
        );
    }

    public function insertJalurPenerimaan()
    {
        if (!empty(Request::post('item_name'))) {
                $insert = array(
                        'uid'    => GenericModel::guid(),
                        'category' => 'jalur_penerimaan',
                        'item_name' => Request::post('item_name'),
                        'note' => Request::post('note'),
                        );
                GenericModel::insert('system_preference', $insert);
            }
        
        Redirect::to('systemPreference/jalurPenerimaan/');
    }

    public function jalurPendaftaran() {
        $data = "SELECT * FROM `system_preference` WHERE `category` = 'jalur_pendaftaran'";
        $this->View->render('systemPreference/jalur_pendaftaran',
            array(
                'title' => 'Seting Jalur Pendaftaran',
                'activelink1' => 'System Preference',
                'activelink2' => 'Jalur Pendaftaran',
                'data' => GenericModel::rawSelect($data),
                )
        );
    }

    public function insertJalurPendaftaran()
    {
        if (!empty(Request::post('item_name'))) {
                $insert = array(
                        'uid'    => GenericModel::guid(),
                        'category' => 'jalur_pendaftaran',
                        'item_name' => Request::post('item_name'),
                        'note' => Request::post('note'),
                        );
                GenericModel::insert('system_preference', $insert);
            }
        
        Redirect::to('systemPreference/jalurPendaftaran/');
    }

	public function activateProdi($uid)
    {
        $update = array(
                        'is_active'    => 1,
                        );

        GenericModel::update('study_program', $update, "`uid` = '{$uid}'");

        Redirect::to('systemPreference/studyProgram/');
    }

    public function unactivateProdi($uid)
    {
        $update = array(
                        'is_active'    => 0,
                        );

        GenericModel::update('study_program', $update, "`uid` = '{$uid}'");

        Redirect::to('systemPreference/studyProgram/');
    }
}