<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/MVC/SugarModule.php';
/**
 * SNIP data handling implementation
 */
class SugarSNIP
{
    // Username for SNIP system user
    const SNIP_USER = 'SNIPuser';

    /**
     * Singleton instance
     * @var SugarSNIP
     */
    public static $instance;

    /**
     * Sugar configuration
     * @var array
     */
    public $config;

    /**
     * Last REST call result
     * @var mixed
     */
    public $last_result;

    /**
     * Get instance of the SNIP client
     * @return SugarSNIP SNIP client instance
     */
    public static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __construct()
    {
        global $sugar_config;
        $this->config = $sugar_config;
        $this->setClient(new SugarSNIPClient());
    }

    /**
    * Set client to talk to SNIP
    * @param SugarSNIPClient $client
    */
    public function setClient(SugarSNIPClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Generic REST call to SNIP instance
     *
     * @param string $name function to call
     * @param string $params parameters
     * @param bool $json encode params as JSON in data var or send as query?
     * @return bool Success?
     */
    public function callRest($name, $params = array(), $json = false)
    {
        if(isset($params['url'])) {
            $url = $params['url'];
            unset($params['url']);
        } else {
            $url = $this->getSnipURL();
        }

        $url .= $name;
        $params["sugarkey"] = $this->config['unique_key'];
        if($json) {
            $postArgs = http_build_query(array('data' => json_encode($params)));
        } else {
            $postArgs = http_build_query($params);
        }
        $response = $this->client->callRest($url, $postArgs);
        if(!empty($response)) {
            $result = json_decode($response);
        } else {
            $GLOBALS['log']->debug("SNIP: REST request failed");
            return false;
        }
        $this->last_result = $result;
        $GLOBALS['log']->debug(var_export($result, true));
        return is_object($result) && $result->result == 'ok';
    }

    /**
     * Get instance callback URL
     * @return string
     */
    public function getURL()
    {
        return $this->config['site_url'].'/service/v3snip/rest.php';
    }

    /**
     * Set SNIP instance URL
     * @param string $url
     */
    public function setSnipURL($url)
    {
        $cfg = new Configurator();
        $cfg->config['snip_url']=$url;
        $cfg->handleOverride();
        $this->config['snip_url'] = $url;
        return $this;
    }

    /**
     * Get SNIP instance URL
     * @return string
     */
    public function getSnipURL()
    {
        if (!isset($this->config['snip_url']))
            return 'http://localhost:20000/';
        return $this->config['snip_url'];
    }

    /**
     * Get result of the last REST call
     * @return mixed
     */
    public function getLastResult()
    {
        return $this->last_result;
    }

    /**
     * Check if SNIP interface is active
     * @return bool
     */
    public function isActive()
    {
        return $this->getSnipURL() != '';
    }

    /**
     * Register this installation with SNIP
     * @param array $params Registration parameters
     */
    public function register($params)
    {
        $params["client_api_url"] = $this->getURL();
        $user = $this->getSnipUser();
        $params["user"] = $user->user_name;
        $params["password"] = $user->user_hash;
        $res = $this->callRest("registerApplication", $params, true);
        if($res) {
            SugarSNIPHook::installHook();
            $this->setSnipURL($params['url']);
        }
        return $res;
    }

    /**
     * Register this installation with SNIP
     * @param array $params Registration parameters
     */
    public function unregister()
    {
        $res = $this->callRest("unregisterApplication", array(), true);
        if($res) {
            SugarSNIPHook::removeHook();
            $this->setSnipURL('');
        }
        return $res;
    }

    /**
     * Adds emails to SNIP system
     * @param string|array $params one email or list of emails
     */
    public function addEmails($params)
    {
        if(!is_array($params)) {
            $params = array($params);
        }
        foreach($params as $mail) {
            if(is_array($mail)) {
                $mail = $mail["email_address"];
            }
            if(empty($mail)) continue;
            if(!$this->callRest("addContact", array("emailaddress" => $mail), false)) return false;
        }
        return true;
    }

    /**
     * Add email account for SNIP monitoring
     *
     * @param InboundEmail $acct Inbound email account object
     * @param user $user user object
     */
    public function addAccount(InboundEmail $acct, User $user, $password_encoded = true)
    {
        $service = explode('::', $acct->service);
        if($acct->protocol != 'imap') {
            $GLOBALS['log']->error("SNIP: cannot add non-IMAP account");
            return false;
        }
        $password = $acct->email_password;
        if($password_encoded) {
            $password = blowfishDecode(blowfishGetKey('InboundEmail'), $password);
        }
        $data = array(
            "server" => $acct->server_url,
            "user" => $acct->email_user,
            "password" => $password,
            "params" => array("port" => $acct->port),
            "sugaruser" => $user->user_name,
        );
        if(!empty($acct->snip_mailbox)) {
            $GLOBALS['log']->debug("Setting SNIP mailbox values to:");
            $GLOBALS['log']->debug($acct->snip_mailbox);
            $data['params']['mailbox'] = explode(',', $acct->snip_mailbox);
        }
        if($service[2] == 'ssl') {
            $data['params']['ssl'] = true;
        }
        return $this->callRest("monitorAccount", $data, true);
    }

    /**
     * Remove account from SNIP monitoring
     * @param InboundEmail $acct Inbound email account object
     */
    public function removeAccount(InboundEmail $acct)
    {
        $data = array(
            "server" => $acct->server_url,
            "user" => $acct->email_user,
        );
        return $this->callRest("removeAccount", $data, true);
    }

    /**
     * Get status of the SNIP installation
     * Expects to receive one of the following:
     * - purchased_enabled  (instance has snip license, and snip is enabled)
     * - purchased_down     (instance has snip license, but snip server is down)
     * - purchased_disabled (instance has snip license, but snip has been disabled by instance admin)
     * - notpurchased       (instance has no active snip license)
     */
    public function getStatus()
    {
        if(!$this->isActive()) {
            return false;
        }
        if($this->callRest("status")) {
            return $this->getLastResult();
        }
        return false;
    }

    /**
     * Create user to use for SNIP imports
     * @return User
     */
    protected function createSnipUser()
    {
        $user = new User();
        $user->user_name = self::SNIP_USER;
        $user->title = translate('LBL_SNIP_USER_DESC', 'SNIP');
        $user->description = $user->title;
        $user->first_name = "";
        $user->last_name = $user->title;
        $user->status='Active';
        $user->external_auth_only = 1;
        $user->receive_notifications = 0;
        $user->is_admin = 0;
        $user->user_hash = strtolower(md5(time().mt_rand()));
        //$user->default_team = '1'; // TODO: which team should we set?
        $user->save();
        return $user;
    }

    /**
     * Get user used for SNIP imports
     * @return User
     */
    public function getSnipUser()
    {
        $id = User::retrieve_user_id(self::SNIP_USER);
        if(!$id) {
            return $this->createSnipUser();
        }
        $u = new User();
        $u->retrieve($id);
        return $u;
    }

    /**
     * Assign the email to proper user
     * @param Email $email
     * @param string $username
     */
    protected function assignUser($email, $username = null)
    {
        $user = new User();
        // if sugar_config['snip']['assign_ignore_email'] is set, assign everything to one user
        // which will be specified below
        if(empty($GLOBALS['sugar_config']['snip']['assign_ignore_email'])) {
	        foreach($email->all_addrs as $addr) {
	        	$iusr = $user->retrieve_by_email_address($addr);
				if(!empty($iusr) && !empty($user->id)) {
					$email->assigned_user_id = $user->id;
					break;
				}
	        }
        }

        if(empty($email->assigned_user_id) && !empty($username)) {
            $email->assigned_user_id = $user->retrieve_user_id($username);
        }

        if(empty($email->assigned_user_id) && !empty($GLOBALS['current_user'])) {
            $email->assigned_user_id = $GLOBALS['current_user']->id;
        }
    }

    /**
     * Imports an email from the SNIP serice
     *
     * @param array $email
     */
    public function importEmail($email)
    {
        global $current_user;

        if(!$email['message']['message_id']) {
            // messages should have IDs
            $GLOBALS['log']->error("SNIP: message has no ID, can't import");
            return;
        }
        $e = new Email();
        $e->retrieve_by_string_fields(array("message_id" => $email['message']['message_id']));
        if(!empty($e->id)) {
            $GLOBALS['log']->debug("SNIP: Duplicate ID {$email['message']['message_id']} - not importing");
            return;
        }

        $e->id = create_guid();
        $e->new_with_id = true;
        //Can't use sugar_bean field definition to determine which fields to import.
        $copyFields = array('from_name','description','description_html','to_addrs','cc_addrs','bcc_addrs','date_sent', 'message_id', 'subject');
        foreach ($copyFields as $field)
        {
            if(isset($email['message'][$field])) {
                $e->$field = $email['message'][$field];
            } else {
                $e->$field = '';
            }
        }
        $e->from_addr_name = $e->from_name;
        $from = $this->splitEmailAddress($e, $e->from_name);
        $e->from_addr = $from["email"];
        $e->from_name = $from["name"];
        $e->name = $e->subject;
        $e->date_sent = gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime($e->date_sent));
        $e->type = 'archived';
        $e->status = 'unread';
        $e->to_addrs_names = $e->to_addrs;
        $e->cc_addrs_names = $e->cc_addrs;
        $e->bcc_addrs_names = $e->bcc_addrs;

        $addrs = explode(',',$e->to_addrs.",".$e->cc_addrs.",".$e->bcc_addrs);
        $e->all_addrs = array();
    	foreach($addrs as $addr) {
    		if(empty($addr)) continue;
    		$addr = $this->splitEmailAddress($e, $addr);
    		if(!empty($addr["email"])) {
        		$e->all_addrs[] = $addr["email"];
    		}
    	}
        if(!empty($e->from_addr)) {
        	array_unshift($e->all_addrs, $e->from_addr);
        }

        // assign to proper user
        if(!empty($e->all_addrs)) {
        	$this->assignUser($e, $email['user']);
        }
        // For snipLite, use Global team
        $e->team_id = $e->default_team = '1';
        $tid = self::assignUserTeam($e, $e->assigned_user_id);

        $e->save(FALSE);
        // Object creation hook
        if(!empty($e->all_addrs)) {
        	$this->createObject($e);
        }

        //Process attachments
        if(isset($email['message']['attachments']) && count($email['message']['attachments'])) {
            foreach ($email['message']['attachments'] as $attach)
            {
                $this->processEmailAttachment($attach,$e);
            }
        }
    }

    /**
     * Split email address into name & address part
     * @param Email $email
     * @param string $addr
     * @return array
     */
    protected function splitEmailAddress($email, $addr)
    {
    	$email = $email->emailAddress->_cleanAddress($addr);
		$name = trim(str_replace(array($email, '<', '>', '"', "'"), '', $addr));
		return array("name" => $name, "email" => strtolower($email));
    }

    /**
     * Create objects from createdef definitions
     * Example definition:
     * <code>
     * $createdef['email@host.com']['Contacts'] = array(
     * 		'fields' => array(
     * 			'email1' => '{from_addr}',
     * 			'last_name' => '{from_name}',
     * 			'description' => 'created from {subject}',
     * 			'lead_source' => 'Email',
     * 		),
     * );
     * </code>
     * Supported variables:
     * - from
     * - from_addr
     * - from_name
     * - subject
     * - date
     * - description
     * - description_html
     * - message_id
     * - email_id
     * @param Email $email
     */
    protected function createObject($email)
    {
    	if(!file_exists('custom/modules/SNIP/createdefs.php')) {
    		return false;
    	}
    	$createdef = array();
		include 'custom/modules/SNIP/createdefs.php';
		$emaildata = array();
		foreach(array("subject", "description", "description_html", "message_id", "from_addr", "from_name") as $prop) {
			$emaildata["{".$prop."}"] = $email->$prop;
		}
		$emaildata["{from}"] = $email->from_addr_name;
		$emaildata["{date}"] = $email->date_sent;
		$emaildata["{email_id}"] = $email->id;
    	foreach($email->all_addrs as $cleanaddr) {
			if(!isset($createdef[$cleanaddr])) {
				continue;
			}
			foreach($createdef[$cleanaddr] as $module => $data) {
				//
				$obj = SugarModule::get($module)->loadBean();
				if(!$obj) {
					$GLOBALS['log']->error("Unable to create bean for module $module");
					continue;
				}
				// instantiate the data
				foreach($data["fields"] as $key => $value) {
					$obj->$key = str_replace(array_keys($emaildata), array_values($emaildata), $value);;
				}
				// save
				$obj->save();
				// associate email to new object
				if(empty($obj->id)) continue; // save failed
	            $mod = strtolower($module);
	            $rel = array_key_exists($mod, $email->field_defs) ? $mod : $mod . "_activities_emails"; //Custom modules rel name
	            if($email->load_relationship($rel) ) {
	            	$email->$rel->add($obj->id);
	            }
			}
    	}
    	return true;
    }

    /**
    * Assign user's private team to an email
    * @param SugarBean $email Email object
    * @param string $userid User ID
    */
    static function assignUserTeam($email, $userid)
    {
        if(empty($userid)) return null;

        $teamid = User::staticGetPrivateTeamID($userid);
        if(empty($email->teams)){
            $email->load_relationship('teams');
        }
        $GLOBALS['log']->debug("Assigning {$email->id} to user $userid team $teamid");
        $email->teams->add($teamid, array(), false);
        return $teamid;
    }

    /**
    * Save a snip email attachment and assoicated it to a parent email.  Content is base64 encoded.
    *
    */
    protected function processEmailAttachment($data, $email)
    {
        if (substr($data['filename'], - 4) === '.ics') {
            require_once ('modules/SNIP/iCalParser.php');
            $ic = new iCalendar();
            try {
                $ic->parse(base64_decode($data['content']));
                $ic->createSugarEvents($email);
            } catch(Exception $e) {
                $GLOBALS['log']->info("Could not process calendar attachment: ".$e->getMessage());
            }
        } else {
            $this->createNote($data, $email);
        }
    }

    /**
    * Create a new Note object
    * @param array $data Note data
    * @param Email $email parent email
    */
    protected function createNote($data, $email)
    {
        require_once 'include/upload_file.php';
        $upload_file = new UploadFile('uploadfile');
        $decodedFile = base64_decode($data['content']);
        $upload_file->set_for_soap($data['filename'], $decodedFile);
        $ext_pos = strrpos($upload_file->stored_file_name, ".");
        $upload_file->file_ext = substr($upload_file->stored_file_name, $ext_pos + 1);

        $note = new Note();
        $note->id = create_guid();
        $note->new_with_id = true;
        if (in_array($upload_file->file_ext, $this->config['upload_badext'])) {
            $upload_file->stored_file_name .= ".txt";
            $upload_file->file_ext = "txt";
        }

        $note->filename = $upload_file->get_stored_file_name();
        if(isset($data['type'])) {
            $note->file_mime_type = $data['type'];
        } else {
            $note->file_mime_type = $upload_file->getMimeSoap($note->filename);
        }

        $note->team_id = $email->team_id;
        $note->team_set_id = $email->team_set_id;
        $note->assigned_user_id = $email->assigned_user_id;
        $note->parent_type = 'Emails';
        $note->parent_id = $email->id;
        $note->name = $note->filename;

        $note->save();
        $upload_file->final_move($note->id);
    }
}

/**
 * CURL client for communicating with SNIP Rest service
 *
 */
class SugarSNIPClient
{
    /**
     * sends POST request to REST service via CURL
     * @param string $url URL to call
     * @param string $postArgs POST args
     */
    public function callRest($url, $postArgs)
    {
        if(!function_exists("curl_init")) {
            $GLOBALS['log']->fatal("SNIP call failed - no cURL!");
            return false;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
        $GLOBALS['log']->debug("SNIP call: $url -> $postArgs");
        $response = curl_exec($curl);
        if($response === false) {
            $GLOBALS['log']->debug("SNIP: cURL call failed");
            return false;
        }
        $GLOBALS['log']->debug("SNIP response: $response");
        curl_close($curl);
        return $response;
    }
}

/**
 * Workflow hooks processing
 */
class SugarSNIPHook
{
    public static $hooks = array(
      // Disabled for snipLite  array('EmailAddresses', "after_save", Array(1, 'SNIP push contacts', 'modules/SNIP/SugarSNIP.php', __CLASS__, 'saveEmail')),
      // Disabled for snipLite  array('Emails', "before_save", Array(1, 'SNIP link teams', 'modules/SNIP/SugarSNIP.php', __CLASS__, 'setTeams')),
        );
    /**
     * Install the workflow hooks
     */
    public static function installHook()
    {
        foreach(self::$hooks as $hook) {
            call_user_func_array('check_logic_hook_file', $hook);
        }
    }

    /**
     * Remove the workflow hooks
     */
    public static function removeHook()
    {
        foreach(self::$hooks as $hook) {
            call_user_func_array('remove_logic_hook', $hook);
        }
    }

    /**
     * Called when new EmailAddress is saved
     * @param SugarBean $bean
     * @param string $event will be 'after_save'
     * @param array $args call arguments
     */
    public function saveEmail($bean, $event, $args)
    {
        // ignore user emails
        if($args["module"] == "Users") return true;
        if(empty($bean->addresses)) return true;

        $snip = SugarSNIP::getInstance();
        $snip->addEmails($bean->addresses);
        // TODO: handle deletes?
    }

    /**
     * Assign email to related user's teams
     * @param SugarBean $bean
     * @param string $event will be 'before_save'
     * @param array $args call arguments
     */
    public function setTeams($bean, $event, $args)
    {
        $query = "SELECT u.id as userid FROM users u
        JOIN email_addr_bean_rel eabr ON eabr.bean_module='Users' AND eabr.bean_id=u.id AND eabr.deleted=0
        JOIN emails_email_addr_rel eear ON eear.email_address_id=eabr.email_address_id AND eear.deleted=0
        WHERE eear.email_id = '{$bean->id}' AND u.deleted=0";
        $res = $bean->db->query($query);
        while($row = $bean->db->fetchByAssoc($res)) {
            SugarSNIP::assignUserTeam($bean, $row["userid"]);
        }
        if(empty($bean->teams)) {
            return;
        }
        $bean->teams->save(false);
    }
}
