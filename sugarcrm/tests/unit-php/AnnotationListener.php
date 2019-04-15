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

namespace Sugarcrm\SugarcrmTestsUnit;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

/**
 *
 * Annotation listener ensuring every test has @covers annotation.
 *
 */
final class AnnotationListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * {@inheritdoc}
     */
    public function endTest(Test $test, float $time) : void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        // check for @covers or @coversNothing
        if ($this->hasNoCoversAnnotation($test->getAnnotations())) {
            $this->raiseFailure($test, 'Missing @covers annotation', $time);
        }
    }

    /**
     * Raise failure on given test case
     * @param TestCase $test
     * @param string $failure Failure message
     * @param double $time Elapsed time
     */
    private function raiseFailure(TestCase $test, string $failure, float $time) : void
    {
        if ($resultObject = $test->getTestResultObject()) {
            $e = new AssertionFailedError($failure);
            $resultObject->addFailure($test, $e, $time);
        }
    }

    /**
     * Check if annotations has @covers(Nothing)
     * @param array $annotations
     * @return bool
     */
    private function hasNoCoversAnnotation(array $annotations) : bool
    {
        return (
            empty($annotations['class']['covers']) &&
            empty($annotations['class']['coversNothing']) &&
            empty($annotations['method']['covers']) &&
            empty($annotations['method']['coversNothing'])
        );
    }
}
