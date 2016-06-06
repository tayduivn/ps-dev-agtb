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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Adapter;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Exception\ExportException;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Exception\ImportException;

/**
 * Interface for beans import export adapters.
 *
 * Interface DataAdapterInterface
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
interface DataAdapterInterface
{
    /**
     * Should be used as return value of export & import methods in case in nothing should be saved.
     */
    const NOTHING = 0;

    /**
     * Should be used as return value of export & import methods in case if bean should be saved.
     */
    const SAVE = 1;

    /**
     * Should be used as return value of export & import methods in case if bean should be deleted.
     */
    const DELETE = 2;

    /**
     * Should be used as return value of export & import methods in case if bean should be restored.
     */
    const RESTORE = 3;

    /**
     * @param \SugarBean $bean
     * @param mixed|false $previousData in case of false full export should be processed
     * @return mixed|false returns data for export or false if required things weren't changed and nothing to export
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false);

    /**
     * Prepare bean for export with full rebuild. All children will be pass to export.
     *
     * @param \SugarBean $bean
     * @param mixed|false $previousData in case of false full export should be processed
     * @return mixed|false returns data for export or false if required things weren't changed and nothing to export
     */
    public function prepareForRebuild(\SugarBean $bean, $previousData = false);

    /**
     * @param mixed $data should be updated for verifyImportAfterExport
     * @param \CalDavEventCollection $collection
     * @return DataAdapterInterface::NOTHING|DataAdapterInterface::SAVE|DataAdapterInterface::DELETE|DataAdapterInterface::RESTORE
     * @throws ExportException if conflict has been found
     */
    public function export(&$data, \CalDavEventCollection $collection);

    /**
     * @param mixed $exportData current export data
     * @param mixed $importData new import data which was generated on save of bean
     * @param \CalDavEventCollection $collection
     * @return mixed updated new data for import, false if import isn't required
     */
    public function verifyImportAfterExport($exportData, $importData, \CalDavEventCollection $collection);

    /**
     * @param \CalDavEventCollection $collection
     * @param mixed|false $previousData in case of false full import should be processed
     * @return mixed returns data for import or false if required things weren't changed and nothing to import
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false);

    /**
     * @param \SugarBean $bean
     * @param \CalDavEventCollection $calDavBean
     * @param mixed $importData
     * @return \SugarBean
     */
    public function getBeanForImport(\SugarBean $bean, \CalDavEventCollection $calDavBean, $importData);

    /**
     * @param mixed $data should be updated for verifyExportAfterImport
     * @param \SugarBean $bean
     * @return DataAdapterInterface::NOTHING|DataAdapterInterface::SAVE|DataAdapterInterface::DELETE|DataAdapterInterface::RESTORE
     * @throws ImportException if conflict has been found
     */
    public function import(&$data, \SugarBean $bean);

    /**
     * @param mixed $importData current import data
     * @param mixed $exportData new export data which was generated on save of collection
     * @param \SugarBean $bean
     * @return mixed|false updated new data for export, false if export isn't required
     */
    public function verifyExportAfterImport($importData, $exportData, \SugarBean $bean);
}
