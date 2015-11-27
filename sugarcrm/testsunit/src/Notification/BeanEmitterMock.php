<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Notification;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter as BeanEmitter;

class BeanEmitterMock implements BeanEmitterInterface
{
    public $beanEmitter;

    /**
     * @param BeanEmitter $beanEmitter
     */
    public function __construct(BeanEmitter $beanEmitter)
    {
        $this->beanEmitter = $beanEmitter;
    }

    public function exec(\SugarBean $bean, $event, $arguments)
    {
    }

    public function getEventPrototypeByString($string)
    {
    }

    public function getEventStrings()
    {
    }

    public function __toString()
    {
        return 'TestToString';
    }
}
