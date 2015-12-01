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

use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as AdapterInvalidArgumentException;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract as CalDavAbstractAdapter;

/**
 * Class for processing Meetings by iCal protocol
 *
 * Class Meetings
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Adapter
 */
class Meetings extends CalDavAbstractAdapter implements AdapterInterface
{
    /**
     * @param \SugarBean $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function export(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {

    }

    /**
     * set meeting bean property
     * @param \SugarBean $sugarBean
     * @param \CalDavEvent $calDavBean
     * @return bool
     */
    public function import(\SugarBean $sugarBean, \CalDavEvent $calDavBean)
    {

    }
}
