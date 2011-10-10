<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 ********************************************************************************/

require_once 'modules/Home/UnifiedSearchAdvanced.php';

/**
 * @brief Try to find force_unifedsearch fields
 * @ticket 42961
 */
class Bug42961Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @brief generation of new cache file and search for force_unifiedsearch fields in it
     * @group 42961
     */
    public function testBuildCache()
    {
        $unifiedSearchAdvanced = new UnifiedSearchAdvanced();
        $unifiedSearchAdvanced->buildCache();
        $this->assertFileExists($GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php', 'Here should be cache file with data');
        include $GLOBALS['sugar_config']['cache_dir'].'modules/unified_search_modules.php';
        $force_unifiedsearch = 0;
        foreach ($unified_search_modules as $moduleName=>$moduleInformation)
        {
            foreach ($moduleInformation['fields'] as $fieldName=>$fieldInformation)
            {
                if (key_exists('force_unifiedsearch', $fieldInformation)) {
                    $force_unifiedsearch++;
                }
            }
        }
        $this->assertGreaterThan(0, $force_unifiedsearch, 'Here should be fields with force_unifiedsearch key');
    }
}