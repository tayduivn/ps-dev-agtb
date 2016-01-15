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

namespace Sugarcrm\SugarcrmTests\Notification;

use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;

/**
 * Class EmitterRegistryTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry
 */
class EmitterRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var EmitterRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitterRegistry;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('files');
        \SugarTestHelper::ensureDir(array(
            'modules/CallsCRYS1299',
            'modules/MeetingsCRYS1299',
            'modules/UsersCRYS1299',
            'modules/AccountsCRYS1299',
            'custom/modules/CallsCRYS1299',
            'custom/modules/MeetingsCRYS1299',
            'custom/modules/AccountsCRYS1299',
        ));

        \SugarTestHelper::saveFile(array(
            sugar_cached(EmitterRegistry::CACHE_FILE),
            'modules/CallsCRYS1299/Emitter.php',
            'modules/MeetingsCRYS1299/Emitter.php',
            'modules/UsersCRYS1299/Emitter.php',
            'modules/AccountsCRYS1299/Emitter.php',
            'custom/modules/CallsCRYS1299/Emitter.php',
            'custom/modules/MeetingsCRYS1299/Emitter.php',
            'custom/modules/AccountsCRYS1299/Emitter.php',
        ));

        $GLOBALS['moduleList'] = array(
            'CallsCRYS1299',
            'MeetingsCRYS1299',
            'UsersCRYS1299',
            'AccountsCRYS1299',
            'LeadsCRYS1299',
        );
        $GLOBALS['beanList'] = array(
            'CallsCRYS1299' => 'CallCRYS1299',
            'MeetingsCRYS1299' => 'MeetingCRYS1299',
            'UsersCRYS1299' => 'UserCRYS1299',
            'AccountsCRYS1299' => 'AccountCRYS1299',
        );

        static::saveEmitter('CallCRYS1299', 'CallsCRYS1299', true, false);
        static::saveEmitter('MeetingCRYS1299', 'MeetingsCRYS1299', false, false);
        static::saveEmitter('CallCRYS1299', 'CallsCRYS1299', false, true);
        static::saveEmitter('MeetingCRYS1299', 'MeetingsCRYS1299', true, true);

        //module does not implements interface
        static::saveEmitter('AccountsCRYS1299', 'AccountsCRYS1299', false, false);
        static::saveEmitter('AccountsCRYS1299', 'AccountsCRYS1299', false, true);

        static::saveBeanEmitter('UserCRYS1299', 'UsersCRYS1299');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->emitterRegistry = EmitterRegistry::getInstance();
        if (\SugarAutoLoader::fileExists(sugar_cached(EmitterRegistry::CACHE_FILE))) {
            \SugarAutoLoader::unlink(sugar_cached(EmitterRegistry::CACHE_FILE));
        }
    }

    /**
     * Should return object with correct instance.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\EmitterRegistry', EmitterRegistry::getInstance());
    }

    /**
     * Should returns application emitter.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getApplicationEmitter
     */
    public function testGetApplicationEmitter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Emitter',
            $this->emitterRegistry->getApplicationEmitter()
        );
    }

    /**
     * Should returns bean emitter.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getBeanEmitter
     */
    public function testGetBeanEmitter()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter',
            $this->emitterRegistry->getBeanEmitter()
        );
    }

    /**
     * Data provider for testModuleEmitter.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\EmitterRegistryTest::testModuleEmitter
     * @return array
     */
    public static function moduleEmitterProvider()
    {
        return array(
            'setCallsGetCallEmitter' => array(
                'name' => 'CallsCRYS1299',
                'expected' => 'CallCRYS1299Emitter',
                'expectedParent' => null,
            ),
            'setMeetingsGetMeetingCustomEmitter' => array(
                'name' => 'MeetingsCRYS1299',
                'expected' => 'CustomMeetingCRYS1299Emitter',
                'expectedParent' => null,
            ),
            'setUsersGetUserBeanEmitter' => array(
                'name' => 'UsersCRYS1299',
                'expected' => 'UserCRYS1299Emitter',
                'expectedParent' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter',
            ),
        );
    }

    /**
     * Tests checking module name and creating instance of module emitter.
     *
     * @dataProvider moduleEmitterProvider
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getModuleEmitter
     * @param string $name Name of module.
     * @param string $expected Name of instance.
     * @param string|null $expectedParent
     */
    public function testModuleEmitter($name, $expected, $expectedParent)
    {
        if (isset($expectedParent)) {
            $expectedParent = new $expectedParent();
        }
        /** @var \Sugarcrm\Sugarcrm\Notification\EmitterInterface $emitter */
        $emitter = $this->emitterRegistry->getModuleEmitter($name);
        $this->assertInstanceOf($expected, $emitter);
        $this->assertEquals($expectedParent, $emitter->parent);
    }

    /**
     * Data provider for testGetFilterWrongData.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\EmitterRegistryTest::testGetFilterWrongData
     * @return array
     */
    public static function getModuleEmitterWrongDataProvider()
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
     * Should return null if result not exists.
     *
     * @dataProvider getModuleEmitterWrongDataProvider
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getModuleEmitter
     * @param string $name Name of module
     */
    public function testModuleEmitterWrongData($name)
    {
        /** @var \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface|null $result */
        $result = $this->emitterRegistry->getModuleEmitter($name);
        $this->assertNull($result);
    }

    /**
     * Data provider for testGetModuleEmitters.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\EmitterRegistryTest::testGetModuleEmitters
     * @return array
     */
    public static function getModuleEmittersProvider()
    {
        return array(
            'getAllEmitters' => array(
                'expectedEmitters' => array(
                    'CallsCRYS1299',
                    'MeetingsCRYS1299',
                    'UsersCRYS1299',
                ),
            ),
        );
    }

    /**
     * Should check if module emitter name is exists in all module's emitters.
     *
     * @dataProvider getModuleEmittersProvider
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getModuleEmitters
     * @param array $expectedEmitters Name of module
     */
    public function testGetModuleEmitters($expectedEmitters)
    {
        $this->assertEquals($expectedEmitters, $this->emitterRegistry->getModuleEmitters());
    }

    /**
     * Data provider for getModuleEmitterSaveCache.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\EmitterRegistryTest::getModuleEmitterSaveCache
     * @return array
     */
    public static function getModuleEmitterSaveCacheProvider()
    {
        return array(
            'getEmittersSaveToCache' => array(
                'expectedCache' => array(
                    'CallsCRYS1299' => array(
                        'path' => 'modules/CallsCRYS1299/Emitter.php',
                        'class' => 'CallCRYS1299Emitter',
                    ),
                    'MeetingsCRYS1299' => array(
                        'path' => 'custom/modules/MeetingsCRYS1299/Emitter.php',
                        'class' => 'CustomMeetingCRYS1299Emitter',
                    ),
                    'UsersCRYS1299' => array(
                        'path' => 'modules/UsersCRYS1299/Emitter.php',
                        'class' => 'UserCRYS1299Emitter',
                    ),
                ),
            ),
        );
    }

    /**
     * Should save data to cache. File with emitterRegistry variable should exists.
     * getCache should return data that was cached in file.
     *
     * @dataProvider getModuleEmitterSaveCacheProvider
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getModuleEmitters
     * @covers Sugarcrm\Sugarcrm\Notification\EmitterRegistry::getCache
     * @param array $expectedCache
     */
    public function testGetModuleEmitterSaveCache($expectedCache)
    {
        ${EmitterRegistry::CACHE_VARIABLE} = array();
        $cacheFile = sugar_cached(EmitterRegistry::CACHE_FILE);

        $this->emitterRegistry->getModuleEmitters();

        $this->assertTrue(\SugarAutoLoader::fileExists($cacheFile));

        include $cacheFile;
        $this->assertEquals($expectedCache, ${EmitterRegistry::CACHE_VARIABLE});
    }

    /**
     * Generate emitter for given module.
     *
     * @param string $beanName
     * @param string $emitterFolder
     * @param bool $isImplementInterface
     * @param bool $saveInCustom
     */
    protected static function saveEmitter($beanName, $emitterFolder, $isImplementInterface, $saveInCustom)
    {
        $implement = '';

        $className = "{$beanName}Emitter";
        if ($saveInCustom) {
            $className = 'Custom' . $className;
        }
        if ($isImplementInterface) {
            $implement = sprintf(
                "%s",
                'implements Sugarcrm\Sugarcrm\Notification\EmitterInterface'
            );
        }

        $classCode = "<?php
/**
 * Class Emitter
 */
class {$className} {$implement}
{
    /** @var Emitter */
    public \$parent = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(\$parent = null)
    {
        \$this->parent = \$parent;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return '{$emitterFolder}';
    }
    /**
     * {@inheritdoc}
     */
    public function getEventPrototypeByString(\$string)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
    }
}
";
        $filePath = "modules/{$emitterFolder}/Emitter.php";
        if ($saveInCustom) {
            $filePath = 'custom/' . $filePath;
        }
        sugar_file_put_contents($filePath, $classCode);
    }

    /**
     * Generate bean emitter.
     *
     * @param string $beanName
     * @param string $emitterFolder
     */
    protected static function saveBeanEmitter($beanName, $emitterFolder)
    {

        $classCode = "<?php
use Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter as BeanEmitter;

/**
 * Class Emitter
 */
class {$beanName}Emitter implements Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\BeanEmitterInterface
{
    /** @var Emitter */
    public \$parent = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(BeanEmitter \$parent)
    {
        \$this->parent = \$parent;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return '{$emitterFolder}';
    }

    /**
     * {@inheritdoc}
     */
    public function getEventPrototypeByString(\$string)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function exec(\\SugarBean \$bean, \$event, \$arguments)
    {
    }
}
";
        sugar_file_put_contents("modules/{$emitterFolder}/Emitter.php", $classCode);
    }
}
