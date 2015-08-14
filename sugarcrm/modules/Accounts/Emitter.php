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

use Sugarcrm\Sugarcrm\Notification\BeanEmitter\BeanEmitterInterface;
use Sugarcrm\Sugarcrm\Notification\BeanEmitter\Emitter as BeanEmitter;

/**
 * Class AccountEmitter
 * AccountEmitter provides possibility to detect event which has happened in account module.
 */
class AccountEmitter implements BeanEmitterInterface
{
    /**
     * Base Bean Emitter which will be uses via composition.
     *
     * @var BeanEmitter
     */
    protected $emitter;

    /**
     * @param BeanEmitter $beanEmitter base Bean Emitter
     */
    public function __construct(BeanEmitter $beanEmitter)
    {
        $this->emitter = $beanEmitter;
    }

    /**
     * Return name of module in which emitter work.
     *
     * @return string name of module
     */
    public function __toString()
    {
        return 'Accounts';
    }

    /**
     * Accounts module events detector.
     *
     * {@inheritdoc}
     */
    public function exec(\SugarBean $bean, $event, $arguments)
    {
        return $this->emitter->exec($bean, $event, $arguments);
    }

    /**
     * Get all event strings for Accounts module.
     *
     * {@inheritdoc}
     */
    public function getEventPrototypeByString($eventString)
    {
        return $this->emitter->getEventPrototypeByString($eventString);
    }

    /**
     * Get all event strings for Accounts module.
     *
     * {@inheritdoc}
     */
    public function getEventStrings()
    {
        return $this->emitter->getEventStrings();
    }
}
