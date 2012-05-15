<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 * ****************************************************************************** */

require_once('modules/InboundEmail/InboundEmail.php');
/**
 * Bug #49543
 * Email cache reset issue causing email deletion slowness
 * @ticket 49543
 */
class Bug49543Test extends Sugar_PHPUnit_Framework_TestCase
{
    private function createMail($subj, $from, $to, $imap_uid, $date, $uid)
    {
        $mail = new stdClass();
        $mail->subject = $subj;
        $mail->from = $from;
        $mail->to = $to;
        $mail->imap_uid = $imap_uid;
        $mail->date = $date;
        $mail->message_id = '';
        $mail->size = '1234';
        $mail->uid = $uid;
        $mail->msgno = 0;
        $mail->recent = 0;
        $mail->flagged = 0;
        $mail->answered = 0;
        $mail->deleted = 0;
        $mail->draft = 0;
        $mail->seen = 1;
        
        return $mail;
    }
    
    /**
     * @group 49543
     */
    public function testSetCacheValue()
    {
        global $timedate;
        
        $ie_id = '123';
        $mailbox = 'trash';
        $time = mt_rand();
        $subj = 'test ' . $time;
        $GLOBALS['db']->query(sprintf("INSERT INTO email_cache (ie_id, mbox, subject, fromaddr, toaddr, imap_uid) 
                                VALUES ('%s', '%s', '%s', 'from@test.com', 'to@test.com', '11')", 
                                $ie_id, $mailbox, $subj));
        
        //deleted item from inbox which will be inserted in trash
        $insert[0] = $this->createMail($subj.'_new', 'from@test.com', 'to@test.com', '12', '2012-11-11 11:11:11', '12');
        
        //old trash item which should be updated
        $insert[1] = $this->createMail($subj.'_old', 'from@test.com', 'to@test.com', '11', '2011-11-11 11:11:11', '11');
        
        $ie = new InboundEmail();
        $ie->id = $ie_id;
        
        $ie->setCacheValue($mailbox, $insert, '', '');
        
        $fr = $GLOBALS['db']->fetchRow($GLOBALS['db']->query("SELECT subject FROM email_cache WHERE imap_uid = '11'"));
        
        //if old trash item was updated successfully then 'subject' has new value
        $this->assertTrue($fr['subject'] == $subj.'_old');
        
        $GLOBALS['db']->query(sprintf("DELETE FROM email_cache WHERE mbox = '%s'", $mailbox));
    }
}
?>