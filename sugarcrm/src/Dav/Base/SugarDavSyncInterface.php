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

namespace Sugarcrm\Sugarcrm\Dav\Base;

/**
 * Interface SugarDavSyncInterface
 * @package Sugarcrm\Sugarcrm\Dav\Base
 */
interface SugarDavSyncInterface
{
    /*
     * Auto increment Bean Sync Counter
     *
     * @return int Bean Sync Counter
     */
    public function setBeanSyncCounter();

    /*
     * Auto increment Dav Sync Counter
     *
     * @return int Dav Sync Counter
     */
    public function setDavSyncCounter();

    /*
     * Return  Bean Sync Counter
     *
     * @return int Bean Sync Counter
     */
    public function getBeanSyncCounter();

    /*
     * Return Dav Sync Counter
     *
     * @return int Dav Sync Counter
     */
    public function getDavSyncCounter();
}
