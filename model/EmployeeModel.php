<?php

/**
 * Class RegistrationModel
 *
 * Everything registration-related happens here.
 */
class EmployeeModel
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
        $full_name = Request::post('full_name');
        $email = Request::post('email');
        $phone = FormaterModel::getNumberOnly(Request::post('phone'));
        $user_password_new = Request::post('user_password_new');
        $user_password_repeat = Request::post('user_password_repeat');

        // stop registration flow if registrationInputValidation() returns false (= anything breaks the input check rules)
        $validation_result = self::registrationInputValidation($user_name, $full_name, $user_password_new, $user_password_repeat, $email, $phone);
        if (!$validation_result) {
            return false;
        }

        // crypt the password with the PHP 5.5's password_hash() function, results in a 60 character hash string.
        // @see php.net/manual/en/function.password-hash.php for more, especially for potential options
        $user_password_hash = password_hash($user_password_new, PASSWORD_DEFAULT);

        // make return a bool variable, so both errors can come up at once if needed
        $return = true;
        // check if username already exists
        if (self::doesUsernameAlreadyExist($user_name)) {
            Session::add('feedback_negative', 'Maaf username sudah dipakai orang lain');
            $return = false;
        }
        // check if email already exists
        if (UserModel::doesEmailAlreadyExist($email)) {
            Session::add('feedback_negative', 'Alamat email sudah pernah dipakai');
            $return = false;
        }
                // check if email already exists
        if (UserModel::doesPhoneAlreadyExist($phone)) {
            Session::add('feedback_negative', 'Nomer telpon sudah pernah dipakai');
            $return = false;
        }
        // if Username, Email and Phone were false, return false
        if (!$return) return false;

        // generate random hash for email verification (40 char string)
        $user_activation_hash = sha1(uniqid(mt_rand(), true));

        // create data user
        if (!self::writeNewUserToDatabase($user_name, $full_name, $user_password_hash, $email, $phone, $user_activation_hash)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_CREATION_FAILED'));
            return false; // no reason not to return false here
        }


        // get uid of the user that has been created, to keep things clean we DON'T use lastInsertId() here
        $uid = UserModel::getUserIdByUsername($user_name);

        if (!$uid) {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
        }

        // send verification email
        if (self::sendVerificationEmail($uid, $email, $user_activation_hash)) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED'));
            return true;
        }

        // if verification email sending failed: instantly delete the user
        self::rollbackRegistrationByUserId($uid);
        Session::add('feedback_negative', Text::get('FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED'));
        return false;
    }

    /**
     * Validates the registration input
     *
     * @param $captcha
     * @param $user_name
     * @param $user_password_new
     * @param $user_password_repeat
     * @param $email
     * @param $email_repeat
     *
     * @return bool
     */
    public static function registrationInputValidation($user_name, $full_name, $user_password_new, $user_password_repeat, $email, $phone)
    {
        $return = true;

        // if username, email and password are all correctly validated, but make sure they all run on first sumbit
        if (self::validateUserName($user_name) AND self::validateUserEmail($email) AND self::validateUserPassword($user_password_new, $user_password_repeat) AND !empty($phone) AND !empty($full_name) AND $return) {
            return true;
        }

        // otherwise, return false
        return false;
    }

    /**
     * Checks if a username is already taken
     *
     * @param $user_name string username
     *
     * @return bool
     */
    public static function doesUsernameAlreadyExist($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT uid FROM users WHERE user_name = :user_name LIMIT 1");
        $query->execute(array(':user_name' => $user_name));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
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
        if (empty($email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

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
    public static function writeNewUserToDatabase($user_name, $full_name, $user_password_hash, $email, $phone, $user_activation_hash)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // write new users data into database
        $sql = "INSERT INTO users (user_name, full_name, user_password_hash, email, phone, user_activation_hash, user_provider_type, is_active)
                    VALUES (:user_name, :full_name, :user_password_hash, :email, :phone, :user_activation_hash, :user_provider_type, :is_active)";
        $query = $database->prepare($sql);
        $query->execute(array(
                            ':user_name' => $user_name,
                            ':full_name' => $full_name,
                            ':user_password_hash' => $user_password_hash,
                            ':email' => $email,
                            ':phone' => $phone,
                            ':user_activation_hash' => $user_activation_hash,
                            ':user_provider_type' => 'employee',
                            ':is_active' => 1,
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
    public static function rollbackRegistrationByUserId($uid)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("DELETE FROM users WHERE uid = :uid");
        $query->execute(array(':uid' => $uid));
    }

    /**
     * Sends the verification email (to confirm the account).
     * The construction of the mail $body looks weird at first, but it's really just a simple string.
     *
     * @param int $uid user's id
     * @param string $email user's email
     * @param string $user_activation_hash user's mail verification hash string
     *
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    public static function sendVerificationEmail($uid, $email, $user_activation_hash)
    {
        $body = Config::get('EMAIL_VERIFICATION_CONTENT') . Config::get('URL') . Config::get('EMAIL_VERIFICATION_URL')
                . '/' . urlencode($uid) . '/' . urlencode($user_activation_hash);

        $mail = new Mail;
        $mail_sent = $mail->sendMail($email, Config::get('EMAIL_VERIFICATION_FROM_EMAIL'),
            Config::get('EMAIL_VERIFICATION_FROM_NAME'), Config::get('EMAIL_VERIFICATION_SUBJECT'), $body
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
    public static function verifyNewUser($uid, $user_activation_verification_code)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE users SET is_active = 1, user_activation_hash = NULL
                WHERE uid = :uid AND user_activation_hash = :user_activation_hash LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':uid' => $uid, ':user_activation_hash' => $user_activation_verification_code));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_ACCOUNT_ACTIVATION_FAILED'));
        return false;
    }
}
