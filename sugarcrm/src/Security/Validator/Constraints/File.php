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

/**
 *
 * @see PhpSerializedValidator
 *
 */
class File extends Constraint
{
    const ERROR_STRING_REQUIRED = 1;
    const ERROR_NULL_BYTES = 2;
    const ERROR_FILE_NOT_FOUND = 3;
    const ERROR_OUTSIDE_BASEDIR = 4;

    protected static $errorNames = array(
        self::ERROR_STRING_REQUIRED => 'ERROR_STRING_REQUIRED',
        self::ERROR_NULL_BYTES => 'ERROR_NULL_BYTES',
        self::ERROR_FILE_NOT_FOUND => 'ERROR_FILE_NOT_FOUND',
        self::ERROR_OUTSIDE_BASEDIR => 'ERROR_OUTSIDE_BASEDIR',
    );

    public $message = 'File name violation: %msg%';
    public $baseDirs = array(SUGAR_BASE_DIR);

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        // add additional base directory when shadow is enabled
        if (defined('SHADOW_INSTANCE_DIR')) {
            $this->baseDirs[] = SHADOW_INSTANCE_DIR;
        }
    }
}
