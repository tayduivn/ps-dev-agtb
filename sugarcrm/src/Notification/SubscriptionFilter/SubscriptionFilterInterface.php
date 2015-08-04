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
 * Interface SubscriptionFilter/SubscriptionFilterInterface
 * @package Notification
 */
interface SubscriptionFilterInterface
{

    /**
     * Function return should subscription filter name
     *
     * @return string
     */
    public function __toString();

    /**
     * Filtering query, join user, return joined user table name
     *
     * @param EventInterface $event
     * @param \SugarQuery $query
     * @return string
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query);

    /**
     * Order name
     *
     * @return integer
     */
    public function getOrder();

    /**
     * Function return is Subscription Filter support event
     *
     * @param EventInterface $event
     * @return boolean
     */
    public function supports(EventInterface $event);
}
