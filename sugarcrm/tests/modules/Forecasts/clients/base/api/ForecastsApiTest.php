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
require_once 'modules/Forecasts/clients/base/api/ForecastsApi.php';

class ForecastsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @expectedException SugarApiExceptionInvalidHash
     */
    public function testCompareConfigsThrowsException()
    {
        $adm = $this->getMock('Administration', array('save'));
        $adm->expects($this->any())
            ->method('save');

        $rs = new SugarTestRestServiceMock();
        $obj = new ForecastsApi();

        SugarTestReflection::callProtectedMethod(
            $obj,
            'compareSettingsToDefaults',
            array($adm, array('two' => 'one'), $rs)
        );
    }

    public function testCompareConfigsDoestThrowsException()
    {
        $adm = $this->getMock('Administration', array('save'));
        $adm->expects($this->any())
            ->method('save');

        $rs = new SugarTestRestServiceMock();
        $obj = new ForecastsApi();

        SugarTestReflection::callProtectedMethod(
            $obj,
            'compareSettingsToDefaults',
            array($adm, ForecastsDefaults::getDefaults(), $rs)
        );

        $this->assertTrue(true);
    }
}
