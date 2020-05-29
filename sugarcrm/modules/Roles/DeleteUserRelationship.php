<?php
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
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/






$focus = BeanFactory::getBean('Roles', $_REQUEST['record']);

$focus->clear_user_relationship($focus->id, $_REQUEST['user_id']);

$query = http_build_query(array(
    'module' => $_REQUEST['return_module'],
    'action' => $_REQUEST['return_action'],
    'record' => $_REQUEST['return_id'],
));

$header_URL = 'Location: index.php?' . $query;
$GLOBALS['log']->debug("about to post header URL of: $header_URL");

header($header_URL);
