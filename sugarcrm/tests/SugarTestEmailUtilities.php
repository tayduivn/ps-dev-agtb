<?php
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
 
require_once 'modules/Emails/Email.php';

class SugarTestEmailUtilities
{
    private static $_createdEmails = array();

    private function __construct() {}

    public static function createEmail($id = '', $override = array()) 
    {
        global $timedate;
        
        $time = mt_rand();
    	$name = 'SugarEmail';
    	$email = new Email();
        $email->name = $name . $time;
        $email->type = 'out';
        $email->status = 'sent';
        $email->date_sent = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", (gmmktime() - (3600 * 24 * 2) ))) ; // Two days ago
        if(!empty($id))
        {
            $email->new_with_id = true;
            $email->id = $id;
        }
        foreach($override as $key => $value)
        {
            $email->$key = $value;
        }
        $email->save();
        if(!empty($override['parent_id']) && !empty($override['parent_type']))
        {
            self::createEmailsBeansRelationship($email->id, $override['parent_type'], $override['parent_id']);
        }
        self::$_createdEmails[] = $email;
        return $email;
    }

    public static function removeAllCreatedEmails() 
    {
        $email_ids = self::getCreatedEmailIds();
        $GLOBALS['db']->query('DELETE FROM emails WHERE id IN (\'' . implode("', '", $email_ids) . '\')');
        self::removeCreatedEmailBeansRelationships();
    }
    
    private static function createEmailsBeansRelationship($email_id, $parent_type, $parent_id)
    {
        $unique_id = create_guid();
        $GLOBALS['db']->query("INSERT INTO emails_beans ( id, email_id, bean_id, bean_module, date_modified, deleted ) ".
							  "VALUES ( '{$unique_id}', '{$email_id}', '{$parent_id}', '{$parent_type}', '".gmdate('Y-m-d H:i:s')."', 0)");
    }
    
    private static function removeCreatedEmailBeansRelationships(){
    	$email_ids = self::getCreatedEmailIds();
        $GLOBALS['db']->query('DELETE FROM emails_beans WHERE email_id IN (\'' . implode("', '", $email_ids) . '\')');
    }
    
    public static function getCreatedEmailIds() 
    {
        $email_ids = array();
        foreach (self::$_createdEmails as $email) {
            $email_ids[] = $email->id;
        }
        return $email_ids;
    }
}
?>