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


global $sugar_config, $mod_strings;

$usa = new UnifiedSearchAdvanced();
$unified_search_modules = $usa->getUnifiedSearchModules();
if (!empty($unified_search_modules)) {
    print( $mod_strings['LBL_CLEAR_UNIFIED_SEARCH_CACHE_CLEARING'] . "<br>" );
    UnifiedSearchAdvanced::clearCache();
}

echo "\n--- " . $mod_strings['LBL_DONE'] . "---<br />\n";
?>
