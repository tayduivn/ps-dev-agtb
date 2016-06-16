<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

if (is_admin($GLOBALS['current_user'])) {
    echo "<h2>{$GLOBALS['mod_strings']['LBL_REBUILD_EXPORT_CAL_DAV_STARTED']}</h2>";
    $ss = new Sugar_Smarty();
    $ss->display('modules/Administration/templates/ReExportEvents.tpl');
}
