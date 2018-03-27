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
use Throwable;

/**
 *
 * Result listener ensuring that any risky or incomplete tests are marked a failures,
 * since they should not be merged into master
 *
 */
final class ResultListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * {@inheritdoc}
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time) : void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $this->raiseFailure($test, 'Risky tests are NOT allowed', $time);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time) : void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $this->raiseFailure($test, 'Incomplete tests are NOT allowed', $time);
    }

    /**
     * Raise failure on given test case
     *
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
}
