<?php
require_once 'modules/Emails/Email.php';

class SugarTestEmailUtilities
{
    private static $_createdEmails = array();

    private function __construct() {}

    public static function createEmail($id = '', $override = array()) 
    {
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
        self::$_createdEmails[] = $email;
        return $email;
    }

    public static function removeAllCreatedEmails() 
    {
        $email_ids = self::getCreatedEmailIds();
        $GLOBALS['db']->query('DELETE FROM emails WHERE id IN (\'' . implode("', '", $email_ids) . '\')');
        self::removeCreatedEmailBeansRelationships();
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