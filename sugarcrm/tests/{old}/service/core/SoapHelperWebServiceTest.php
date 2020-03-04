<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

/**
 * SoapHelperWebServiceTest.php
 *
 * This test may be used to write tests against the SoapHelperWebService.php file and the utility functions found there.
 *
 * @author Collin Lee
 */

require_once('service/core/SoapHelperWebService.php');

class SoapHelperWebServiceTest extends TestCase
{
    private static $original_service_object;

    public static function setUpBeforeClass()
    {
        global $service_object;
        if (!empty($service_object)) {
            self::$original_service_object = $service_object;
        }
    }

    public static function tearDownAfterClass()
    {
        if (!empty(self::$original_service_object)) {
            global $service_object;
            $service_object = self::$original_service_object;
        }
    }

    /**
     * retrieveCheckQueryProvider
     */
    public function retrieveCheckQueryProvider()
    {
        global $service_object;
        $service_object = new ServiceMockObject();
        $error = new SoapError();
        return array(
            array($error, "id = 'abc'", true),
            array($error, "user.id = prospects.id", true),
            array($error, "id $% 'abc'", false),
        );
    }

    /**
     * testCheckQuery
     * This function tests the checkQuery function in the SoapHelperWebService class
     *
     * @dataProvider retrieveCheckQueryProvider();
     */
    public function testCheckQuery($errorObject, $query, $expected)
    {
        $helper = new SoapHelperWebServices();
        if (!method_exists($helper, 'checkQuery')) {
         $this->markTestSkipped('Method checkQuery does not exist');
        }

        $result = $helper->checkQuery($errorObject, $query);
        $this->assertEquals($expected, $result, 'SoapHelperWebService->checkQuery functions as expected');
    }

    /**
     * test the result matches old mcrypt_decrypt
     *
     * @dataProvider openSslDecryptTripledesMethodProvider
     */
    public function testTripledesDecryptBackwordCompatible(string $data, $key)
    {
        if (!extension_loaded('mcrypt')) {
            $this->markTestSkipped("mcrypt extension is not loaded, skip");
        }

        $key = substr(md5($key), 0, 24);
        $iv = 'password';

        $oldDecryptResult = rtrim(mcrypt_decrypt(MCRYPT_3DES, $key, pack("H*", $data), MCRYPT_MODE_CBC, $iv), chr(0));
        $this->assertSame($oldDecryptResult, \SoapHelperWebServices::decrypt_tripledes($data, $key, $iv));
    }

    public function openSslDecryptTripledesMethodProvider()
    {
        return [
            ['232c4daa440989ec433ec701d06bcd92', '123456789012345678901234'],
            ['baa6d122523d31b0bf5be57999525e0b', '123456789012345678901234'],
        ];
    }
}

/**
 * ServiceMockObject
 *
 * Used to override global service_object
 */
class ServiceMockObject {
    public function error()
    {
    }
}
