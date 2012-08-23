<?php
require_once('modules/Trackers/BreadCrumbStack.php');
require_once('summer/splash/boxoffice/BoxOffice.php');
require_once('summer/splash/boxoffice/lib/BoxOfficeMail/BoxOfficeMail.php');
class BoxOfficeClient
{
    protected $user = null;
    protected $instances = null;
    protected $instance = null;
    protected static $client = null;
    protected $loginUrl;
    protected $session;
    protected $config = array();

    /**
     * returns a singleton of BoxOfficeClient
     * @static
     * @return BoxOfficeClient
     */
    public static function getInstance()
    {
        if (empty (self::$client)) self::$client = new BoxOfficeClient();
        return self::$client;

    }

    /**
     * contstructor for singleton
     */
    protected function __construct()
    {
        //We may need to start the session if it isn't already started
        $session_id = session_id();
        if (empty($session_id)) session_start();

        // FIXME: should be moved to REST API
        include('boxoffice/config.php');
        $this->config = $config;
        $this->box = new BoxOffice($config['dbconfig']);
        // FIXME
        $this->loginUrl = $config['top_url']."summer/splash/";

        // if we have config in session, use it, otherwise - load if from boxoffice
        // FIXME: should be passed as parameter, not in session
        if(isset($_REQUEST['login_token'])) {
            $this->session = $_REQUEST['login_token'];
        } else if(isset($_REQUEST['token'])) {
            $this->session = $_REQUEST['token'];
        }
        if(empty($_SESSION['boxoffice']) && !empty($this->session)) {
            $_SESSION['boxoffice'] = $this->box->getConfig($this->session);
        }
        if (!empty($_SESSION['boxoffice']['user'])) $this->user = $_SESSION['boxoffice']['user'];
        if (!empty($_SESSION['boxoffice']['instances'])) $this->instances = $_SESSION['boxoffice']['instances'];
        if (!empty($_SESSION['boxoffice']['instance'])) $this->instance = $_SESSION['boxoffice']['instance'];
        if (!empty($_SESSION['logged_in_user'])) $this->user = $_SESSION['logged_in_user'];

    }

    /**
     * Get config settings variable
     * @param string $name
     * @return string
     */
    public function getSetting($name)
    {
        return empty($this->config[$name])?'':$this->config[$name];
    }

    /**
     * authenticates a user based on the email address and password
     * @param $email
     * @param $password
     * @return array|bool
     */
    function authenticateUser($email, $password, $remoteID = null)
    {
        if(empty($password)) {
            $this->user = $this->box->authenticateRemoteUser($email, $remoteID);
        } else {
            $this->user = $this->box->authenticateUser($email, $password);
        }
        if ($this->user) {
            $_SESSION['logged_in_user'] = $this->user;
            return $this->getUserInstances();
        } else {
            return false;
        }

    }

    /**
     * Get list of current user's instances
     * @return array
     */
    public function getUserInstances()
    {
        if(empty($this->user['id'])) {
            $this->noLogin();
        }
        $instances = $this->box->getUserInstances($this->user['id']);
        $filteredList = array();
        foreach ($instances as $instance) {
        	$filteredList[$instance['id']] = array('id' => $instance['id'], 'name' => $instance['name'], 'owner' => array('name' => $instance['first_name'] . ' ' . $instance['last_name'], 'email' => $instance['email']));
        }
        $this->instances = $filteredList;
        return array('user' => $this->user, 'instances' => $filteredList);
    }

    /**
     * Set user's oauth tokens
     * @param string $token
     * @param string $refreshToken
     * @param int $expires
     */
    public function setUserTokens($token, $refreshToken, $expires)
    {
        if(empty($this->user['id'])) {
            return;
        }
        $this->box->setUserTokens($this->user['id'], $token, $refreshToken, $expires);
    }

    /**
     * sets the current instance based on the instance id
     * @param $instance_id
     * @return instance
     * @throws Exception
     */
    function selectInstance($instance_id)
    {
        if (empty($this->user['id'])) {
            $this->noLogin();
        }
        $instances = $this->box->getUserInstances($this->user['id'], $instance_id);
        if (empty($instances)) throw new Exception('User Does Not Have Access To This Instance');
        $this->instance = $instances[0];
        if($this->instance['status'] == 'Pending') {
            return false;
        }
        $this->session = $this->box->selectInstance($this->user['id'], $this->user['email'], $this->instance['id']);
        return $this->session;
    }

    /**
     * returns the config of the current instance based on session
     * @return array
     * @throws Exception
     */
    function getConfig()
    {
        if (empty($this->instance)) {
            $this->noLogin();
        }
        $flavor = strtolower($this->instance['flavor']);
        include('summer/splash/configs/' . $flavor . '.config.php');
        foreach ($this->instance['config'] as $k => $v) {
            $sugar_config[$k] = $v;
        }
        return $sugar_config;
    }

    /**
     * loads the configs for a given instance to allow the creating of a user
     */
    function bootstrapInstance()
    {
        global $locale, $db;
        if(!defined('sugarEntry'))define('sugarEntry', true);
        $sugar_config = $this->getConfig();
        $GLOBALS['sugar_config'] = $sugar_config;
        require_once('include/entryPoint.php');

        if (file_exists('config_override.php')) {
            include('config_override.php');
        }
        $this->setupUser();
    }

    /**
     * returns the currently selected instance
     * @return instance
     */
    function getCurrentInstance()
    {
        return $this->instance;
    }

    public function createSession()
    {
        if(empty($this->session) || empty($this->user) || empty($this->instance)) {
            return;
        }
        $usr_id = $this->setupUser();

        if(!empty($usr_id)) {
            // reset the session since we're changing security context
            session_regenerate_id();
            $_SESSION['authenticated_user_id'] = $usr_id;
            $GLOBALS['current_user'] = new User();
            $GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
            $ac = AuthenticationController::getInstance();
            $ac->authController->postLoginAuthenticate();
            // after we're done, we don't need session token anymore, delete it
            $this->box->deleteSession($this->user['id'], $this->instance['id']);
        }
    }

    /**
     * Creates or updates the user in the session on the instance o
     * @throws Exception
     */
    function setupUser()
    {
        if (empty($this->user['id'])) {
            $this->noLogin();
        }
        $data = $this->user;
        $id = 'rmt-'.md5($data['remote_id']);
        $user = new User();
        $user->retrieve($id);
        if (empty($user->id)) {
            // create new user
            $user->id = $id;
            $user->new_with_id = true;
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->email1 = $data['email'];
            $user->user_name = $data['email'];
            $user->receive_notifications = 0;
            $user->status = 'Active';
	        $user->is_admin = 0;
	        $user->external_auth_only = 1;
	        $user->system_generated_password = 0;
	        $user->authenticate_id = $data['remote_id'];
	        if(!empty($data['photo'])) {
	        	$picid = create_guid();
	        	if(copy($data['photo'], "upload://$picid")) {
	        		$user->picture = $picid;
	        	}
	        }
            $user->save();
            $user->setPreference('ut', 1);
            $user->savePreferencesToDB();
        } else {
            //always update on login
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->email = $data['email'];
            $user->email1 = $data['email'];
            $user->user_name = $data['email'];
            $user->save();
        }
        return $user->id;
    }

    /**
     * filters a list of modules based on the modules available to a given instance.
     * @param $moduleList
     * @return mixed
     * @throws Exception
     */
    public function filterModules()
    {

        if (empty($this->instance)) {
            $this->noLogin();
        }
        if (1 || empty($_SESSION['moduleList'])) {
            $_SESSION['moduleList'] = $this->box->getUserModules($this->instance['id'], $this->user['id']);
        }

        return $_SESSION['moduleList'];
    }

    /**
     * Registers a user into the system creates a confirmation record and sends an activation email out to the user
     * @param string $email
     * @param string $password
     * @param array $data
     * @return bool
     */
    public function registerUser($email, $password, $data){
        $user = $this->box->registerUser($email, $password, $data);
        $guid = $this->box->generateConfirmation($email);
        BoxOfficeMail::sendTemplate($email, 'activateuser', array('user'=>$user, 'guid'=>$guid, 'config' => $this->config));
        return true;
    }

    /**
     * Create remote user in the system, without confirmation
     * @param $email
     * @return bool
     */
    public function createRemoteUser($email, $data)
    {
        $user = $this->box->registerUser($email, "", $data, 'Active');
        return true;
    }

    /**
     * given an email it will return the user for that email
     * @param $email
     * @param bool $throwException
     * @return array
     */
    public function getUser($email, $throwException=true)
    {
        return $this->box->getUser($email, $throwException);
    }

    /**
     * Changes a user's status from Pending Confirmation to Active based on the email and confirmation guid
     * @param $email
     * @param $guid
     * @return bool
     */
    public function activateUser($email, $guid)
    {
        return $this->box->activateUser($email, $guid, $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Sends an Activation email to a given email address
     * @param $email
     * @return bool
     */
    public function resendActivation($email)
    {
        $user = $this->box->getUser($email, true);
        if($user['status'] == 'Pending Confirmation'){
            $guid = $this->box->generateConfirmation($email);
            BoxOfficeMail::sendTemplate($email, 'activateuser', array('user'=>$user, 'guid'=>$guid));
            return true;
        }
        return false;
    }

    /**
     * Reset password for uses
     * @param string $email
     * @return boolean
     */
    public function requestPasswordReset($email){
        $user = $this->box->getUser($email, true);
        $guid = $this->box->generateConfirmation($email, 'Reset Password');
        BoxOfficeMail::sendTemplate($email, 'activateuser', array('user'=>$user, 'guid'=>$guid));
        return true;
    }

    /**
     * Return current user data
     * @return array
     */
    public function getCurrentUser()
    {
        return $this->user;
    }

    /**
     * Delete current login session from BoxOffice
     */
    public function deleteSession()
    {
        if(empty($this->user) || empty($this->instance)) {
            return;
        }
        $this->box->deleteSession($this->user['id'], $this->instance['id']);
    }

    /**
     * Get initial login URL
     * @return string
     */
    public function loginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * Error condition: not logged in
     * @throws Exception
     */
    public function noLogin()
    {
        //throw new Exception("No Login!");
        ob_end_clean();
        header('Location: '.$this->loginUrl());
        exit();
    }

    /**
     * Get current session token
     */
    public function getToken()
    {
        return $this->session;
    }
}