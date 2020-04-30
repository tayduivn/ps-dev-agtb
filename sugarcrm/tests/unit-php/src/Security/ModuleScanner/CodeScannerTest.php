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

namespace Sugarcrm\SugarcrmTestsUnit\src\Security\ModuleScanner;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\CodeScanner;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedClassExtended;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedStaticMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\BlacklistedStaticMethodOfClassCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\CompilerHalted;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\DynamicallyNamedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\DynamicallyNamedClassUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\DynamicallyNamedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\DynamicallyNamedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\DynamicallyNamedStaticMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\EvalUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Issues\ShellExecUsed;

/**
 * @coversDefaultClass CodeScanner
 */
class CodeScannerTest extends TestCase
{
    /**
     * @var CodeScanner
     */
    private $codeScanner;

    public function setUp(): void
    {
        $this->codeScanner = new CodeScanner(['reflectionclass'], ['system'], ['setlevel', 'put' => ['sugarautoloader']]);
    }

    public function allowedCodeProvider(): array
    {
        return [
            [
                <<<'PHP'
<?php
namespace A\b\c;
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
use B as D;
use function f as myfunc;
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
function hello($a, $b = '')
{
   echo __FUNCTION__, PHP_EOL;
}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
$a = true;
$b = 'a';
$c = 42;
$d = [];
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
echo $$b;
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
$c = &$b;
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
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
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
interface SomeInterface
{
    public function doNothing();
}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
abstract class Bar {}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
final class Foo {

}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Foo extends Bar {

}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Bar implements SomeInterface {

}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Foo {
   use World;
}

PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Foo {
   public $a;
   protected $b;
   private $c;
}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Foo {

   public function doNothing(): void
   {
   }
   
   public function s(string $a, int $b): bool
   {
       return $a + $b >  0;
   }
   
   public static function show(string $string): string
   {
       return $string;
   }
}
PHP
                ,
            ],
            [
                <<<'PHP'
<?php
class Foo {

   public function foo()
   {}
   
   protected function bar()
   {}
   
   private function baz()
   {}
}
PHP
                ,
            ],
            [
                <<<'PHP'
SugarAutoloader::requireWithCustom('config.php');
PHP
                ,
            ],
        ];
    }

    /**
     * @dataProvider allowedCodeProvider
     * @covers ::scanCode
     */
    public function testScanCodeSucceeded(string $code): void
    {
        $issues = $this->codeScanner->scan($code);
        $this->assertCount(0, $issues);
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
                <<<'PHP'
<?php
'system'('ls');
PHP
                ,
                BlacklistedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$object->setLevel();
PHP
                ,
                BlacklistedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
$object->{'setLevel'}();
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
AnyClass::setLevel('foo');
PHP
                ,
                BlacklistedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
SugarAutoloader::put('/etc/passwd', 'hacked');
PHP
                ,
                BlacklistedStaticMethodOfClassCalled::class,
            ],
            [
                <<<'PHP'
<?php
AnyClass::{'setLevel'}('foo');
PHP
                ,
                BlacklistedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
'AnyClass'::{'setLevel'}('foo');
PHP
                ,
                BlacklistedStaticMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
'SugarAutoloader'::put('/etc/passwd', 'hacked');
PHP
                ,
                BlacklistedStaticMethodOfClassCalled::class,
            ],
            [
                <<<'PHP'
<?php
__halt_compiler();
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
$object = new $class();
PHP
                ,
                DynamicallyNamedClassInstantiated::class,
            ],
            [
                <<<'PHP'
<?php
$object::world();
PHP
                ,
                DynamicallyNamedClassUsed::class,
            ],
            [
                <<<'PHP'
<?php
$function();
PHP
                ,
                DynamicallyNamedFunctionCalled::class,
            ],
            [
                <<<'PHP'
<?php
$o->$method();
PHP
                ,
                DynamicallyNamedMethodCalled::class,
            ],
            [
                <<<'PHP'
<?php
Foo::$a();
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
    public function testForbiddenStatement(string $code, string $exepectedIssue)
    {
        $issues = $this->codeScanner->scan($code);
        $this->assertInstanceOf($exepectedIssue, $issues[0]);
    }
}
