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
 * Interface for filters.
 * Filter should detect events and extend query to filter users for suitable events.
 *
 * Interface SubscriptionFilter/SubscriptionFilterInterface
 * @package Notification
 */
interface SubscriptionFilterInterface
{

    /**
     * Returns key of label from $app_string with own name.
     *
     * @return string label from app_string
     */
    public function __toString();

    /**
     * Filtering query, join user, return joined user table name.
     *
     * @param EventInterface $event with source data to build query
     * @param \SugarQuery $query empty SugarQuery object
     * @return string alias of the user table in query
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query);

    /**
     * Returns order of filter, higher number wins.
     *
     * @return integer order number
     */
    public function getOrder();

    /**
     * Checks passed event object and returns true of false if the event can be processed by this filter.
     *
     * @param EventInterface $event for checking
     * @return boolean true if event is supported or false
     */
    public function supports(EventInterface $event);
}
