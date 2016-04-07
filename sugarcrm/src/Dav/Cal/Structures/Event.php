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

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar;
use Sabre\VObject\Property\ICalendar\CalAddress;
use Sugarcrm\Sugarcrm\Dav\Base\Helper as DavHelper;

class Event
{
    const STATE_PARENT = -1;
    const STATE_VIRTUAL = 0;
    const STATE_CUSTOM = 1;
    const STATE_DELETED = 2;
    /**
     * @var \Sabre\VObject\Component\VEvent
     */
    protected $event;

    /**
     * List of participants
     * @var Participant[]
     */
    protected $participants;

    /**List of reminders
     * @var Reminder[]
     */
    protected $reminders;

    /**
     * @var array[]
     */
    protected $participantsLinks;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper
     */
    protected $dateTimeHelper;

    /**
     * Describes state of event
     * 0 - virtual event
     * 1 - custom event
     * 2 - deleted event
     * @var int
     */
    protected $state;

    /**
     * @var \SugarDateTime
     */
    protected $recurrenceId = null;

    /**
     * @param VEvent $event
     * @param int $state
     * @param array $participantsLinks array that contains dav emails that was linked on SugarCRM Persons:
     *                                 array(
     *                                      'email@example.com' => array(
     *                                                          beanName => 'Users'
     *                                                          beanId => 'a1',
     *                                                      ),
     *                                      'email1@example.com' => array(
     *                                                          beanName => 'Contacts'
     *                                                          beanId => 'a2',
     *                                                      ),
     *                                 )
     *
     * @param DavHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(
        VEvent $event = null,
        $state = self::STATE_VIRTUAL,
        array $participantsLinks = array(),
        DavHelper\DateTimeHelper $dateTimeHelper = null
    ) {
        $this->dateTimeHelper = $dateTimeHelper ?: new DavHelper\DateTimeHelper();
        $this->participantsLinks = $participantsLinks;

        $this->reminders = $this->participants = array();
        $this->recurrenceId = null;
        $this->state = $state;

        if ($event) {
            $this->event = $event;
            $this->getParticipants();
            if ($this->event->VALARM) {
                $reminderClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Reminder');
                foreach ($this->event->VALARM as $reminder) {
                    $this->reminders[] = new $reminderClass($reminder, $this->dateTimeHelper);
                }
            }
        }
    }

    /**
     * @param CalAddress $node
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant
     */
    protected function createParticipantObject(CalAddress $node)
    {
        $participantClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Participant');

        $participant = new $participantClass($node);
        $email = $participant->getEmail();
        if (isset($this->participantsLinks[$email])) {
            $linkInfo = $this->participantsLinks[$email];
            $participant->setBeanName($linkInfo['beanName']);
            $participant->setBeanId($linkInfo['beanId']);
        }

        return $participant;
    }

    /**
     * Gets string property from event
     * @param string $propertyName
     * @return null|string
     */
    protected function getStringProperty($propertyName)
    {
        if (!$this->event) {
            return null;
        }
        return $this->event->$propertyName ? $this->event->$propertyName->getValue() : null;
    }

    /**
     * Set string property of event
     * Return true if property was changed or false otherwise
     * @param string $propertyName
     * @param string $value
     * @return bool
     */
    protected function setStringProperty($propertyName, $value)
    {
        if (!$this->event) {
            return false;
        }

        if (!$value) {
            return $this->deleteProperty($propertyName);
        }

        if (!$this->event->$propertyName) {
            $prop = $this->event->parent->createProperty($propertyName, $value);
            $this->event->add($prop);
            $this->setCustomized();
            return true;
        }

        if ($this->event->$propertyName->getValue() !== $value) {
            $this->event->$propertyName->setValue($value);
            $this->setCustomized();
            return true;
        }

        return false;
    }

    /**
     * Get datetime property converted to SugarCRM DB format
     * @param $propertyName
     * @return null|\SugarDateTime
     */
    protected function getDateTimeProperty($propertyName)
    {
        if (!$this->event) {
            return null;
        }
        return $this->event->$propertyName ? $this->dateTimeHelper->davDateToSugar($this->event->$propertyName) : null;
    }

    /**
     * Set DateTime property of event
     * @param string $propertyName
     * @param \SugarDateTime $value
     * @return bool
     */
    protected function setDateTimeProperty($propertyName, $value = null)
    {
        if (!$this->event) {
            return false;
        }
        if (!$value) {
            return $this->deleteProperty($propertyName);
        }
        if (!$this->event->$propertyName) {
            $dateTimeElement = $this->event->parent->createProperty($propertyName);
            $dateTimeElement->setDateTime($value);
            $this->event->add($dateTimeElement);
            $this->setCustomized();
            return true;
        }

        $currentDavValue = $this->getDateTimeProperty($propertyName);

        if ($currentDavValue != $value) {
            $value->setTimezone($currentDavValue->getTimezone());
            $this->event->$propertyName->setDateTime($value);
            $this->setCustomized();
            return true;
        }

        return false;
    }

    /**
     * Delete VObject property
     * @param string $propertyName
     * @return bool
     */
    protected function deleteProperty($propertyName)
    {
        if (!$this->event) {
            return false;
        }

        if ($this->event->$propertyName) {
            $this->event->remove($this->event->$propertyName);
            $this->setCustomized();
            return true;
        }

        return false;
    }

    /**
     * Set end of event based on DTEND or DURATION
     * @param int $duration
     * @param \SugarDateTime $endDate
     *
     * @return bool
     */
    protected function setEndOfEvent($duration, \SugarDateTime $endDate = null)
    {
        if (!$this->event) {
            return false;
        }

        if ($this->event->DTEND) {
            if (!empty($endDate)) {
                return $this->setDateTimeProperty('DTEND', $endDate);
            } else {
                $this->deleteProperty('DTEND');
            }
        }
        $duration = $this->dateTimeHelper->secondsToDuration($duration);

        return $this->setStringProperty('DURATION', $duration);
    }

    /**
     * Create or replace participant node
     * @param Participant $participant
     * @param string $type (ATTENDEE or ORGANIZER)
     * @return bool
     */
    protected function setParticipantNode(Participant $participant, $type = 'ATTENDEE')
    {
        if (!$this->event) {
            return false;
        }

        $foundIndex = $this->findParticipantsByEmail($participant->getEmail());
        if ($foundIndex != - 1) {
            $isChanged = false;
            $found = $this->participants[$foundIndex];
            $isChanged |= $found->setStatus($participant->getStatus());
            $isChanged |= $found->setDisplayName($participant->getDisplayName());
            $isChanged |= $found->setBeanName($participant->getBeanName());
            $isChanged |= $found->setRole($participant->getRole());

            $found->setBeanId($participant->getBeanId());
            $found->setType($type);
            if ($isChanged) {
                $this->setCustomized();
                return true;
            }
            return false;
        }

        $participant->setType($type);
        $this->event->add($participant->getObject());
        $this->participants = array();
        $this->setCustomized();
        return true;
    }

    /**
     * Is event deleted or not
     * @return bool
     */
    public function isDeleted()
    {
        return $this->state == static::STATE_DELETED;
    }

    /**
     * Is event customized or not
     * @return bool
     */
    public function isCustomized()
    {
        return $this->state == static::STATE_CUSTOM;
    }

    /**
     * Is event not customized
     * @return bool
     */
    public function isVirtual()
    {
        return $this->state == static::STATE_VIRTUAL;
    }

    /**
     * Is event parent
     * @return bool
     */
    public function isParent()
    {
        return $this->state == static::STATE_PARENT;
    }

    /**
     * Check is event all day or not.
     *
     * @return bool
     */
    public function isAllDay()
    {
        if (!$this->event) {
            return false;
        }
        
        if ($this->event->DTSTART) {
            return !$this->event->DTSTART->hasTime();
        }
        
        return false;
    }

    /**
     * Get state of event
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get title (SUMMARY) of event
     * @return null|string
     */
    public function getTitle()
    {
        return $this->getStringProperty('SUMMARY');
    }

    /**
     * Get description of event
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getStringProperty('DESCRIPTION');
    }

    /**
     * Get event location
     * @return null|string
     */
    public function getLocation()
    {
        return $this->getStringProperty('LOCATION');
    }

    /**
     * Get event visibility (PUBLIC, PRIVATE, CONFIDENTIAL)
     * @return null|string
     */
    public function getVisibility()
    {
        return $this->getStringProperty('CLASS');
    }

    /**
     * Get status of event
     * @return null|string
     */
    public function getStatus()
    {
        return $this->getStringProperty('STATUS');
    }

    /**
     * Get start datetime of event and convert it to SugarCRM DB format
     * @return null|\SugarDateTime
     */
    public function getStartDate()
    {
        return $this->getDateTimeProperty('DTSTART');
    }

    /**
     * Get end datetime of event and convert it to SugarCRM DB format.
     * DTEND is present only for VEVENT
     * For VTODO DUE should be used
     * Format of CalDav datetime 20150806T110000
     * @return null|\SugarDateTime
     */
    public function getEndDate()
    {
        if (!$this->event) {
            return null;
        }

        $dtEnd = $this->getDateTimeProperty('DTEND');
        if($dtEnd) {
            return $dtEnd;
        }

        $dtStart = $this->getDateTimeProperty('DTSTART');
        if($this->event->DURATION && $dtStart) {
            $dtEnd = clone $dtStart;
            return $dtEnd->add(new \DateInterval($this->event->DURATION->getValue()));
        }

        return null;
    }

    /**
     * Get DURATION or calculate duration by begin and end time
     * @see http://tools.ietf.org/html/rfc5545#page-34
     * @return integer
     */
    public function getDuration()
    {
        if (!$this->event) {
            return null;
        }

        $duration = 0;
        if (!empty($this->event->DURATION)) {
            $duration = intval($this->dateTimeHelper->durationToSeconds($this->event->DURATION->getValue()) / 60);
        } else {
            $begin = $this->getDateTimeProperty('DTSTART');
            $end = $this->getDateTimeProperty('DTEND');
            if ($begin && $end) {
                $duration = intval((strtotime($end) - strtotime($begin)) / 60);
            }
        }

        return $duration;
    }

    /**
     * Get duration full hours
     * @return int
     */
    public function getDurationHours()
    {
        return intval($this->getDuration() / 60);
    }

    /**
     * Get duration full minutes
     * @return int
     */
    public function getDurationMinutes()
    {
        return intval($this->getDuration() % 60);
    }

    /**
     * Get organizer of event.
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant
     */
    public function getOrganizer()
    {
        if ($this->event->ORGANIZER) {
            return $this->createParticipantObject($this->event->ORGANIZER);
        }

        return null;
    }

    /**
     * Get participants of event
     * @return Participant[]
     */
    public function getParticipants()
    {
        if ($this->participants) {
            return $this->participants;
        }
        if ($this->event->ATTENDEE) {
            $organizer = $this->getOrganizer();
            foreach ($this->event->ATTENDEE as $attendee) {
                $participant = $this->createParticipantObject($attendee);
                if ($organizer && $organizer->getEmail() == $participant->getEmail()) {
                    continue;
                }
                $this->participants[] = $participant;
            }
        }

        return $this->participants;
    }

    /**
     * Get event UID
     * @return null|string
     */
    public function getUID()
    {
        return $this->getStringProperty('UID');
    }

    /**
     * Get recurrence-id of event
     * @return null|\SugarDateTime
     */
    public function getRecurrenceID()
    {
        return $this->getDateTimeProperty('RECURRENCE-ID') ?: $this->recurrenceId;
    }


    /**
     * Get all reminders info
     *
     * @return Reminders[]
     *
     */
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * Set the title (SUMMARY) of event
     * Return true if title was changed or false otherwise
     * @param string $value
     * @return bool
     */
    public function setTitle($value)
    {
        return $this->setStringProperty('SUMMARY', $value);
    }

    /**
     * Set the description of event
     * Return true if description was changed or false otherwise
     * @param string $value
     * @return bool
     */
    public function setDescription($value)
    {
        return $this->setStringProperty('DESCRIPTION', $value);
    }

    /**
     * Set start date of event
     * @param \SugarDateTime $value
     * @return bool
     */
    public function setStartDate(\SugarDateTime $value = null)
    {
        $dtEnd = $this->getEndDate();
        $isChanged = $this->setDateTimeProperty('DTSTART', $value);
        if ($isChanged && $dtEnd && !empty($this->event->DURATION)) {
            $duration = $dtEnd->getTimestamp() - $value->getTimestamp();
            $duration = $this->dateTimeHelper->secondsToDuration($duration);
            $this->setStringProperty('DURATION', $duration);
        }

        return $isChanged;
    }

    /**
     * Set end of event
     * @param \SugarDateTime $value
     * @return bool
     */
    public function setEndDate(\SugarDateTime $value)
    {
        $dtBegin = $this->getStartDate();
        if ($dtBegin) {
            $duration = $value->getTimestamp() - $dtBegin->getTimestamp();

            return $this->setEndOfEvent($duration, $value);
        }

        return $this->deleteProperty('DURATION') | $this->setDateTimeProperty('DTEND', $value);
    }

    /**
     * Set end of event
     * @param int $hours
     * @param int $minutes
     * @return bool
     */
    public function setDuration($hours, $minutes)
    {
        $seconds = $hours * 3600 + $minutes * 60;
        $duration = $this->dateTimeHelper->secondsToDuration($seconds);

        $dtBegin = $this->getStartDate();

        if ($dtBegin) {
            $dtEnd = clone $dtBegin;
            $dtEnd->add(new \DateInterval($duration));

            return $this->setEndOfEvent($seconds, $dtEnd);
        }

        return $this->deleteProperty('DTEND') | $this->setStringProperty('DURATION', $duration);
    }

    /**
     * Set the location of event
     * Return true if location was changed or false otherwise
     * @param string $value
     * @return bool
     */
    public function setLocation($value)
    {
        return $this->setStringProperty('LOCATION', $value);
    }

    /**
     * Set the status of event
     * @see $statusMap for avaliable statuses
     * Return true if status was changed or false otherwise
     * @param string $value
     * @return bool
     */
    public function setStatus($value)
    {
        return $this->setStringProperty('STATUS', $value);
    }

    /**
     * Add new reminder to event
     * @param int $secondsBefore
     * @param string $action
     * @param string $description
     *
     * @return Reminder
     */
    public function addReminder($secondsBefore, $action, $description = '')
    {
        if (!$this->event) {
            return false;
        }

        $alarm = $this->event->parent->createComponent('VALARM');
        $this->event->add($alarm);
        $reminderClass = \SugarAutoLoader::customClass('Sugarcrm\\Sugarcrm\\Dav\\Cal\\Structures\\Reminder');
        $reminder = new $reminderClass($alarm, $this->dateTimeHelper);
        $reminder->setAction($action);
        $reminder->setTrigger($secondsBefore);
        $reminder->setDescription($description);
        $this->reminders[] = $reminder;
        $this->setCustomized();

        return $reminder;
    }

    /**
     * Delete reminder
     * @param Reminder $reminder
     * @return bool
     */
    public function deleteReminder(Reminder $reminder)
    {
        if (!$this->event) {
            return false;
        }

        foreach ($this->reminders as $index => $current) {
            if ($reminder === $current) {
                $this->event->remove($reminder->getObject());
                unset($this->reminders[$index]);
                $this->setCustomized();
                return true;
            }
        }

        return false;
    }

    /**
     * Delete all reminders
     * @return bool
     */
    public function deleteAllReminders()
    {
        if (!$this->event || !$this->reminders) {
            return false;
        }

        foreach ($this->reminders as $index => $current) {
            $this->event->remove($current->getObject());
        }
        $this->reminders = array();
        $this->setCustomized();
        return true;
    }

    /**
     * Set organizer of event
     * @param Participant $organizer
     * @return bool
     */
    public function setOrganizer(Participant $organizer)
    {
        $currentOrganizer = $this->getOrganizer();
        if ($currentOrganizer && $currentOrganizer->getEmail() != $organizer->getEmail()) {
            $currentOrganizer->setType('ATTENDEE');

        }
        $participant = clone $organizer;
        $participant->setRole('CHAIR');
        $participant->setStatus('ACCEPTED');
        $participant->setType('ATTENDEE');

        $organizer->setType('ORGANIZER');
        $organizer->setRole(null);
        $organizer->setStatus(null);

        return (bool)($this->setParticipantNode($organizer, 'ORGANIZER') |
            $this->setParticipantNode($participant, 'ATTENDEE'));
    }

    /**
     * Set participant of event
     * @param Participant $participant
     * @return bool
     */
    public function setParticipant(Participant $participant)
    {
        return $this->setParticipantNode($participant, 'ATTENDEE');
    }

    /**
     * Set state of event
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Set recurrence-id of event
     * @param \SugarDateTime $value
     * @return null|string
     */
    public function setRecurrenceID(\SugarDateTime $value)
    {
        $this->recurrenceId = $value;
        return $this->setDateTimeProperty('RECURRENCE-ID', $value);
    }

    /**
     * Set event as custom event and add it to real calendar
     */
    protected function setCustomized()
    {
        if ($this->isVirtual() && $this->getRecurrenceID()) {
            $this->state = self::STATE_CUSTOM;
            $this->event->parent->add($this->event);
        }
    }

    /**
     * Delete participant
     * @param string $email
     * @return bool
     */
    public function deleteParticipant($email)
    {
        if (!$this->event) {
            return false;
        }

        $foundIndex = $this->findParticipantsByEmail($email);
        if ($foundIndex != - 1) {
            $found = $this->participants[$foundIndex];
            $this->event->remove($found->getObject());
            unset($this->participants[$foundIndex]);
            $this->setCustomized();
            return true;
        }

        return false;
    }

    /**
     * Found participant in collection by email
     * @param string $email
     * @return int - found index
     */
    public function findParticipantsByEmail($email)
    {
        foreach ($this->getParticipants() as $i => $participant) {
            if ($participant->getEmail() == $email) {
                return $i;
            }
        }

        return - 1;
    }

    /**
     * Get VEvent object
     * @return VEvent
     */
    public function getObject()
    {
        return $this->event;
    }
}
