<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=int ONLY
global $current_user;
require_once('modules/Queues/Queue.php');

$focus = new Queue();
$focus->disable_row_level_security = true;
$focus->getQueueFromOwnerId($current_user->id, true);
$focus->moveItemsIntoMyQueue(1);

header("Location: index.php?module=Home&action=index");
?>
