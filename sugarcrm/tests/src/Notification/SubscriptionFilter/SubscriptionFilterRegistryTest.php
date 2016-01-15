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

namespace Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry;

/**
 * Class SubscriptionFilterRegistryTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry
 */
class SubscriptionFilterRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var SubscriptionFilterRegistry */
    protected $filterRegistry = null;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $cachedFile = sugar_cached(SubscriptionFilterRegistry::CACHE_FILE);
        \SugarTestHelper::setUp('files');
        \SugarTestHelper::ensureDir(array(
            'custom/include',
            'custom/modules/Notification/SubscriptionFilter',
        ));

        \SugarTestHelper::saveFile('custom/' . SubscriptionFilterRegistry::REGISTRY_FILE);
        \SugarTestHelper::saveFile(array(
            sugar_cached(SubscriptionFilterRegistry::CACHE_FILE),
            sugar_cached('CustomFilterCRYS1295.php'),
            sugar_cached('CustomFilterNotImplementInterfaceCRYS1295.php'),
            sugar_cached('ApplicationCRYS1295.php'),
            sugar_cached('ReminderCRYS1295.php'),
            'custom/modules/Notification/SubscriptionFilter/Team.php',
            'custom/modules/Notification/SubscriptionFilter/AssignedToMe.php',
            $cachedFile,
        ));

        write_array_to_file(
            'sfr',
            array(
                'CustomFilter' => 'Sugarcrm\Sugarcrm\cache\CustomFilterCRYS1295',
                'Application' => 'Sugarcrm\Sugarcrm\cache\ApplicationCRYS1295',
                'Reminder' => 'Sugarcrm\Sugarcrm\cache\ReminderCRYS1295',
                'CustomFilterNotImplementInterfaceCRYS1295' =>
                    'Sugarcrm\Sugarcrm\cache\CustomFilterNotImplementInterfaceCRYS1295',
            ),
            'custom/' . SubscriptionFilterRegistry::REGISTRY_FILE
        );
        //clear cache file if it exists
        if (file_exists($cachedFile)) {
            unlink($cachedFile);
        }
        static::saveSubscriptionFilter('CustomFilterCRYS1295');
        static::saveSubscriptionFilter('CustomFilterNotImplementInterfaceCRYS1295', false);
        static::saveSubscriptionFilter(
            'ApplicationCRYS1295',
            false,
            '\\Sugarcrm\\Sugarcrm\\Notification\\SubscriptionFilter\\Application'
        );
        \SugarAutoLoader::$classMap['Sugarcrm\Sugarcrm\custom\Notification\SubscriptionFilter\Team']
            = 'custom/modules/Notification/SubscriptionFilter/Team.php';
        \SugarAutoLoader::$classMap['Sugarcrm\Sugarcrm\custom\Notification\SubscriptionFilter\AssignedToMe']
            = 'custom/modules/Notification/SubscriptionFilter/AssignedToMe.php';

        static::saveSubscriptionFilter('ReminderCRYS1295');
        static::saveCustomFilter('Team', '\Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team');
        static::saveCustomFilter('AssignedToMe');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->filterRegistry = SubscriptionFilterRegistry::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        unset(\SugarAutoLoader::$classMap['Sugarcrm\Sugarcrm\custom\Notification\SubscriptionFilter\Team']);
        unset(
            \SugarAutoLoader::$classMap['Sugarcrm\Sugarcrm\custom\Notification\SubscriptionFilter\AssignedToMe']
        );

        parent::tearDownAfterClass();
    }

    /**
     * Should return correct object.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry',
            SubscriptionFilterRegistry::getInstance()
        );
    }

    /**
     * Data provider for testGetFilter.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\SubscriptionFilterRegistryTest::testGetFilter
     * @return array
     */
    public static function getFilterProvider()
    {
        return array(
            'Application' => array(
                'name' => 'Application',
                'expected' => 'Sugarcrm\Sugarcrm\cache\ApplicationCRYS1295',
            ),
            'Reminder' => array(
                'name' => 'Reminder',
                'expected' => 'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder',
            ),
            'TeamReturnCustom' => array(
                'name' => 'Team',
                'expected' => 'Sugarcrm\Sugarcrm\custom\Notification\SubscriptionFilter\Team',
            ),
            'AssignedToMeReturnDefault' => array(
                'name' => 'AssignedToMe',
                'expected' => 'Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe',
            ),
            'CustomFilter' => array(
                'name' => 'CustomFilter',
                'expected' => 'Sugarcrm\Sugarcrm\cache\CustomFilterCRYS1295',
            ),
        );
    }

    /**
     * Should return correct filter registry object by name.
     *
     * @dataProvider getFilterProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry::getFilter
     * @param string $name
     * @param string $expected
     */
    public function testGetFilter($name, $expected)
    {
        $result = $this->filterRegistry->getFilter($name);
        $this->assertInstanceOf($expected, $result);
    }

    /**
     * Data provider for testNotExistsFilter.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\SubscriptionFilterRegistryTest::testNotExistsFilter
     * @return array
     */
    public static function notExistsFilterProvider()
    {
        return array(
            'Date' => array(
                'name' => 'Date',
            ),
            'DateTime' => array(
                'name' => 'DateTime',
            ),
        );
    }

    /**
     * Should return null if filter registry not exists for given class.
     *
     * @dataProvider notExistsFilterProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry::getFilter
     * @param string $name
     */
    public function testNotExistsFilter($name)
    {
        $result = $this->filterRegistry->getFilter($name);
        $this->assertNull($result);
    }

    /**
     * Should return null if filter does not implement MessageBuilderInterface.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry::getFilter
     */
    public function testGetFilterNotImplementInterface()
    {
        $this->assertNull($this->filterRegistry->getFilter('CustomFilterNotImplementInterfaceCRYS1295'));
    }

    /**
     * Data provider for testGetFilters.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\SubscriptionFilterRegistryTest::testGetFilters
     * @return array
     */
    public static function getFiltersProvider()
    {
        return array(
            'returnsAllFilters' => array(
                'expectedFilters' => array(
                    'Application',
                    'AssignedToMe',
                    'Team',
                    'Reminder',
                    'CustomFilter',
                ),
            ),
        );
    }


    /**
     * Should return all exists filters including custom.
     *
     * @dataProvider getFiltersProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterRegistry::getFilters
     * @param array $expectedFilters
     */
    public function testGetFilters($expectedFilters)
    {
        $this->assertEquals($expectedFilters, $this->filterRegistry->getFilters());
    }

    /**
     * Generate custom subscription filter with given name.
     *
     * @param string $className
     * @param bool $isImplementInterface
     * @param string $parentClass
     */
    protected static function saveSubscriptionFilter($className, $isImplementInterface = true, $parentClass = '')
    {
        $implement = '';
        $parent = '';
        if ($isImplementInterface) {
            $implement = sprintf(
                "%s",
                'implements \Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\SubscriptionFilterInterface'
            );
        }
        if ($parentClass) {
            $parent = "extends {$parentClass}";
        }

        $classCode = "<?php
namespace Sugarcrm\\Sugarcrm\\cache;

use Sugarcrm\\Sugarcrm\\Notification\\EventInterface;

/**
 * Class {$className}
 */
class {$className} {$parent} {$implement}
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery(EventInterface \$event, \\SugarQuery \$query)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(EventInterface \$event)
    {
    }
}
";
        sugar_file_put_contents("cache/$className.php", $classCode);
    }

    /**
     * Generate subscription filter with given name in customs modules.
     *
     * @param string $className
     * @param string $parentClass
     */
    protected static function saveCustomFilter($className, $parentClass = '')
    {
        $parent = '';

        if ($parentClass) {
            $parent = "extends {$parentClass}";
        }

        $classCode = "<?php
namespace Sugarcrm\\Sugarcrm\\custom\\Notification\\SubscriptionFilter;

use Sugarcrm\\Sugarcrm\\Notification\\EventInterface;

/**
 * Class {$className}
 */
class {$className} {$parent}
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function filterQuery(EventInterface \$event, \\SugarQuery \$query)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(EventInterface \$event)
    {
    }
}
";
        sugar_file_put_contents("custom/modules/Notification/SubscriptionFilter/$className.php", $classCode);
    }
}
