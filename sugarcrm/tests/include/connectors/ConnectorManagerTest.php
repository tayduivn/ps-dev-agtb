<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * ConnectorsManagerTest
 *
 */


require_once 'include/connectors/utils/ConnectorUtils.php';
require_once 'include/connectors/sources/SourceFactory.php';
require_once 'modules/EAPM/EAPM.php';
require_once 'include/connectors/ConnectorManager.php';


class SourceTest
{
    private $id;

    public function __construct($param)
    {
        $this->id = $param;
        $this->name = $param;
    }

    public function hasTestingEnabled()
    {
        if ($this->id == 'ValidTestingDisabledAuth') {
            return false;
        }
        return true;
    }

    public function test()
    {
        if ($this->id == 'ValidTestingEnabledAuth' || $this->id == 'ValidTestingEnabledUnAuth') {
            return true;
        }
        return false;
    }

    public function getMapping()
    {
        return array(
            'testfield1' => 'value',
            'testfield2' => 'value'
        );
    }
}


class ConnectorManagerTest extends ConnectorManager
{
    public function getConnectorList() {
        return array(
            'ValidTestingDisabledAuth' =>
                array('id' => 'ValidTestingDisabledAuth'),
            'ValidTestingEnabledAuth' =>
                array('id' => 'ValidTestingEnabledAuth'),
            'ValidTestingEnabledUnAuth' =>
                array('id' => 'ValidTestingEnabledUnAuth'),
            'InvalidTestFails' =>
                array('id' => 'InvalidTestFails'),
            'InvalidNoSource' =>
                array('id' => 'InvalidNoSource')
        );
    }
    public function getEAPMForConnector($connector)
    {
        if ($connector['id'] == 'ValidTestingDisabledAuth' || $connector['id'] == 'ValidTestingEnabledAuth') {
            $toRet = new stdClass();
            $toRet->id = 1;
            return $toRet;
        } else {
            return null;
        }
    }

    public function getSourceForConnector($connector)
    {
        if ($connector['id'] == 'InvalidNoSource') {
            return null;
        } else {
            return new SourceTest($connector['id']);
        }
    }
}

class ConnectorsValidTest extends Sugar_PHPUnit_Framework_OutputTestCase
{

    public function tearDown()
    {
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            // delete the current file because it has trash data in it
            unlink($cacheFile);
        }
    }

    /*
     * test get connectors and initial cache
     */
    public function testGetConnectors()
    {
        $expectedOut = array(
            'ValidTestingDisabledAuth' =>
                array(
                    'id' => 'ValidTestingDisabledAuth',
                    'testing_enabled' => false,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'ValidTestingEnabledAuth' =>
                array(
                    'id' => 'ValidTestingEnabledAuth',
                    'testing_enabled' => true,
                    'test_passed' => true,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'ValidTestingEnabledUnAuth' =>
                array(
                    'id' => 'ValidTestingEnabledUnAuth',
                    'testing_enabled' => true,
                    'test_passed' => true,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'InvalidTestFails' =>
                array(
                    'id' => 'InvalidTestFails',
                    'testing_enabled' => true,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
            'InvalidNoSource' =>
                array(
                    'id' => 'InvalidNoSource',
                    'testing_enabled' => false,
                    'test_passed' => false,
                    'eapm_bean' => false,
                    'field_mapping' => array()
                ),
        );

        $connectorManager = new ConnectorManagerTest();
        $connectors = $connectorManager->buildConnectorsMeta();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));
        $currentHash = $connectors['_hash'];
        unset($connectors['_hash']);
        $this->assertEquals($connectors, $expectedOut);

        // should create cache file
        // Handle the cache file
        $cacheFile = sugar_cached('api/metadata/connectors.php');
        if (file_exists($cacheFile)) {
            require $cacheFile;
        }
        $this->assertEquals($currentHash, $connectors['_hash']);
    }

    /**
     * test getting current hash
     */
    public function testHashes()
    {
        $connectorManager = new ConnectorManagerTest();

        $connectors = $connectorManager->getUserConnectors();

        // should get valid connectors with a hash
        $this->assertTrue(!empty($connectors['_hash']));


        $currentUserHash = $connectors['_hash'];
        unset($connectors['_hash']);

        $this->assertTrue($connectorManager->isHashValid($currentUserHash));

        $this->assertFalse($connectorManager->isHashValid('invalidHash'));


    }
}