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

/**
 * Interface for beans import export adapters
 * Interface AdapterInterface
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */

interface AdapterInterface
{
    /**
     * @param array $data
     * @param \CalDavEventCollection $collection
     * @return bool
     */
    public function export(array $data, \CalDavEventCollection $collection);

    /**
     * @param array $exportData current export data
     * @param array $importData new import data which was generated on save of bean
     * @param \CalDavEventCollection $collection
     * @return mixed updated new data for import, false if import isn't required
     */
    public function verifyImportAfterExport(array $exportData, array $importData, \CalDavEventCollection $collection);

    /**
     * @param array $data
     * @param \SugarBean $bean
     * @return bool
     */
    public function import(array $data, \SugarBean $bean);

    /**
     * @param array $importData current import data
     * @param array $exportData new export data which was generated on save of collection
     * @param \SugarBean $bean
     * @return mixed updated new data for export, false if export isn't required
     */
    public function verifyExportAfterImport(array $importData, array $exportData, \SugarBean $bean);

    /**
     * @param \SugarBean $bean
     * @param mixed|false $previousData in case of false full export should be processed
     * @return mixed returns data for export or false if required things weren't changed and nothing to export
     */
    public function prepareForExport(\SugarBean $bean, $previousData = false);

    /**
     * @param \CalDavEventCollection $collection
     * @param mixed|false $previousData in case of false full import should be processed
     * @return mixed returns data for import or false if required things weren't changed and nothing to import
     */
    public function prepareForImport(\CalDavEventCollection $collection, $previousData = false);
}
