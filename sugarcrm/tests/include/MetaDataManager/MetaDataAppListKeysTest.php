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

require_once 'include/MetaDataManager/MetaDataManager.php';


class MetaDataAppListKeysTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testAppListKeys()
    {
        $app_list_strings = array(
            'alpha_list'=>
            array(
                "3" => "A",
                "4" => "B",
                "1" => "C",
                "6" => "D",
                "2" => "E",
                "5" => "F"
            )
        );

        $app_list_keys = array('alpha_list'=> array("3", "4", "1", "6", "2", "5"));

        $md = new MetaDataManager();

        $result = $md->getAppListKeys($app_list_strings);

        $this->assertEquals($app_list_keys, $result, "Output array did not match");
    }
}

