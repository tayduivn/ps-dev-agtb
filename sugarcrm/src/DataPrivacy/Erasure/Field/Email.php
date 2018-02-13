<?php declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field;

use SugarBean;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field;

/**
 * Represents an email field
 */
final class Email implements Field
{
    /**
     * @var string
     */
    private $id;

    /**
     * Constructor
     *
     * @param string $id The ID of the email to be erased
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return [
            'field_name' => 'email',
            'id' => $this->id,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function erase(SugarBean $bean) : void
    {
        // TODO: implement this
    }
}
