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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Hook;

use \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JQManager;
use \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier\ExportNotifier;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier\ImportNotifier;

/**
 *
 * Logic hook handler
 *
 */
class Handler
{
    /**
     * Allow us to enable / disable hook.
     * Should be used in UTs.
     *
     * @var bool
     */
    protected static $disabled = false;

    /**
     * Notifier class used to notify import job listeners.
     *
     * @var ImportNotifier
     */
    protected static $importNotifier;

    /**
     * Notifier class used to notify export job listeners.
     *
     * @var ExportNotifier
     */
    protected static $exportNotifier;

    /**
     * @param \CalDavEventCollection $collection
     * @param mixed|false $previousData in case of false full import should be processed
     * @param bool $conflictSolver
     * @return bool success of operation
     */
    public function import(\CalDavEventCollection $collection, $previousData = false, $conflictSolver = false)
    {
        if (static::$disabled) {
            return false;
        }
        if (!$collection->isImportable() || !$collection->parent_type) {
            return false;
        }
        $adapter = $this->getAdapterFactory()->getAdapter($collection->parent_type);
        if (!$adapter) {
            return false;
        }

        $preparedDataSet = $adapter->prepareForImport($collection, $previousData);
        if (!$preparedDataSet) {
            return false;
        }

        foreach ($preparedDataSet as $preparedData) {
            $continue = $this->getImportNotifier()->notify($collection->module_name, $collection->id, $preparedData);
            if ($continue && $preparedData) {
                $saveCounter = $collection->getSynchronizationObject()->setSaveCounter();
                if ($conflictSolver) {
                    $collection->getSynchronizationObject()->setConflictCounter(true);
                }

                $collection->getQueueObject()->import($preparedData, $saveCounter);

                if ($saveCounter - $collection->getSynchronizationObject()->getJobCounter() == 1) {
                    $this->getManager()->calDavHandler($collection->id);
                }
            }
        }
        return true;
    }

    /**
     * @param \SugarBean $bean
     * @param mixed|false $previousData in case of false full export should be processed
     * @param bool $conflictSolver
     * @return bool success of operation
     */
    public function export(\SugarBean $bean, $previousData = false, $conflictSolver = false)
    {
        if (static::$disabled) {
            return false;
        }
        $adapter = $this->getAdapterFactory()->getAdapter($bean->module_name);
        if (!$adapter) {
            return false;
        }

        $preparedDataSet = $conflictSolver ?
            $adapter->prepareForRebuild($bean, $previousData) :
            $adapter->prepareForExport($bean, $previousData);
        
        if (!$preparedDataSet) {
            return false;
        }

        if (!empty($bean->repeat_root_id)) {
            $rootBeanId = $bean->repeat_root_id;
        } else {
            $rootBeanId = $bean->id;
        }

        /** @var \CalDavEventCollection $collection */
        $collection = \BeanFactory::getBean('CalDavEvents');
        $collection = $collection->findByParentModuleAndId($bean->module_name, $rootBeanId);
        if (!$collection) {
            $collection = \BeanFactory::getBean('CalDavEvents');
            $collection->setParentModuleAndId($bean->module_name, $rootBeanId);
            $collection->event_uid = $rootBeanId;
            if ($bean->created_by) {
                $collection->created_by = $bean->created_by;
                $collection->set_created_by = false;
            }
            $collection->save();
        }

        foreach ($preparedDataSet as $preparedData) {
            $continue = $this->getExportNotifier()->notify($bean->module_name, $rootBeanId, $preparedData);
            if ($continue && $preparedData) {
                $saveCounter = $collection->getSynchronizationObject()->setSaveCounter();
                if ($conflictSolver) {
                    $collection->getSynchronizationObject()->setConflictCounter(true);
                    $conflictSolver = false;
                }

                $queue = $collection->getQueueObject();
                if ($bean->created_by) {
                    $queue->created_by = $bean->created_by;
                    $queue->set_created_by = false;
                }
                $queue->export($preparedData, $saveCounter);

                if ($saveCounter - $collection->getSynchronizationObject()->getJobCounter() == 1) {
                    $this->getManager()->calDavHandler($collection->id);
                }
            }
        }
        return true;
    }

    /**
     * Get ImportNotifier.
     *
     * @return ImportNotifier
     */
    public function getImportNotifier()
    {
        if (!isset(static::$importNotifier)) {
            static::$importNotifier = new ImportNotifier();
        }
        return static::$importNotifier;
    }

    /**
     * Get ExportNotifier.
     *
     * @return ExportNotifier
     */
    public function getExportNotifier()
    {
        if (!isset(static::$exportNotifier)) {
            static::$exportNotifier = new ExportNotifier();
        }
        return static::$exportNotifier;
    }

    /**
     * Get manager object for handler processing.
     * @return \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getManager()
    {
        return new JQManager();
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }
}
