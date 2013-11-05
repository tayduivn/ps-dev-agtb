<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'modules/ModuleBuilder/parsers/relationships/AbstractRelationships.php';

/**
 * Class Bug65942Test
 *
 * Test if saveLabels saved multiple labels for same module properly
 *
 * @author avucinic@sugarcrm.com
 */
class Bug65942Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $path = 'custom/Extension/modules/relationships';
    private $files = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();

        foreach ($this->files as $file) {
            unlink($file);
        }
    }

    /**
     * @param $labelDefinitions -  Label Definitions
     * @param $testLabel - Test if this label was saved
     *
     * @group Bug65942
     * @dataProvider getLabelDefinitions
     */
    public function testIfAllLabelsSaved($labelDefinitions, $testLabel)
    {
        $abstractRelationships = new AbstractRelationships65942Test();
        $abstractRelationships->saveLabels(
            $this->path,
            '',
            null,
            $labelDefinitions
        );

        $generatedLabels = file_get_contents($this->path . '/language/' . $labelDefinitions[0]['module'] . '.php');
        $this->files[] = $this->path . '/language/' . $labelDefinitions[0]['module'] . '.php';

        $this->assertContains($testLabel, $generatedLabels);
    }

    public static function getLabelDefinitions()
    {
        return array(
            array(
                array(
                    0 =>
                    array(
                        'module' => 'Bug65942Test',
                        'system_label' => 'LBL_65942_TEST_1',
                        'display_label' => 'Bug65942Test 1',
                    ),
                    1 =>
                    array(
                        'module' => 'Bug65942Test',
                        'system_label' => 'LBL_65942_TEST_2',
                        'display_label' => 'Bug65942Test 2',
                    )
                ),
                '$mod_strings[\'LBL_65942_TEST_1\'] = \'Bug65942Test 1\';'
            ),
            array(
                array(
                    0 =>
                    array(
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_1',
                        'display_label' => '65942Test 1',
                    ),
                    1 =>
                    array(
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_2',
                        'display_label' => '65942Test 2',
                    ),
                    2 =>
                    array(
                        'module' => '65942Test',
                        'system_label' => '65942_TEST_3',
                        'display_label' => '65942Test 3',
                    )
                ),
                '$mod_strings[\'65942_TEST_2\'] = \'65942Test 2\';'
            )
        );
    }
}

/**
 * Class AbstractRelationships65942Test
 *
 * Test Helper class, override saveLabels so we can test it
 */
class AbstractRelationships65942Test extends AbstractRelationships
{
    public function saveLabels($basepath, $installDefPrefix, $relationshipName, $labelDefinitions)
    {
        return parent::saveLabels($basepath, $installDefPrefix, $relationshipName, $labelDefinitions);
    }
}
