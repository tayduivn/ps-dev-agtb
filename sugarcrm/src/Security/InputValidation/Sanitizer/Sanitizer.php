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

namespace Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer;

use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SanitizerException;

/**
 *
 * Input sanitizer class containing generic sanitizing which is applied on
 * every superglobal value before constraint specific sanitizing and validation
 * occurs.
 *
 * Note that sanitizing is a bad habit. It is better to teach your users how to
 * properly format input data by having full whitelist validation reject badly
 * crafted values.
 *
 */
class Sanitizer implements SanitizerInterface
{
    /**
     * Ctor
     * @throws SanitizerException
     */
    public function __construct()
    {
        // Don't deal with magic quotes anymore, it needs to be disabled, period.
        if ($this->hasMagicQuotesEnabled()) {
            throw new SanitizerException('magic_quotes_gpc needs to be disabled');
        }
    }

    /**
     * Sanitize super globals
     *
     * Calling this method should happen *after* the Superglobals class
     * received the $_POST and $_GET parameters as we want to have the raw
     * unaltered values in there.
     *
     * In general sanitizing user input is not the correct way. We should
     * reject the request through the validator/constraints framework instead
     * of trying to clean it up. Sanitizing confuses the two distinct steps
     * of input validation and output escaping.
     *
     * If sanitizing is still required this should happen only when requesting
     * a specific superglobal parameter which is possible in a generic way
     * using `Santizer::sanitize` and on a per contraint base by implementing
     * `ConstraintSanitizerInterface` on the constraint checks.
     *
     * @return Sanitizer
     *
     * @deprecated This will be removed and is solely present for the migration
     * process to the new InputValidation framework.
     */
    public function sanitizeSuperglobals()
    {
        clean_special_arguments();
        clean_incoming_data();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize($value)
    {
        return $value;
    }

    /**
     * Check if magic quotes are enabled
     * @return boolean
     */
    protected function hasMagicQuotesEnabled()
    {
        return get_magic_quotes_gpc();
    }
}
