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

namespace Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener;

use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Listener;

/**
 * Null implementation of the listener
 */
final class NullListener implements Listener
{
    /**
     * {@inheritDoc}
     */
    public function teamSetCreated($teamSetId, array $teamIds)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetReplaced($teamSetId, $replacementId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function teamSetDeleted($teamSetId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function userAddedToTeam($userId, $teamId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function userRemovedFromTeam($userId, $teamId)
    {
    }
}
