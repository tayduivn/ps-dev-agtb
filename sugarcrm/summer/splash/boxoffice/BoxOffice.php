<?php
require_once('summer/splash/boxoffice/lib/utils.php');
/**
 * BoxOffice server class
 * FIXME: should be rewritten as set of REST API
 * There are two kinds of functions here:
 * - Trusted API (should be only accesses by Splash)
 * - Client API (can be accessed by Summer)
 *
 * Client API functions should be marked as such and should verify the client has valid session.
 */
class BoxOffice
{

    const FREE_LIFETIME = 7776000; // 90 days

    function __construct($dbconfig)
    {
        $this->dbh = new PDO('mysql:host=' . $dbconfig['host'] . ';dbname=' . $dbconfig['name'], $dbconfig['user'], $dbconfig['password']);
    }

    /**
     * Returns a user based on email and password
     * @param $email
     * @param $password
     * @return array
     */
    function authenticateUser($email, $password)
    {
        if(empty($password)) {
            return false;
        }
        $now = gmdate('Y-m-d');
        $sql = 'SELECT * FROM users WHERE email = :email AND deleted=0';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->execute();
        $user = $sth->fetch(PDO::FETCH_ASSOC);
        $sql = 'INSERT INTO logins (date_created, email, status) VALUES (:date_created, :email, :status)';
        $sth = $this->dbh->prepare($sql);
        if(!crypt($password, $user['hash']) === $user['hash']) {
            $user = false;
        }
        if (empty($user)) {
            $status = 'Login Failed';
            $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
            $sth->bindParam(':email', $email, PDO::PARAM_STR);
            $sth->bindParam(':status', $status, PDO::PARAM_STR);
            $sth->execute();
            return false;
        }
        $status = 'Login Success';
        unset($user['hash']);
        $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':status', $status, PDO::PARAM_STR);
        $sth->execute();
        return $user;
    }

    /**
     * Returns a user based on email and password
     * @param $email
     * @param $password
     * @return array
     */
    function authenticateRemoteUser($email, $remote_id)
    {
    	$now = gmdate('Y-m-d H:i:s');
    	$sql = 'SELECT * FROM users WHERE email = :email AND remote_id = :rid AND deleted=0';
    	$sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    	$sth->bindParam(':email', $email, PDO::PARAM_STR);
    	$sth->bindParam(':rid', $remote_id, PDO::PARAM_STR);
    	$sth->execute();
    	$user = $sth->fetchAll(PDO::FETCH_ASSOC);
    	if (empty($user)) {
    		return false;
    	}
    	$sql = 'INSERT INTO logins (date_created, email, status) VALUES (:date_created, :email, :status)';
    	$sth = $this->dbh->prepare($sql);
    	$status = 'Login Success';
    	unset($user[0]['hash']);
    	$sth->bindParam(':date_created', $now, PDO::PARAM_STR);
    	$sth->bindParam(':email', $email, PDO::PARAM_STR);
    	$sth->bindParam(':status', $status, PDO::PARAM_STR);
    	$sth->execute();
    	return $user[0];
    }


    /**
     * Logs the fact that a user switched instances and create new session
     * @param $user_id
     * @param $email
     * @param $instance_id
     */
    function selectInstance($user_id, $email, $instance_id)
    {
        $inst = $this->getInstanceById($instance_id);
        if(empty($inst) || $inst['status'] == 'Pending' || empty($inst['config']['dbconfig'])) {
            return null;
        }
        $sql = 'INSERT INTO logins (date_created, email, status, user_id, instance_id) VALUES (:date_created, :email, :status, :user_id, :instance_id)';
        $sth = $this->dbh->prepare($sql);
        $status = 'Switched Instance';
        $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':status', $status, PDO::PARAM_STR);
        $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $sth->bindParam(':instance_id', $instance_id, PDO::PARAM_INT);
        $sth->execute();

        $sql = 'INSERT INTO sessions (id, user_id, instance_id) VALUES (:id, :user_id, :instance_id)';
        $sth = $this->dbh->prepare($sql);
        $newsession = generate_guid();
        $sth->bindParam(':id', $newsession, PDO::PARAM_STR);
        $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $sth->bindParam(':instance_id', $instance_id, PDO::PARAM_INT);
        $sth->execute();
        return $newsession;
    }

    /**
     * Get session config
     * CLIENT API
     * @param string $session
     * @return array|false
     */
    public function getConfig($session)
    {
        $sth = $this->dbh->prepare('SELECT * FROM sessions WHERE id=:id AND login_time IS NULL');
        $sth->execute(array(":id" => $session));
        $sess = $sth->fetch(PDO::FETCH_ASSOC);
        if(empty($sess) || empty($sess['user_id']) || empty($sess['instance_id'])) {
            return false;
        }
        $sth = $this->dbh->prepare('UPDATE sessions SET login_time=:now WHERE id=:id');
        $sth->execute(array(":id" => $session, ':now' => gmdate('Y-m-d H:i:s')));

        $user = $this->getUserById($sess['user_id']);
        $instance = $this->getInstanceById($sess['instance_id']);
        return array('user' => $user, 'instance' => $instance, "token" => $session);
    }

    /**
     * Get all users on the same instance as my session and all my instances
     * CLIENT API
     * @param string $session
     */
    public function getUsersInstances($session)
    {
        $sth = $this->dbh->prepare('SELECT * FROM sessions WHERE id=:id');
        $sth->execute(array(":id" => $session));
        $sess = $sth->fetch(PDO::FETCH_ASSOC);
        if(empty($sess) || empty($sess['user_id']) || empty($sess['instance_id'])) {
        	return false;
        }

        $sth = $this->dbh->prepare('SELECT users.*, sessions.login_time FROM sessions
            INNER JOIN users_instances ui ON ui.instance_id=sessions.instance_id AND ui.deleted=0
            INNER JOIN users ON users.id = ui.user_id AND users.deleted=0
          WHERE sessions.id=:id');
        $sth->execute(array(":id" => $session));
        $users = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach($users as $k => $data) {
            // drop hashes
            unset($users[$k]['hash']);
            unset($users[$k]['oauth_token']);
            unset($users[$k]['refresh_token']);
            unset($users[$k]['token_expires']);
        }

        $instances = $this->getUserInstances($sess['user_id']);
        foreach($instances as $k => $inst) {
            // drop configs and licenses, no need to send it out
            unset($instances[$k]['config']);
            unset($instances[$k]['license']);
        }

        return array("users" => $users, "instances" => $instances);
    }

    /**
     * Invite the user into the instance
     * CLIENT API
     * @param string $session
     * @param string $email
     * @return boolean
     */
    public function inviteUser($session, $email)
    {
        $sth = $this->dbh->prepare('SELECT * FROM sessions WHERE id=:id');
        $sth->execute(array(":id" => $session));
        $sess = $sth->fetch(PDO::FETCH_ASSOC);
        if(empty($sess) || empty($sess['user_id']) || empty($sess['instance_id'])) {
        	return false;
        }

        $sth = $this->dbh->prepare('INSERT INTO invites(email, instance_id, date_created) VALUES(:email, :instance_id, :now)');
        $sth->execute(array(
            ":email" => $email,
            ":instance_id" => $sess['instance_id'],
            ":now" => gmdate('Y-m-d H:i:s')
        ));
        return true;
    }

    /**
     * Delete session by user/instance
     * @param string $user
     * @param string $instance
     */
    public function deleteSession($user, $instance)
    {
        $sth = $this->dbh->prepare("DELETE FROM sessions WHERE user_id=:user AND instance_id=:instance");
        $sth->execute(array(":user" => $user, ":instance" => $instance));
    }

    /**
     * Delete session by ID
     * CLIENT API
     * @param string $session
     */
    public function deleteSessionById($session)
    {
        $sth = $this->dbh->prepare("DELETE FROM sessions WHERE id=:id");
        $sth->execute(array(":id" => $session));
    }

    /**
     * Get user by ID
     * @param int $id
     * @return array
     */
    public function getUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id AND deleted=0';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(":id" => $id));
        $user = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (empty($user)) {
            return null;
        }
        unset($user[0]['hash']);
        return $user[0];
    }

    /**
     * Get instance by ID
     * @param int $id
     * @return array
     */
    public function getInstanceById($id)
    {
        $sql = 'SELECT * FROM instances WHERE id = :id AND deleted=0 AND date_start <= :date AND date_end >= :date';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(":id" => $id, ":date" => gmdate('Y-m-d H:i:s')));
        $inst = $sth->fetch(PDO::FETCH_ASSOC);
        if (empty($inst)) {
            return null;
        }
        if(!empty($inst['config'])) {
            $inst['config'] = json_decode($inst['config'], true);
        }

        return $inst;
    }

    /**
     * returns a given user based on email address
     * @param $email
     * @param bool $throwException
     * @return array
     */
    function getUser($email, $throwException = true)
    {
        $sql = 'SELECT * FROM users WHERE email = :email AND deleted=0';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->execute();
        $user = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (empty($user)) {
            if ($throwException) throw new Exception('User does not exist');
            else return null;
        }
        unset($user[0]['hash']);
        return $user[0];
    }

    /**
     * Returns a list of instances related to a given user
     * @param null $user_id
     * @return array
     */
    function getUserInstances($user_id, $instance_id = null)
    {
        $this->addInvites($user_id);

        $today = gmdate('Y-m-d H:i:s');
        $sql = 'SELECT instances.*, owner.first_name, owner.last_name, owner.email FROM instances INNER JOIN users owner on owner.id = instances.owner_id AND owner.deleted = 0 INNER JOIN users_instances ui ON ui.user_id = :user_id AND ui.instance_id = instances.id  AND ui.deleted = 0 WHERE instances.date_start <= :date AND instances.date_end >= :date AND instances.deleted=0';
        if (!empty ($instance_id)) {
            $sql .= ' AND instances.id = :instance_id';
        }
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $sth->bindParam(':date', $today, PDO::PARAM_STR);
        if (!empty ($instance_id)) {
            $sth->bindParam(':instance_id', $instance_id, PDO::PARAM_INT);
        }
        $sth->execute();
        $instances = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($instances as $k => &$v) {
            if (!empty($v['config'])) $v['config'] = json_decode($v['config'], true);

        }

        return $instances;
    }

    /**
     * Returns a list of modules for a given user based on their instance
     * @param $instance_id
     * @param null $user_id
     * @return array
     */
    function getUserModules($instance_id, $user_id)
    {
        $today = gmdate('Y-m-d H:i:s');
        $modules = array();
        $instances = $this->getUserInstances($user_id, $instance_id);
        if (empty($instances)) return array();
        $instance = $instances[0];
        $sql = 'SELECT * FROM modules
        INNER JOIN modules_instances mi ON modules.id = mi.module_id AND mi.instance_id = :instance_id AND mi.date_start <= :date AND mi.date_end >= :date AND mi.deleted = 0
        INNER JOIN users_instances ui ON ui.user_id = :user_id AND ui.instance_id = mi.instance_id AND ui.deleted = 0
        WHERE modules.deleted=0';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':instance_id', $instance_id, PDO::PARAM_INT);
        $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $sth->bindParam(':date', $today, PDO::PARAM_STR);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            $modules[$result['name']] = $result;
        }
        $flavor = strtolower($instance['flavor']);
        if (file_exists(dirname(__FILE__) . '/flavors/' . $flavor . '.modules.php')) {
            include(dirname(__FILE__) . '/flavors/' . $flavor . '.modules.php');
            foreach ($flavors[$flavor]['modules'] as $module) {
                if (empty($modules[$module])) {
                    $modules[$module] = array('name' => $module, 'deleted' => 0, 'date_start' => $instance['date_start'], 'date_end' => $instance['date_end']);
                }
            }
        }

        return $modules;

    }

    /**
     * Hash password
     * @param string $password
     * @return string
     */
    protected function hashPassword($password)
    {
        return crypt($password, '$2y$09$'.base_convert(md5(mcrypt_create_iv(32)), 16, 36));
    }

    protected $user_params = array("first_name", "last_name", "company", "remote_id", "photo");

    /**
     * Registers a new User if the email address already exists it will throw an exception
     * @param string $email
     * @param string $hash
     * @param array $data
     * @param string $status
     * @throws Exception
     */
    function registerUser($email, $password, $data, $status = 'Pending Confirmation')
    {
        if (!$this->getUser($email, false)) {
            $now = gmdate('Y-m-d H:i:s');
            $params_i = implode(" ,", $this->user_params);
            $params_v = implode(" ,:", $this->user_params);
            $sql = "INSERT INTO users (email,hash, date_created, date_modified, status, $params_i)
                VALUES (:email, :password, :now, :now, :status, :$params_v)";
            $sth = $this->dbh->prepare($sql);
            $sth->bindParam(':email', $email, PDO::PARAM_STR);
            $sth->bindParam(':now', $now, PDO::PARAM_STR);
            $sth->bindParam(':status', $status, PDO::PARAM_STR);
            $sth->bindParam(':password', empty($password)?'':$this->hashPassword($password), PDO::PARAM_STR);

            foreach($this->user_params as $key) {
                if(isset($data[$key])) {
                    $sth->bindParam(":$key", $data[$key], PDO::PARAM_STR);
                } else {
                    $dummy = null;
                    $sth->bindParam(":$key", $dummy, PDO::PARAM_NULL);
                }

            }
            $sth->execute();
            $user = $this->getUser($email);
            if($user['status'] == 'Active') {
                $this->createUserInstance($user);
            }
        } else {
            throw new Exception('User Already Exists');
        }

    }

    /**
     * sends a confirmation email to the given user
     * @param int $user_id
     * @param string $type
     * @param int $hoursTillExpires
     */
    function generateConfirmation($email, $type = 'Activate User', $hoursTillExpires = 24)
    {
        $user = $this->getUser($email);
        $guid = generate_guid();
        $now = gmdate('Y-m-d H:i:s');
        $until = gmdate('Y-m-d H:i:s', time() + ($hoursTillExpires * 60 * 60));
        $sql = "UPDATE confirmations SET date_modified=:now, status='Expired' WHERE type=:type AND user_id=:user_id";
        $sth = $this->dbh->prepare($sql);

        $sth->execute(array(
            ':now' => $now,
            ':type' => $type,
            ':user_id' => $user['id']
        ));

        $sql = "INSERT INTO confirmations (user_id,date_created,date_modified, guid,type,until, status) VALUES (:user_id,:now, :now, :guid, :type, :until,  'Pending')";
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array(':user_id' => $user['id'],
            ':now' => $now,
            ':guid' => $guid,
            ':type' => $type,
            ':until' => $until
        ));
        return $guid;
    }


    /**
     * Activates a given user based on their email and confirmation hash
     * @param $email
     * @param $hash
     * @param $ip_address
     */
    function activateUser($email, $hash, $ip_address)
    {
        $now = gmdate('Y-m-d H:i:s');

        $sql = "SELECT confirmations.id, confirmations.user_id FROM confirmations INNER JOIN users on confirmations.user_id = users.id AND users.deleted=0 AND users.email=:email WHERE confirmations.status = 'Pending' AND confirmations.guid=:guid AND confirmations.until >= :now";
        $sth = $this->dbh->prepare($sql);
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':guid', $hash, PDO::PARAM_STR);
        $sth->bindParam(':now', $now, PDO::PARAM_STR);
        $sth->execute();
        $confirmation = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($confirmation[0]['user_id'])) {
            $sqlUserUpdate = "UPDATE users SET status='Active', date_modified=:now WHERE id=:id";
            $sth = $this->dbh->prepare($sqlUserUpdate);
            $sth->bindParam(':now', $now, PDO::PARAM_STR);
            $sth->bindParam(':id', $confirmation[0]['user_id'], PDO::PARAM_INT);
            $sth->execute();
            $sqlConfirmationUpdate = "UPDATE confirmations SET status='Complete', date_modified=:now, ip_address=:ip_address WHERE id=:id";
            $sth = $this->dbh->prepare($sqlConfirmationUpdate);
            $sth->bindParam(':now', $now, PDO::PARAM_STR);
            $sth->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
            $sth->bindParam(':id', $confirmation[0]['id'], PDO::PARAM_INT);
            $sth->execute();
            $user = $this->getUser($email);
            if($user['status'] == 'Active') {
            	$this->createUserInstance($user);
            }

            return true;
        }
        return false;
    }

    /**
     * Add invited user to instances where he is invited
     * @param string $email
     */
    protected function addInvites($user_id)
    {
        $sth = $this->dbh->prepare("SELECT invites.* FROM invites INNER JOIN users on users.email=invites.email WHERE users.id=:id");
        $sth->execute(array(":id" => $user_id));
        $invh = $this->dbh->prepare("INSERT INTO users_instances(date_created, date_modified, user_id, instance_id) VALUES(:now, :now, :userid, :instanceid)");
        $delh = $this->dbh->prepare("DELETE FROM invites WHERE id=:id");
        foreach($sth->fetchAll(PDO::FETCH_ASSOC) as $invite) {
            $invh->execute(array(
                ':now' => gmdate('Y-m-d H:i:s'),
                ':userid' => $user_id,
                ':instanceid' => $invite['instance_id']
            ));
            $delh->execute(array(":id" => $invite['id']));
        }
    }

    /**
     * Set oauth tokens
     * @param int $user_id
     * @param string $token
     * @param string $refresh
     * @param int $expires
     */
    public function setUserTokens($user_id, $token, $refresh, $expires)
    {
        if(!empty($refresh)) {
            $sth = $this->dbh->prepare("UPDATE users SET oauth_token=:token, refresh_token=:refresh, token_expires=:expires WHERE id=:id");
            $sth->execute(array(
            ":token" => $token,
            ":refresh" => $refresh,
            ":expires" => gmdate('Y-m-d H:i:s', time()+$expires),
            ":id" => $user_id
            ));
        } else {
            $sth = $this->dbh->prepare("UPDATE users SET oauth_token=:token, token_expires=:expires WHERE id=:id");
            $sth->execute(array(
            ":token" => $token,
            ":expires" => gmdate('Y-m-d H:i:s', time()+$expires),
            ":id" => $user_id
            ));
        }
    }

    /**
     * Create personal instance for given user
     * @param array $user
     */
    public function createUserInstance($user)
    {
        $sth = $this->dbh->prepare("SELECT * FROM instances WHERE owner_id = :id");
        $sth->execute(array(":id" => $user['id']));
        if($sth->fetch(PDO::FETCH_ASSOC)) {
            return; // already have one
        }
        $sth = $this->dbh->prepare("INSERT INTO
            instances(owner_id, date_created, date_modified, name, company, date_start, date_end, flavor, status)
        VALUES (:owner, :now, :now, :name, :company, :now, :end, 'free', 'Pending')
        ");
        $sth->execute(array(
            ":owner" => $user['id'],
            ":name" => "{$user['first_name']} {$user['last_name']}'s instance",
            ":now" => gmdate('Y-m-d H:i:s'),
            ":end" => gmdate('Y-m-d H:i:s', time()+self::FREE_LIFETIME),
            ":company" => $user['company'],
        ));
        $newinst = $this->dbh->lastInsertId();
        $sth = $this->dbh->prepare("INSERT INTO users_instances(user_id, instance_id, date_created, date_modified)
        VALUES (:owner, :instance, :now, :now)");
        $sth->execute(array(
            ":owner" => $user['id'],
            ":instance" => $newinst,
            ":now" => gmdate('Y-m-d H:i:s')
        ));
    }




}



