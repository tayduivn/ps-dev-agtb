<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/utils.php');

function testFuncString()
{
    return 'func string';
}

function testFuncArgs($args)
{
    return $args;
}

class testBeanParam
{
    public function testFuncBean()
    {
        return 'func bean';
    }
}

/**
 * @ticket 65074
 */
class Bug65074Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $customIncludeDir = 'custom/include';
    protected $customIncludeFile = 'bug65074_include.php';

    public function setUp()
    {

        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
function testFuncInclude()
{
        return 'func include';
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }

        SugarAutoLoader::put($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent, true);
    }

    public function tearDown()
    {
        // remove the custom include file
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            SugarAutoLoader::unlink($this->customIncludeDir . '/' . $this->customIncludeFile, true);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testGetFunctionValue
     */
    public function dataProviderForTestGetFunctionValue()
    {
        return array(
                array(null, 'testFuncString', array(), 'func string'),
                array(null, 'testFuncArgs', array('func args'), 'func args'),
                array(new testBeanParam(), 'testFuncBean', array(), 'func bean'),
                array('', array('name'=>'testFuncInclude', 'include'=>$this->customIncludeDir . '/' . $this->customIncludeFile), array(), 'func include')
        );
    }

    /**
     * Tests function getFunctionValue()
     * @dataProvider dataProviderForTestGetFunctionValue
     */
    public function testGetFunctionValue($bean, $function, $args, $value)
    {
        $this->assertEquals($value, getFunctionValue($bean, $function, $args), 'Function getFunctionValue() returned wrong result.');
    }
}
