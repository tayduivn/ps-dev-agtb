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

namespace Sugarcrm\Sugarcrm\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Query builder which filter out users by some reference to an event.
 *
 * Interface SubscriptionFilter/SubscriptionFilterInterface
 * @package Notification
 */
interface SubscriptionFilterInterface
{

    /**
     * Function return string label from $app_string in which hold subscription filter name.
     *
     * @return string label from app_string
     */
    public function __toString();

    /**
     * Filtering query, join user, return joined user table name.
     *
     * @param EventInterface $event for which will be filter outed users
     * @param \SugarQuery $query which will be filter out users by some reference
     * @return string alias of the user table in query
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query);

    /**
     * Order which depict priority subscription filter.
     *
     * @return integer index number
     */
    public function getOrder();

    /**
     * Function return is Subscription Filter support current event.
     *
     * @param EventInterface $event for checking is subscription filter support it
     * @return boolean is subscription filter support event
     */
    public function supports(EventInterface $event);
}
