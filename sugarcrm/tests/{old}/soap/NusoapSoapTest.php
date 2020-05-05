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

require_once 'service/core/NusoapSoap.php';
require_once 'soap/SoapErrorDefinitions.php';

class NusoapSoapTest extends TestCase
{
    /**
     * @dataProvider errorProvider
     */
    public function testError($error_name)
    {
        /** @var NusoapSoap $service */
        $service = $this->getMockBuilder('NusoapSoap')
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $server = new soap_server();
        SugarTestReflection::setProtectedValue($service, 'server', $server);

        $error = new SoapError();
        $error->set_error($error_name);
        $service->error($error);
        $fault = $server->fault;
        $string = $fault->serialize();

        $document = new DOMDocument();
        $document->loadXML($string);

        $schema = __DIR__ . '/envelope.xsd';
        $result = $document->schemaValidate($schema);

        $this->assertTrue($result, 'The resulting XML document is invalid');
    }

    public static function errorProvider()
    {
        global $error_defs;

        return array_map(function ($code) {
            return [$code];
        }, array_keys($error_defs));
    }
}
