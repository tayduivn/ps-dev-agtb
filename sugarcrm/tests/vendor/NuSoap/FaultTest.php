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

require_once 'vendor/nusoap/nusoap.php';

/**
 * @covers nusoap_fault
 */
class NuSoap_FaultTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testFaultSerialization()
    {
        $fault = new nusoap_fault(1);
        $string = $fault->serialize();

        $xml = simplexml_load_string($string);
        $ns = $xml->getNamespaces(true);
        $nodes = $xml->children($ns['SOAP-ENV'])->Body->Fault->children();

        $names = array();
        foreach ($nodes as $node) {
            $names[] = $node->getName();
        }

        $this->assertEquals(array(
            'faultcode',
            'faultstring',
            'faultactor',
            'detail',
        ), $names);
    }
}
