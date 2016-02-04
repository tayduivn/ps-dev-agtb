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

namespace Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status;

class DayMap extends MapBase
{
    protected $map = array(
        'SU' => array(0),
        'MO' => array(1),
        'TU' => array(2),
        'WE' => array(3),
        'TH' => array(4),
        'FR' => array(5),
        'SA' => array(6),
    );
}
