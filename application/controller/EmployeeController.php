<?php


class EmployeeController extends Controller
{

    /**
     * Construct this object by extending the basic Controller class
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions. If all of your pages should only
        // be usable by logged-in users: Put this line into libs/Controller->__construct
        Auth::checkAuthentication();
    }

    /**
     * This method controls what happens when you move to /note/index in your app.
     * Gets all notes (of the user).
     */
    public function index()
    {
        if(isset($_GET['find'])) {
            $find = strtolower(Request::get('find')); //lower case string to easily (case insensitive) remove unwanted characters
            $terms = explode(" ", trim($find));
            $first = true;
            $string_search = '';
            foreach($terms as $term)
                {
                    if($term != '') {
                        if(!$first) $string_search .= " OR ";
                          $string_search .= "`users`.`user_name` LIKE '%".trim($term)."%' OR `full_name` LIKE '%".trim($term)."%'";
                          $first = false;
                    }
                }
            $where = $string_search . " AND `users`.`user_provider_type` = 'employee' AND `users`.`is_deleted` != 1 GROUP BY `users`.`user_name` ORDER By `users`.`full_name`";
            $sql = "SELECT
                        `users`.`uid`,
                        `users`.`user_name`,
                        `users`.`full_name`,
                        `users`.`department`,
                        `users`.`grade`,
                        CONCAT(`users`.`address_street`, ". ", `users`.`address_city`,  ". ", `users`.`address_state`) AS `address`
                    FROM 
                        `users`
                    WHERE
                        $where ASC";
            $contact = GenericModel::rawSelect($sql);
        } else {
            $field = '`users`.`uid`,
                    `users`.`user_name`,
                    `users`.`full_name`,
                    `users`.`department`,
                    `users`.`grade`,
                    CONCAT(`users`.`address_street`, ". ", `users`.`address_city`,  ". ", `users`.`address_state`) AS `address`';
            $table = '`users`';
            $where = "`users`.`user_provider_type` = 'employee' AND `users`.`is_deleted` = 0";
            $contact = GenericModel::getAll($table, $where, $field);
        }

        $header_script = '<link rel="stylesheet" href="' . Config::get('URL') . 'bootstrap-3.3.7/css/excel-2007.css" media="screen"/>';
        $this->View->render('employee/index',
              array(
                
                'title' => 'Daftar Pegawai',
                'activelink1' => 'Employee',
                'activelink2' => 'daftar pegawai',
                'contact' => $contact
            )
        );
    }
	
	
    public function detail($uid)
    {
		 Auth::isInSession('user_provider_type', 'employee');
        $contact = "SELECT `users`.*  FROM `users` WHERE `users`.`uid` = '$uid' AND `users`.`is_deleted` = 0 LIMIT 1";
        $uploaded_file = "SELECT `item_name`, `item_id`, `value`, `uid`, `note`  FROM `upload_list` WHERE `category` =  'employee' AND `item_id` = '{$uid}' AND `is_deleted` = 0";
       

        $this->View->render('employee/detail',
                array(
                'title' => 'Employee Detail ',
                'activelink1' => 'Employee',
                'activelink2' => 'daftar pegawai',
               
                'contact' => GenericModel::rawSelect($contact, false),
                'uploaded_file' => GenericModel::rawSelect($uploaded_file),
                )
            );
    }

	
	
    public function updateEmployee($uid)
    {
        //Start make log
        $oldData         = GenericModel::getOne('users', "`uid` = '$uid'", 'log');
        $post_array      = $_POST; // get all post array
        $log             = json_encode($_POST); // change to json to easily replaced like string
        $log             = str_replace('","', '<br />', $log);
        $log             = str_replace('":"', ' : ', $log);
        $log             = str_replace('_', ' ', $log);
        $log             = str_replace('{"', '', $log);
        $log             = str_replace('"}', '', $log);
        $log             = '<li><span class="badge badge-grey">' . SESSION::get('uid') .'</span> edit employee:<br />' . $log . '<br>(' . date("Y-m-d") . ')</li>' . $oldData->log;

        $custom_array = array(
                        'log'    => $log,
                        'modifier_id'    => SESSION::get('uid'),
                        'modified_timestamp'    => date("Y-m-d H:i:s")
                        );
        $update = array_merge($post_array, $custom_array);
        GenericModel::update('users', $update, "`uid` = '{$uid}'");
        Redirect::to('employee/detail/' . $uid);
    }

    /**
     * Handles the entire registration process for DEFAULT users (not for people who register with
     * 3rd party services, like facebook) and creates a new user in the database if everything is fine
     *
     * @return boolean Gives back the success status of the registration
    */
    public static function registerNewEmployee()
    {
        //validate name
        if (empty(Request::post('full_name'))) {
            Session::add('feedback_negative', Text::get('INSERT_FAILED'));
            Redirect::to('employee/index'); exit;
        }

        // clean the input
        $full_name = strtolower(Request::post('full_name'));
        $email = Request::post('email');
        $phone = FormaterModel::getNumberOnly(Request::post('phone'));
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        // create user_name
        $name = trim($full_name);
        $name_count = str_word_count($name);
        $name_array = str_word_count($name, 1);

        if ($name_count > 2) {
            $name = substr($name, 0, 1).substr($name_array[1], 0, 1).substr($name_array[2], 0, 1);
        } else if ($name_count == 2) {
            $name = substr($name, 0, 1).substr($name_array[1], 0, 2);
        } else {
            $name = substr($name, 0, 3);
        }

        $query = "SELECT `user_name` AS max FROM `users` WHERE `user_name` LIKE '%$name%' ORDER BY user_name DESC LIMIT 1";
        $max = GenericModel::rawSelect($query, false);
        $max = (int) FormaterModel::getNumberOnly($max->max) +1;
        $user_name = $name . $max;

        // stop registration flow if registrationInputValidation() returns false (= anything breaks the input check rules)
        $validation_result = EmployeeModel::registrationInputValidation($user_name, $full_name, $user_password_new, $user_password_repeat, $email, $phone);
        if (!$validation_result) {
            Redirect::to('employee/index'); exit;
        }

        // crypt the password with the PHP 5.5's password_hash() function, results in a 60 character hash string.
        // @see php.net/manual/en/function.password-hash.php for more, especially for potential options
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        // check if username already exists
        if (EmployeeModel::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', 'Maaf username sudah dipakai orang lain');
            Redirect::to('employee/index'); exit;
        }

        // check if email already exists
        if (UserModel::doesEmailAlreadyExist($email)) {
            Session::add('feedback_negative', 'Alamat email sudah pernah dipakai');
            Redirect::to('employee/index'); exit;
        }

        // check if email already exists
        if (UserModel::doesPhoneAlreadyExist($phone)) {
            Session::add('feedback_negative', 'Nomer telpon sudah pernah dipakai');
            Redirect::to('employee/index'); exit;
        }

        // generate random hash for email verification (40 char string)
        $user_activation_hash = sha1(uniqid(mt_rand(), true));

        // create data user
        if (!EmployeeModel::writeNewUserToDatabase($user_name, $full_name, $user_password_hash, $email, $phone, $user_activation_hash)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            Redirect::to('employee/index'); exit;
        }

         Redirect::to('employee/detail/' . $user_name);
    }

    public function deleteEmployee($user_name)
    {
        if (SESSION::get('user_account_type') > 55) { //Make sure only previleged user can delete this data
           $update = array(
                        'is_deleted'      =>  1,
                        'modifier_id'    => SESSION::get('user_name'),
                        'modified_timestamp'    => date("Y-m-d H:i:s")
                        );
           GenericModel::update('users', $update, "`user_name` = '$user_name'");
           Redirect::to(Request::get('forward'));
        } else {
            Redirect::to(Request::get('forward'));
        }
    }

    
    

    /**
     * Perform the upload image
     * POST-request
     */
    public function uploadImage($uid = null)
    {
        if (empty($uid)) {
            Redirect::to('employee/detail/' . $uid);
            Session::add('feedback_negative', 'GAGAL!, upload file tidak berhasil');
        }

        $image_name = 'file_name';
        $image_rename = Request::post('image_name');
        $destination = 'employee';
        $note = Request::post('note');
        UploadModel::uploadImage($image_name, $image_rename, $destination, $uid, $note);
        Redirect::to('employee/detail/' . $uid);
    }

     /**
     * Perform the upload pdf, xlsx, doc, docx
     * POST-request
     */
	 public function verifyPayment()
    {
        $uid = Request::post('uid');
        $full_name = Request::post('full_name');
		$update = array(
                        'is_data_verified'    => 1
                );
        // Send Status insert to front end
        GenericModel::update('users', $update, "`uid` = '{$uid}'");

        self::VerificationDataEmail($uid);
        //feedback for ajax request
        $feedback_positive = Session::get('feedback_positive');
        $feedback_negative = Session::get('feedback_negative');
        // echo out positive messages
        if (isset($feedback_positive)) {
            echo 'SUKSES, Data Wisudawan ' . $full_name . ' Berhasil Diverifikasi';
        }
        // echo out negative messages
        if (isset($feedback_negative)) {
            echo 'GAGAL!, Data Wisudawan ' . $full_name . ' Gagal Diverifikasi';
            var_dump($feedback_negative) ;
        }
        // RESET counter feedback to unconfuse user
        Session::set('feedback_positive', null);
        Session::set('feedback_negative', null);
    }
	

	 
    public function uploadDocument($uid = null)
    {
        if (empty($uid)) {
            Redirect::to('employee/detail/' . $uid);
            Session::add('feedback_negative', 'GAGAL!, upload file tidak berhasil');
        }

        $image_name = 'file_name';
        $image_rename = Request::post('document_name');
        $destination = 'employee';
        $note = Request::post('note');
        UploadModel::uploadDocument($image_name, $image_rename, $destination, $uid, $note);
        Redirect::to('employee/detail/' . $uid);
    }
	
	

		
}