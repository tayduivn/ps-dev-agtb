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
 * Class Constants
 * Constants for DAV
 * @package Sugarcrm\Sugarcrm\Dav\Base
 */
class Constants
{
    /**
     * Add operation code
     */
    const OPERATION_ADD = 1;

    /**
     * Modify operation code
     */
    const OPERATION_MODIFY = 2;

    /**
     * Delete operation code
     */
    const OPERATION_DELETE = 3;

    /**
     * Maximum date count for INFINITE RECCURENCE
     */
    const MAX_INFINITE_RECCURENCE_COUNT = 1000;

    const PARTICIPIANT_NOT_MODIFIED = 'notModified';

    const PARTICIPIANT_MODIFIED = 'modified';

    const PARTICIPIANT_ADDED = 'added';

    const PARTICIPIANT_DELETED = 'delete';

    const DEFAULT_CALENDAR_URI = 'default';

    const NS_SUGAR = 'http://sugarcrm.com/ns';
}
