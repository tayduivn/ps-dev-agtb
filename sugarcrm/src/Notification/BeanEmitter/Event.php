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

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Class Event.
 * Should be used, when something happens on a bean level.
 * @package Sugarcrm\Sugarcrm\Notification\BeanEmitter
 */
class Event implements EventInterface
{
    /**
     * Event name.
     * @var string
     */
    protected $name;

    /**
     * All data and information about the current event.
     * Is represented by a Bean where the event occurred.
     * @var \SugarBean
     */
    protected $bean;

    /**
     * Create an Event with a specified name.
     * @param string $name name of the Event.
     * @param \SugarBean $bean Bean where event occurred.
     */
    public function __construct($name, \SugarBean $bean)
    {
        $this->name = $name;
        $this->bean = $bean;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get Bean where event occurred.
     * @return \SugarBean Bean where event has occurred.
     */
    public function getBean()
    {
        return $this->bean;
    }

    /**
     * Get name of the module where event occurred.
     * @return string|null name of the module if specified, otherwise null.
     */
    public function getModuleName()
    {
        if (!empty($this->bean->module_name)) {
            return $this->bean->module_name;
        } elseif (!empty($this->bean->module_dir)) {
            return $this->bean->module_dir;
        }

        return null;
    }

}
