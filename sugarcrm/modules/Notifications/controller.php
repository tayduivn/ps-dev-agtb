<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('modules/Notifications/Notifications.php');

class NotificationsController extends SugarController
{
    var $action_remap = array ( ) ;

    /**
     * @deprecated Since 7.2 will be removed on 7.5
     */
    function action_checkNewNotifications()
    {
	    global $timedate;
	   
	    $thirtySecondsAgoFormatted = $timedate->getNow()->get("30 seconds ago")->asDb();

	    $now = $timedate->nowDb();

	    $lastNotiticationCheck = !empty($_SESSION['lastNotificationCheck']) ? $_SESSION['lastNotificationCheck'] : $thirtySecondsAgoFormatted;
	    
        $n = BeanFactory::getBean('Notifications');
        $unreadCount = $n->retrieveUnreadCountFromDateEnteredFilter($lastNotiticationCheck);
        
        //Store the last datetime checked.
        $_SESSION['lastNotificationCheck'] = $now;
        
        $results = array('unreadCount' => $unreadCount );

	    $json = getJSONobj();
		$out = $json->encode($results);
		ob_clean();
		print($out);
		sugar_cleanup(true);
	    
    }
}
?>
