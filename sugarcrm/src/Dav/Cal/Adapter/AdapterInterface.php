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
     * @param array $data
     * @param \SugarBean $bean
     * @return bool
     */
    public function import(array $data, \SugarBean $bean);

    /**
     * @param \SugarBean $bean
     * @param array $changedFields
     * @param array $invitesBefore
     * @param array $invitesAfter
     * @param bool $insert
     * @return mixed
     */
    public function prepareForExport(\SugarBean $bean, $changedFields, $invitesBefore, $invitesAfter, $insert);
}
