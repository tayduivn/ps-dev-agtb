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

    public function __construct()
    {
        $this->statusMapper = new DavStatusMapper\AcceptedMap();
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
     *  Key - SugarCRM user id
     *  [1] => Array
     *      (
     *          [email] =>  sally@example.com - user email
     *          [status] => NEEDS-ACTION - Event accept status
     *          [cn] =>     user display name
     *          [role] =>   REQ-PARTICIPANT - Participant event's role
     *      )
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
            $email = str_replace('mailto:', '', strtolower($participant->getValue()));
            if (!empty($params['X-SUGARUID']) && $params['X-SUGARUID']->getValue()) {
                $userIds = array($params['X-SUGARUID']->getValue());
            } else {
                $userIds = $emailBean->getRelatedId($email, 'Users');
            }
            if ($userIds) {
                foreach ($userIds as $userId) {
                    $statusMap = $this->statusMapper->getMapping($event);
                    $status = isset($statusMap[$params['PARTSTAT']->getValue()]) ?
                        $statusMap[$params['PARTSTAT']->getValue()] : 'none';

                    $cn = isset($params['CN']) ? $params['CN']->getValue() : '';

                    $result[$userId] = array(
                        'email' => $email,
                        'accept_status' => $status,
                        'cn' => $cn,
                        'role' => $params['ROLE']->getValue(),
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
     * @param \CalDavEvent $event
     * @param string $componentType - DAV component to process
     * @return array See above
     */
    public function prepareForDav(\CalDavEvent $event, $componentType = 'ATTENDEE')
    {
        $bean = $event->getBean();
        $preResult = array();
        if (!$bean->load_relationship('users')) {
            return array();
        }

        switch ($componentType) {
            case 'ATTENDEE':
                $davParticipants = $event->getParticipants();
                $sugarParticipants = $bean->users->getBeans();
                break;
            case 'ORGANIZER':
                $davParticipants = $event->getOrganizer();
                $sugarParticipants = $bean->users->getBeans(array(
                    'where' => array(
                        'lhs_field' => 'id',
                        'operator' => '=',
                        'rhs_value' => $bean->assigned_user_id
                    )
                ));
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
                    );
                }
            }
        }

        foreach ($sugarParticipants as $userId => $userBean) {
            $email = $this->getUserPrimaryAddress($userBean);
            if ($email) {
                $displayName =
                    !empty($davParticipants[$userId]['cn']) ? $davParticipants[$userId]['cn'] : $userBean->full_name;
                $role = !empty($davParticipants[$userId]['role']) ? $davParticipants[$userId]['role'] : null;

                $preResult[DavConstants::PARTICIPIANT_NOT_MODIFIED][$userId] = array(
                    'email' => $email,
                    'accept_status' => $bean->users->rows[$userId]['accept_status'],
                    'cn' => $displayName,
                    'role' => $role,
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

        $statusMap = array_flip($this->statusMapper->getMapping($event));

        $davAttendees = array();

        foreach ($preResult as $internalStatus => $attendeesInfo) {
            foreach ($attendeesInfo as $attendeeId => $attendee) {
                $status = isset($statusMap[$attendee['accept_status']]) ? $statusMap[$attendee['accept_status']] : null;
                $davLink = isset($attendee['davLink']) ? $attendee['davLink'] : null;

                $davAttendees[$internalStatus][strtolower('mailto:' . $attendee['email'])] = array(
                    'PARTSTAT' => $status,
                    'CN' => $attendee['cn'],
                    'ROLE' => $attendee['role'],
                    'davLink' => $davLink,
                    'X-SUGARUID' => $attendeeId,
                    'RSVP' => 'TRUE',
                );
            }
        }

        return $davAttendees;
    }

    /**
     * Retrieve primary email from user bean
     * @param \User $userBean
     * @return null | string
     */
    protected function getUserPrimaryAddress(\User $userBean)
    {
        $result = $userBean->getUsersNameAndEmail();
        if (!empty($result['email'])) {
            return $result['email'];
        }

        return null;
    }
}
