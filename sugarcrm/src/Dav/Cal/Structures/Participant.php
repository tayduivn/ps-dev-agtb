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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Structures;

use Sabre\VObject\Property\ICalendar\CalAddress;
use Sabre\VObject\Component\VCalendar;

/**
 * Class Participant
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Structures
 */
class Participant
{
    /**
     * @var CalAddress
     */
    protected $participant;

    /**
     * Participant bean name
     * @var string
     */
    protected $beanName;

    /**
     * Participant bean id
     * @var string
     */
    protected $beanId;

    /**
     * @param CalAddress|null $participant
     * @param string $beanName
     * @param string $beanId
     */
    public function __construct(CalAddress $participant = null, $beanName = null, $beanId = null)
    {
        if ($participant) {
            $this->participant = $participant;
        } else {
            $this->participant = new CalAddress(new VCalendar(), 'ATTENDEE');
        }

        $this->beanName = $beanName;
        $this->beanId = $beanId;
    }

    /**
     * Cloning participant structure
     */
    public function __clone()
    {
        $this->participant = clone $this->participant;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        $params = $this->participant->parameters();

        return isset($params[$name]) ? $params[$name]->getValue() : null;
    }

    /**
     * Set participant parameter
     * @param string $name
     * @param string $value
     * @return bool
     */
    protected function setParameter($name, $value)
    {
        if (is_null($value)) {
            if ($this->getParameter($name)) {
                unset($this->participant[$name]);
                return true;
            }

            return false;
        }
        if ($this->getParameter($name) == $value) {
            return false;
        }

        $this->participant[$name] = $value;

        return true;
    }

    /**
     * Get status action for participant:
     *      "NEEDS-ACTION"        ; Event needs action
     *      "ACCEPTED"            ; Event accepted
     *      "DECLINED"            ; Event declined
     *      "TENTATIVE"           ; Event tentatively
     * @return mixed
     */
    public function getStatus()
    {
        return $this->getParameter('PARTSTAT');
    }

    /**
     * @return bool
     */
    public function isOrganizer()
    {
        return $this->getType() == 'ORGANIZER';
    }

    /**
     * Get display name of participant
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getParameter('CN');
    }

    /**
     * Get role of participant
     * List of roles:
     *      REQ-PARTICIPANT, OPT-PARTICIPANT, CHAIR
     * @return string
     */
    public function getRole()
    {
        return $this->getParameter('ROLE');
    }

    /**
     * Get email of participant
     * @return string
     */
    public function getEmail()
    {
        return str_replace('mailto:', '', strtolower($this->participant->getNormalizedValue()));
    }

    /**
     * Get Sugar CRM module name
     * @return string
     */
    public function getBeanName()
    {
        return $this->beanName;
    }

    /**
     * Get Sugar user id from participant
     * @return string
     */
    public function getBeanId()
    {
        return $this->beanId;
    }

    /**
     * Get node type (ATTENDEE or ORGANIZER)
     * @return string
     */
    public function getType()
    {
        return $this->participant->name;
    }

    /**
     * Get RSVP (FALSE or TRUE)
     * @return string
     */
    public function getRSVP()
    {
        return $this->getParameter('RSVP');
    }

    /**
     * Set status of participant
     * @see Participant::getStatus for availiable statuses
     * @param string $value
     * @return bool
     */
    public function setStatus($value)
    {
        return $this->setParameter('PARTSTAT', $value);
    }

    /**
     * Set display name of participant
     * @param string $value
     * @return bool
     */
    public function setDisplayName($value)
    {
        return $this->setParameter('CN', $value);
    }

    /**
     * Set role of participant
     * @see Participant::getRole for availiable roles
     * @param string $value
     * @return bool
     */
    public function setRole($value)
    {
        return $this->setParameter('ROLE', $value);
    }

    /**
     * Set uri of participant
     * @param string $value
     * @return bool
     */
    public function setEmail($value)
    {
        if ($this->getEmail() == $value) {
            return false;
        }
        $this->participant->setValue('mailto:' . $value);

        return true;
    }

    /**
     * Set Sugar module to participant.
     *
     * @param string $value
     * @return bool
     */
    public function setBeanName($value)
    {
        if ($this->beanName == $value) {
            return false;
        }
        $this->beanName = $value;
        return true;
    }

    /**
     * Set Sugar id to participant.
     *
     * @param string $value
     * @return bool
     */
    public function setBeanId($value)
    {
        if ($this->beanId == $value) {
            return false;
        }
        $this->beanId = $value;
        return true;
    }

    /**
     * Set node type (ATTENDEE or ORGANIZER)
     * @param string $value
     * @return bool
     */
    public function setType($value)
    {
        if ($this->getType() == $value) {
            return false;
        }
        $this->participant->name = $value;

        return true;
    }

    /**
     * Set RSVP
     * @param string $value Possible value 'FALSE' or 'TRUE'
     * @return bool
     */
    public function setRSVP($value)
    {
        return $this->setParameter('RSVP', $value);
    }

    /**
     * Get current CalAddress object
     * @return CalAddress
     */
    public function getObject()
    {
        return $this->participant;
    }
}
