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

namespace Sugarcrm\Sugarcrm\Security\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * GUID validator
 *
 */
class GuidValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Guid) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Guid');
        }

        // check for string
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'string expected')
                ->setInvalidValue($value)
                ->setCode(Guid::ERROR_STRING_REQUIRED)
                ->addViolation();
            return;
        }

        // check for allowed characters
        if (!preg_match('/^[a-z0-9\-]*$/i', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'invalid format')
                ->setInvalidValue($value)
                ->setCode(Guid::ERROR_INVALID_FORMAT)
                ->addViolation();
            return;
        }
    }
}
