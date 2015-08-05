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
 * Class event which emits in Bean related to Bean.
 *
 * class BeanEmitter/Event
 * @package Notification
 */
class Event implements ModuleEventInterface
{
    /**
     * Bean in which event is emitted.
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
     * @param \SugarBean $bean in which event is emitted
     */
    public function __construct($name, \SugarBean $bean)
    {
        $this->name = $name;
        $this->bean = $bean;
    }

    /**
     * Event name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Function return SugarBean with which event is emitted.
     *
     * @return \SugarBean
     */
    public function getBean()
    {
        return $this->bean;
    }

    /**
     * Function return module name in with which event is emitted.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->bean->module_name;
    }
}
