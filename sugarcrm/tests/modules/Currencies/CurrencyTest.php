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
require_once('modules/Currencies/Currency.php');

class CurrencyTest extends Sugar_PHPUnit_Framework_TestCase {

	var $previousCurrentUser;
    var $currencyYen;
    var $currencyId = 'abc123'; // test currency_id

    /**
     * pre test setup
     */
    public function setUp() 
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->currencyYen = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87,$this->currencyId);
    	global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('number_grouping_seperator', ',', 0, 'global');
        $current_user->setPreference('decimal_seperator', '.', 0, 'global');
        $current_user->save();
        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);
    }

    /**
     * post test teardown
     */
    public function tearDown() 
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        global $current_user;
        $current_user = $this->previousCurrentUser;
        $this->currencyYen = null;
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }

    /**
     * test retrieval of base currency
     *
     * @group currency
     */
    public function testCurrencyRetrieveBase()
    {
        $currency = BeanFactory::getBean('Currencies','-99');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals(1.0,$currency->conversion_rate);
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertToDollar()
    {
        $this->assertEquals(1.267909,$this->currencyYen->convertToDollar(100.00));
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertFromDollar()
    {
        $this->assertEquals(7887,$this->currencyYen->convertFromDollar(100.00));
    }

    /**
     * test retrieval of base currency name
     *
     * @group currency
     */
    public function testGetBaseCurrencyName()
    {
        $this->assertEquals('US Dollars',$this->currencyYen->getDefaultCurrencyName());
    }

    /**
     * test retrieval of base currency symbol
     *
     * @group currency
     */
    public function testGetBaseCurrencySymbol()
    {
        $this->assertEquals('$',$this->currencyYen->getDefaultCurrencySymbol());
    }

    /**
     * test retrieval of base currency ISO code
     *
     * @group currency
     */
    public function testGetBaseCurrencyISO()
    {
        $this->assertEquals('USD',$this->currencyYen->getDefaultISO4217());
    }

    /**
     * test retrieval of currency by symbol
     *
     * @dataProvider testRetrieveIdBySymbolProvider
     * @param string $expectedId
     * @param string $symbol
     * @group currency
     */
    public function testRetrieveIdBySymbol($expectedId,$symbol)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDBySymbol($symbol));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function testRetrieveIdBySymbolProvider()
    {
        return array(
            array($this->currencyId,'¥'),
            array('-99','$'),
        );
    }

    /**
     * test retrieval of currency by ISO
     *
     * @dataProvider testRetrieveIdByIsoProvider
     * @param string $expectedId
     * @param string $ISO
     * @group currency
     */
    public function testRetrieveIdByIso($expectedId,$ISO)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDByISO($ISO));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function testRetrieveIdByIsoProvider()
    {
        return array(
            array($this->currencyId,'YEN'),
            array('-99','USD'),
        );
    }

    /**
     * test retrieval of currency by symbol
     *
     * @dataProvider testRetrieveIdByNameProvider
     * @param string $expectedId
     * @param string $name
     * @group currency
     */
    public function testRetrieveIdByName($expectedId,$name)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDByName($name));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function testRetrieveIdByNameProvider()
    {
        return array(
            array($this->currencyId,'Yen'),
            array('-99','US Dollars'),
        );
    }

    /**
     * test unformatting currency
     *
     * @group currency
     */

    public function testUnformatNumber()
    {
    	global $current_user;
    	$testValue = "$100,000.50";
    	
    	$unformattedValue = unformat_number($testValue);
    	$this->assertEquals(100000.50, $unformattedValue, "Assert that $100,000.50 becomes 100000.50. Unformatted value is: ".$unformattedValue);
    	
    	//Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);       
        
        $testValue = "$100.000,50";
        $unformattedValue = unformat_number($testValue);
    	$this->assertEquals(100000.50, $unformattedValue, "Assert that $100.000,50 becomes 100000.50. Unformatted value is: ".$unformattedValue);

        $testValue = "0.9";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(0.9, $unformattedValue, "Assert that 0.9 stays 0.9. Unformatted value is: ".$unformattedValue);

        $testValue = "-0.9";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(-0.9, $unformattedValue, "Assert that -0.9 stays -0.9. Unformatted value is: ".$unformattedValue);

    }

    /**
     * test formatting currency
     *
     * @group currency
     */

    public function testFormatNumber()
    {
    	global $current_user;
    	$testValue = "100000.50";
    	
    	$formattedValue = format_number($testValue);
    	$this->assertEquals("100,000.50", $formattedValue, "Assert that 100000.50 becomes 100,000.50. Formatted value is: ".$formattedValue);
    	
    	//Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);       
        
        $testValue = "100000.50";
        $formattedValue = format_number($testValue);
    	$this->assertEquals("100.000,50", $formattedValue, "Assert that 100000.50 becomes 100.000,50. Formatted value is: ".$formattedValue);
    }    
    
} 

?>
