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

use Contact;
use Doctrine\DBAL\Connection;
use Sugarcrm\Sugarcrm\Audit\EventRepository;
use Sugarcrm\Sugarcrm\Audit\FieldChangeList;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use SugarTestContactUtilities;
use SugarTestHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Audit\EventRepository
 */
class EventRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Contact
     */
    private $contactBean;

    /**
     * @var EventRepository
     */
    private $eventRepo;

    /**
     * @var Connection
     */
    private $conn;

    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $container = Container::getInstance();
        $context = $container->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        $this->contactBean = SugarTestContactUtilities::createContact(
            null,
            ['phone_work' => '(111) 111-1111',
                'first_name' => 'John',
                'phone_fax' => '(111) 111-1111',
                'phone_mobile' => '(111) 111-1111',
                'phone_home' => '(111) 111-1111']
        );

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
     * @covers ::registerUpdate()
     */
    public function registerUpdate()
    {
        $list = FieldChangeList::fromChanges([
            [
                'field_name' => 'phone_work',
                'data_type' => 'varchar',
                'before' => '(111) 111 1111',
                'after' => '(222) 222 2222',
            ],
        ]);

        $auditEventId = $this->eventRepo->registerUpdate($this->contactBean, $list);

        $this->assertTrue($this->eventExists($auditEventId), 'Audit event not created.');
    }

    /**
     * @test
     * @covers ::registerErasure()
     */
    public function registerErasure()
    {
        $list = FieldList::fromArray(array('phone_work'));
        $auditEventId = $this->eventRepo->registerErasure($this->contactBean, $list);

        $this->assertTrue($this->eventExists($auditEventId), 'Audit event not created.');
    }

    private function eventExists(string $id) : bool
    {
        return $this->conn
            ->executeQuery('SELECT id FROM audit_events WHERE id = ?', [$id])
            ->fetchColumn() === $id;
    }

    /**
     * @test
     * @covers ::getLatestBeanEvents()
     */
    public function getLatestBeanEvents()
    {
        //retrieve bean otherwise change will not be detected.
        $this->contactBean = $this->contactBean->retrieve();
        $this->contactBean->phone_home = '(222) 222-2222';
        $this->contactBean->phone_fax = '(222) 222-2222';
        $this->contactBean->phone_mobile = '(222) 222-2222';
        $this->contactBean->first_name = 'John 2';
        $this->contactBean->save(false);

        //generate another set of changes
        $this->contactBean = $this->contactBean->retrieve();
        $this->contactBean->phone_home = '(333) 333-3333';
        $this->contactBean->phone_fax = '(333) 333-3333';
        $this->contactBean->phone_mobile = '(333) 333-3333';
        $this->contactBean->phone_home = '(333) 333-3333';
        $this->contactBean->first_name = 'John 3';
        $this->contactBean->save(false);

        $actual = $this->eventRepo->getLatestBeanEvents('Contacts', $this->contactBean->id, ['phone_mobile', 'phone_fax', 'title', 'first_name']);
        $this->assertCount(3, $actual, 'Expected number of rows not found.');
    }
}
