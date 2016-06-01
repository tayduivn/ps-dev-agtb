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

namespace Sugarcrm\Sugarcrm\Dav\Base\Helper;

use Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status as DavStatusMapper;
use Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

/**
 * Provides methods to  convert participants from CalDav to SugarCRM and back
 * Class ParticipantsHelper
 * @package Sugarcrm\Sugarcrm\Dav\Base\Helper
 */
class ParticipantsHelper
{
    /**
     * @var LoggerTransition
     */
    protected $logger;

    /**
     * Set up logger.
     */
    public function __construct()
    {
        $this->logger = new LoggerTransition(\LoggerManager::getLogger());
    }

    /**
     * Calculates simple diff between to arrays.
     *
     * Keys:
     * 0 - beanName
     * 1 - beanId
     * 2 - email
     * 3 - status
     * 4 - display name
     *
     * The same records should have the same beanName, beanId and email.
     * For the same records difference can be only in status.
     *
     * @param array $inviteesBefore
     * @param array $inviteesAfter
     * @return array
     */
    public function getInviteesDiff(array $inviteesBefore, array $inviteesAfter)
    {
        $this->logger->debug(
            sprintf(
                'CalDav: Invitees before are: %s, Invitees after are: %s',
                var_export($inviteesBefore, true),
                var_export($inviteesAfter, true)
            )
        );

        $changedInvitees = array();
        foreach ($inviteesBefore as $keyBefore => $inviteeBefore) {
            foreach ($inviteesAfter as $keyAfter => $inviteeAfter) {
                if ($inviteeBefore[0] != $inviteeAfter[0]) {
                    continue;
                }
                if ($inviteeBefore[1] != $inviteeAfter[1]) {
                    continue;
                }
                if ($inviteeBefore[2] != $inviteeAfter[2]) {
                    continue;
                }
                if ($inviteeBefore[3] != $inviteeAfter[3]) {
                    $changedInvitees['changed'][] = $inviteeAfter;
                }
                unset($inviteesBefore[$keyBefore], $inviteesAfter[$keyAfter]);
                break;
            }
        }
        if ($inviteesBefore) {
            $changedInvitees['deleted'] = array();
            foreach ($inviteesBefore as $invitee) {
                $changedInvitees['deleted'][] = array_slice($invitee, 0, 3);
            }
        }
        if ($inviteesAfter) {
            $changedInvitees['added'] = array_values($inviteesAfter);
        }

        $this->logger->debug('CalDav: Invitees diff is: ' . var_export($changedInvitees, true));
        return $changedInvitees;
    }

    /**
     * Converts sugar's array (with sugar status) to Participant.
     *
     * @param array $invitee
     * @return Participant
     */
    public function sugarArrayToParticipant($invitee)
    {
        $participant = new Participant();
        $participantStatuses = new DavStatusMapper\AcceptedMap();
        $participant->setBeanName($invitee[0]);
        $participant->setBeanId($invitee[1]);
        $participant->setEmail($invitee[2]);
        $participant->setStatus($participantStatuses->getCalDavValue($invitee[3]));
        $participant->setDisplayName($invitee[4]);
        return $participant;
    }

    /**
     * Returns array representation of Participant.
     *
     * @param Participant $participant
     * @return array
     */
    public function participantToArray(Participant $participant)
    {
        return array(
            $participant->getBeanName(),
            $participant->getBeanId(),
            $participant->getEmail(),
            $participant->getStatus(),
            $participant->getDisplayName(),
        );
    }
}
