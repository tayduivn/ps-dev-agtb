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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures;

use Sabre\VObject\Component\VAlarm;
use Sabre\VObject\Property\ICalendar\Duration;
use Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper;

class Reminder
{
    /**
     * @var VAlarm
     */
    protected $reminder;

    protected $dateTimeHelper;

    /**
     * @param VAlarm $reminder
     * @param DateTimeHelper $dateTimeHelper
     */
    public function __construct(VAlarm $reminder, DateTimeHelper $dateTimeHelper = null)
    {
        $this->reminder = $reminder;
        $this->dateTimeHelper = $dateTimeHelper ?: new DateTimeHelper();
    }

    /**
     * Gets string property from reminder
     * @param string $propertyName
     * @return null|string
     */
    protected function getStringProperty($propertyName)
    {
        return $this->reminder->$propertyName ? $this->reminder->$propertyName->getValue() : null;
    }

    /**
     * Set string property of reminder
     * Return true if property was changed or false otherwise
     * @param string $propertyName
     * @param string $value
     * @return bool
     */
    protected function setStringProperty($propertyName, $value)
    {

        if (!$this->reminder->$propertyName) {
            $prop = $this->reminder->parent->parent->createProperty($propertyName, $value);
            $this->reminder->add($prop);

            return true;
        }

        if ($this->reminder->$propertyName->getValue() !== $value) {
            $this->reminder->$propertyName->setValue($value);

            return true;
        }

        return false;
    }

    /**
     * Get reminder type
     * @return null|string
     */
    public function getAction()
    {
        return $this->getStringProperty('ACTION');
    }

    /**
     * Get trigger of reminder
     * @return null | int Duration in seconds
     */
    public function getTrigger()
    {
        if ($this->reminder->TRIGGER instanceof Duration) {
            return $this->dateTimeHelper->durationToSeconds($this->reminder->TRIGGER->getValue());
        }

        return null;
    }

    /**
     * Get description of reminder
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getStringProperty('DESCRIPTION');
    }

    /**
     * Set reminder action
     * @param string $value (DISPLAY, EMAIL)
     * @return bool
     */
    public function setAction($value)
    {
        return $this->setStringProperty('ACTION', $value);
    }

    /**
     * Set reminder description
     * @param string $value
     * @return bool
     */
    public function setDescription($value)
    {
        return $this->setStringProperty('DESCRIPTION', $value);
    }

    /**
     * Set trigger duration
     * @param int $value Duration in seconds
     * @return bool
     */
    public function setTrigger($value)
    {
        $duration = $this->dateTimeHelper->secondsToDuration(0 - $value);

        if (!$this->reminder->TRIGGER) {
            $this->reminder->add($this->reminder->parent->parent->createProperty('TRIGGER', $duration));
            return true;
        }

        if ($this->getDuration() != $duration) {
            $this->reminder->TRIGGER->setValue($duration);
            return true;
        }

        return false;
    }

    /**
     * Get VAlarm object
     * @return VAlarm
     */
    public function getObject()
    {
        return $this->reminder;
    }
}
