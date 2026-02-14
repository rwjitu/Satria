<?php

/**
 * UserModel
 * Handles all the PUBLIC profile stuff. This is not for getting data of the logged in user, it's more for handling
 * data of all the other users. Useful for display profile information, creating user lists etc.
 */
class UserModel
{
    /**
     * Gets an array that contains all the users in the database. The array's keys are the user ids.
     * Each array element is an object, containing a specific user's data.
     * The avatar line is built using Ternary Operators, have a look here for more:
     * @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
     *
     * @return array The profiles of all users
     */
    public static function getPublicProfilesOfAllUsers()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT uid, nama_sdm, user_name, email, address_street, address_city, address_state, address_stayed, phone, is_active, user_has_avatar, is_deleted FROM users";
        $query = $database->prepare($sql);
        $query->execute();

        $all_users_profiles = array();

        foreach ($query->fetchAll() as $user) {

            // all elements of array passed to Filter::XSSFilter for XSS sanitation, have a look into
            // application/core/Filter.php for more info on how to use. Removes (possibly bad) JavaScript etc from
            // the user's values
            array_walk_recursive($user, 'Filter::XSSFilter');

            $all_users_profiles[$user->uid] = new stdClass();
            $all_users_profiles[$user->uid]->uid = $user->uid;
            $all_users_profiles[$user->uid]->nama_sdm = $user->nama_sdm;
            $all_users_profiles[$user->uid]->user_name = $user->user_name;
            $all_users_profiles[$user->uid]->email = $user->email;			
            $all_users_profiles[$user->uid]->address_street = $user->address_street;
            $all_users_profiles[$user->uid]->address_city = $user->address_city;
            $all_users_profiles[$user->uid]->address_state = $user->address_state;
            $all_users_profiles[$user->uid]->address_stayed = $user->address_stayed;
            $all_users_profiles[$user->uid]->phone = $user->phone;
            $all_users_profiles[$user->uid]->is_active = $user->is_active;
            $all_users_profiles[$user->uid]->is_deleted = $user->is_deleted;
            $all_users_profiles[$user->uid]->user_avatar_link = (Config::get('USE_GRAVATAR') ? AvatarModel::getGravatarLinkByEmail($user->email) : AvatarModel::getPublicAvatarFilePathOfUser($user->user_has_avatar, $user->uid));
        }

        return $all_users_profiles;
    }

    /**
     * Gets a user's profile data, according to the given $uid
     * @param int $uid The user's id
     * @return mixed The selected user's profile
     */
    public static function getPublicProfileOfUser($uid)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT uid, nama_sdm, user_name, email, address_street, address_city, address_state, previous_address, phone, is_active, user_has_avatar, is_deleted
                FROM users WHERE uid = :uid LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':uid' => $uid));

        $user = $query->fetch();

        if ($query->rowCount() == 1) {
            if (Config::get('USE_GRAVATAR')) {
                $user->user_avatar_link = AvatarModel::getGravatarLinkByEmail($user->email);
            } else {
                $user->user_avatar_link = AvatarModel::getPublicAvatarFilePathOfUser($user->user_has_avatar, $user->uid);
            }
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_DOES_NOT_EXIST'));
        }

        // all elements of array passed to Filter::XSSFilter for XSS sanitation, have a look into
        // application/core/Filter.php for more info on how to use. Removes (possibly bad) JavaScript etc from
        // the user's values
        array_walk_recursive($user, 'Filter::XSSFilter');

        return $user;
    }

    /**
     * Gets the user's data
     *
     * @param $user_name string User's name
     *
     * @return mixed Returns false if user does not exist, returns object with user's data when user exists
     */
    public static function getUserDataByLogin($login)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT
                    uid,
                    user_name,
                    nama_sdm,
                    department,
                    grade,
                    email,
                    phone,
                    user_password_hash,
                    is_active,
                    is_deleted,					
                    user_suspension_timestamp,
                    user_provider_type,
                    user_failed_logins,
                    user_last_failed_login
                  FROM users
                 WHERE user_name = :login OR email = :login OR phone = :login
                 LIMIT 1";
        $query = $database->prepare($sql);

        // DEFAULT is the marker for "normal" accounts (that have a password etc.)
        // There are other types of accounts that don't have passwords etc. (FACEBOOK)
        $query->execute(array(':login' => $login));

        // return one row (we only have one result or nothing)
        return $query->fetch();
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

        $query = $database->prepare("SELECT uid FROM users WHERE `nisn` = :user_name OR `passport` = :user_name LIMIT 1");
        $query->execute(array(':user_name' => $user_name));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a email is already used
     *
     * @param $email string email
     *
     * @return bool
     */
    public static function doesEmailAlreadyExist($email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT uid FROM users WHERE email = :email LIMIT 1");
        $query->execute(array(':email' => $email));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks if phone number is already used
     *
     * @param $email string email
     *
     * @return bool
     */
    public static function doesPhoneAlreadyExist($phone)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT uid FROM users WHERE phone = :phone LIMIT 1");
        $query->execute(array(':phone' => $phone));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Writes new username to database
     *
     * @param $uid int user id
     * @param $new_user_name string new username
     *
     * @return bool
     */
    public static function saveNewUserName($uid, $new_user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_name = :user_name WHERE uid = :uid LIMIT 1");
        $query->execute(array(':user_name' => $new_user_name, ':uid' => $uid));
        if ($query->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Writes new email address to database
     *
     * @param $uid int user id
     * @param $new_email string new email address
     *
     * @return bool
     */
    public static function saveNewEmailAddress($uid, $new_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET email = :email WHERE uid = :uid LIMIT 1");
        $query->execute(array(':email' => $new_email, ':uid' => $uid));
        $count = $query->rowCount();
        if ($count == 1) {
            return true;
        }
        return false;
    }

    /**
     * Edit the user's name, provided in the editing form
     *
     * @param $new_user_name string The new username
     *
     * @return bool success status
     */
    public static function editUserName($new_user_name)
    {
        // new username same as old one ?
        if ($new_user_name == Session::get('user_name')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_SAME_AS_OLD_ONE'));
            return false;
        }

        // username cannot be empty and must be azAZ09 and 2-64 characters
        if (!preg_match("/^[a-zA-Z0-9]{2,64}$/", $new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        // clean the input, strip usernames longer than 64 chars (maybe fix this ?)
        $new_user_name = substr(strip_tags($new_user_name), 0, 64);

        // check if new username already exists
        if (self::doesUsernameAlreadyExist($new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN'));
            return false;
        }

        $status_of_action = self::saveNewUserName(Session::get('uid'), $new_user_name);
        if ($status_of_action) {
            Session::set('user_name', $new_user_name);
            Session::add('feedback_positive', Text::get('FEEDBACK_USERNAME_CHANGE_SUCCESSFUL'));
            return true;
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
        }
    }

    /**
     * Edit the user's email
     *
     * @param $new_email
     *
     * @return bool success status
     */
    public static function editUserEmail($new_email)
    {
        // email provided ?
        if (empty($new_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

        // check if new email is same like the old one
        if ($new_email == Session::get('email')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_SAME_AS_OLD_ONE'));
            return false;
        }

        // user's email must be in valid email format, also checks the length
        // @see http://stackoverflow.com/questions/21631366/php-filter-validate-email-max-length
        // @see http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        // strip tags, just to be sure
        $new_email = substr(strip_tags($new_email), 0, 254);

        // check if user's email already exists
        if (self::doesEmailAlreadyExist($new_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_email_ALREADY_TAKEN'));
            return false;
        }

        // write to database, if successful ...
        // ... then write new email to session, Gravatar too (as this relies to the user's email address)
        if (self::saveNewEmailAddress(Session::get('uid'), $new_email)) {
            Session::set('email', $new_email);
            Session::set('user_gravatar_image_url', AvatarModel::getGravatarLinkByEmail($new_email));
            Session::add('feedback_positive', Text::get('FEEDBACK_EMAIL_CHANGE_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
        return false;
    }

    /**
     * Gets the user's id
     *
     * @param $user_name
     *
     * @return mixed
     */
    public static function getUserIdByUsername($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT uid FROM users WHERE user_name = :user_name AND user_provider_type = :provider_type LIMIT 1";
        $query = $database->prepare($sql);

        // DEFAULT is the marker for "normal" accounts (that have a password etc.)
        // There are other types of accounts that don't have passwords etc. (FACEBOOK)
        $query->execute(array(':user_name' => $user_name, ':provider_type' => 'DEFAULT'));

        // return one row (we only have one result or nothing)
        return $query->fetch()->uid;
    }

    /**
     * Gets the user's data by user's id and a token (used by login-via-cookie process)
     *
     * @param $uid
     * @param $token
     *
     * @return mixed Returns false if user does not exist, returns object with user's data when user exists
     */
    public static function getUserDataByUserIdAndToken($uid, $token)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        // get real token from database (and all other data)
        $query = $database->prepare("SELECT uid, user_name, email, user_password_hash, is_active,
                                          user_account_type,  user_has_avatar, user_failed_logins, user_last_failed_login
                                     FROM users
                                     WHERE uid = :uid
                                       AND user_remember_me_token = :user_remember_me_token
                                       AND user_remember_me_token IS NOT NULL
                                       AND user_provider_type = :provider_type LIMIT 1");
        $query->execute(array(':uid' => $uid, ':user_remember_me_token' => $token, ':provider_type' => 'DEFAULT'));

        // return one row (we only have one result or nothing)
        return $query->fetch();
    }
}
