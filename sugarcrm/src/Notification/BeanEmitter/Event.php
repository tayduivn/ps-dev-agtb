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

namespace Sugarcrm\Sugarcrm\Notification\BeanEmitter;

use Sugarcrm\Sugarcrm\Notification\ModuleEventInterface;

/**
 * Prototype event for Bean Emitter. Should be used for all events on bean.
 *
 * class BeanEmitter/Event
 * @package Notification
 */
class Event implements ModuleEventInterface
{
    /**
     * Target bean is enough here.
     *
     * @var \SugarBean
     */
    protected $bean;

    /**
     * Event name.
     *
     * @var string
     */
    protected $name;

    /**
     * @param string $name event name
     * @param \SugarBean $bean target bean
     */
    public function __construct($name, \SugarBean $bean)
    {
        $this->name = $name;
        $this->bean = $bean;
    }

    /**
     * Returns name of event.
     *
     * @return string event name
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns target bean.
     *
     * @return \SugarBean target bean
     */
    public function getBean()
    {
        return $this->bean;
    }

    /**
     * Returns name of module of target bean.
     *
     * @return string name of module
     */
    public function getModuleName()
    {
        return $this->bean->module_name;
    }
}
