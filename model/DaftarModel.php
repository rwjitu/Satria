<?php

/**
 * Class RegistrationModel
 *
 * Everything registration-related happens here.
 */
class DaftarModel
{
    /**
     * Handles the entire registration process for DEFAULT users (not for people who register with
     * 3rd party services, like facebook) and creates a new user in the database if everything is fine
     *
     * @return boolean Gives back the success status of the registration
     */
    public static function TambahPegawaiBaru()
    {
        // clean the input
        $user_name = Request::post('user_name');
        $nama_sdm = strtoupper(Request::post('nama_sdm'));        
        $phone 	   = Request::post('phone');
        $email      = Request::post('email');  
		$nuptk    = Request::post('nuptk');
		$nip    = Request::post('nip');
		$nama_status_pegawai   = Request::post('nama_status_pegawai');
		$jenis_sdm  = Request::post('jenis_sdm');
		$department  = Request::post('department');
		$nama_status_aktif  = Request::post('nama_status_aktif');
		$id_sdm	   = Request::post('id_sdm');		
		
        // stop registration flow if there's empty field
        
        if (empty($nama_sdm)) {
            Session::add('feedback_negative', 'Gagal, Nama tidak boleh kosong');
            return false;
        }
        if (empty($phone)) {
            Session::add('feedback_negative', 'Gagal, Nomer telpon tidak boleh kosong');
            return false;
        }
        
        $pin = rand(100000,999999);
        $user_password_hash = password_hash($pin, PASSWORD_DEFAULT);

        // make return a bool variable, so both errors can come up at once if needed
        $return = true;
        // check if username already exists
        if (UserModel::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', 'Gagal, data sudah ada');
            $return = false;
        }
        
		// check if phone number already exists
        if (UserModel::doesPhoneAlreadyExist($phone)) {
            Session::add('feedback_negative', 'Gagal, Nomer telpon sudah digunakan');
            $return = false;
        }
                        
        
        if (!self::writeNewUserToDatabase($user_name, $id_sdm, $nama_sdm, $phone, $email, $nuptk, $nip, $nama_status_pegawai, $jenis_sdm, $department, $nama_status_aktif, $pin, $user_password_hash)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
        }           		
        
    }
	
	
	 public static function TambahTagihanBaru()
    {
        // clean the input
        $uid 		= $_POST['uid'];
        $bulan 		= Request::post('bulan');        
        $tahun 	   	= Request::post('tahun');		
		
        // stop registration flow if there's empty field
        
       
        $return = true;

       
        // check if username already exists
        if (self::tagihanSudahAda($bulan, $tahun)) {
            Session::add('feedback_negative', 'Gagal, Tagihan sudah ada');
            $return = false;
        }
        
		// check if phone number already exists
        if (UserModel::doesPhoneAlreadyExist($phone)) {
            Session::add('feedback_negative', 'Gagal, Nomer telpon sudah digunakan');
            $return = false;
        }
        // if Username, Email and Phone were false, return false
        if (!$return) return false;
                
        
        if (!self::writeNewTagihanToDatabase($uid, $bulan, $tahun)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
        }           		
        
    }

    /**
     * Validates the username
     *
     * @param $user_name
     * @return bool
     */
	 
	  public static function tagihanSudahAda($bulan, $tahun)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT uid FROM tagihan WHERE `bulan` = :bulan AND `tahun` = :tahun LIMIT 1");
        $query->execute(array(':bulan' => $bulan, ':tahun' => $tahun));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }
	 
    public static function validateUserName($user_name)
    {
        if (empty($user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_FIELD_EMPTY'));
            return false;
        }

        // if username is too short (2), too long (64) or does not fit the pattern (aZ09)
        if (!preg_match('/^[a-zA-Z0-9]{2,64}$/', $user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        return true;
    }

    /**
     * Validates the email
     *
     * @param $email
     * @param $email_repeat
     * @return bool
     */
    

    /**
     * Validates the password
     *
     * @param $user_password_new
     * @param $user_password_repeat
     * @return bool
     */
    public static function validateUserPassword($user_password_new, $user_password_repeat)
    {
        if (empty($user_password_new) OR empty($user_password_repeat)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_FIELD_EMPTY'));
            return false;
        }

        if ($user_password_new !== $user_password_repeat) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_REPEAT_WRONG'));
            return false;
        }

        if (strlen($user_password_new) < 6) {
            Session::add('feedback_negative', Text::get('FEEDBACK_PASSWORD_TOO_SHORT'));
            return false;
        }

        return true;
    }

    /**
     * Writes the new user's data to the database
     *
     * @param $user_name
     * @param $user_password_hash
     * @param $email
     * @param $user_creation_timestamp
     * @param $user_activation_hash
     *
     * @return bool
     */
   public static function writeNewUserToDatabase(
    $user_name, 
    $id_sdm, 
    $nama_sdm, 
    $phone, 
    $email, 
    $nuptk, 
    $nip, 
    $nama_status_pegawai, 
    $jenis_sdm, 
    $department, 
    $nama_status_aktif, 
    $pin, 
    $user_password_hash
	) {
    $database = DatabaseFactory::getFactory()->getConnection();

    // Cek apakah user_name sudah ada
    $check = $database->prepare("SELECT COUNT(*) FROM users WHERE user_name = :user_name");
    $check->execute([':user_name' => $user_name]);
    if ($check->fetchColumn() > 0) {
        Session::add('feedback_negative', 'Gagal, user_name sudah digunakan');
        return false;
    }

    // Query insert
    $sql = "INSERT INTO `users` (
                `nisn`,
                `user_name`,
                `id_sdm`,
                `nama_sdm`,
                `email`,
                `phone`,
                `nuptk`,
                `nip`,
                `nama_status_pegawai`,
                `jenis_sdm`,
                `department`,
                `nama_status_aktif`,
                `pin`,
                `user_password_hash`,
                `user_provider_type`,
                `sister`,
                `is_active`
            ) VALUES (
                :nisn,
                :user_name,
                :id_sdm,
                :nama_sdm,
                :email,
                :phone,
                :nuptk,
                :nip,
                :nama_status_pegawai,
                :jenis_sdm,
                :department,
                :nama_status_aktif,
                :pin,
                :user_password_hash,
                :user_provider_type,
                :sister,
                :is_active
            )";

    $query = $database->prepare($sql);

    try {
        $success = $query->execute([
            ':nisn' => $user_name,
            ':user_name' => $user_name,
            ':id_sdm' => $id_sdm,
            ':nama_sdm' => $nama_sdm,
            ':email' => $email,
            ':phone' => $phone,
            ':nuptk' => $nuptk,
            ':nip' => $nip,
            ':nama_status_pegawai' => $nama_status_pegawai,
            ':jenis_sdm' => $jenis_sdm,
            ':department' => $department,
            ':nama_status_aktif' => $nama_status_aktif,
            ':pin' => $pin,
            ':user_password_hash' => $user_password_hash,
            ':user_provider_type' => 'pegawai',
            ':sister' => 1,
            ':is_active' => 1
        ]);

        if ($success) {
            Session::add('feedback_positive', 'Hore, data berhasil disimpan');
            return true;
        }
    } catch (PDOException $e) {
        // Tangkap error duplicate entry (kode 23000)
        if ($e->getCode() == 23000) {
            Session::add('feedback_negative', 'Gagal, user_name sudah ada (duplicate entry)');
        } else {
            Session::add('feedback_negative', 'Error: ' . $e->getMessage());
        }
        return false;
    }

    return false;
}
	
	public static function writeNewTagihanToDatabase($uid, $bulan, $tahun) {

        //Get Gelombang Pendaftaran
        //$gelombang_pendaftaran = GenericModel::getOne('gelombang_pendaftaran', '`is_active` = 1', '`item_name`');
		$datanominal = GenericModel::getOne('`users`', "`uid` = {$uid}", '`nominal`');
		$nominal = $datanominal->nominal;
		$user = GenericModel::getOne('`users`', "`uid` = {$uid}", '`email`');
		$tanggal_tagihan = date('Y-m-d H:i:s');

        $database = DatabaseFactory::getFactory()->getConnection();

        // write new tagihan data into database
		for ($i = 1;$i < count($uid);$i++)
		{
		$uid = $uid[$i];
        $sql = "INSERT INTO
                    `tagihan`(
                        `uid`,
                        `bulan`,
                        `tahun`,                       
                        `nominal`,																		
                        `status`						                                               
                        )
                VALUES (
                        :uid,
                        :bulan,
                        :tahun,                    
                        :nominal,						
						:status						
                    )";
        $query = $database->prepare($sql);
        $query->execute(array(
                            ':uid' => $uid,
                            ':bulan' => $bulan,
                            ':tahun' => $tahun,                          
                            ':nominal' => $nominal,							
							':status' => 1							
                            ));

        $count =  $query->rowCount();
        if ($count == 1) {
            Session::add('feedback_positive', 'Hore, data berhasil disimpan');
			return true;
        }
		}

        return false;
    }

    /**
     * Deletes the user from users table. Currently used to rollback a registration when verification mail sending
     * was not successful.
     *
     * @param $uid
     */
    public static function rollbackRegistrationByUserId($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("DELETE FROM users WHERE `nisn` = :nisn");
        $query->execute(array(':nisn' => $user_name));
    }

   public static function inputTagihanBaru()
    {
        // clean the input
        $uid 		= $_POST['uid'];
        $bulan 		= Request::post('bulan');        
        $tahun 	   	= Request::post('tahun');		
		$total		= count($uid);
        
		
        
       
        //$return = true;

       
        // check if username already exists
        
        // if Username, Email and Phone were false, return false
        // if (!$return) return false;
                
        
        /*if (!self::writeNewTagihanToDatabase($uid, $bulan, $tahun)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
        } */          		
        
    }

    
}
