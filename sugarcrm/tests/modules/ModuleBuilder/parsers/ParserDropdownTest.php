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

class ParserDropdownTest extends Sugar_PHPUnit_Framework_TestCase
{
    // Custom include/language file path
    private $customFile;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');

        $this->customFile =  'custom/include/language/' . $GLOBALS['current_language'] . '.lang.php';
        if (file_exists($this->customFile)) {
            $this->fileBackup = file_get_contents($this->customFile);
        }
    }

    public function tearDown()
    {
        if (isset($this->fileBackup)) {
            file_put_contents($this->customFile, $this->fileBackup);
        }

        // Clear cache so it can be reloaded later
        $cache_key = 'app_list_strings.' . $GLOBALS['current_language'];
        sugar_cache_clear($cache_key);
        SugarTestHelper::tearDown();
    }

    /**
     *
     */
    public function testSavingEmptyLabels()
    {
        $_REQUEST['view_package'] = 'studio';
        $params = array(
            'dropdown_name' => 'moduleList',
            'list_value' => '',
            'skipSaveExemptDropdowns' => true,
        );

        $parser = $this->getMock('ParserDropDown', array('saveExemptDropdowns', 'synchDropDown', 'saveContents', 'finalize'));
        $parser->expects($this->never())->method('saveExemptDropdowns');
        $parser->saveDropDown($params);
    }

    /**
     * Check if saveExemptDropdowns works as expected
     * This method should set a NULL value to keys that were deleted on exempt dropdowns
     *
     * @param $dropdownValues New dropdown values
     * @param $dropdownName Dropdown key
     * @param $appListStrings Old app_list_strings, containing old dropdown values
     * @param $customFileContents Contents of custom/include/language file
     * @param $expected Dropdown returned from saveExemptDropdowns call
     *
     * @dataProvider providerTestSaveExemptDropdowns
     */
    public function testSaveExemptDropdowns(
        $dropdownValues,
        $dropdownName,
        $appListStrings,
        $customFileContents,
        $expected
    ) {

        $dirName = dirname($this->customFile);
        SugarAutoLoader::ensureDir($dirName);
        SugarAutoLoader::put($this->customFile, $customFileContents);

        $parser = new ParserDropDown();
        $output = $parser->saveExemptDropdowns(
            $dropdownValues,
            $dropdownName,
            $appListStrings,
            $GLOBALS['current_language']
        );

        $this->assertEquals($expected, $output, 'Save Exempt Dropdowns not working properly.');
    }

    public static function providerTestSaveExemptDropdowns()
    {
        return array(
            // Check if non-exempt dropdowns are just passed through
            array(
                array(
                    0 => 'test 0',
                ),
                'test',
                array(
                    'test' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                    ),
                ),
                "",
                array(
                    0 => 'test 0',
                ),
            ),
            // Check if deleted exempt dropdown values are NULL
            array(
                array(
                    0 => 'test 0',
                ),
                'parent_type_display',
                array(
                    'parent_type_display' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                    ),
                ),
                "",
                array(
                    0 => 'test 0',
                    1 => null,
                ),
            ),
            // Check if NULL values from custom/include/language file are copied over so we don't loose the keys
            array(
                array(
                    0 => 'test 0',
                ),
                'parent_type_display',
                array(
                    'parent_type_display' => array(
                        0 => 'test 0',
                        1 => 'test 1',
                        2 => 'test 2',
                    ),
                ),
                "<?php
                    \$app_list_strings['parent_type_display'] = array(
                        'ProjectTask' => 'Project Task',
                        'Prospects' => null,
                    );
                ",
                array(
                    0 => 'test 0',
                    1 => null,
                    2 => null,
                    'Prospects' => null,
                ),
            ),
        );
    }
}
