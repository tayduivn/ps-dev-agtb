<?php
declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\Security\ModuleScanner;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedClassExtended;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\BlacklistedStaticMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\EvalUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\CompilerHalted;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\ShellExecUsed;

class BlacklistVisitor extends NodeVisitorAbstract
{
    private $classesBlackList;

    private $functionsBlackList;

    private $methodsBlackList;

    public function __construct(array $classesBlackList, array $functionsBlackList, array $methodsBlackList)
    {
        $this->classesBlackList = $classesBlackList;
        $this->functionsBlackList = $functionsBlackList;
        $this->methodsBlackList = $methodsBlackList;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\Eval_) {
            throw new EvalUsed();
        }

        if ($node instanceof Node\Stmt\HaltCompiler) {
            throw new CompilerHalted();
        }
        if ($node instanceof Node\Expr\ShellExec) {
            throw new ShellExecUsed();
        }

        if ($node instanceof Node\Expr\MethodCall) {
            if ($node->name instanceof Node\Identifier) {
                $method = $node->name->toString();
                if (in_array(strtolower($method), $this->methodsBlackList, true)) {
                    throw new BlacklistedMethodCalled($method);
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            if ($node->name instanceof Node\Name) {
                $function = $node->name->toString();
                if (in_array(strtolower($function), $this->functionsBlackList, true)) {
                    throw new BlacklistedFunctionCalled($function);
                }
            }
        } elseif ($node instanceof Node\Stmt\Class_) {
            if ($node->extends instanceof Node\Name) {
                $class = $node->extends->toString();
                if (in_array(strtolower($class), $this->classesBlackList, true)) {
                    throw new BlacklistedClassExtended($class);
                }
            }
        } elseif ($node instanceof Node\Expr\New_) {
            $class = $node->class->toString();
            if (in_array(strtolower($class), $this->classesBlackList, true)) {
                throw new BlacklistedClassInstantiated($class);
            }
        } elseif ($node instanceof Node\Expr\StaticCall) {
            if ($node->class instanceof Node\Name) {
                $method = $node->name->toString();
                $className = $node->class->toString();
                if (in_array(strtolower($method), $this->methodsBlackList, true)) {
                    throw new BlacklistedStaticMethodCalled($method);
                }
                if (isset($this->methodsBlackList[strtolower($method)]) && in_array(strtolower($className), $this->methodsBlackList[strtolower($method)], true)) {
                    throw new BlacklistedStaticMethodCalled($method, $className);
                }
            }
        }
    }
}
