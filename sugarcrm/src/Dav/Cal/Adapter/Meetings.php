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
use \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\ExportException;

/**
 * Class for processing Meetings by iCal protocol
 *
 * Class Meetings
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Meetings extends CalDavAbstractAdapter implements AdapterInterface
{
    /**
     * Updates caldav bean and returns true if anything was changed
     *
     * @param array $data
     * @param \CalDavEventCollection $collection
     * @return bool
     * @throws ExportException if conflict has been found
     */
    public function export(array $data, \CalDavEventCollection $collection)
    {
        $isChanged = false;
        $event = $collection->getParent();
        list($beanData, $changedFields, $invites) = $data;
        list($beanModuleName, $beanId, $repeatParentId, $childEventsId, $insert) = $beanData;

        // checking before values
        if (!$insert) {
            if (isset($changedFields['name'][1]) && !$this->checkCalDavTitle($changedFields['name'][1], $event)) {
                throw new ExportException("Conflict with CalDav Title field");
            }
            if (isset($changedFields['description'][1]) && !$this->checkCalDavDescription($changedFields['description'][1], $event)) {
                throw new ExportException("Conflict with CalDav Description field");
            }
            if (isset($changedFields['status'][1]) && !$this->checkCalDavStatus($changedFields['status'][1], $event)) {
                throw new ExportException("Conflict with CalDav Status field");
            }
            if (isset($changedFields['date_start'][1]) && !$this->checkCalDavStartDate($changedFields['date_start'][1], $event)) {
                throw new ExportException("Conflict with CalDav Start Date field");
            }
            if (isset($changedFields['date_end'][1]) && !$this->checkCalDavEndDate($changedFields['date_end'][1], $event)) {
                throw new ExportException("Conflict with CalDav End Date field");
            }
            if ($invites && !$this->checkCalDavInvites($invites, $event)) {
                throw new ExportException("Conflict with CalDav Invites");
            }
        }

        // setting values
        if (isset($changedFields['name'][0])) {
            $isChanged = $isChanged | $this->setCalDavTitle($changedFields['name'][0], $event);
        }
        if (isset($changedFields['description'][0])) {
            $isChanged = $isChanged | $this->setCalDavDescription($changedFields['description'][0], $event);
        }
        if (isset($changedFields['status'][0])) {
            $isChanged = $isChanged | $this->setCalDavStatus($changedFields['status'][0], $event);
        }
        if (isset($changedFields['date_start'][0])) {
            $isChanged = $isChanged | $this->setCalDavStartDate($changedFields['date_start'][0], $event);
        }
        if (isset($changedFields['date_end'][0])) {
            $isChanged = $isChanged | $this->setCalDavEndDate($changedFields['date_end'][0], $event);
        }
        if ($invites) {
            $isChanged = $isChanged | $this->setCalDavInvites($invites, $event);
        }

        return $isChanged;
    }

    /**
     * Updates bean and returns true if anything was changed
     *
     * @param array $data
     * @param \SugarBean $bean
     * @return bool
     * @throws ImportException if conflict has been found
     */
    public function import(array $data, \SugarBean $bean)
    {
        /**@var \Call $bean*/
        $isChanged = false;
        list($beanData, $changedFields, $invites) = $data;
        list($beanId, $childEventsId, $insert) = $beanData;

        // checking before values
        if (!$insert) {
            if (isset($changedFields['title'][1]) && !$this->checkBeanName($changedFields['title'][1], $bean)) {
                throw new ImportException("Conflict with Bean Name field");
            }
            if (isset($changedFields['description'][1]) && !$this->checkBeanDescription($changedFields['description'][1], $bean)) {
                throw new ImportException("Conflict with Bean Description field");
            }
            if (isset($changedFields['location'][1]) && !$this->checkBeanLocation($changedFields['location'][1], $bean)) {
                throw new ImportException("Conflict with Bean Location field");
            }
            if (isset($changedFields['status'][1]) && !$this->checkBeanStatus($changedFields['status'][1], $bean)) {
                throw new ImportException("Conflict with Bean Status field");
            }
            if (isset($changedFields['date_start'][1]) && !$this->checkBeanStartDate($changedFields['date_start'][1], $bean)) {
                throw new ImportException("Conflict with Bean Start Date field");
            }
            if (isset($changedFields['date_end'][1]) && !$this->checkBeanEndDate($changedFields['date_end'][1], $bean)) {
                throw new ImportException("Conflict with Bean End Date field");
            }
            if ($invites && !$this->checkBeanInvites($invites, $bean)) {
                throw new ImportException("Conflict with Bean Invites");
            }
        }

        $bean->invitesBefore = \CalendarUtils::getInvites($bean);

        // setting values
        if (isset($changedFields['title'][0])) {
            $isChanged |= $this->setBeanName($changedFields['title'][0], $bean);
        }
        if (isset($changedFields['description'][0])) {
            $isChanged |= $this->setBeanDescription($changedFields['description'][0], $bean);
        }
        if (isset($changedFields['location'][0])) {
            $isChanged |= $this->setBeanLocation($changedFields['location'][0], $bean);
        }
        if (isset($changedFields['status'][0])) {
            $isChanged |= $this->setBeanStatus($changedFields['status'][0], $bean);
        }
        if (isset($changedFields['date_start'][0])) {
            $isChanged |= $this->setBeanStartDate($changedFields['date_start'][0], $bean);
        }
        if (isset($changedFields['date_end'][0])) {
            $isChanged |= $this->setBeanEndDate($changedFields['date_end'][0], $bean);
        }
        if ($invites) {
            $isChanged |= $this->setBeanInvites($invites, $bean);
        }

        return $isChanged;
    }
}
