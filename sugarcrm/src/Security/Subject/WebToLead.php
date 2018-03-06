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

/**
 * WebToLead subject
 */
final class WebToLead implements Subject
{
    /**
     * The campaign id that is attributed with any changes by this
     * subject
     * @var string
     */
    private $campaign_id;

    /**
     * Object constructor
     * @param string $campaign_id The campaign id
     */
    public function __construct($campaign_id)
    {
        $this->campaign_id = $campaign_id;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            '_type' => 'web-to-lead',
            'campaign_id' => $this->campaign_id,
        ];
    }
}
