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
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedClassUsed;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedClassInstantiated;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedFunctionCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedMethodCalled;
use Sugarcrm\Sugarcrm\Security\ModuleScanner\Exception\DynamicallyNamedStaticMethodCalled;

class DynamicNameVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            if ($node->name instanceof Node\Expr\Variable) {
                throw new DynamicallyNamedMethodCalled();
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            if ($node->name instanceof Node\Expr\Variable) {
                throw new DynamicallyNamedFunctionCalled();
            }
        } elseif ($node instanceof Node\Expr\StaticCall) {
            $class = $node->class;
            $method = $node->name;
            if ($class instanceof Node\Expr\Variable) {
                throw new DynamicallyNamedClassUsed();
            }
            if ($method instanceof Node\Expr\Variable) {
                throw new DynamicallyNamedStaticMethodCalled();
            }
        } elseif ($node instanceof Node\Expr\New_) {
            if ($node->class instanceof Node\Expr\Variable) {
                throw new DynamicallyNamedClassInstantiated();
            }
        }
    }
}
