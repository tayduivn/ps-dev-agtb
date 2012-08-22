<?php
require_once('summer/splash/boxoffice/lib/utils.php');
class BoxOffice
{

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
        $now = gmdate('Y-m-d');
        // FIXME: not lookup by password!
        $sql = 'SELECT * FROM users WHERE email = :email AND hash = :password AND deleted=0';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':password', $password, PDO::PARAM_STR);
        $sth->execute();
        $user = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sql = 'INSERT INTO logins (date_created, email, status) VALUES (:date_created, :email, :status)';
        $sth = $this->dbh->prepare($sql);
        if (empty($user)) {
            $status = 'Login Failed';
            $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
            $sth->bindParam(':email', $email, PDO::PARAM_STR);
            $sth->bindParam(':status', $status, PDO::PARAM_STR);
            $sth->execute();
            return false;
        }
        $status = 'Login Success';
        $this->user_id = $user[0]['id'];
        unset($user[0]['hash']);
        $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':status', $status, PDO::PARAM_STR);
        $sth->execute();
        return $user[0];
    }

    /**
     * Returns a user based on email and password
     * @param $email
     * @param $password
     * @return array
     */
    function authenticateRemoteUser($email, $remote_id)
    {
    	$now = gmdate('Y-m-d');
    	// FIXME:
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
    	$this->user_id = $user[0]['id'];
    	unset($user[0]['hash']);
    	$sth->bindParam(':date_created', $now, PDO::PARAM_STR);
    	$sth->bindParam(':email', $email, PDO::PARAM_STR);
    	$sth->bindParam(':status', $status, PDO::PARAM_STR);
    	$sth->execute();
    	return $user[0];
    }


    /**
     * Logs the fact that a user switched instances
     * @param $user_id
     * @param $email
     * @param $instance_id
     */
    function selectInstance($user_id, $email, $instance_id)
    {
        $sql = 'INSERT INTO logins (date_created, email, status, user_id, instance_id) VALUES (:date_created, :email, :status, :user_id, :instance_id)';
        $sth = $this->dbh->prepare($sql);
        $status = 'Switched Instance';
        $sth->bindParam(':date_created', $now, PDO::PARAM_STR);
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':status', $status, PDO::PARAM_STR);
        $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $sth->bindParam(':instance_id', $instance_id, PDO::PARAM_INT);
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
        $this->user_id = $user[0]['id'];
        unset($user[0]['hash']);
        return $user[0];
    }

    /**
     * Returns a list of instances related to a given user
     * @param null $user_id
     * @return array
     */
    function getUserInstances($user_id = null, $instance_id = null)
    {
        if (empty($user_id)) $user_id = $this->user_id;
        $today = gmdate('Y-m-d');
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
    function getUserModules($instance_id, $user_id = null)
    {
        if (empty($user_id)) $user_id = $this->user_id;
        $today = gmdate('Y-m-d');
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

    protected $user_params = array("first_name", "last_name", "company", "remote_id", "photo");

    /**
     * Registers a new User if the email address already exists it will throw an exception
     * @param string $email
     * @param string $hash
     * @param array $data
     * @param string $status
     * @throws Exception
     */
    function registerUser($email, $hash, $data, $status = 'Pending Confirmation')
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
            // FIXME: encrypt password
            $sth->bindParam(':password', $password, PDO::PARAM_STR);

            foreach($this->user_params as $key) {
                if(isset($data[$key])) {
                    $sth->bindParam(":$key", $data[$key], PDO::PARAM_STR);
                } else {
                    $dummy = null;
                    $sth->bindParam(":$key", $dummy, PDO::PARAM_NULL);
                }

            }
            $sth->execute();
            return $this->getUser($email);
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
            return true;
        }
        return false;
    }

    public function createUserInstance($email)
    {
        // TODO
    }




}



