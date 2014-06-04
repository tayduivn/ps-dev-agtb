<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_FixAddressStreetFields.php';

class FixAddressStreetFieldsTest extends UpgradeTestCase
{
    protected $testClass = null;

    protected $sampleVardefs = array(
        'example' => array(
            'type' => 'id',
        ),
        'validCandidate_street' => array(
            'type' => 'varchar',
        ),
        'validCandidate_city' => array(
            'type' => 'varchar',
        ),
        'noCity_street' => array(
            'type' => 'varchar',
        ),
        'alreadyUpgraded_street' => array(
            'type' => 'text',
        ),
        'alreadyUpgraded_city' => array(
            'type' => 'varchar',
        ),
        'trailingCharacter_street_3' => array(
            'type' => 'varchar',
        ),
        'trailingCharacter_city' => array(
            'type' => 'varchar',
        ),

    );

    public function setup() {
        parent::setUp();
        $this->testClass = new SugarUpgradeFixAddressStreetFields($this->upgrader);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testValidateStreetField() {
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'example'));
        $this->assertTrue($this->testClass->validateStreetField($this->sampleVardefs, 'validCandidate_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'validCandidate_city'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'noCity_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'alreadyUpgraded_street'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'alreadyUpgraded_city'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'trailingCharacter_street_3'));
        $this->assertFalse($this->testClass->validateStreetField($this->sampleVardefs, 'trailingCharacter_city'));
    }
}
