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
 * @ticket 46740
 */
class Bug46740Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Module to be renamed
     *
     * @var string
     */
    protected $module = 'Contracts';

    /**
     * Module name translation
     *
     * @var string
     */
    protected $translation = 'ContractsBug46740Test';

    /**
     * Temporary file path
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Generates custom module localization file
     */
    public function setUp()
    {
        SugarTestHelper::setUp('moduleList');
        global $sugar_config;
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->language = $sugar_config['default_language'];

        // create custom localization file
        $this->file = 'custom/include/language/' . $this->language . '.lang.php';
        $dirName = dirname($this->file);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $contents = <<<FILE
<?php
\$app_list_strings["moduleList"]["{$this->module}"] = "{$this->translation}";
FILE;

        file_put_contents($this->file, $contents);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes custom module localization file
     */
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unlink($this->file);
        SugarTestHelper::tearDown();
    }

    /**
     * Tests that custom module localization data is used
     */
    public function testCustomModuleLocalizationIsUsed()
    {
        global $sugar_flavor, $server_unique_key, $current_language;
        $app_list_strings = return_app_list_strings_language($this->language);

        $admin_group_header = array();
        require 'modules/Administration/metadata/adminpaneldefs.php';

        $found = false;
        foreach ($admin_group_header as $header)
        {
            $headerGroup = array_shift($header);
            if ($headerGroup === $this->translation)
            {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
