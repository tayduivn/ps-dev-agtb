<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA") which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * ConnectorsValidTest
 *
 * @author Andrew Lee
 */


require_once 'include/connectors/utils/ConnectorUtils.php';
require_once 'include/connectors/sources/SourceFactory.php';
require_once 'modules/EAPM/EAPM.php';


class SourceTest
{
    private $id;

    public function __construct($param){
        $this->id = $param;
    }

    public function hasTestingEnabled(){
        if ($this->id == 'ValidTestingDisabledAuth'){
            return false;
        }
        return true;
    }

    public function test(){
        if ($this->id == 'ValidTestingEnabledAuth' || $this->id == 'ValidTestingEnabledUnAuth'){
            return true;
        }
        return false;
    }
}


class ConnectorUtilsTest extends ConnectorUtils
{
    public function getEAPMForConnector($connector){
        if ($connector['id'] == 'ValidTestingDisabledAuth' || $connector['id'] == 'ValidTestingEnabledAuth'){
            $toRet = new stdClass();
            $toRet->id = 1;
            return $toRet;
        }
        else {
            return null;
        }
    }

    public function getSourceForConnector($connector){
        if ($connector['id'] == 'InvalidNoSource') {
            return null;
        }
        else{
            return new SourceTest($connector['id']);
        }
    }
}

class ConnectorsValidTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function testGetValidConnectors()
    {
        $allConnectors = array(
            array('id'=>'ValidTestingDisabledAuth'),
            array('id'=>'ValidTestingEnabledAuth'),
            array('id'=>'ValidTestingEnabledUnAuth'),
            array('id'=>'InvalidTestFails'),
            array('id'=>'InvalidNoSource'));
        $connectorUtils =  new ConnectorUtilsTest();
        $validConnectors = $connectorUtils->getValidConnectors($allConnectors);

        $this->assertEquals(count($allConnectors), 5);
        $this->assertEquals(count($validConnectors), 3);
        $this->assertTrue($validConnectors[0]['auth']);
        $this->assertTrue($validConnectors[1]['auth']);
        $this->assertTrue(!$validConnectors[2]['auth']);
    }
}