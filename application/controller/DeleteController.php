<?php

/**
 * The note controller: Just an example of simple create, read, update and delete (CRUD) actions.
 */
class DeleteController extends Controller
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

    function index()
    {
        Redirect::to('dashboard');
    }

    public function remove($table, $uid, $value)
    {
        GenericModel::remove($table, $uid, $value);
        Redirect::to(Request::get('forward'));
    }

    public function removeAjax($table, $uid, $value)
    {
        GenericModel::remove($table, $uid, $value);
        $feedback_positive = Session::get('feedback_positive');
        $feedback_negative = Session::get('feedback_negative');
        // echo out positive messages
        if (isset($feedback_positive)) {
            echo 'SUKSES, ' . count($feedback_positive) . ' data berhasil dihapus';           
        }
        // echo out negative messages
        if (isset($feedback_negative)) {
            echo 'GAGAL!, ' . count($feedback_positive) . ' data berhasil dihapus';
        }
        // RESET counter feedback to unconfuse user
        Session::set('feedback_positive', null);
        Session::set('feedback_negative', null);
    }

    public function soft($table, $uid, $value)
    {
        $update = array(
                        'is_deleted'      =>  1,
                        'modifier_id'    => SESSION::get('uid')
                        );
        $cond = "`$uid` = '$value'";
        GenericModel::update($table, $update, $cond);
        Redirect::to(Request::get('forward'));
    }
	
	 public function data($table, $uid, $value)
    {
        $update = array(
                        'status'      =>  1                        
                        );
        $cond = "`$uid` = '$value'";
        GenericModel::update($table, $update, $cond);
        Redirect::to(Request::get('forward'));
    }
	
	 public function status($table, $uid, $value)
    {
        $update = array(
                        'status'      =>  0                        
                        );
        $cond = "`$uid` = '$value'";
        GenericModel::update($table, $update, $cond);		
		$feedback_positive = Session::get('feedback_positive');
        $feedback_negative = Session::get('feedback_negative');
		if (isset($feedback_positive)) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
			Session::add('feedback_positive', 'SUKSES <strong>'. count($feedback_positive) .' </strong> data berhasil dihapus');						           
        }
        // echo out negative messages
        if (isset($feedback_negative)) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
			Session::add('feedback_negative', 'GAGAL <strong>'. count($feedback_negative) .' </strong> data gagal dihapus');			
        }
        // RESET counter feedback to unconfuse user
        
    }
}
