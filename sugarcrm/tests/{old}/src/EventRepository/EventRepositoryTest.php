<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\EventRepositoryTest;

use Doctrine\DBAL\Connection;
use Sugarcrm\Sugarcrm\Audit\EventRepository;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use SugarTestContactUtilities;
use SugarTestHelper;

/**
 * @covers \Sugarcrm\Sugarcrm\Audit\EventRepository
 */
class EventRepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $contactBean;
    private $eventRepo;
    private $conn;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->contactBean = SugarTestContactUtilities::createContact(null, array('phone_work' => '(111) 111-1111'));

        $container = Container::getInstance();
        $this->eventRepo = $container->get(EventRepository::class);
        $this->conn = $container->get(Connection::class);
    }

    public static function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    /**
     * @test
     */
    public function registerUpdate()
    {
        $list = FieldList::fromArray(array('phone_work'));
        $auditEventId = $this->eventRepo->registerUpdate($this->contactBean, $list);

        $count = $this->getAuditEvent($auditEventId);
        $this->assertEquals(1, $count, 'Audit event not created.');
    }

    /**
     * @test
     */
    public function registerErasure()
    {
        $list = FieldList::fromArray(array('phone_work'));
        $auditEventId = $this->eventRepo->registerErasure($this->contactBean, $list);

        $count = $this->getAuditEvent($auditEventId);
        $this->assertEquals(1, $count, 'Audit event not created.');
    }

    private function getAuditEvent($auditEventId)
    {
        $query = 'SELECT EXISTS (SELECT id
                FROM audit_events
                WHERE id = ?) AS cnt';

        $stmt = $this->conn->executeQuery($query, array($auditEventId));

        $row = $stmt->fetch();

        return $row['cnt'];
    }
}
