<?php

/**
 * Class RegistrationModel
 *
 * Everything registration-related happens here.
 */
class RegistrationModel
{
    /**
     * Handles the entire registration process for DEFAULT users (not for people who register with
     * 3rd party services, like facebook) and creates a new user in the database if everything is fine
     *
     * @return boolean Gives back the success status of the registration
     */
    public static function registerNewUser()
    {
        // clean the input
        $user_name = Request::post('user_name');
        $full_name = strtoupper(Request::post('full_name'));
        $user_email = Request::post('user_email');
		$kode_negara = Request::post('kode_negara');
        $phone 		= (int)Request::post('user_phone');
		$user_phone = $kode_negara. $phone;
		//$date = Request::post('date');
		//$month = Request::post('month');
		//$year = Request::post('year');		
		//$date_of_birth = "$date-$month-$year";
        //$bank_name = Request::post('bank_name');
        //$bank_account_number = Request::post('bank_account_number');
        //$bank_account_holder = Request::post('bank_account_holder');
        //$bank_transfer_date = Request::post('bank_transfer_date');
		// $captcha = Request::post('captcha');

        // stop registration flow if there's empty field
        if (empty($user_name)) {
            Session::add('feedback_negative', 'Gagal, NIM tidak boleh kosong');
            return false;
        }
        if (empty($full_name)) {
            Session::add('feedback_negative', 'Gagal, Nama tidak boleh kosong');
            return false;
        }
        if (empty($user_email)) {
            Session::add('feedback_negative', 'Gagal, Email tidak boleh kosong');
           return false;
        }
        if (empty($user_phone)) {
            Session::add('feedback_negative', 'Gagal, Nomer telpon tidak boleh kosong');
            return false;
        }
        /**if (empty($bank_name)) {
            Session::add('feedback_negative', 'Gagal, Nama Bank Pengirim tidak boleh kosong');
            $return = false;
        }
        if (empty($bank_account_number)) {
            Session::add('feedback_negative', 'Gagal, Nomer Rekening Bank Pengirim tidak boleh kosong');
            $return = false;
        }
        if (empty($bank_account_holder)) {
            Session::add('feedback_negative', 'Gagal, Nama Pengirim tidak boleh kosong');
           return false;
        }
        if (empty($bank_transfer_date)) {
            Session::add('feedback_negative', 'Gagal, Tanggal pengiriman/transfer tidak boleh kosong');
           return false;
        }
		if (!CaptchaModel::checkCaptcha($captcha)) {
            Session::add('feedback_negative', 'Gagal, kode captcha yang dimasukkan salah.');
           return false;
        }
		**/

        $pin = rand(100000,999999);
        $user_password_hash = password_hash($pin, PASSWORD_DEFAULT);

        // make return a bool variable, so both errors can come up at once if needed
        $return = true;
        // check if username already exists
        if (UserModel::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', 'Gagal, NIM sudah digunakan.');
            $return = false;
        }

        // check if email already exists
        if (UserModel::doesEmailAlreadyExist($user_email)) {
            Session::add('feedback_negative', 'Gagal, Alamat email sudah digunakan');
            $return = false;
        }

        // check if email address format correct
        if (!self::validateUserEmail($user_email)) {
            Session::add('feedback_negative', 'Gagal, Format penulisan email keliru');
            $return = false;
        }
        
        // check if phone number already exists
        if (UserModel::doesPhoneAlreadyExist($user_phone)) {
            Session::add('feedback_negative', 'Gagal, Nomer telpon sudah digunakan');
            $return = false;
        }
        // if Username, Email and Phone were false, return false
        if (!$return) return false;

        // generate random hash for email verification (40 char string)
        $user_activation_hash = sha1(uniqid(mt_rand(), true));

        if (!self::writeNewUserToDatabase($user_name, $full_name, $user_email, $user_phone, $pin, $user_password_hash, $user_activation_hash)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
        }

        
        // send verification email
       if (self::sendVerificationEmail($user_name, $full_name, $user_email, $user_phone, $pin, $user_activation_hash)) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
            return true;
        }
		
		
        // if verification email sending failed: instantly delete the user
        self::rollbackRegistrationByUserId($user_name);
        Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED'));
        return false;
        
    }

    /**
     * Validates the username
     *
     * @param $user_name
     * @return bool
     */
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
    public static function validateUserEmail($email)
    {
        // validate the email with PHP's internal filter
        // side-fact: Max length seems to be 254 chars
        // @see http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        return true;
    }

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
    public static function writeNewUserToDatabase($user_name, $full_name, $user_email, $user_phone, $pin, $user_password_hash, $user_activation_hash) {

        //Get Gelombang Pendaftaran
        $gelombang_pendaftaran = GenericModel::getOne('gelombang_pendaftaran', '`is_active` = 1', '`item_name`');

        $database = DatabaseFactory::getFactory()->getConnection();

        // write new users data into database
        $sql = "INSERT INTO
                    `users` (
                        `nisn`,
                        `user_name`,
                        `full_name`,
                        `email`,
                        `phone`,
                        `pin`,						
                        `user_password_hash`,
						`user_activation_hash`,
                        `user_provider_type`,
                        `is_active`,
                        `gelombang_pendaftaran`
                        )
                VALUES (
                        :nisn,
                        :user_name,
                        :full_name,
                        :email,
                        :phone,                       
						:pin,						
                        :user_password_hash,
						:user_activation_hash,
                        :user_provider_type,
                        :is_active,
                        :gelombang_pendaftaran
                    )";
        $query = $database->prepare($sql);
        $query->execute(array(
                            ':nisn' => $user_name,
                            ':user_name' => $user_name,
                            ':full_name' => $full_name,
                            ':email' => $user_email,
                            ':phone' => $user_phone,
                            ':pin' => $pin,							
                            ':user_password_hash' => $user_password_hash,
							':user_activation_hash' => $user_activation_hash,
                            ':user_provider_type' => 'mahasiswa',
                            ':is_active' => 0,
                            ':gelombang_pendaftaran' => $gelombang_pendaftaran->item_name,
                            ));

        $count =  $query->rowCount();
        if ($count == 1) {
            return true;
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

    /**
     * Sends the verification email (to confirm the account).
     * The construction of the mail $body looks weird at first, but it's really just a simple string.
     *
     * @param int $uid user's id
     * @param string $$user_phone user's email
     * @param string $user_activation_hash user's mail verification hash string
     *
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public static function sendVerificationEmail($user_name, $full_name, $user_email, $user_phone, $pin, $user_activation_hash)
    {
        $body = Config::get('EMAIL_VERIFICATION_CONTENT');
		$link = Config::get('URL') . Config::get('EMAIL_VERIFICATION_URL')
                . '/' . urlencode($user_name) . '/' . urlencode($user_activation_hash) ;
		$noWa = $user_phone;		
		$pesanWa = "Hai $full_name

Terimakasih Telah Mendaftar pada Sistem Informasi Pendaftaran Mahasiswa Baru

Panitia akan melakukan aktivasi akun. Mohon menunggu.
User dan password akun dikirim melalui email pendaftar, pastikan email yang digunakan aktif.

Terimakasih.
Panitia Penerimaan Mahasiswa Baru

Unitri - 2024
";
		
		$head = '<html>
		<table class="body-wrap" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: transparent; margin: 0;">
                                    <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                        <td style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
                                        <td class="container" width="600" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto;" valign="top">
                                            <div class="content" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                                                <table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope itemtype="http://schema.org/ConfirmAction" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; border: none;">
                                                    <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                        <td class="content-wrap" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; color: #495057; font-size: 14px; vertical-align: top; margin: 0;padding: 30px; box-shadow: 0 0.75rem 1.5rem rgba(18,38,63,.03); ;border-radius: 7px; background-color: #fff;" valign="top">
                                                            <meta itemprop="name" content="Confirm Email" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;" />
                                                            <table width="100%" cellpadding="0" cellspacing="0" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                                <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                                    <td class="content-block" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
																	Terimakasih Telah Mendaftar pada Sistem Informasi Pendaftaran Mahasiswa Baru
                                                                  </td>
                                                                </tr>
                                                                <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                                    <td class="content-block" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                                                                        Panitia akan melakukan aktivasi akun. Mohon menunggu.
                                                                      User dan password akun dikirim melalui email pendaftar, pastikan email yang digunakan aktif.
                                                                    </td>
                                                                </tr>
                                                               
                                                                <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                                    <td class="content-block" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
                                                                        <b>Panitia </b>
                                                                        <p>Penerimaan Mahasiswa Baru Unitri</p>
                                                                    </td>
                                                                </tr>
        
                                                                <tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
                                                                    <td class="content-block" style="text-align: center;font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0;" valign="top">
                                                                        © 2024 Unitri
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
		
		</html>';
		$isiemail = 'Silahkan menunggu akun anda akan diaktivasi oleh panitia';
		//kirim wa
		$curl = curl_init();
		curl_setopt_array($curl, array(
  		CURLOPT_URL => 'https://ngirim.my.id/api/create-message',
 		CURLOPT_RETURNTRANSFER => true,
  		CURLOPT_ENCODING => '',
  		CURLOPT_MAXREDIRS => 10,
  		CURLOPT_TIMEOUT => 0,
  		CURLOPT_FOLLOWLOCATION => true,
  		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  		CURLOPT_CUSTOMREQUEST => 'POST',
  		CURLOPT_POSTFIELDS => array(
  		'appkey' => '20921155-7aca-4f1e-813e-78b70ec224da',
  		'authkey' => 'ZHQCZk5BnVtPKsqYjTNkvVsOhM1xS2Fz1VvUT4didASr3mrc9g',
  		'to' => $noWa,
  		'message' => $pesanWa, 		
  		'sandbox' => 'false'
  		),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		//kirim email
        $mail = new Mail;
        $mail_sent = $mail->sendMail($user_email, Config::get('EMAIL_VERIFICATION_FROM_EMAIL'),
            Config::get('EMAIL_VERIFICATION_FROM_NAME'), Config::get('EMAIL_VERIFICATION_SUBJECT'), $head
        );

        if ($mail_sent) {
            Session::add('feedback_positive', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_SUCCESSFUL'));
            return true;
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_ERROR') . $mail->getError() );
            return false;
        }
    }

    /**
     * checks the email/verification code combination and set the user's activation status to true in the database
     *
     * @param int $uid user id
     * @param string $user_activation_verification_code verification token
     *
     * @return bool success status
     */

    public static function verifyNewUser($user_name, $user_activation_verification_code)
    {
        
		$database = DatabaseFactory::getFactory()->getConnection();		

		$uid = GenericModel::getOne('`users`', "`user_name` = {$user_name}", '`uid`');
		$kode = $uid->uid;
		$virtual = "988396462401$kode" ;
        $sql = "UPDATE users SET is_active = 1, user_activation_hash = NULL, bank_account_number = $virtual
                WHERE user_name = :user_name AND user_activation_hash = :user_activation_hash LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':user_name' => $user_name, ':user_activation_hash' => $user_activation_verification_code));
		
		$user = GenericModel::getOne('`users`', "`user_name` = {$user_name}", '`email`');		
		$id_pin = GenericModel::getOne('`users`', "`user_name` = {$user_name}", '`pin`');
		$nama = GenericModel::getOne('`users`', "`user_name` = {$user_name}", '`full_name`');
		
				
		$pin = $id_pin->pin;
		$full_name = $nama->full_name;
		$pesan  = "<h3>Akun Anda telah Aktif.</h3><br>";
		$pesan .= "Silahkan Login di https://pmb.unitri.ac.id/login dan lengkapi data pendaftaran.<br>";
		$pesan .= "Silahkan Login Menggunakan Akun:<br>";
		$pesan .= "<hr>";		
		$pesan .= "<table><tr><td style='width: 150px;'>Akun / Username </td><td>: <strong>" . $user_name . "</strong></td></tr>" ;
		$pesan .= "<tr><td style='width: 150px;'>PIN </td><td>: <strong>" . $pin . "</strong></td></tr></table>";
		$pesan .= "<hr>";
		$pesan .= "<p>Silahkan Melakukan Pembayaran Biaya Pendaftaran sebesar <strong>Rp. 250.000,- </strong> untuk Jenjang S1, <strong>Rp. 350.000,- </strong> untuk jenjang S2 ke No. Rekening <strong>3964646468</strong> a.n. <strong>Yayasan Bina Patria Nusantara</strong> dan Unggah bukti pembayaran untuk validasi.</p><br><br> Panitia PMB Unitri";
		$pesan .= "<p>Atau lakukan pembayaran menggunakan virtual Akun BNI, informasi virtual akun :
					<table><tr><td style='width: 150px;'>Akun / Username </td><td>: <strong>" . $virtual . "</strong></td></tr>
					<tr><td style='width: 150px;'>Nama di VA </td><td>: <strong>Maba ". $kode." </strong></td></tr>
					<tr><td style='width: 150px;'>Nominal </td><td>: <strong>Rp. 250.000,- untuk S1 dan Rp. 350.000 untuk S2</strong></td></tr></table>";

        $aktif = '<table class="MsoTableGrid" style="background: #A894FE; border-collapse: collapse; border: none;" border="0" cellspacing="0" cellpadding="0">
					<tbody>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Hi<strong>, ' . $full_name .'</strong></span></p>
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><strong><span style="font-size: 12.0pt; font-family: Arial, sans-serif; color: black;">Hore &hellip;&hellip;. Akun Telah Aktif</span></strong></p>
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><strong><span style="font-size: 12.0pt; font-family: Arial, sans-serif;">&nbsp;</span></strong></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Silahkan masuk ke Sistem Informasi PMB Online :</span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 49.6pt; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Akun</span></p>
					</td>
					<td style="width: 262.25pt; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">:<strong> '.$user_name.'</strong></span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 49.6pt; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">PIN</span></p>
					</td>
					<td style="width: 262.25pt; background: #F2F2F2; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">:<strong> '.$pin.'</strong></span></p>
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: #7030A0; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Untuk pembayaran biaya pendaftaran dapat dilakukan dengan 2 cara :</span></p>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
						<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<ol style="margin-bottom: 0cm; margin-top: 0px;">
					<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pembayaran melalui Rekening BNI : <strong>3964646468</strong> an. Yayasan Bina Patria Nusantara.</span></li>
					</ol>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
					<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					<td style="width: 11.0cm; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
					<ol style="margin-bottom: 0cm; margin-top: 0px;" start="2">
					<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pembayaran Melalui Virtual Akun Mahasiswa Pendaftar**</span></li>
					</ol>
					</td>
					<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
					<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
					</td>
					</tr>
					<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 148.8pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Virtual Akun BNI</span></p>
</td>
<td style="width: 163.05pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 1.45pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">: <strong>'.$virtual.'</strong></span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 148.8pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Nama Akun</span></p>
</td>
<td style="width: 163.05pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 1.45pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">:<strong>&nbsp;Camaba&nbsp;'.$kode.'</strong></span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 11.0cm; background: #7030A0; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 148.8pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Biaya Pendaftaran </span></p>
</td>
<td style="width: 163.05pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 1.45pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">: Rp. 250.000,- untuk S1</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 148.8pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="2" valign="top">
<p style="margin: 0cm 0cm 0cm 8.95pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 163.05pt; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 1.45pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">: Rp. 350.000,- untuk S2</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm 0cm 0cm 36pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 11.0cm; background: #7030A0; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
<p style="margin: 0cm 0cm 0cm 8.95pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 11.0cm; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
<p style="margin: 0cm 0cm 0cm 1.85pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">** Untuk memudahkan tracking atau melacak pembayaran disarankan membayar melalui Virtual Akun</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 11.0cm; background: #7030A0; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
<p style="margin: 0cm 0cm 0cm 1.85pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
<tr>
<td style="width: 77.75pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 11.0cm; background: white; padding: 0cm 5.4pt 0cm 5.4pt;" colspan="3" valign="top">
<p style="margin: 0cm 0cm 0cm 1.85pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Petunjuk Pembayaran :</span></p>
<ol style="list-style-type: upper-alpha; margin-bottom: 0cm; margin-top: 0px;">
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pembayaran melalui teller (Khusus Bank BNI 46)</span>
<ol style="margin-top: 0cm; margin-bottom: 0cm;">
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Tujukkan Nomor Virtual Account pembayaran kepada Teller bank</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Masukan nominal biaya pendaftaran</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Unggah dan Simpan slip pembayaran</span></li>
</ol>
</li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pembayaran Melalui ATM BNI 46</span>
<ol style="margin-top: 0cm; margin-bottom: 0cm;">
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Buka Menu transfer ke Rekening BNI</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Masukkan nomor akun pembayaran pada rekening tujuan</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pastikan nominal transfer sesuai dengan nominal biaya pendaftaran</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Unggah dan Simpan bukti pembayaran</span></li>
</ol>
</li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pembayaran Melalui ATM Bersama (Non ATM BNI)</span>
<ol style="margin-top: 0cm; margin-bottom: 0cm;">
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Pilih Menu&nbsp;<strong>Transfer</strong></span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Transfer ke&nbsp;<strong>Rekening Bank Lain</strong></span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Masukkan kode BNI&nbsp;<strong>009&nbsp;</strong>diikuti kode&nbsp;<strong>Virtual Account</strong>&nbsp;kemudian tekan "<strong>Benar</strong>"</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Masukkan nominal pembayaran pendaftaran Maba</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Jika benar akan muncul konfirmasi Tranfer ke rekening tujuan BNI dan Nama beserta jumlah yang dibayarkan.</span></li>
<li style="margin: 0cm 0cm 0cm 0px; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif; color: black;">Unggah dan Simpan bukti pembayaran</span></li>
</ol>
</li>
</ol>
<p style="margin: 0cm 0cm 0cm 51.5pt; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
<td style="width: 77.9pt; padding: 0cm 5.4pt 0cm 5.4pt;" valign="top">
<p style="margin: 0cm; line-height: normal; font-size: 11pt; font-family: Calibri, sans-serif;"><span style="font-family: Arial, sans-serif;">&nbsp;</span></p>
</td>
</tr>
</tbody>
</table>';
		
        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL'));
			$mail = new Mail;
			$mail_sent = $mail->sendMail($user->email, Config::get('EMAIL_VERIFICATION_FROM_EMAIL'),
            Config::get('EMAIL_VERIFICATION_FROM_NAME'), 'Aktivasi Akun : Data Akun Pembayaran dan Nominal', $aktif
			);
            return true;
        }
        Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
        return false;
    }
}
