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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
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
