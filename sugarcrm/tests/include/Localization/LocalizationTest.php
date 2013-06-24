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
 
require_once 'include/Localization/Localization.php';

class LocalizationTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Localization
     */
    protected $_locale;

    /**
     * @var User
     */
    protected $_user;
    /**
     * pre-class environment setup
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function setUp() 
    {
        global $current_user;
        $this->_locale = new Localization();
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->_user;
        $this->_currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

    }
    
    public function tearDown()
    {
        // remove test user
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->_locale);
        unset($this->_user);
        unset($this->_currency);

        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     * post-object environment teardown
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function providerGetLocaleFormattedName()
    {
        return array(
            array(
                't s f l',
                'Mason',
                'Hu',
                'Mr.',
                'Saler',
                'Saler Mr. Mason Hu',
                ),
            array(
                'l f',
                'Mason',
                'Hu',
                '',
                '',
                'Hu Mason',
                ),
                    
            );
    }
    
    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatInUserPreference($nameFormat,$firstName,$lastName,$salutation,$title,$expectedOutput)
    {
    	$this->_user->setPreference('default_locale_name_format', $nameFormat);
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, '',$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    }
    
    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatSpecified($nameFormat,$firstName,$lastName,$salutation,$title,$expectedOutput)
    {
    	$outputName = $this->_locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, $nameFormat,$this->_user);
    	$this->assertEquals($expectedOutput, $outputName);
    }
    
    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmpty()
    {
        $this->_user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = ' ';
        $outputName = $this->_locale->getLocaleFormattedName('', '', '', '', '',$this->_user);
        
        $this->assertEquals($expectedOutput, $outputName);
    }
    
    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmptyAndReturningEmptyString()
    {
        $this->_user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = '';
        $outputName = $this->_locale->getLocaleFormattedName('', '', '', '', '',$this->_user,true);
        
        $this->assertEquals($expectedOutput, $outputName);
    }
    
    public function testCurrenciesLoadingCorrectly()
    {
        global $sugar_config;
        
        $currencies = $this->_locale->getCurrencies();
        
        $this->assertEquals($currencies['-99']['name'],$sugar_config['default_currency_name']);
        $this->assertEquals($currencies['-99']['symbol'],$sugar_config['default_currency_symbol']);
        $this->assertEquals($currencies['-99']['conversion_rate'],1);
    }
    
    public function testConvertingUnicodeStringBetweenCharsets()
    {
        $string = "アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド";
        
        $convertedString = $this->_locale->translateCharset($string,'UTF-8','EUC-CN');
        $this->assertNotEquals($string,$convertedString);
        
        // test for this working by being able to convert back and the string match
        $convertedString = $this->_locale->translateCharset($convertedString,'EUC-CN','UTF-8');
        $this->assertEquals($string,$convertedString);
    }
    
    public function testConvertKS_C_56011987AsCP949()
    {
        if ( !function_exists('iconv') ) {
            $this->markTestSkipped('Requires iconv');
        }
        
        $string = file_get_contents(dirname(__FILE__)."/Bug49619.txt");
        
        $convertedString = $this->_locale->translateCharset($string,'KS_C_5601-1987','UTF-8', true);
        $this->assertNotEquals($string,$convertedString);
        
        // test for this working by being able to convert back and the string match
        $convertedString = $this->_locale->translateCharset($convertedString,'UTF-8','KS_C_5601-1987',true);
        $this->assertEquals($string,$convertedString);
    }
    
    public function testCanDetectAsciiEncoding()
    {
        $string = 'string';
        
        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'ASCII'
            );
    }
    
    public function testCanDetectUtf8Encoding()
    {
        $string = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド';
        
        $this->assertEquals(
            $this->_locale->detectCharset($string),
            'UTF-8'
            );
    }
    
    public function testGetPrecedentPreferenceWithUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','John is Really Cool');
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $this->_user->getPreference('export_delimiter')
            );
        
        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }
    
    public function testGetPrecedentPreferenceWithNoUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user),
            $GLOBALS['sugar_config']['export_delimiter']
            );
        
        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }
    
    /**
     * @ticket 33086
     */
    public function testGetPrecedentPreferenceWithUserPreferenceAndSpecifiedConfigKey()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->_user->setPreference('export_delimiter','');
        $GLOBALS['sugar_config']['default_random_setting_for_localization_test'] = 'John is not Cool at all';
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('export_delimiter',$this->_user,'default_random_setting_for_localization_test'),
            $GLOBALS['sugar_config']['default_random_setting_for_localization_test']
            );
        
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        unset($GLOBALS['sugar_config']['default_random_setting_for_localization_test']);
    }
    
    /**
     * @ticket 39171
     */
    public function testGetPrecedentPreferenceForDefaultEmailCharset()
    {
        $emailSettings = array('defaultOutboundCharset' => 'something fun');
        $this->_user->setPreference('emailSettings',$emailSettings, 0, 'Emails');
        
        $this->assertEquals(
            $this->_locale->getPrecedentPreference('default_email_charset',$this->_user),
            $emailSettings['defaultOutboundCharset']
            );
    }
    
    /**
     * @ticket 23992
     */
    public function testGetCurrencySymbol()
    {
        $this->_user->setPreference('currency',$this->_currency->id);

        $this->assertEquals(
            $this->_locale->getCurrencySymbol($this->_user),
            '¥'
            );
    }
    
    /**
     * @ticket 23992
     */
    public function testGetLocaleFormattedNumberWithNoCurrencySymbolSpecified()
    {
        $this->_user->setPreference('currency',$this->_currency->id);
        $this->_user->setPreference('dec_sep','.');
        $this->_user->setPreference('num_grp_sep',',');
        $this->_user->setPreference('default_currency_significant_digits',2);

        $this->assertEquals(
            $this->_locale->getLocaleFormattedNumber(20,'',true,$this->_user),
            '¥20'
            );
    }

    /**
     * @bug 60672
     */
    public function testGetNumberGroupingSeparatorIfSepIsEmpty()
    {
        $this->_user->setPreference('num_grp_sep','');
        $this->assertEmpty($this->_locale->getNumberGroupingSeparator(), "1000s separator should be ''");
    }

    /**
     * Test to make sure that when num_grp_sep is passed with out a sugarDefaultConfig Name it returns null if not set
     *
     * @covers getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsNullForNumGrpSep()
    {
        $this->assertNull($this->_locale->getPrecedentPreference('num_grp_sep', $this->_user));
    }

    /**
     * Test to make sure that the proper value is returned from getPrecedentPreference for num_grp_sep
     * when the user has one
     *
     * @covers getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsValueForNumGrpSep()
    {
        $this->_user->setPreference('num_grp_sep', '!');
        $this->assertEquals('!', $this->_locale->getPrecedentPreference('num_grp_sep', $this->_user));
    }
}
