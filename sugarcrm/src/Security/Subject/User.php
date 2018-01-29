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

namespace Sugarcrm\Sugarcrm\Security\Subject;

use Sugarcrm\Sugarcrm\Security\Subject;
use User as SugarUser;

/**
 * A Sugar user making changes through an API client
 */
final class User implements Subject
{
    /**
     * @var SugarUser
     */
    private $user;

    /**
     * @var ApiClient
     */
    private $client;

    /**
     * Constructor
     *
     * @param SugarUser $user
     * @param ApiClient $client
     */
    public function __construct(SugarUser $user, ApiClient $client)
    {
        $this->user = $user;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'type' => 'user',
            'id' => $this->user->id,
            'client' => $this->client->jsonSerialize(),
        ];
    }
}
