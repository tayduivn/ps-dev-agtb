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

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Query builder which filter out admins.
 *
 * Class Application
 * @package Notification
 */
class Application implements SubscriptionFilterInterface
{

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'Application';
    }

    /**
     * Filter out admins, return joined user table name.
     *
     * @param EventInterface $event
     * @param \SugarQuery $query which will be filter out admins
     * @return string alias of the user table in query
     */
    public function filterQuery(EventInterface $event, \SugarQuery $query)
    {
        $userBean = \BeanFactory::getBean('Users');
        $query->from($userBean, array('team_security' => false));
        $query->where()->equals('is_admin', 1);

        return $query->getFromAlias();
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 1000;
    }

    /**
     * Function return is Subscription Filter support current event.
     *
     * We check is event instance of ApplicationEvent and is event name one form emitter event strings list
     *
     * @param EventInterface $event for checking is subscription filter support event
     * @return boolean is event supported by subscription filter
     */
    public function supports(EventInterface $event)
    {
        return $event instanceof ApplicationEvent;
    }
}
