<?php
if ( !defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once("modules/Meetings/Meeting.php");
require_once("modules/Calls/Call.php");
require_once("modules/Users/User.php");
require_once("modules/Contacts/Contact.php");
require_once("modules/Leads/Lead.php");
require_once "modules/Mailer/MailerFactory.php"; // imports all of the Mailer classes that are needed

/**
 * Class for sending email reminders of meetings and call to invitees
 * 
 */
class EmailReminder
{
    
    /**
     * string db datetime of now
     */
    protected $now;
    
    /**
     * string db datetime will be fetched till
     */
    protected $max;
    
    /**
     * constructor
     */
    public function __construct()
    {
        $max_time = 0;
        if(isset($GLOBALS['app_list_strings']['reminder_time_options'])){
            foreach($GLOBALS['app_list_strings']['reminder_time_options'] as $seconds => $value ) {
                if ( $seconds > $max_time ) {
                    $max_time = $seconds;
                }
            }
        }else{
            $max_time = 8400;
        }
        $this->now = $GLOBALS['timedate']->nowDb();
        $this->max = $GLOBALS['timedate']->getNow()->modify("+{$max_time} seconds")->asDb();
    }
    
    /**
     * main method that runs reminding process
     * @return boolean
     */
    public function process()
    {

        $admin = Administration::getSettings();
        
        $meetings = $this->getMeetingsForRemind();
        foreach($meetings as $id ) {
            $recipients = $this->getRecipients($id,'Meetings');
            $bean = BeanFactory::getBean('Meetings', $id);
            if ( $this->sendReminders($bean, $admin, $recipients) ) {
                $bean->email_reminder_sent = 1;
                $bean->save();
            }            
        }
        
        $calls = $this->getCallsForRemind();
        foreach($calls as $id ) {
            $recipients = $this->getRecipients($id,'Calls');
            $bean = BeanFactory::getBean('Calls', $id);
            if ( $this->sendReminders($bean, $admin, $recipients) ) {
                $bean->email_reminder_sent = 1;
                $bean->save();
            }
        }
        
        return true;
    }
    
    /**
     * send reminders
     * @param SugarBean $bean
     * @param Administration $admin *use is deprecated*
     * @param array $recipients
     * @return boolean
     */
    protected function sendReminders(SugarBean $bean, Administration $admin, $recipients) {
        $currentLanguage = $_SESSION["authenticated_user_language"];

        if (empty($_SESSION["authenticated_user_language"])) {
            $currentLanguage = $GLOBALS["sugar_config"]["default_language"];
        }

        $user = BeanFactory::getBean('Users', $bean->created_by);
            
        $xtpl = new XTemplate(get_notify_template_file($currentLanguage));
        $xtpl = $this->setReminderBody($xtpl, $bean, $user);

        $templateName = "{$GLOBALS["beanList"][$bean->module_dir]}Reminder";
        $xtpl->parse($templateName);
        $xtpl->parse("{$templateName}_Subject");

        $mailTransmissionProtocol = "unknown";

        try {
            $mailer                   = MailerFactory::getMailerForUser($GLOBALS["current_user"]);
            $mailTransmissionProtocol = $mailer->getMailTransmissionProtocol();

            // set the subject of the email
            $subject = $xtpl->text("{$templateName}_Subject");
            $mailer->setSubject($subject);

            // set the body of the email
            $body = trim($xtpl->text($templateName));
            $textOnly = EmailFormatter::isTextOnly($body);
            if ($textOnly) {
                $mailer->setTextBody($body);
            } else {
                $textBody = strip_tags(br2nl($body)); // need to create the plain-text part
                $mailer->setTextBody($textBody);
                $mailer->setHtmlBody($body);
            }

            foreach ($recipients as $recipient) {
                // reuse the mailer, but process one send per recipient
                $mailer->clearRecipients();
                $mailer->addRecipientsTo(new EmailIdentity($recipient["email"], $recipient["name"]));
                $mailer->send();
            }
        } catch (MailerException $me) {
            $message = $me->getMessage();

            switch ($me->getCode()) {
                case MailerException::FailedToConnectToRemoteServer:
                    $GLOBALS["log"]->fatal("Email Reminder: error sending email, system smtp server is not set");
                    break;
                default:
                    $GLOBALS["log"]->fatal("Email Reminder: error sending e-mail (method: {$mailTransmissionProtocol}), (error: {$message})");
                    break;
            }

            return false;
        }

        return true;
    }
    
    /**
     * set reminder body
     * @param XTemplate $xtpl
     * @param SugarBean $bean
     * @param User $user
     * @return XTemplate 
    */
    protected function setReminderBody(XTemplate $xtpl, SugarBean $bean, User $user)
    {
    
        $object = strtoupper($bean->object_name);

        $xtpl->assign("{$object}_SUBJECT", $bean->name);
        $date = $GLOBALS['timedate']->fromUser($bean->date_start,$GLOBALS['current_user']);
        $xtpl->assign("{$object}_STARTDATE", $GLOBALS['timedate']->asUser($date, $user)." ".TimeDate::userTimezoneSuffix($date, $user));
        if ( isset($bean->location) ) {
            $xtpl->assign("{$object}_LOCATION", $bean->location);
        }
        $xtpl->assign("{$object}_CREATED_BY", $user->full_name);
        $xtpl->assign("{$object}_DESCRIPTION", $bean->description);

        return $xtpl;
    }
    
    /**
     * get meeting ids list for remind
     * @return array
     */
    public function getMeetingsForRemind()
    {
        global $db;
        $query = "
            SELECT id, date_start, email_reminder_time FROM meetings 
            WHERE email_reminder_time != -1
            AND deleted = 0
            AND email_reminder_sent = 0
            AND status != 'Held'
            AND date_start >= '{$this->now}'
            AND date_start <= '{$this->max}'
        ";
        $re = $db->query($query);
        $meetings = array();
        while($row = $db->fetchByAssoc($re) ) {
            $remind_ts = $GLOBALS['timedate']->fromDb($db->fromConvert($row['date_start'],'datetime'))->modify("-{$row['email_reminder_time']} seconds")->ts;
            $now_ts = $GLOBALS['timedate']->getNow()->ts;
            if ( $now_ts >= $remind_ts ) {
                $meetings[] = $row['id'];
            }
        }
        return $meetings;
    }
    
    /**
     * get calls ids list for remind
     * @return array
     */
    public function getCallsForRemind()
    {
        global $db;
        $query = "
            SELECT id, date_start, email_reminder_time FROM calls
            WHERE email_reminder_time != -1
            AND deleted = 0
            AND email_reminder_sent = 0
            AND status != 'Held'
            AND date_start >= '{$this->now}'
            AND date_start <= '{$this->max}'
        ";
        $re = $db->query($query);
        $calls = array();
        while($row = $db->fetchByAssoc($re) ) {
            $remind_ts = $GLOBALS['timedate']->fromDb($db->fromConvert($row['date_start'],'datetime'))->modify("-{$row['email_reminder_time']} seconds")->ts;
            $now_ts = $GLOBALS['timedate']->getNow()->ts;
            if ( $now_ts >= $remind_ts ) {
                $calls[] = $row['id'];
            }
        }
        return $calls;
    }
    
    /**
     * get recipients of reminding email for specific activity
     * @param string $id
     * @param string $module
     * @return array
     */
    protected function getRecipients($id, $module = "Meetings")
    {
        global $db;
    
        switch($module ) {
            case "Meetings":
                $field_part = "meeting";
                break;
            case "Calls":
                $field_part = "call";
                break;
            default:
                return array();
        }
    
        $emails = array();
        // fetch users
        $query = "SELECT user_id FROM {$field_part}s_users WHERE {$field_part}_id = '{$id}' AND accept_status != 'decline' AND deleted = 0
        ";
        $re = $db->query($query);
        while($row = $db->fetchByAssoc($re) ) {
            $user = BeanFactory::getBean('Users', $row['user_id']);
            if ( !empty($user->email1) ) {
                $arr = array(
                    'type' => 'Users',
                    'name' => $user->full_name,
                    'email' => $user->email1,
                );
                $emails[] = $arr;
            }
        }        
        // fetch contacts
        $query = "SELECT contact_id FROM {$field_part}s_contacts WHERE {$field_part}_id = '{$id}' AND accept_status != 'decline' AND deleted = 0";
        $re = $db->query($query);
        while($row = $db->fetchByAssoc($re) ) {
            $contact = BeanFactory::getBean('Contacts', $row['contact_id']);
            if ( !empty($contact->email1) ) {
                $arr = array(
                    'type' => 'Contacts',
                    'name' => $contact->full_name,
                    'email' => $contact->email1,
                );
                $emails[] = $arr;
            }
        }        
        // fetch leads
        $query = "SELECT lead_id FROM {$field_part}s_leads WHERE {$field_part}_id = '{$id}' AND accept_status != 'decline' AND deleted = 0";
        $re = $db->query($query);
        while($row = $db->fetchByAssoc($re) ) {
            $lead = BeanFactory::getBean('Leads', $row['lead_id']);
            if ( !empty($lead->email1) ) {
                $arr = array(
                    'type' => 'Leads',
                    'name' => $lead->full_name,
                    'email' => $lead->email1,
                );
                $emails[] = $arr;
            }
        }
        return $emails;
    }
}

