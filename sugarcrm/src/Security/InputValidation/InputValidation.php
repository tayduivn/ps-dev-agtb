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

namespace Sugarcrm\Sugarcrm\Security\InputValidation;

use Sugarcrm\Sugarcrm\Security\Validator\Validator;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer\Sanitizer;

/**
 *
 * Input validation service
 *
 */
class InputValidation
{
    /**
     * @var Request
     */
    private static $service;

    /**
     * Service class, dont instantiate.
     */
    private function __construct()
    {
    }

    /**
     * Get service
     * @return Request
     */
    public static function getService()
    {
        if (empty(self::$service)) {

            // create instance using raw request parameters
            self::$service = $request = self::create($_GET, $_POST);

            // configure softFail mode - enabled by default
            $softFail = \SugarConfig::getInstance()->get('validation.soft_fail', true);
            $request->setSoftFail($softFail);
        }
        return self::$service;
    }

    /**
     * Create new Request validator service object. Use
     * `InputValidation::getService()` unless you know what you are doing.
     *
     * @param array $get Raw GET parameters
     * @param array $post Raw POST parameters
     * @return Request
     */
    public static function create(array $get, array $post)
    {
        $validator = Validator::getService();
        $superglobals = new Superglobals($get, $post);
        $constraintBuilder = new ConstraintBuilder();
        $request = new Request($superglobals, $validator, $constraintBuilder);

        // attach sanitizer (may disappear)
        $request->setSanitizer(new Sanitizer());

        return $request;
    }
}
