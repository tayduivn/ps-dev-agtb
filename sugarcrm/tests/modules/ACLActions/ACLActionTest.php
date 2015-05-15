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

class ACLActionTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        ACLAction::clearACLCache();

        if (!session_id()) {
            session_id(create_guid());
        }
    }

    protected function tearDown()
    {
        ACLAction::clearACLCache();
    }

    /**
     * @ticket BR-2747
     */
    public function testCache()
    {
        $this->storeToCache('user_1', 'x');
        $data = $this->loadFromCache('user_1');
        $this->assertEquals('x', $data);

        $this->storeToCache('user_2', 'x');
        $data = $this->loadFromCache('user_2');
        $this->assertEquals('x', $data);

        $this->storeToCache('user_1', 'y');
        $data = $this->loadFromCache('user_1');
        $this->assertEquals('y', $data);

        $data = $this->loadFromCache('user_2');
        $this->assertEquals('x', $data, 'The cached ACL data for user #2 should have remained unchanged');
    }

    private function loadFromCache($userId)
    {
        return SugarTestReflection::callProtectedMethod('ACLAction', 'loadFromCache', array($userId, 'test'));
    }

    private function storeToCache($userId, $data)
    {
        return SugarTestReflection::callProtectedMethod('ACLAction', 'storeToCache', array($userId, 'test', $data));
    }
}
