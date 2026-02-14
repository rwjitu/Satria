<?php

class AvatarModel
{
    /**
     * Gets a gravatar image link from given email address
     *
     * Gravatar is the #1 (free) provider for email address based global avatar hosting.
     * The URL (or image) returns always a .jpg file ! For deeper info on the different parameter possibilities:
     * @see http://gravatar.com/site/implement/images/
     * @source http://gravatar.com/site/implement/images/php/
     *
     * This method will return something like http://www.gravatar.com/avatar/79e2e5b48aec07710c08d50?s=80&d=mm&r=g
     * Note: the url does NOT have something like ".jpg" ! It works without.
     *
     * Set the configs inside the application/config/ files.
     *
     * @param string $email The email address
     * @return string
     */
    public static function getGravatarLinkByEmail($email)
    {
        return 'http://www.gravatar.com/avatar/' .
        md5(strtolower(trim($email))) .
        '?s=' . Config::get('AVATAR_SIZE') . '&d=' . Config::get('GRAVATAR_DEFAULT_IMAGESET') . '&r=' . Config::get('GRAVATAR_RATING');
    }

    /**
     * Gets the user's avatar file path
     * @param int $user_has_avatar Marker from database
     * @param int $uid User's id
     * @return string Avatar file path
     */
    public static function getPublicAvatarFilePathOfUser($user_has_avatar, $uid)
    {
        if ($user_has_avatar) {
            return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . $uid . '.jpg';
        }

        return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . Config::get('AVATAR_DEFAULT_IMAGE');
    }

    /**
     * Gets the user's avatar file path
     * @param $uid integer The user's id
     * @return string avatar picture path
     */
    public static function getPublicUserAvatarFilePathByUserId($uid)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_has_avatar FROM users WHERE uid = :uid LIMIT 1");
        $query->execute(array(':uid' => $uid));

        if ($query->fetch()->user_has_avatar) {
            return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . $uid . '.jpg';
        }

        return Config::get('URL') . Config::get('PATH_AVATARS_PUBLIC') . Config::get('AVATAR_DEFAULT_IMAGE');
    }

    /**
     * Create an avatar picture (and checks all necessary things too)
     * TODO decouple
     * TODO total rebuild
     */
    public static function createAvatar($user_id)
    {
        // check avatar folder writing rights, check if upload fits all rules
        if (self::isAvatarFolderWritable() AND self::validateImageFile()) {
            // create a jpg file in the avatar folder, write marker to database
            
            //make avatar ukuran kecil buat user icon
            $target_file_path = Config::get('PATH_AVATARS') . $user_id;
            self::resizeAvatarImage($_FILES['avatar_file']['tmp_name'], $target_file_path, Config::get('AVATAR_SIZE'), Config::get('AVATAR_SIZE'));

            //make photo ukuran medium (200x200), untuk foto scan barcode, identitas, dll
            $target_file_path = Config::get('PATH_AVATARS') . $user_id . '_medium';
            self::resizeAvatarImage($_FILES['avatar_file']['tmp_name'], $target_file_path, 220, 220);

            self::writeAvatarToDatabase($user_id);

            //update avatar file in session if user change their own avatar
            if ($user_id == Session::get('uid')) {
                Session::set('user_avatar_file', self::getPublicUserAvatarFilePathByUserId($user_id));
            }
            Session::add('feedback_positive', Text::get('FEEDBACK_AVATAR_UPLOAD_SUCCESSFUL'));
        }
    }

    /**
     * Checks if the avatar folder exists and is writable
     *
     * @return bool success status
     */
    public static function isAvatarFolderWritable()
    {
        if (is_dir(Config::get('PATH_AVATARS')) AND is_writable(Config::get('PATH_AVATARS'))) {
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE'));
        return false;
    }

    /**
     * Validates the image
     * Only accepts gif, jpg, png types
     * @see http://php.net/manual/en/function.image-type-to-mime-type.php
     *
     * @return bool
     */
    public static function validateImageFile()
    {
        if (!isset($_FILES['avatar_file'])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED'));
            return false;
        }

        // if input file too big (>5MB)
        if ($_FILES['avatar_file']['size'] > 3000000) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_TOO_BIG'));
            return false;
        }

        // get the image width, height and mime type
        $image_proportions = getimagesize($_FILES['avatar_file']['tmp_name']);

        // if input file too small, [0] is the width, [1] is the height
        if ($image_proportions[0] < Config::get('AVATAR_SIZE') OR $image_proportions[1] < Config::get('AVATAR_SIZE')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_TOO_SMALL'));
            return false;
        }

        // if file type is not jpg, gif or png
        if (!in_array($image_proportions['mime'], array('image/jpeg', 'image/gif', 'image/png'))) {
            Session::add('feedback_negative', Text::get('FEEDBACK_AVATAR_UPLOAD_WRONG_TYPE'));
            return false;
        }

        return true;
    }

    /**
     * Writes marker to database, saying user has an avatar now
     *
     * @param $uid
     */
    public static function writeAvatarToDatabase($uid)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_has_avatar = TRUE WHERE uid = :uid LIMIT 1");
        $query->execute(array(':uid' => $uid));
    }

    /**
     * Resize avatar image (while keeping aspect ratio and cropping it off in a clean way).
     * Only works with gif, jpg and png file types. If you want to change this also have a look into
     * method validateImageFile() inside this model.
     *
     * TROUBLESHOOTING: You don't see the new image ? Press F5 or CTRL-F5 to refresh browser cache.
     *
     * @param string $source_image The location to the original raw image
     * @param string $destination The location to save the new image
     * @param int $final_width The desired width of the new image
     * @param int $final_height The desired height of the new image
     *
     * @return bool success state
     */
    public static function resizeAvatarImage($source_image, $destination, $final_width = 44, $final_height = 44)
{
    if (!function_exists('getimagesize')) return false;
    $imageData = @getimagesize($source_image);
    if (!$imageData) return false;

    $width = $imageData[0];
    $height = $imageData[1];
    $mimeType = $imageData['mime'] ?? '';

    if (!$width || !$height) return false;

    // create source image based on mime (with fallbacks)
    $srcImg = false;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
        case 'image/pjpeg':
        case 'image/jfif':
            $srcImg = @imagecreatefromjpeg($source_image);
            break;
        case 'image/png':
            $srcImg = @imagecreatefrompng($source_image);
            break;
        case 'image/gif':
            $srcImg = @imagecreatefromgif($source_image);
            break;
        case 'image/bmp':
        case 'image/x-ms-bmp':
            if (function_exists('imagecreatefrombmp')) {
                $srcImg = @imagecreatefrombmp($source_image);
            } else {
                // fallback: try imagecreatefromstring
                $srcImg = @imagecreatefromstring(file_get_contents($source_image));
            }
            break;
        default:
            // try a generic fallback (handles some uncommon types)
            $srcImg = @imagecreatefromstring(file_get_contents($source_image));
            break;
    }

    if (!$srcImg) return false;

    // calculate scale to fit (preserve aspect ratio)
    $scale = min($final_width / $width, $final_height / $height);
    $new_width = (int) round($width * $scale);
    $new_height = (int) round($height * $scale);

    // create destination canvas (fixed final size)
    $thumb = imagecreatetruecolor($final_width, $final_height);

    // Fill background white to avoid black background after JPEG conversion
    $white = imagecolorallocate($thumb, 255, 255, 255);
    imagefilledrectangle($thumb, 0, 0, $final_width, $final_height, $white);

    // If source has transparency (PNG/GIF), preserve alpha while compositing onto white background
    if ($mimeType === 'image/png') {
        // ensure alpha blending off and save alpha on source
        imagealphablending($srcImg, true);
        imagesavealpha($srcImg, true);
    } elseif ($mimeType === 'image/gif') {
        // nothing special needed for source; we'll composite onto white
    }

    // calculate destination position to center the resized image
    $dst_x = (int) floor(($final_width - $new_width) / 2);
    $dst_y = (int) floor(($final_height - $new_height) / 2);

    // resample into center of canvas
    imagecopyresampled($thumb, $srcImg, $dst_x, $dst_y, 0, 0, $new_width, $new_height, $width, $height);

    // determine final filename: always .jpg because view expects .jpg
    $destFile = $destination . '.jpg';

    // JPEG quality from config or default 85
    $quality = (int) (Config::get('AVATAR_JPEG_QUALITY') ?: 85);

    // save as JPEG
    $saved = imagejpeg($thumb, $destFile, $quality);

    // cleanup
    imagedestroy($srcImg);
    imagedestroy($thumb);

    return $saved && file_exists($destFile);
}

    /**
     * Delete a user's avatar
     *
     * @param int $userId
     * @return bool success
     */
    public static function deleteAvatar($userId)
    {
        if (!ctype_digit($userId)) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }

        // try to delete image, but still go on regardless of file deletion result
        self::deleteAvatarImageFile($userId);

        $database = DatabaseFactory::getFactory()->getConnection();

        $sth = $database->prepare("UPDATE users SET user_has_avatar = 0 WHERE uid = :uid LIMIT 1");
        $sth->bindValue(":uid", (int)$userId, PDO::PARAM_INT);
        $sth->execute();

        if ($sth->rowCount() == 1) {
            Session::set('user_avatar_file', self::getPublicUserAvatarFilePathByUserId($userId));
            Session::add("feedback_positive", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_SUCCESSFUL"));
            return true;
        } else {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }
    }

    /**
     * Removes the avatar image file from the filesystem
     *
     * @param integer $userId
     * @return bool
     */
    public static function deleteAvatarImageFile($userId)
    {
        // Check if file exists
        if (!file_exists(Config::get('PATH_AVATARS') . $userId . ".jpg")) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_NO_FILE"));
            return false;
        }

        // Delete avatar file
        if (!unlink(Config::get('PATH_AVATARS') . $userId . ".jpg")) {
            Session::add("feedback_negative", Text::get("FEEDBACK_AVATAR_IMAGE_DELETE_FAILED"));
            return false;
        }

        return true;
    }
}
