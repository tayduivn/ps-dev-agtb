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

use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueInterface;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * File validator
 *
 *
 */
class FileValidator extends ConstraintValidator implements ConstraintReturnValueInterface
{
    // use ConstraintReturnValueTrait;

    /* START TRAIT - remove when on +5.4 */

    /**
     * @var mixed
     */
    protected $formattedReturnValue;

    /**
     * {@inheritdoc}
     */
    public function getFormattedReturnValue()
    {
        if (count($this->context->getViolations()) !== 0) {
            throw new ConstraintReturnValueException(
                'Cannot get formatted value when violations are present',
                $this->context->getViolations()
            );
        }
        return $this->formattedReturnValue;
    }

    /* END TRAIT */

    /**
     * Ctor
     */
    public function __construct()
    {
        // Add shadow directory when in use
        if (defined('SHADOW_INSTANCE_DIR')) {
            $this->baseDirs[] = SHADOW_INSTANCE_DIR;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof File) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\File');
        }

        // check for string
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->setCode(File::ERROR_STRING_REQUIRED)
                ->setParameter('%msg%', 'string expected')
                ->addViolation();
            return;
        }

        // check for null bytes
        if (strpos($value, chr(0)) !== false) {
            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->setCode(File::ERROR_NULL_BYTES)
                ->setParameter('%msg%', 'null bytes detected')
                ->addViolation();
            return;
        }

        // normalize using realpath, implies a fileExists check
        $normalized = realpath($value);

        if ($normalized === false) {
            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->setCode(File::ERROR_FILE_NOT_FOUND)
                ->setParameter('%msg%', 'file not found')
                ->addViolation();
            return;
        }

        // normalized format needs to start with baseDir value
        $baseDirCompliant = false;
        foreach ($constraint->baseDirs as $baseDir) {
            if (strpos($normalized, $baseDir) === 0) {
                $baseDirCompliant = true;
                break;
            }
        }

        if (!$baseDirCompliant) {
            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($normalized)
                ->setCode(File::ERROR_OUTSIDE_BASEDIR)
                ->setParameter('%msg%', 'file outside basedir')
                ->addViolation();
            return;
        }

        $this->formattedReturnValue = $normalized;
    }
}
