<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License. Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party. Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited. You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution. See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License. Please refer to the License for the specific language
 * governing these rights and limitations under the License. Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/ModuleBuilder/parsers/StandardField.php');

/**
 * Bug #46869
 * @ticket 46869
 */
class Bug46869Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    private $customVardefPath;

    public function setUp()
    {
        $this->customVardefPath = 'custom' . DIRECTORY_SEPARATOR .
                                  'Extension' . DIRECTORY_SEPARATOR .
                                  'modules' . DIRECTORY_SEPARATOR .
                                  'Cases' . DIRECTORY_SEPARATOR .
                                  'Ext' . DIRECTORY_SEPARATOR .
                                  'Vardefs' . DIRECTORY_SEPARATOR .
                                  'sugarfield_resolution46869.php';
        $dirname = dirname($this->customVardefPath);

        if (file_exists($dirname) === false)
        {
            mkdir($dirname, 0777, true);
        }

        $code = <<<PHP
<?php
\$dictionary['Case']['fields']['resolution46869']['required']=true;
PHP;

        file_put_contents($this->customVardefPath, $code);

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
    }

    public function tearDown()
    {
        unlink($this->customVardefPath);

        SugarTestHelper::tearDown();
    }

    public function testLoadingCustomVardef()
    {
        $df = new StandardFieldBug46869Test('Cases') ;
        $df->base_path = dirname($this->customVardefPath);
        $customDef = $df->loadCustomDefBug46869Test('resolution46869');

        $this->assertArrayHasKey('required', $customDef, 'Custom definition of Case::resolution46869 does not have required property.');
    }

}

class StandardFieldBug46869Test extends StandardField
{
    public function loadCustomDefBug46869Test($field)
    {
        $this->loadCustomDef($field);

        return $this->custom_def;
    }
}