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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

/**
 * @ticket 67014
 */
class Bug67014Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        $_GET = array();
        parent::tearDown();
    }

    public function testNoException()
    {
        global $service_object;

        require_once 'service/core/SugarWebService.php';
        $service_object = $this->getMockForAbstractClass('SugarWebService');

        require_once 'service/v4/SugarWebServiceUtilv4.php';
        $helper = new SugarWebServiceUtilv4();

        require_once 'soap/SoapError.php';
        $error = new SoapError();

        $_GET['oauth_signature_method'] = null;

        $result = $helper->checkOAuthAccess($error);
        $this->assertFalse($result);
    }
}
