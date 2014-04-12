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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once "modules/Filters/Filters.php";

class FiltersTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Tests may create some files to test customizations. During `setUp` we
     * need to back up these files and delete them temporarily.
     *
     * @var array The list of generated files.
     */
    public static $generatedOperatorsFile = array(
        'custom/clients/base/filters/operators/operators.php',
        'clients/latrop/filters/operators/operators.php',
        'custom/clients/latrop/filters/operators/operators.php'
    );

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('files');

        foreach (self::$generatedOperatorsFile as $file) {
            // Back up and remove existing files.
            SugarTestHelper::saveFile($file);
            if (file_exists($file)) {
                SugarAutoLoader::unlink($file);
            }
        }
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function tearDown() {
        foreach (self::$generatedOperatorsFile as $file) {
            if (file_exists($file)) {
                SugarAutoLoader::unlink($file);
            }
        }
        parent::tearDown();
    }

    public function testGetOperators()
    {
        $filters = BeanFactory::getBeanName('Filters');
        $operators = $filters::getOperators();

        $this->assertArrayHasKey('enum', $operators, 'It should contain "enum" field type');
        $this->assertArrayHasKey('name', $operators, 'It should contain "name" field type');
        $this->assertArrayHasKey('varchar', $operators, 'It should contain "varchar" field type');
    }

    public function testGetOperatorsWithCustom()
    {
        $generatedFile = self::$generatedOperatorsFile[0];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['base']['filter']['operators'] = array(
    'enum' => array(
        '\$contains' => 'LBL_OPERATOR_CONTAINS',
        '\$not_contains' => 'LBL_OPERATOR_NOT_CONTAINS',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanName('Filters');
        $operators = $filters::getOperators();

        $this->assertArrayHasKey('enum', $operators, 'It should contain "enum" field type');
        $this->assertArrayNotHasKey('name', $operators, 'It should not contain "name" field type');
        $this->assertArrayNotHasKey('varchar', $operators, 'It should not contain "varchar" field type');
    }

    public function testGetOperatorsForClient()
    {
        $generatedFile = self::$generatedOperatorsFile[1];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['latrop']['filter']['operators'] = array(
    'name' => array(
        '\$equals' => 'LBL_OPERATOR_MATCHES',
        '\$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanName('Filters');
        $operators = $filters::getOperators('latrop');

        $this->assertArrayNotHasKey('enum', $operators, 'It should not contain "enum" field type');
        $this->assertArrayHasKey('name', $operators, 'It should contain "name" field type');
        $this->assertArrayNotHasKey('varchar', $operators, 'It should not contain "varchar" field type');
    }

    public function testGetOperatorsForClientWithCustom()
    {
        $generatedFile = self::$generatedOperatorsFile[2];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['latrop']['filter']['operators'] = array(
    'varchar' => array(
        '\$equals' => 'LBL_OPERATOR_MATCHES',
        '\$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanName('Filters');
        $operators = $filters::getOperators('latrop');

        $this->assertArrayNotHasKey('enum', $operators, 'It should not contain "enum" field type');
        $this->assertArrayNotHasKey('name', $operators, 'It should not contain "name" field type');
        $this->assertArrayHasKey('varchar', $operators, 'It should contain "varchar" field type');
    }

    /**
     * Helper method to write the file and add it to the file map.
     *
     * @param string $file Location of the file.
     * @param string $contents File Contents.
     */
    private function generateOperatorsFile($file, $contents)
    {
        $path = explode('/', $file);
        array_pop($path); // Remove file name
        $path = implode('/', $path);
        SugarTestHelper::ensureDir($path);
        SugarAutoLoader::put($file, $contents, false);
    }
}
