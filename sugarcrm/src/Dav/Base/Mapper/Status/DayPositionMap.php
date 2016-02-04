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

class DayPositionMap extends MapBase
{
    protected $map = array(
        '1' => array('first'),
        '2' => array('second'),
        '3' => array('third'),
        '4' => array('fourth'),
        '5' => array('fifth'),
        '-1' => array('last'),
    );
}
