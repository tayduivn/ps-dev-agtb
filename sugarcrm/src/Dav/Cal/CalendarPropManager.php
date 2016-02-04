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

namespace Sugarcrm\Sugarcrm\Dav\Cal;

use Sabre\VObject\Component\VCalendar;

/**
 * Manage VCalendar properties and types classes
 * Class CalendarPropManager
 * @package Sugarcrm\Sugarcrm\Dav\Cal
 */
class CalendarPropManager
{
    /**
     * Set class handler name for VCalendar properties
     * @param string $property
     * @param string $value
     */
    public function setPropertyMapHandler($property, $value)
    {
        if ($property && $value) {
            VCalendar::$propertyMap[$property] = $value;
        }
    }

    /**
     * Set class handler name for VCalendar value-types
     * @param string $property
     * @param string $value
     */
    public function setValueMapHandler($property, $value)
    {
        if ($property && $value) {
            VCalendar::$valueMap[$property] = $value;
        }
    }
}
