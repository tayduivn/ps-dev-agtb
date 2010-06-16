<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('include/SugarFolders/SugarFolders.php');
require_once('modules/Campaigns/ProcessBouncedEmails.php');

/**
 * @group bug33918 
 */
class Bug333918Test extends Sugar_PHPUnit_Framework_TestCase
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
		$this->_ie = new InboundEmail();
	}

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->_user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }
    
    function testGetExistingCampaignLogEntry()
    {
        $targetTrackerKey = uniqid();
        $campaignLogID = uniqid();
        
        $tst = new CampaignLog();
        $tst->activity_type  = 'targeted';
        $tst->target_tracker_key = $targetTrackerKey;
        $tst->id = $campaignLogID;
        $tst->new_with_id = TRUE;
        $tst->save(FALSE);
        
        $row = getExistingCampaignLogEntry($targetTrackerKey);
        
        $this->assertEquals($tst->activity_type, $row['activity_type'] , "Unable to get existing bounced campaign log entry");
        $this->assertEquals($tst->id, $row['id'] , "Unable to get existing bounced campaign log entry");
        $this->assertEquals($tst->target_tracker_key, $row['target_tracker_key'] , "Unable to get existing bounced campaign log entry");
        
        $emptyRow = getExistingCampaignLogEntry(uniqid());
        $this->assertTrue(empty($emptyRow), "Unable to get existing bounced campaign log entry");
        
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='$campaignLogID'");
        
    }
    
    function testCreateBouncedCampaignLogEntry()
    {
        $row = array(
            'campaign_id' => 'UNIT TEST 1',
            'target_tracker_key' => 'UNIT TEST 2',
            'target_id' => 'UNIT TEST 3',
            'target_type' => 'Lead',
            'list_id' => 'UNIT TEST 4',
            'marketing_id' => 'UNIT TEST 5',
            
        );
        $email = new stdClass();
        $email->date_created = gmdate('Y-m-d H:i:s');
        $email->id = uniqid();    
        $email_description = " Unit test with permanent[undeliverable] error ";

        
        $bounce_id = createBouncedCampaignLogEntry($row, $email, $email_description);
        $bounce = new CampaignLog();
        $bounce->retrieve($bounce_id);

        $this->assertEquals($row['campaign_id'], $bounce->campaign_id , "Unable to create bounced campaign log entry");
        $this->assertEquals($row['target_id'], $bounce->target_id , "Unable to create bounced campaign log entry");
        $this->assertEquals($row['marketing_id'], $bounce->marketing_id, "Unable to create bounced campaign log entry");
        $this->assertEquals('send error', $bounce->activity_type , "Unable to create bounced campaign log entry");
        $this->assertEquals($email->id, $bounce->related_id , "Unable to create bounced campaign log entry");
        
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='$bounce_id'");
    }
    
    function testErrorReportRetrieval()
    {   
        $noteID = uniqid();
        $parentID = uniqid();
        
        $note = new Note();
        $note->description = "Unit Test";
        $note->file_mime_type = 'messsage/rfc822';
        $note->subject = "Unit Test";
        $note->new_with_id = TRUE;
        $note->id = $noteID;
        $note->parent_id = $parentID;
        $note->parent_type = 'Emails';
        $note->save();
        
        $email = new stdClass();
        $email->id = $parentID;
        
        $emailEmpty = new stdClass();
        $emailEmpty->id = '1234';
        
        $this->assertEquals($note->description, retrieveErrorReportAttachment($email), "Unable to retrieve error report for bounced email");
        $this->assertEquals("",retrieveErrorReportAttachment($emailEmpty), "Unable to retrieve error report for bounced email");
        $GLOBALS['db']->query("DELETE FROM notes WHERE id='{$note->id}'");
    }
    
    /**
     * @dataProvider _breadCrumbOffsetsData
     */
    function testAddBreadCrumbOffset($base, $offset, $expected)
    {
        $rs = $this->_ie->addBreadCrumbOffset($base, $offset);
        $this->assertEquals($expected, $rs, "Unable to add bread crumb offset");
    }
    
    function _breadCrumbOffsetsData()
    {
        return array(
            array('base' => '1.0', 'offset' => '0.1', 'expected' => '1.1'),
            array('base' => '2.0', 'offset' => '1.0', 'expected' => '3.0'),
            array('base' => '1.0.1', 'offset' => '0.1', 'expected' => '1.1.1'),
            array('base' => '4', 'offset' => '0.1', 'expected' => '4.1'),
            array('base' => '0.0', 'offset' => '0.1', 'expected' => '0.1'),
            array('base' => '0', 'offset' => '0', 'expected' => '0')
        
        );
    }
    
    function testProcessBouncedEmail()
    {
        $trackerKey = '173e8e08-5826-c6a4-a17f-4be9d7c6d8b4';
        $message = <<<CIA
Received: from localhost (unknown [172.16.161.1])
	by asandberg (Postfix) with ESMTP id E2E9FE42F0
	for <ljsdf2323@sugarcrm.com>; Tue, 11 May 2010 15:15:50 -0700 (PDT)
Date: Tue, 11 May 2010 15:15:50 -0700
To: Random Sandberg <ljsdf2323@sugarcrm.com>
From: Administrator <asandberg@sugarcrm.com>
Reply-to: 
Subject: Pop Newsletter
Message-ID: <ddc9b4df5b9e4dcdacc4dac14471864c@localhost>
X-Priority: 3
X-Mailer: PHPMailer (phpmailer.codeworxtech.com) [version 2.3]
MIME-Version: 1.0
Content-Transfer-Encoding: quoted-printable
Content-Type: text/html; charset="UTF-8"

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.=
w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns=3D"http://www.w3.org/1999/xhtml">
<head>
=09<meta http-equiv=3D"Content-Type" content=3D"text/html; charset=3DUTF-8"=
 />
<title>Pop Newsletter</title>
</head>
<body><p>text stuff in here</p>
<p>&nbsp;</p>
<p><a href=3D"http://localhost/engineering/kobeAgain/sugarcrm/index.php?ent=
ryPoint=3Dremoveme&identifier=3D$trackerKey"> http=
://localhost/engineering/kobeAgain/sugarcrm/index.php?entryPoint=3Dremoveme=
&identifier=3D$trackerKey </a></p><br><IMG HEIGHT=
=3D'1' WIDTH=3D'1' src=3D'http://localhost/engineering/kobeAgain/sugarcrm/i=
ndex.php?entryPoint=3Dimage&identifier=3D173e8e08-5826-c6a4-a17f-4be9d7c6d8=
b4'></body></html>
CIA;
    $noteID = uniqid();
    $parentID = uniqid();
    $note = new Note();
    $note->description = $message;
    $note->file_mime_type = 'messsage/rfc822';
    $note->subject = "Unit Test";
    $note->new_with_id = TRUE;
    $note->id = $noteID;
    $note->parent_id = $parentID;
    $note->parent_type = 'Emails';
    $note->save();

    $email = new stdClass();
    $email->id = $parentID;
    $logID = $this->_createCampaignLogForTrackerKey($trackerKey);
    $email_header = new stdClass();
    $email_header->fromaddress = "MAILER-DAEMON";
    $this->assertTrue(campaign_process_bounced_emails($email, $email_header), "Unable to process bounced email");

    $GLOBALS['db']->query("DELETE FROM notes WHERE id='{$note->id}'");
    $GLOBALS['db']->query("DELETE FROM campaign_log WHERE id='{$logID}' OR target_tracker_key='{$trackerKey}'");
        
    }
    
    function _createCampaignLogForTrackerKey($trackerKey)
    {
        $l = new CampaignLog();
        $l->activity_type = 'targeted';
        $l->target_tracker_key = $trackerKey;
        $l->id = uniqid();
        $l->new_with_id = TRUE;
        $l->save();
        return $l->id;
    }
}
?>