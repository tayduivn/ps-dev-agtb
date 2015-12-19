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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract as CalDavAbstractAdapter;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;

/**
 * Class for processing Calls by iCal protocol
 *
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Calls extends CalDavAbstractAdapter implements AdapterInterface
{
    /**
     * @param array $exportData
     * @param \CalDavEventCollection $eventCollection
     * @return bool
     */
    public function export(array $exportData, \CalDavEventCollection $eventCollection)
    {
        $isCalDavChanged = false;
        $parentEvent = $eventCollection->getParent();
        $participantHelper = $this->getParticipantHelper();
        list($beanData, $changedFields, $invites) = $exportData;
        list($beanModuleName, $beanId, $repeatParentId, $childEventsId, $insert) = $beanData;

        if (isset($changedFields['name'])) {
            $this->setCalDavTitle($changedFields['name'], $parentEvent);
            $isCalDavChanged = true;
        }
        if (isset($changedFields['description'])) {
            $this->setCalDavDescription($changedFields['description'], $parentEvent);
            $isCalDavChanged = true;
        }
        if (isset($changedFields['location'])) {
            $this->setCalDavLocation($changedFields['location'], $parentEvent);
            $isCalDavChanged = true;
        }
        if (isset($changedFields['status'])) {
            $this->setCalDavStatus($changedFields['status'], $parentEvent);
            $isCalDavChanged = true;
        }
        if (isset($changedFields['date_start'])) {
            $this->setCalDavStartDate($changedFields['date_start'], $parentEvent);
            $isCalDavChanged = true;
        }
        if (isset($changedFields['date_end'])) {
            $this->setCalDavEndDate($changedFields['date_end'], $parentEvent);
            $isCalDavChanged = true;
        }

        if (isset($invites['deleted'])) {
            foreach ($invites['deleted'] as $invite) {
                if (!$parentEvent->deleteParticipant($invite[3])) {
                    new ExportException("Email {$invite[3]} hasn't found on invite deleting in Call bean");
                }
            }
            $isCalDavChanged = true;
        }

        if (isset($invites['changed'])) {
            foreach ($invites['changed'] as $invite) {
                if ($parentEvent->findParticipantsByEmail($invite[3]) == - 1) {
                    new ExportException("Email {$invite[3]} hasn't found on invite updating in Call bean");
                }
                $parentEvent->setParticipant($participantHelper->inviteToParticipant($invite));
            }
            $isCalDavChanged = true;
        }

        if (isset($invites['added'])) {
            foreach ($invites['added'] as $invite) {
                if (isset($changedFields['created_by'][0]) &&
                    $invite[1] == $changedFields['created_by'][0]
                ) {
                    $parentEvent->setOrganizer($participantHelper->inviteToParticipant($invite));
                } else {
                    $parentEvent->setParticipant($participantHelper->inviteToParticipant($invite));
                }
            }
            $isCalDavChanged = true;
        }

        return $isCalDavChanged;
    }

    /**
     * set meeting bean property
     * @param \array $importData
     * @param \SugarBean $callBean
     * @return bool
     */
    public function import(array $importData, \SugarBean $callBean)
    {
        /**@var \Call $callBean*/
        $isBeanChanged = false;
        list($beanData, $changedFields, $invites) = $importData;
        if (isset($changedFields['title'])) {
            $this->setBeanName($changedFields['title'], $callBean);
            $isBeanChanged = true;
        }
        if (isset($changedFields['description'])) {
            $this->setBeanDescription($changedFields['description'], $callBean);
            $isBeanChanged = true;
        }
        if (isset($changedFields['location'])) {
            $this->setBeanLocation($changedFields['location'], $callBean);
            $isBeanChanged = true;
        }
        if (isset($changedFields['status'])) {
            $this->setBeanStatus($changedFields['status'], $callBean);
            $isBeanChanged = true;
        }
        if (isset($changedFields['date_start'])) {
            $this->setBeanStartDate($changedFields['date_start'], $callBean);
            $isBeanChanged = true;
        }
        if (isset($changedFields['date_end'])) {
            $this->setBeanEndDate($changedFields['date_end'], $callBean);
            $isBeanChanged = true;
        }

        if (isset($invites['added'])) {
            if (!$callBean->id) {
                $callBean->id = create_guid();
                $callBean->new_with_id = true;
            }
            $isBeanChanged = true;
        }
        $contactsInvites = $this->getChangedInviteesByModule($invites, 'Contacts');
        if ($contactsInvites) {
            $this->setContactsToBean($contactsInvites, $callBean);
            $isBeanChanged = true;
        }

        $leadsInvites = $this->getChangedInviteesByModule($invites, 'Leads');
        if ($leadsInvites) {
            $this->setLeadsToBean($leadsInvites, $callBean);
            $isBeanChanged = true;
        }

        $usersInvites = $this->getChangedInviteesByModule($invites, 'Users');
        if ($usersInvites) {
            $this->setUsersToBean($usersInvites, $callBean);
            $isBeanChanged = true;
        }

        $addressesInvites = $this->getChangedInviteesByModule($invites, 'Addresses');
        if ($addressesInvites) {
            $this->setAddressesToBean($addressesInvites, $callBean);
            $isBeanChanged = true;
        }

        $this->setInvitesStatuses($invites, $callBean);
        return $isBeanChanged;
    }
}
