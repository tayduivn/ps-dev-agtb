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

use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;

/**
 * Class CarrierRegistryTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry
 */
class CarrierRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{

    /** @var \Sugarcrm\Sugarcrm\Notification\CarrierRegistry */
    protected $carrierRegistry = null;

    /** @var array */
    protected $existsCarriers = array(
        'CarrierCRYS1297' => array(
            'path' => 'modules/CarrierCRYS1297/Carrier.php',
            'class' => 'CarrierCRYS1297Carrier',
        ),
        'CarrierSecondCRYS1297' => array(
            'path' => 'modules/CarrierSecondCRYS1297/Carrier.php',
            'class' => 'CarrierSecondCRYS1297Carrier',
        ),
        'CarrierThirdCRYS1297' => array(
            'path' => 'modules/CarrierThirdCRYS1297/Carrier.php',
            'class' => 'CarrierThirdCRYS1297Carrier',
        ),
        'CarrierCustomCRYS1297' => array(
            'path' => 'modules/CarrierCustomCRYS1297/Carrier.php',
            'class' => 'CarrierCustomCRYS1297Carrier',
        ),
    );

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        if (!is_writable('modules/')) {
            static::markTestSkipped("Folder modules should be writable");
        }

        \SugarTestHelper::setUp('files');
        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('modInvisList');
        \SugarTestHelper::saveFile(sugar_cached(CarrierRegistry::CACHE_FILE));

        $GLOBALS['moduleList'] = array(
            'CarrierCRYS1297',
            'CarrierSecondCRYS1297',
            'CarrierCustomCRYS1297',
            'CarrierNotImplementedInterfaceCRYS1297',
            'ModuleWithoutCarrierCRYS1297',
        );

        $GLOBALS['modInvisList'] = array(
            'CarrierThirdCRYS1297',
        );

        \SugarTestHelper::ensureDir(array(
            'modules/CarrierCRYS1297',
            'modules/CarrierSecondCRYS1297',
            'modules/CarrierCustomCRYS1297',
            'modules/CarrierNotImplementedInterfaceCRYS1297',
            'modules/ModuleWithoutCarrierCRYS1297',
            'custom/modules/CarrierCustomCRYS1297',
            'modules/CarrierThirdCRYS1297',
        ));

        \SugarTestHelper::saveFile(array(
            'modules/CarrierCRYS1297/Carrier.php',
            'modules/CarrierSecondCRYS1297/Carrier.php',
            'modules/CarrierThirdCRYS1297/Carrier.php',
            'modules/CarrierCustomCRYS1297/Carrier.php',
            'modules/CarrierNotImplementedInterfaceCRYS1297/Carrier.php',
            'custom/modules/CarrierCustomCRYS1297/Carrier.php',
        ));

        static::saveCarrierClass('CarrierCRYS1297');
        static::saveCarrierClass('CarrierSecondCRYS1297');
        static::saveCarrierClass('CarrierThirdCRYS1297');
        static::saveCarrierClass('CarrierCustomCRYS1297');
        static::saveCarrierClass('CarrierNotImplementedInterfaceCRYS1297', false);
        static::saveCustomCarrierClass('CarrierCustomCRYS1297');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        /* remove cache if it exists */
        $cacheFile = sugar_cached(CarrierRegistry::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($cacheFile)) {
            \SugarAutoLoader::unlink($cacheFile);
        }

        $this->carrierRegistry = CarrierRegistry::getInstance();
    }

    /**
     * Method should return CarrierRegistry object.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\CarrierRegistry', CarrierRegistry::getInstance());
    }

    /**
     * Data provider for testGetCarriers.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testGetCarriers
     * @return array
     */
    public static function getCarriersProvider()
    {
        return array(
            'returnAllCarriersWithInvisible' => array(
                'expectedCarriers' => array(
                    'CarrierCRYS1297',
                    'CarrierCustomCRYS1297',
                    'CarrierSecondCRYS1297',
                    'CarrierThirdCRYS1297',//carrier for invisible module
                ),
            ),
        );
    }

    /**
     * Should return keys of array with carriers list.
     *
     * @dataProvider getCarriersProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCarriers
     * @param array $expectedCarriers
     */
    public function testGetCarriers($expectedCarriers)
    {
        $carriers = $this->carrierRegistry->getCarriers();
        sort($carriers);
        $this->assertEquals($expectedCarriers, $carriers);
    }

    /**
     * Data provider for testGetDictionaryCacheExists.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testGetDictionaryCacheExists
     * @return array
     */
    public static function getDictionaryCacheExistsProvider()
    {
        return array(
            'setCacheReturnsCachedData' => array(
                'loadedData' => array(
                    'CarrierCRYS1297' => array(
                        'path' => 'Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest' . rand(1000, 1999),
                        'class' => 'Sugarcrm\SugarcrmTests\Notification\CarrierCRYS1297' . rand(1000, 1999),
                    ),
                    'CarrierSecondCRYS1297' => array(
                        'path' => 'Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest' . rand(1000, 1999),
                        'class' => 'Sugarcrm\SugarcrmTests\Notification\CarrierSecondCRYS1297' . rand(1000, 1999),
                    ),
                    'CarrierThirdCRYS1297' => array(
                        'path' => 'Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest' . rand(1000, 1999),
                        'class' => 'Sugarcrm\SugarcrmTests\Notification\CarrierThirdCRYS1297' . rand(1000, 1999),
                    ),
                ),
            ),
        );
    }

    /**
     * Should return list of carriers loaded from cache file.
     * We setup cache data manually and getDictionary result should be equal to it not for real.
     *
     * @dataProvider getDictionaryCacheExistsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getDictionary
     * @param array $loadedData
     */
    public function testGetDictionaryCacheExists($loadedData)
    {
        \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'setCache', array($loadedData));
        $result = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'getDictionary');
        $this->assertEquals($loadedData, $result);
    }

    /**
     * Data provider for testGetDictionaryCacheNotExists.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testGetDictionaryCacheNotExists
     * @return array
     */
    public static function getDictionaryCacheNotExistsProvider()
    {
        return array(
            'returnsAllCarriers' => array(
                'expectedCarriers' => array(
                    'CarrierCRYS1297' => array(
                        'path' => 'modules/CarrierCRYS1297/Carrier.php',
                        'class' => 'CarrierCRYS1297Carrier',
                    ),
                    'CarrierSecondCRYS1297' => array(
                        'path' => 'modules/CarrierSecondCRYS1297/Carrier.php',
                        'class' => 'CarrierSecondCRYS1297Carrier',
                    ),
                    'CarrierThirdCRYS1297' => array(
                        'path' => 'modules/CarrierThirdCRYS1297/Carrier.php',
                        'class' => 'CarrierThirdCRYS1297Carrier',
                    ),
                    'CarrierCustomCRYS1297' => array(
                        'path' => 'custom/modules/CarrierCustomCRYS1297/Carrier.php',
                        'class' => 'CarrierCustomCRYS1297Carrier',
                    ),
                ),
            ),
        );
    }

    /**
     * Should get carriers from scan method and load it to cache if cache is empty.
     *
     * @dataProvider getDictionaryCacheNotExistsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getDictionary
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCache
     * @param array $expectedCarriers
     */
    public function testGetDictionaryCacheNotExists($expectedCarriers)
    {
        $result = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'getDictionary');
        $this->assertEquals($expectedCarriers, $result);
        $cachedResult = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'getCache');
        $this->assertEquals($expectedCarriers, $cachedResult);
    }

    /**
     * Should return null because cache file not exists - it removed forcibly.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCache
     */
    public function testGetCacheNotExistsFile()
    {
        $result = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'getCache');
        $this->assertNull($result);
    }

    /**
     * Should return null - requested variable is not exists.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCache
     */
    public function testGetCacheNotExistsVar()
    {
        /* create cache with data, but with wrong variable name */
        create_cache_directory(CarrierRegistry::CACHE_FILE);
        write_array_to_file(
            'requestedVariable' . rand(1000, 1999),
            array('CarrierData' . rand(2000, 2999)),
            sugar_cached(CarrierRegistry::CACHE_FILE)
        );

        $result = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'getCache');

        $this->assertNull($result);
    }

    /**
     * Should write lists of carriers to cache file.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::setCache
     */
    public function testSetCache()
    {
        $cacheFile = sugar_cached(CarrierRegistry::CACHE_FILE);

        \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'setCache', array($this->existsCarriers));
        $this->assertTrue(\SugarAutoLoader::fileExists($cacheFile));

        include $cacheFile;
        $this->assertEquals($this->existsCarriers, ${CarrierRegistry::CACHE_VARIABLE});
    }

    /**
     * Data provider for testScanCarrierExists.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testScanCarrierExists
     * @return array
     */
    public static function scanCarrierExistsProvider()
    {
        return array(
            'scanModuleDir' => array(
                'expectedDir' => array(
                    'CarrierCRYS1297' => array(
                        'path' => 'modules/CarrierCRYS1297/Carrier.php',
                        'class' => 'CarrierCRYS1297Carrier',
                    ),
                    'CarrierSecondCRYS1297' => array(
                        'path' => 'modules/CarrierSecondCRYS1297/Carrier.php',
                        'class' => 'CarrierSecondCRYS1297Carrier',
                    ),
                    'CarrierThirdCRYS1297' => array(
                        'path' => 'modules/CarrierThirdCRYS1297/Carrier.php',
                        'class' => 'CarrierThirdCRYS1297Carrier',
                    ),
                    'CarrierCustomCRYS1297' => array(
                        'path' => 'custom/modules/CarrierCustomCRYS1297/Carrier.php',
                        'class' => 'CarrierCustomCRYS1297Carrier',
                    ),
                ),
            ),
        );
    }

    /**
     * Should returns array with module's carrier path and class name.
     * If exists custom carrier should return custom instead default.
     *
     * @dataProvider scanCarrierExistsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::scan
     * @param array $expectedDir
     */
    public function testScanCarrierExists($expectedDir)
    {
        $result = \SugarTestReflection::callProtectedMethod($this->carrierRegistry, 'scan');
        $this->assertEquals($expectedDir, $result);
    }

    /**
     * Data provider for testGetCarrierNotExists.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testGetCarrierNotExists
     * @return array
     */
    public static function getCarrierNotExistsProvider()
    {
        return array(
            'notExistsCarrier' => array(
                'requestedCarrier' => 'NotExistsCarrier' . rand(1000, 1999),
            ),
        );
    }

    /**
     * Should return null if carrier with given name not exists.
     *
     * @dataProvider getCarrierNotExistsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCarrier
     * @param string $requestedCarrier
     */
    public function testGetCarrierNotExists($requestedCarrier)
    {
        $this->assertNull($this->carrierRegistry->getCarrier($requestedCarrier));
    }

    /**
     * Data provider for testGetCarrierExists.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\CarrierRegistryTest::testGetCarrierExists
     * @return array
     */
    public static function getCarrierExistsProvider()
    {
        return array(
            'loadCarrierCRYS1297' => array(
                'requestedCarrier' => 'CarrierCRYS1297',
                'expectedInstance' => 'CarrierCRYS1297Carrier',
            ),
            'loadCarrierSecondCRYS1297' => array(
                'requestedCarrier' => 'CarrierSecondCRYS1297',
                'expectedInstance' => 'CarrierSecondCRYS1297Carrier',
            ),
        );
    }

    /**
     * Should load carrier if it exists and return object.
     *
     * @dataProvider getCarrierExistsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\CarrierRegistry::getCarrier
     * @param string $requestedCarrier
     * @param string $expectedInstance
     */
    public function testGetCarrierExists($requestedCarrier, $expectedInstance)
    {
        $this->assertInstanceOf($expectedInstance, $this->carrierRegistry->getCarrier($requestedCarrier));
    }

    /**
     * Generate carrier for module by name.
     *
     * @param string $moduleName
     * @param bool|true $implementCarrierInterface
     */
    protected static function saveCarrierClass($moduleName, $implementCarrierInterface = true)
    {
        $implement = '';
        if ($implementCarrierInterface) {
            $implement = 'implements \\Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface';
        }
        $classCode = "<?php
/**
 * Class {$moduleName}Carrier
 */
class {$moduleName}Carrier {$implement}
{
    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageSignature()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressType()
    {
    }
}
";
        sugar_file_put_contents("modules/{$moduleName}/Carrier.php", $classCode);
    }

    /**
     * Generate custom carrier for module by name.
     *
     * @param string $moduleName
     */
    protected static function saveCustomCarrierClass($moduleName)
    {
        $classCode = "<?php
namespace Sugarcrm\\Sugarcrm\\custom\\{$moduleName};

/**
 * Class {$moduleName}Carrier
 */
class {$moduleName}Carrier implements \\Sugarcrm\\Sugarcrm\\Notification\\Carrier\\CarrierInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageSignature()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressType()
    {
    }
}
";
        sugar_file_put_contents("custom/modules/{$moduleName}/Carrier.php", $classCode);
    }
}
