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

namespace Sugarcrm\Sugarcrm\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\ModuleEventInterface;

/**
 * Class Event.
 * Event for reminder upcoming events calls or meeting
 * @package Sugarcrm\Sugarcrm\Notification\Emitter\Reminder
 */
class Event implements ModuleEventInterface, \Serializable
{

    /**
     * The user who should be reminded.
     *
     * @var \User
     */
    protected $user;

    /**
     * Upcoming call.
     *
     * @var \Call
     */
    protected $bean;

    /**
     * Get upcoming event.
     *
     * @return \Call|\Meeting $bean upcoming event
     */
    public function getBean()
    {
        return $this->bean;
    }

    /**
     * Set upcoming event calls or meeting.
     *
     * @param \Call|\Meeting $bean upcoming event calls or meeting
     * @return $this
     */
    public function setBean(\SugarBean $bean)
    {
        if (!($bean instanceof \Call) && !($bean instanceof \Meeting)) {
            throw new \LogicException('Unsupported Bean class.' . get_class($bean));
        }
        $this->bean = $bean;
        return $this;
    }

    /**
     * Get the user who should be reminded.
     *
     * @return \User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user who should be reminded.
     *
     * @param \User $user
     * @return $this
     */
    public function setUser(\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        $data = array('module_name' => $this->bean->module_name, 'id' => $this->bean->id, 'userId' => $this->user->id);
        return serialize($data);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->user = \BeanFactory::getBean('Users', $data['userId']);
        $this->bean = \BeanFactory::getBean($data['module_name'], $data['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'reminder';
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        return $this->bean->module_name;
    }
}
