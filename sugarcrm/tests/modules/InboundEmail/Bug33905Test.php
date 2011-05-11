<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('include/SugarFolders/SugarFolders.php');

/**
 * @ticket 33405
 */
class Bug33905Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $folder = null;
    public $_user = null;
    public $_team = null;
    public $_ie = null;
    
	public function setUp()
    {
        global $current_user, $currentModule;

        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->_user->default_team=$this->_team->id;
        $this->_team->add_user_to_team($this->_user->id);
		$this->_user->save();
		$ieID = $this->_createInboundAccount();
		$ie = new InboundEmail();
		$this->_ie = $ie->retrieve($ieID);
	}

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->_user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
        
        $GLOBALS['db']->query("DELETE FROM inbound_email WHERE id='{$this->_ie->id}'");
    }
    
    function _createInboundAccount() 
    {
        global $inbound_account_id, $current_user;
        $stored_options = array();
        $stored_options['from_name'] = "UnitTest";
        $stored_options['from_addr'] = "UT@sugarcrm.com";
        $stored_options['reply_to_name'] = "UnitTest";
        $stored_options['reply_to_addr'] = "UT@sugarcrm.com";
        $stored_options['only_since'] = false;
        $stored_options['filter_domain'] = "";
        $stored_options['trashFolder'] = "INBOX.Trash";
        $stored_options['leaveMessagesOnMailServer'] = 1;

        $useSsl = false;
        $focus = new InboundEmail();
        $focus->name = "Unittest";
        $focus->email_user = "ajaysales@sugarcrm.com";
        $focus->email_password = "f00f004";
        $focus->server_url = "mail.sugarcrm.com";
        $focus->protocol = "imap";
        $focus->mailbox = "INBOX";
        $focus->port = "143";
        $focus->service = "0::0::1::IMAP";
        $focus->is_personal = 0;
        $focus->status = "Active";
        $focus->mailbox_type = 'pick';
        $focus->group_id = create_guid();
        $focus->team_id = $this->_team->id;
        $focus->team_set_id = $this->_team->id;
        $focus->stored_options = base64_encode(serialize($stored_options));
        return $focus->save();
    }
    
	function testCreateSubscriptions(){
	    
        $current_user = $this->_user;
	    $this->_ie->createUserSubscriptionsForGroupAccount();

	    $subs = unserialize(base64_decode($current_user->getPreference('showFolders', 'Emails')));
        $this->assertEquals($this->_ie->id, $subs[0], "Unable to create subscriptions for IE Group Account (Import not enabled)");
        
    }

}
?>