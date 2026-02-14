<?php

/**
 * UserController
 * Controls everything that is user-related
 */
class UserController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class.
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions.
        Auth::checkAuthentication();
    }

    /**
     * Show user's PRIVATE profile
     */
    public function index()
    {
		if (Session::get('user_provider_type') == 'employee') {
			
         $this->View->render('user/index', array(
			'uid' => Session::get('uid'),
            'user_name' => Session::get('user_name'),
            'email' => Session::get('email'),
            'user_gravatar_image_url' => Session::get('user_gravatar_image_url'),
            'user_avatar_file' => Session::get('user_avatar_file'),
            'user_account_type' => Session::get('user_account_type')
        ));
		} else { 
		Redirect::to('pegawai/profil/' . Session::get('uid')); }
    }

    /**
     * Show edit-my-username page
     */
    public function editUsername()
    {
        
		Auth::isInSession('user_provider_type', 'employee');
		$user_id = Session::get('uid');
		$user_name = Session::get('user_name');
		$full_name = Session::get('full_name');
		$this->View->render('user/editUsername', array(
            'user_id' => $user_id,
			'user_name' => $user_name,
			'full_name' => $full_name	
		));
    }

    /**
     * Edit user name (perform the real action after form has been submitted)
     */
    public function editUsername_action()
    {
        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        UserModel::editUserName(Request::post('user_name'));
        Redirect::to('user/editUsername');
    }

    /**
     * Show edit-my-user-email page
     */
    public function editUserEmail()
    {
        $this->View->render('user/editUserEmail');
    }

    /**
     * Edit user email (perform the real action after form has been submitted)
     */
    // make this POST
    public function editUserEmail_action()
    {
        UserModel::editUserEmail(Request::post('email'));
        Redirect::to('user/editUserEmail');
    }

    /**
     * Edit avatar
     */
    public function editAvatar($user_id = null)
    {
		
        $this->View->render('user/editAvatar', array(
            'user_id' => $user_id
		
        ));
    }
	
	
	public function editAvatarMhs($user_id = null)
    {
        $this->View->renderMhs('user/editAvatarMhs', array(
            'user_id' => $user_id
        ));
    }

    /**
     * Perform the upload of the avatar
     * POST-request
     */
    public function updateAvatar($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Session::get('uid');
        }
        AvatarModel::createAvatar($user_id);
        Redirect::to('user/editAvatar/' . $user_id);
    }
	
	 public function updateAvatarMhs($user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Session::get('uid');
        }
        AvatarModel::createAvatar($user_id);
        Redirect::to('user/editAvatarMhs/' . $user_id);
    }


    /**
     * Delete the current user's avatar
     */
    public function deleteAvatar_action()
    {
        AvatarModel::deleteAvatar(Session::get("uid"));
        Redirect::to('user/editAvatar');
    }
	
	
	public function deleteAvatarMhs_action()
    {
        AvatarModel::deleteAvatar(Session::get("uid"));
        Redirect::to('user/editAvatarMhs');
    }

    /**
     * Show the change-account-type page
     */
    public function changeUserRole()
    {
        $this->View->render('user/changeUserRole');
    }

    /**
     * Perform the account-type changing
     * POST-request
     */
    public function changeUserRole_action()
    {
        if (Request::post('user_account_upgrade')) {
            // "2" is quick & dirty account type 2, something like "premium user" maybe. you got the idea :)
            UserRoleModel::changeUserRole(2);
        }

        if (Request::post('user_account_downgrade')) {
            // "1" is quick & dirty account type 1, something like "basic user" maybe.
            UserRoleModel::changeUserRole(1);
        }

        Redirect::to('user/changeUserRole');
    }

    /**
     * Password Change Page
     */
    public function changePassword()
    {
        //Auth::isInSession('user_provider_type', 'employee');
		$user_id = Session::get('uid');		
		$full_name = Session::get('full_name');
		$this->View->render('user/changePassword', array(
            'user_id' => $user_id,			
			'full_name' => $full_name	
		));
    }

    /**
     * Password Change Action
     * Submit form, if retured positive redirect to index, otherwise show the changePassword page again
     */
    public function updatePassword()
    {
        $result = PasswordResetModel::changePassword(
            Session::get('uid'), Request::post('user_password_current'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );
        
        if($result)
            Redirect::to('user/index');
        else
            Redirect::to('user/changePassword');
        
    }

    /**
     * Register page
     * Show the register form, but redirect to main-page if user is already logged-in
     */
    public function newUser()
    {

            $this->View->render('register/index');
    }
}

