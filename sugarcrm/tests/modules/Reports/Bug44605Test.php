<?php
//FILE SUGARCRM flav=pro ONLY
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

/**
 * @ticket 44605
 */
class Bug44605Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    /**
     * Test that each "count" column in report is represented by it's own field
     */
    public function testEachCountColumnIsRepresented()
    {
        $primaryModule      = 'PrimaryModule';
        $primaryModuleTable = 'primary_module';
        $relatedModule      = 'RelatedModule';

        // any value greater than 1 would be valid
        $relatedModulesCount = 3;

        // create report definition
        $definition = array(
            'module'     => $primaryModule,
            'group_defs' => array(
                array(
                    'name'      => 'id',
                    'table_key' => 'self',
                ),
            ),
            'summary_columns' => array(
                array(
                    'name'      => 'count',
                    'table_key' => 'self',
                ),
            ),
            'full_table_list' => array(
                'self' => array(
                    'params' => array(
                        'join_table_alias' => $primaryModuleTable,
                    ),
                ),
            ),
            'filters_def' => array(),
            'display_columns' => array(),
        );

        // add "count" field for each related module
        for ($i = 0; $i < $relatedModulesCount; $i++)
        {
            $tableKey = $primaryModule . ':' . $relatedModule . $i;

            $definition['summary_columns'][] = array(
                'name'      => 'count',
                'table_key' => $tableKey,
            );

            $definition['full_table_list'][$tableKey] = array(
                'link_def' => array(),
            );
        }

        require_once 'modules/Reports/Report.php';
        $report = new Report(json_encode($definition));
        $report->create_summary_select();

        $countFields = 0;
        $primaryModuleTableUsages = 0;
        foreach ($report->summary_select_fields as $field)
        {
            // calculate number of "count" fields in request
            if (0 === strpos(strtolower($field), 'count('))
            {
                $countFields++;

                if (false !== strpos($field, $primaryModuleTable))
                {
                    $primaryModuleTableUsages++;
                }
            }
        }

        // ensure that number of "count" fields in request equals to
        // related modules count (one field for each module) + 1 (primary module)
        $this->assertEquals(
            $relatedModulesCount + 1,
            $countFields
        );

        // ensure that primary module table is mentioned by it's name, not alias
        $this->assertEquals(1, $primaryModuleTableUsages);
    }
}
