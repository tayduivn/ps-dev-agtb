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

namespace Sugarcrm\SugarcrmTests\Notification\MessageBuilder;

use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry;
use Sugarcrm\Sugarcrm\Notification\EventInterface as NotificationEventInterface;

/**
 * Class MessageBuilderRegistryTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry
 */
class MessageBuilderRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var NotificationEventInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $event = null;

    /** @var MessageBuilderRegistry */
    protected $builder = null;

    /** @var string */
    protected $messageBuilderNoSupportsClass = '';

    /** @var string */
    protected $messageBuilderSupportsEveryEventClass = '';

    /** @var string */
    protected $foregroundBuilderSupportsEveryEvent = '';

    /** @var string */
    protected $customBuilder = '';

    /** @var string */
    protected $customBuilderNotImplementInterface = '';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('files');

        $this->messageBuilderNoSupportsClass = 'MessageBuilder' . rand(1000, 1999);
        $this->createDummyBuilderCode($this->messageBuilderNoSupportsClass, 'false', 10);
        $this->messageBuilderSupportsEveryEventClass = 'MessageBuilder' . rand(2000, 2999);
        $this->createDummyBuilderCode($this->messageBuilderSupportsEveryEventClass, 'true', 10);
        $this->foregroundBuilderSupportsEveryEvent = 'MessageBuilder' . rand(3000, 3999);
        $this->createDummyBuilderCode($this->foregroundBuilderSupportsEveryEvent, 'true', 20);

        $this->customBuilder = 'MessageBuilder' . rand(4000, 4999);
        $this->createDummyBuilderCode($this->customBuilder, 'true', 20);
        $this->customBuilderNotImplementInterface = 'MessageBuilder' . rand(5000, 5999);
        $this->createDummyBuilderCode($this->customBuilderNotImplementInterface, 'true', 10000, false);

        $this->event = $this->getMock('Sugarcrm\Sugarcrm\Notification\EventInterface');
        $this->builder = MessageBuilderRegistry::getInstance();

        \SugarTestHelper::ensureDir('custom/include');
        \SugarTestHelper::saveFile('custom/' . MessageBuilderRegistry::REGISTRY_FILE);
        $this->saveCustomEventBuildersList(array());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Should return null if module builder is not registered for event.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry::getBuilder
     */
    public function testGetBuilderReturnsNull()
    {
        $this->saveEventBuildersList(array(
            $this->messageBuilderNoSupportsClass
        ));
        $this->assertNull($this->builder->getBuilder($this->event));
    }

    /**
     * Should return suitable module builder if supports.
     * We know two builders, one of them does not support current event.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry::getBuilder
     */
    public function testGetBuilderReturnsSuitable()
    {
        $this->saveEventBuildersList(array(
            $this->messageBuilderNoSupportsClass,
            $this->messageBuilderSupportsEveryEventClass,
        ));

        $expectedBuilder = new $this->messageBuilderSupportsEveryEventClass();
        $this->assertEquals($expectedBuilder, $this->builder->getBuilder($this->event));
    }

    /**
     * Should return builder with higher level if two or more builders are present.
     * Have two supported builders: one builder has level 10, another - 20. Should return with 20.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry::getBuilder
     */
    public function testGetBuilderReturnsWithHigherLevel()
    {
        $this->saveEventBuildersList(array(
            $this->messageBuilderNoSupportsClass,
            $this->messageBuilderSupportsEveryEventClass,
            $this->foregroundBuilderSupportsEveryEvent,
        ));

        $expectedBuilder = new $this->foregroundBuilderSupportsEveryEvent();
        $this->assertEquals($expectedBuilder, $this->builder->getBuilder($this->event));
    }

    /**
     * Should use builder from custom/include/mnb.php if builder implements MessageBuilderInterface interface.
     * Custom builder should has higher or equal level with default.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry::getBuilder
     */
    public function testCustomExtraBuilders()
    {
        $this->saveEventBuildersList(array(
            $this->messageBuilderNoSupportsClass,
            $this->messageBuilderSupportsEveryEventClass,
            $this->foregroundBuilderSupportsEveryEvent,
        ));

        $this->saveCustomEventBuildersList(array(
            $this->customBuilder,
            $this->customBuilderNotImplementInterface,
        ));

        $expectedBuilder = new $this->customBuilder();
        $this->assertEquals($expectedBuilder, $this->builder->getBuilder($this->event));
    }

    /**
     * Save builders list to registry file and clear cache. Need for correct builders list processed.
     *
     * @param array $builders
     */
    protected function saveEventBuildersList(array $builders)
    {
        \SugarTestHelper::saveFile(MessageBuilderRegistry::REGISTRY_FILE);
        write_array_to_file(
            MessageBuilderRegistry::VARIABLE,
            $builders,
            MessageBuilderRegistry::REGISTRY_FILE
        );

        $cacheFile = sugar_cached(MessageBuilderRegistry::CACHE_FILE);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Save custom builders list to registry file and clear cache. Need for correct builders list processed.
     *
     * @param array $builders
     */
    protected function saveCustomEventBuildersList(array $builders)
    {
        write_array_to_file(
            MessageBuilderRegistry::VARIABLE,
            $builders,
            'custom/' . MessageBuilderRegistry::REGISTRY_FILE
        );
        $cacheFile = sugar_cached(MessageBuilderRegistry::CACHE_FILE);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }


    /**
     * Generate builder's code with given params and require generated file.
     * If $implementInterface is false, generated class does not implements MessageBuilderInterface.
     *
     * @param string $className
     * @param string $support
     * @param int $level
     * @param bool $implementInterface
     */
    private function createDummyBuilderCode($className, $support, $level, $implementInterface = true)
    {
        $fileName = sugar_cached($className . '.php');
        $implement = '';
        if ($implementInterface) {
            $implement = 'implements Sugarcrm\\Sugarcrm\\Notification\\MessageBuilder\\MessageBuilderInterface';
        }
        $code = "<?php
            use Sugarcrm\\Sugarcrm\\Notification\\EventInterface;

            class {$className} {$implement}
            {
                public function supports(EventInterface \$event)
                {
                    return {$support};
                }

                public function getLevel()
                {
                    return {$level};
                }

                public function build(EventInterface \$event, \$filter, \\User \$user, array \$messageSignature)
                {

                }
            }
        ";
        \SugarTestHelper::saveFile($fileName);
        sugar_file_put_contents($fileName, $code);
        require_once $fileName;
    }
}
