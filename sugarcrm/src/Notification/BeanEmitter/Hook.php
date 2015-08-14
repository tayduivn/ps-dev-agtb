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

namespace Sugarcrm\Sugarcrm\Notification\BeanEmitter;

/**
 * Logic hook handler, executer of BeanEmitter.
 *
 * Class Hook
 * @package Notification
 */
class Hook
{

    /**
     * To be used from logic hooks to execute BeanEmitter
     *
     * @param \SugarBean $bean Target bean
     * @param string $event Triggered logic hooks event
     * @param array $arguments (optional) Optional arguments
     */
    public function hook(\SugarBean $bean, $event, array $arguments = array())
    {

    }
}
