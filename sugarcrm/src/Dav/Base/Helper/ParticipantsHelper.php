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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sugarcrm\Sugarcrm\Dav\Base\Constants as DavConstants;
use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as DavStatusMapper;
use Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory as SearchFactory;

use Sabre\VObject\Property\ICalendar\CalAddress;

/**
 * Provides methods to  convert participants from CalDav to SugarCRM and back
 * Class ParticipantsHelper
 * @package Sugarcrm\Sugarcrm\Dav\Base\Helper
 */
class ParticipantsHelper
{
    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap
     */
    protected $statusMapper;

    /**
     * @var \Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory;
     */
    protected $searchFactory;

    public function __construct()
    {
        $this->statusMapper = new DavStatusMapper\AcceptedMap();
        $this->searchFactory = new SearchFactory();
    }

    /**
     * Retrieve EmailAddresses bean object
     * @return null|\SugarBean
     */
    protected function getEmailAddressBean()
    {
        return \BeanFactory::getBean('EmailAddresses');
    }

    /**
     * Convert DAV ICalendar\CalAddress to array:
     *  result => array(
     *      Participant module
     *      [moduleName] => Array
     *      Key - SugarCRM user id
     *          [1] => (
     *              [email] =>  sally@example.com - user email
     *              [status] => NEEDS-ACTION - Event accept status
     *              [cn] =>     user display name
     *              [role] =>   REQ-PARTICIPANT - Participant event's role
     *          )
     *          [2] => Array
     *          (
     *              [email] =>  sally@example.com - user email
     *              [status] => NEEDS-ACTION - Event accept status
     *              [cn] =>     user display name
     *              [role] =>   REQ-PARTICIPANT - Participant event's role
     *          )
     *      Participant module
     *      [secondModuleName] => array(
     *      Key - SugarCRM user id
     *          [3] => Array
     *          (
     *              [email] =>  sally@example.com - user email
     *              [status] => NEEDS-ACTION - Event accept status
     *              [cn] =>     user display name
     *              [role] =>   REQ-PARTICIPANT - Participant event's role
     *          )
     *      )
     * )
     * @param \CalDavEvent $event
     * @param \Sabre\VObject\Property\ICalendar\CalAddress $participants
     * @return array[] See above
     */
    public function prepareForSugar(\CalDavEvent $event, CalAddress $participants)
    {
        $result = array();
        $emailBean = $this->getEmailAddressBean();
        foreach ($participants as $participant) {
            $params = $participant->parameters();
            $participantModule = !empty($params['X-SUGAR-MODULE']) ? $params['X-SUGAR-MODULE']->getValue() : 'Users';
            $email = str_replace('mailto:', '', strtolower($participant->getNormalizedValue()));
            if (!empty($params['X-SUGARUID']) && $params['X-SUGARUID']->getValue()) {
                $userIds = array($params['X-SUGARUID']->getValue());
            } else {
                $userIds = $emailBean->getRelatedId($email, $participantModule);
            }

            if ($userIds) {
                foreach ($userIds as $userId) {
                    if (isset($params['PARTSTAT'])) {
                        $status = $this->statusMapper->getSugarValue($params['PARTSTAT']->getValue());
                    } else {
                        $status = 'none';
                    }
                    $cn = isset($params['CN']) ? $params['CN']->getValue() : '';
                    if (empty($result[$participantModule])) {
                        $result[$participantModule] = array();
                    }
                    $result[$participantModule][$userId] = array(
                        'email' => $email,
                        'accept_status' => $status,
                        'cn' => $cn,
                        'role' => isset($params['ROLE']) ? $params['ROLE']->getValue() : 'OPT-PARTICIPANT',
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Compare DAV attendees and SugarCRM attendees and return array with differences
     * array
     *      'operation' => (                    Operation with attendee (notModified, modified, added, deleted)
     *          'mailto:test@test.com' => (     Attendee Email
     *              'PARTSTAT' =>               Attendee accept status
     *              'CN' =>                     Attendee display name
     *              'ROLE' =>                   Attendee role
     *              'davLink' =>                Link to old attendee email if it was modified
     *              'X-SUGARUID' =>               SugarCRM User id
     *          )
     *      )
     *
     * @param \SugarBean $bean
     * @param \CalDavEvent $event
     * @param string $componentType - DAV component to process
     * @return array See above
     */
    public function prepareForDav(\SugarBean $bean, \CalDavEvent $event, $componentType = 'ATTENDEE')
    {
        $preResult = array();

        $sugarParticipants = $davParticipants = array();
        $acceptStatuses = array();
        switch ($componentType) {
            case 'ATTENDEE':
                $allParticipants = $event->getParticipants();
                foreach ($allParticipants as $moduleName => $participants) {
                    if (!empty($participants)) {
                        $davParticipants += $participants;
                    }
                }

                $searchModules = $this->searchFactory->getModulesForSearch();
                foreach ($searchModules as $module) {
                    $loadedParticipants = $this->loadParticipantsByRelationship(strtolower($module), $bean, $acceptStatuses);
                    $sugarParticipants += $loadedParticipants;
                }
                break;
            case 'ORGANIZER':
                if (!$bean->load_relationship('users')) {
                    return array();
                }
                $allParticipants = $event->getOrganizer();
                if (!empty($allParticipants['Users'])) {
                    $davParticipants += $allParticipants['Users'];
                }
                $sugarParticipants = $bean->users->getBeans(array(
                    'where' => array(
                        'lhs_field' => 'id',
                        'operator' => '=',
                        'rhs_value' => $bean->assigned_user_id
                    )
                ));
                foreach ($sugarParticipants as $userId => $participant) {
                    if (isset($bean->users->rows[$userId]['accept_status'])) {
                        $acceptStatuses[$userId] = $bean->users->rows[$userId]['accept_status'];
                    }
                }
                break;
            default:
                return array();
        }
        if ($davParticipants) {
            foreach ($davParticipants as $userId => $userInfo) {
                if (!isset($sugarParticipants[$userId])) {
                    $preResult[DavConstants::PARTICIPIANT_DELETED][$userId] = array(
                        'email' => $userInfo['email'],
                        'accept_status' => null,
                        'role' => null,
                        'cn' => null,
                        'x-sugar-module' => null,
                    );
                }
            }
        }

        foreach ($sugarParticipants as $userId => $userBean) {
            $email = $this->getUserPrimaryAddress($userBean);
            if ($email) {
                $displayName =
                    !empty($davParticipants[$userId]['cn']) ? $davParticipants[$userId]['cn'] : $userBean->full_name;
                $role = !empty($davParticipants[$userId]['role']) ? $davParticipants[$userId]['role'] : 'REQ-PARTICIPANT';

                $preResult[DavConstants::PARTICIPIANT_NOT_MODIFIED][$userId] = array(
                    'email' => $email,
                    'accept_status' => isset($acceptStatuses[$userId]) ? $acceptStatuses[$userId] : 'none',
                    'cn' => $displayName,
                    'role' => $role,
                    'x-sugar-module' => $userBean->module_name,
                );
            }
        }

        if (isset($preResult[DavConstants::PARTICIPIANT_NOT_MODIFIED])) {
            foreach ($preResult[DavConstants::PARTICIPIANT_NOT_MODIFIED] as $userId => $userInfo) {
                if (!isset($davParticipants[$userId])) {
                    $preResult[DavConstants::PARTICIPIANT_ADDED][$userId] = $userInfo;
                } elseif (($davParticipants[$userId]['email'] !== $userInfo['email'] ||
                    $davParticipants[$userId]['accept_status'] !== $userInfo['accept_status'])
                ) {
                    $userInfo['davLink'] = strtolower('mailto:' . $davParticipants[$userId]['email']);
                    $preResult[DavConstants::PARTICIPIANT_MODIFIED][$userId] = $userInfo;
                }
            }

            unset($preResult[DavConstants::PARTICIPIANT_NOT_MODIFIED]);
        }

        if (!$preResult) {
            return array();
        }

        $davAttendees = array();

        foreach ($preResult as $internalStatus => $attendeesInfo) {
            foreach ($attendeesInfo as $attendeeId => $attendee) {
                $status = $this->statusMapper->getCalDavValue($attendee['accept_status']);
                $davLink = isset($attendee['davLink']) ? $attendee['davLink'] : null;
                $sugarModule = isset($attendee['x-sugar-module']) ? $attendee['x-sugar-module'] : 'Users';

                $davAttendees[$internalStatus][strtolower('mailto:' . $attendee['email'])] = array(
                    'PARTSTAT' => $status,
                    'CN' => $attendee['cn'],
                    'ROLE' => $attendee['role'],
                    'davLink' => $davLink,
                    'X-SUGARUID' => $attendeeId,
                    'RSVP' => 'TRUE',
                    'X-SUGAR-MODULE' => $sugarModule,
                );
            }
        }

        return $davAttendees;
    }

    /**
     * Get all SugarCRM participants
     * @param string $relationship
     * @param \SugarBean $bean
     * @param array $acceptStatuses
     * @return array
     */
    protected function loadParticipantsByRelationship($relationship, \SugarBean $bean, array &$acceptStatuses)
    {
        $sugarParticipants = array();
        if ($bean->load_relationship($relationship)) {
            $bean->$relationship->resetLoaded();
            $sugarParticipants = $bean->$relationship->getBeans();
            foreach ($sugarParticipants as $userId => $participant) {
                if (isset($bean->$relationship->rows[$userId]['accept_status'])) {
                    $acceptStatuses[$userId] = $bean->$relationship->rows[$userId]['accept_status'];
                }
            }
        }
        return $sugarParticipants;
    }

    /**
     * Retrieve primary email from user bean
     * @param \SugarBean $userBean
     * @return null | string
     */
    protected function getUserPrimaryAddress(\SugarBean $userBean)
    {
        $emailBean = $this->getEmailAddressBean();
        $result = $emailBean->getPrimaryAddress($userBean);
        if ($result) {
            return $result;
        }

        return null;
    }
}
