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

require_once 'modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticate.php';

/**
 * @covers SAMLAuthenticate
 */
class SAMLAuthenticateTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider addQueryVarsProvider
     */
    public function testAddQueryVars($url, $vars, $expected)
    {
        $auth = new SAMLAuthenticate();
        $actual = SugarTestReflection::callProtectedMethod($auth, 'addQueryVars', array($url, $vars));

        $this->assertEquals($expected, $actual);
    }

    public static function addQueryVarsProvider()
    {
        return array(
            'empty-vars-url-unchanged' => array(
                'http://example.com/',
                array(),
                'http://example.com/',
            ),
            'vars-appended-with-?' => array(
                'http://example.com/',
                array('param1' => 'value1'),
                'http://example.com/?param1=value1',
            ),
            'vars-appended-with-&' => array(
                'http://example.com/?param1=value1',
                array('param2' => 'value2'),
                'http://example.com/?param1=value1&param2=value2',
            ),
            'vars-escaped' => array(
                'http://example.com/',
                array('!' => '@'),
                'http://example.com/?%21=%40',
            ),
        );
    }
}
