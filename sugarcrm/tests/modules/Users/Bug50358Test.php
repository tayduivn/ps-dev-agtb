<?php
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

/**
 * Created by JetBrains PhpStorm.
 * User: idymovsky
 * Date: 2/14/12
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */

require_once 'modules/Users/views/view.wizard.php';

class Bug50358Test extends Sugar_PHPUnit_Framework_TestCase
{
    public $view;
    public function setUp()
    {
        $_REQUEST['module'] = 'Accounts';
        $this->view = new ViewWizard;
    }

    public function tearDown()
    {
        unset($this->view);
    }

    public function currencyDataProvider()
    {
        return array (
            array (
                array (
                    '-99' => array (
                        'name' => 'USD',
                        'symbol' => 'USD'
                    ),
                    '1' => array (
                        'name' => 'EUR',
                        'symbol' => '&'
                    ),
                    '2' => array (
                        'name' => 'AAA',
                        'symbol' => '*'
                    )
                ),
                "currencies[0] = 'USD';\ncurrencies[1] = '*';\ncurrencies[2] = '&';"
            ),
            array (
                array (
                    '-99' => array (
                        'name' => 'USD',
                        'symbol' => 'USD'
                    ),
                    '1' => array (
                        'name' => 'AAA',
                        'symbol' => '*'
                    ),
                    '2' => array (
                        'name' => 'EUR',
                        'symbol' => '&'
                    )
                ),
                "currencies[0] = 'USD';\ncurrencies[1] = '*';\ncurrencies[2] = '&';"
            ),
        );
    }

    /**
     * @dataProvider currencyDataProvider
     */
    public function testPhpArrayToJavascriptArrayConvertion($currencyArray, $javascriptArrayString)
    {
        $this->assertEquals(trim($javascriptArrayString), trim($this->view->correctCurrenciesSymbolsSort($currencyArray)));
    }
}