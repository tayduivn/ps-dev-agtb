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

namespace Sugarcrm\Sugarcrm\Security\Validator;

use Sugarcrm\Sugarcrm\Security\Validator\Exception\ConstraintReturnValueException;

/**
 *
 * Validator constraints are use to only validate given values. In some cases
 * it makes sense to format the given data and return that instead to avoid
 * any duplicate operations.
 *
 */
interface ConstraintReturnValueInterface
{
    /**
     * Get formatted validated return value
     * @return mixed Formatted return value
     * @throws ConstraintReturnValueException
     */
    public function getFormattedReturnValue();
}
