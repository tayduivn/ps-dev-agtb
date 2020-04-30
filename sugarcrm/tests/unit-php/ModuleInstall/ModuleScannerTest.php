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

namespace Sugarcrm\SugarcrmTestsUnit\ModuleInstall;

use LoggerManager;
use PHPUnit\Framework\TestCase;
use ModuleScanner;
use SugarTestReflection;

require_once 'ModuleInstall/ModuleScanner.php';
require_once 'include/dir_inc.php';
require_once 'include/utils/sugar_file_utils.php';

/**
 * @coversDefaultClass ModuleScanner
 */
class ModuleScannerTest extends TestCase
{
    public $fileLoc = "cache/moduleScannerTemp.php";

    protected function setUp(): void
    {
        $GLOBALS['log'] = $this->createMock(LoggerManager::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['log']);
    }

    public function phpSamples()
    {
        return array(
            array("<?php echo blah;", true),
            array("<? echo blah;", true),
            array("blah <? echo blah;", true),
            array("blah <?xml echo blah;", true),
            array("<?xml version=\"1.0\"></xml>", false),
            array("<?xml \n echo blah;", true),
            array("<?xml version=\"1.0\"><? blah ?></xml>", true),
            array("<?xml version=\"1.0\"><?php blah ?></xml>", true),
        );
    }

    /**
     * @param $module
     * @param $functionDef
     * @param $expected
     * @covers ::scanVardefFile
     * @dataProvider providerTestScanVArdefFile
     */
    public function testScanVardefFile($module, $functionDef, $expected)
    {
        $fileModContents = <<<EOQ
<?php
\$dictionary['{$module}'] = array(
    'fields' => array(
        'function_field' => array(
        'name' => 'function_field',
        {$functionDef},
        ),
    ),
);
EOQ;
        $vardefFile = "cache/vardefs.php";
        file_put_contents($vardefFile, $fileModContents);
        $ms = new ModuleScanner();
        $errors = SugarTestReflection::callProtectedMethod($ms, 'scanVardefFile', [$vardefFile]);
        unlink($vardefFile);
        $this->assertSame($expected, empty($errors));
    }

    public function providerTestScanVArdefFile()
    {
        return [
            [
                'testModule_custom_function_name',
                "'function' => ['name' => 'sugarInternalFunction']",
                true,
            ],
            [
                'testModule_custom_function',
                "'function' => 'sugarInternalFunction'",
                true,
            ],
            [
                'testModule_blacklist_function_name',
                "'function' => ['name' => 'call_user_func_array']",
                false,
            ],
            [
                'testModule_blacklist_function',
                "'function' => 'call_user_func_array'",
                false,
            ],
        ];
    }

    /**
     * test isVardefFile
     * @param $fileName
     * @param $expected
     * @covers ::isVardefFile
     * @dataProvider providerTestIsVardefFile
     */
    public function testIsVardefFile($fileName, $expected)
    {
        $vardefsInManifest = [
            'vardefs' => [
                [
                    'from' => '<basepath>/SugarModules/relationships/vardefs/this_is_a_vardefs.php',
                    'to_module' => 'Accounts',
                ],
            ],
        ];
        $ms = new ModuleScanner();
        SugarTestReflection::setProtectedValue($ms, 'installdefs', $vardefsInManifest);
        $result = SugarTestReflection::callProtectedMethod($ms, 'isVardefFile', [$fileName]);
        $this->assertSame($expected, $result);
    }

    public function providerTestIsVardefFile()
    {
        return [
            ['anydir/vardefs.php', true],
            ['anydir/vardefs.ext.php', true],
            ['anydir/Vardefs/any_file_is_vardefs.php', true],
            ['anydir/anyfile.php', false],
            ['/SugarModules/relationships/vardefs/this_is_a_vardefs.php', true],
        ];
    }


    /**
     * @covers ::isValidExtension
     * When ModuleScanner is enabled, validating allowed and disallowed file extension names.
     */
    public function testValidExtsAllowed()
    {
        // Allowed file names
        $allowed = array(
            'php' => 'test.php',
            'htm' => 'test.htm',
            'xml' => 'test.xml',
            'hbs' => 'test.hbs',
            'less' => 'test.less',
            'config' => 'custom/config.php',
        );

        // Disallowed file names
        $notAllowed = array(
            'docx' => 'test.docx',
            'docx(2)' => '../sugarcrm.xml/../sugarcrm/test.docx',
            'java' => 'test.java',
            'phtm' => 'test.phtm',
            'md5' => 'files.md5',
            'md5(2)' => '../sugarcrm/files.md5',

        );

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($allowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertTrue($valid, "The $ext extension should be valid on $file but the ModuleScanner is saying it is not");
        }

        // Test not valid
        foreach ($notAllowed as $ext => $file) {
            $valid = $ms->isValidExtension($file);
            $this->assertFalse($valid, "The $ext extension should not be valid on $file but the ModuleScanner is saying it is");
        }
    }

    /**
     * @covers ::isValidExtension
     */
    public function testValidLicenseFileMissingExtension()
    {
        $ms = new ModuleScanner();
        $valid = $ms->isValidExtension('LICENSE');

        $this->assertTrue($valid);
    }

    /**
     * @covers ::normalizePath
     * @dataProvider normalizePathProvider
     * @param string $path
     * @param string $expected
     */
    public function testNormalize($path, $expected)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($expected, $ms->normalizePath($path));
    }

    public function normalizePathProvider()
    {
        return array(
            array('./foo', 'foo'),
            array('foo//bar///baz/', 'foo/bar/baz'),
            array('./foo/.//./bar/foo', 'foo/bar/foo'),
            array('foo/../bar', false),
            array('../bar/./', false),
            array('./', ''),
            array('.', ''),
            array('', ''),
            array('/', ''),
        );
    }
}

class MockModuleScanner extends ModuleScanner
{
    public $config;
}
