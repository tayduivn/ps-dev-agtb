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

namespace Sugarcrm\SugarcrmTestsUnit;

/**
 *
 * Annotation listener ensuring every test has @covers annotation.
 *
 */
class AnnotationListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * {@inheritdoc}
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        if (!$test instanceof \PHPUnit_Framework_TestCase) {
            return;
        }

        // check for @covers or @coversNothing
        if ($this->hasNoCoversAnnotation($test->getAnnotations())) {
            $e = new \PHPUnit_Framework_AssertionFailedError('Missing @covers annotation');
            $test->getTestResultObject()->addFailure($test, $e, $time);
        }

        // check for @coversDefaultClass
        if ($this->hasNoCoversDefaultClass($test->getAnnotations())) {
            $e = new \PHPUnit_Framework_AssertionFailedError('Missing @coversDefaultClass annotation');
            $test->getTestResultObject()->addFailure($test, $e, $time);
        }
    }

    /**
     * Check if annotations has @covers(Nothing)
     * @param array $annotations
     * @return boolean
     */
    protected function hasNoCoversAnnotation(array $annotations)
    {
        return (
            empty($annotations['class']['covers']) &&
            empty($annotations['class']['coversNothing']) &&
            empty($annotations['method']['covers']) &&
            empty($annotations['method']['coversNothing'])
        );
    }

    /**
     * Check if annotations has @coversDefaultClass
     * @param array $annotations
     * @return boolean
     */
    protected function hasNoCoversDefaultClass(array $annotations)
    {
        return (
            empty($annotations['class']['coversDefaultClass'])
        );
    }
}
