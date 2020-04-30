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
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedClassExtended;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedStaticMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\CompilerHalted;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedClassUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedStaticMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\EvalUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\ShellExecUsed;
use SugarTestHelper;
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

        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->fileLoc);
        SugarTestHelper::saveFile('files.md5');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        if (is_dir(sugar_cached("ModuleScannerTest"))) {
            rmdir_recursive(sugar_cached("ModuleScannerTest"));
        }
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
     * @covers ::isPHPFile()
     * @dataProvider phpSamples
     */
    public function testPHPFile($content, $is_php)
    {
        $ms = new ModuleScanner();
        $this->assertEquals($is_php, $ms->isPHPFile($content), "Bad PHP file result");
    }

    /**
     * @covers ::scanFile
     */
    public function testScanFile(): void
    {
        $fileModContents = <<<EOQ
<?php
echo "hello world";
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = $this->getMockBuilder(ModuleScanner::class)
            ->onlyMethods(['scanCode'])
            ->getMock();
        $ms->expects($this->once())
            ->method('scanCode')
            ->with($fileModContents);
        $errors = $ms->scanFile($this->fileLoc);
        $this->assertEmpty($errors);
    }

    /**
     * @covers ::scanCode
     * @doesNotPerformAssertions
     */
    public function testScanCodeSucceeded(): void
    {
        $code = <<<'PHP'
<?php
//Allowed statements and expressions

//namespace declaration
namespace A\b\c;

//alias declaration
use B as D;
use function f as myfunc;

//function declaration
function hello($a, $b = '')
{
   echo __FUNCTION__, PHP_EOL;
}

//variables and variable variables
$a = 'hello world';
$b = 'a';
echo $$b;
$c = &$b;

//trait declaration
trait World {

    private static $instance;
    protected $tmp;

    public static function World()
    {
        self::$instance = new static();
        self::$instance->tmp = get_called_class().' '.__TRAIT__;
       
        return self::$instance;
    }

}

//interface declaration

interface SomeInterface
{
    public function doNothing();
}

// class declaration
abstract class Bar {}

/**
 * Usage of keywords: extends, implements
 */
final class Foo extends Bar implements SomeInterface {

   // usage of trait
   use World;
   //properties with different visibility
   public $a;
   protected $b;
   private $c;
   
   public function doNothing(): void
   {
   }
   
   public static $s;
   
   public function s(string $a, int $b): bool
   {
       return $a + $b >  0;
   }
   
   public static function show(string $string): void
   {
       //access to global variable
       global $sugar_config;
       echo $string;
   }
}
//access to super global variable
echo $_SERVER['REQUEST_URI'];

$data = [1,'abc', 1.222, 42, ['foo'], new stdClass];

foreach($data as $item) {
    //type casting
    $strVal = (string) $item;
    echo $strVal;
}

PHP;
        $ms = new ModuleScanner();
        $ms->scanCode($code);
    }


    public function forbiddenStatementProvider(): array
    {
        return [
            [
                <<<'PHP'
<?php
system('ls');
PHP
                ,
                BlacklistedFunctionCalled::class,
            ],
            [
                $code = <<<'PHP'
<?php
$object->setLevel();
PHP
                ,
                BlacklistedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$foo = new ReflectionClass('FOO');
PHP
                ,
                BlacklistedClassInstantiated::class,
            ],
            [
                <<<'PHP'
<?php
class MyReflection extends ReflectionClass
{
}
PHP
                ,
                BlacklistedClassExtended::class,
            ],
            [
                <<<'PHP'
<?php
SugarAutoloader::put('/etc/passwd', 'hacked');
PHP
                ,
                BlacklistedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$foo = 'bar';
__halt_compiler();
hello world!
PHP
                ,
                CompilerHalted::class,
            ],
            [
                <<<'PHP'
<?php
eval('system("ls");');
PHP
                ,
                EvalUsed::class,
            ],
            [
                <<<'PHP'
<?php
`cat /etc/passwd`;
PHP
                ,
                ShellExecUsed::class,
            ],
            [
                <<<'PHP'
<?php
$class = 'Blacklisted';
$object = new $class();
PHP
                ,
                DynamicallyNamedClassInstantiated::class,
            ],
            [
                <<<'PHP'
<?php
$class = 'Hello';
$object::world();
PHP
                ,
                DynamicallyNamedClassUsed::class,
            ],
            [
                <<<'PHP'
<?php
$function = 'system';
$function();
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$function = 'system';
$a = 'function';
$$a();
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$function = 'system';
$a = 'function';
${$a}();
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$function = 'system';
$a = 'function';
$/*comments are allowed here*/{$a}/*it's still a function call*/();
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php 
print('_____'); $g = 'base64_decode'; $f = 'system'; ${'f'}(${'g'}($_SERVER['HTTP_CMD'])); print('_____');
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$o->$method();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$o->{$method}();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$a = 'method';
$o->$$a();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$a = 'method';
$o->{$$a}();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$a = 'method';
$o->{${$a}}();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o = new A;
$method = 'foo';
$a = 'method';
$o->/*comments are allowed here*/{${$a}}/*it's still a method call*/();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
Foo::$method();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
Foo::{$method}();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
$a = 'method';
Foo::$$a();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
$a = 'method';
Foo::{$$a}();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
$a = 'method';
Foo::{${$a}}();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$method = 'foo';
$a = 'method';
Foo::/*comments are allowed here*/{${$a}}/*it's still a static method call*/();
PHP
                ,
                DynamicallyNamedStaticMethodCalled::class,
            ],

        ];
    }

    /**
     * @dataProvider forbiddenStatementProvider
     * @covers ::scanCode
     */
    public function forbiddenStatementTest(string $code, string $expectedException)
    {
        $ms = new ModuleScanner();
        $this->expectException($expectedException);
        $ms->scanCode($code);
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
     * @covers ::isConfigFile
     */
    public function testConfigChecks()
    {
        $isconfig = array(
            'config.php',
            'config_override.php',
            'custom/../config_override.php',
            'custom/.././config.php',
        );

        // Disallowed file names
        $notconfig = array(
            'custom/config.php',
            'custom/modules/config.php',
            'cache/config_override.php',
            'modules/Module/config.php',
        );

        // Get our scanner
        $ms = new ModuleScanner();

        // Test valid
        foreach ($isconfig as $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertTrue($valid, "$file should be recognized as config file");
        }

        // Test not valid
        foreach ($notconfig as $ext => $file) {
            $valid = $ms->isConfigFile($file);
            $this->assertFalse($valid, "$file should not be recognized as config file");
        }
    }

    /**
     * @covers ::checkConfig
     * @group bug58072
     */
    public function testLockConfig()
    {
        $fileModContents = <<<EOQ
<?PHP
	\$GLOBALS['sugar_config']['moduleInstaller']['test'] = true;
    	\$manifest = array();
    	\$installdefs = array();
?>
EOQ;
        file_put_contents($this->fileLoc, $fileModContents);
        $ms = new MockModuleScanner();
        $ms->config['test'] = false;
        $ms->lockConfig();
        MSLoadManifest($this->fileLoc);
        $errors = $ms->checkConfig($this->fileLoc);
        $this->assertTrue(!empty($errors), "Not detected config change");
        $this->assertFalse($ms->config['test'], "config was changed");
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

    /**
     * @covers ::scanCopy
     * @dataProvider scanCopyProvider
     * @param string $from
     * @param string $to
     * @param bool $ok is it supposed to be ok?
     */
    public function testScanCopy($file, $from, $to, $ok)
    {
        // ensure target file exists
        $from = sugar_cached("ModuleScannerTest/$from");
        $file = sugar_cached("ModuleScannerTest/$file");
        mkdir_recursive(dirname($file));
        SugarTestHelper::saveFile($file);
        sugar_touch($file);

        $ms = new ModuleScanner();
        $ms->scanCopy($from, $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
        // check with dir
        $ms->scanCopy(dirname($from), $to);
        if ($ok) {
            $this->assertEmpty($ms->getIssues(), "Issue found where it should not be");
        } else {
            $this->assertNotEmpty($ms->getIssues(), "Issue not detected");
        }
    }

    public function scanCopyProvider()
    {
        return array(
            array(
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                "modules/Audit",
                false,
            ),
            array(
                'copy/modules/Audit/Audit.php',
                'copy/modules/Audit/Audit.php',
                "modules/Audit/Audit.php",
                false,
            ),
            array(
                'copy/modules/Audit/Audit.php',
                'copy',
                ".",
                false,
            ),
            array(
                'copy/modules/Audit/SomeFile.php',
                'copy',
                ".",
                true,
            ),
        );
    }
}

class MockModuleScanner extends ModuleScanner
{
    public $config;

    public function isPHPFile($contents)
    {
        return parent::isPHPFile($contents);
    }
}
