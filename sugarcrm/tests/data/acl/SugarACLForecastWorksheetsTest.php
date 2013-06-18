<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

class SugarACLForecastWorksheetsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group forecasts
     */
    public function testCheckAccessWithViewEqualToField()
    {
        $beanMock = $this->getMock('Product', array('ACLFieldAccess'));
        $beanMock->expects($this->once())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(true));

        $userMock = $this->getMock('User', array('isAdminForModule'));
        $userMock->expects($this->once())
            ->method('isAdminForModule')
            ->will($this->returnValue(false));
        $userMock->id = 'test_user_id';

        $acl_class = $this->getMock('SugarACLForecastWorksheets', array('getForecastByBean'));
        $acl_class->expects($this->once())
            ->method('getForecastByBean')
            ->will($this->returnValue($beanMock));

        $context = array('field' => 'test_field', 'action' => 'write', 'user' => $userMock);

        $ret = $acl_class->checkAccess('ForecastWorksheets', 'field', $context);

        $this->assertTrue($ret);
    }

    /**
     * @group forecasts
     */
    public function testCheckAccessWithViewNotEqualToField()
    {
        $beanMock = $this->getMock('Product', array('ACLFieldAccess'));
        $beanMock->expects($this->never())
            ->method('ACLFieldAccess');

        $userMock = $this->getMock('User', array('isAdminForModule'));
        $userMock->expects($this->once())
            ->method('isAdminForModule')
            ->will($this->returnValue(false));
        $userMock->id = 'test_user_id';

        $acl_class = $this->getMock('SugarACLForecastWorksheets', array('getForecastByBean'));
        $acl_class->expects($this->once())
            ->method('getForecastByBean')
            ->will($this->returnValue($beanMock));

        $context = array('field' => 'test_field', 'action' => 'write', 'user' => $userMock);

        $ret = $acl_class->checkAccess('ForecastWorksheets', 'view', $context);

        $this->assertTrue($ret);
    }
}
