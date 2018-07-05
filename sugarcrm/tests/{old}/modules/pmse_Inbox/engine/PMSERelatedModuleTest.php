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
use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSERelatedModule
 */
class PMSERelatedModulesTest extends TestCase
{
   /**
    * Unit test class to cover getRelatedBeans method for a type of relationship 'all'.
    * @covers ::geRelatedBeans
    */
    public function testGetRelatedBeans()
    {
        // We are testing this object
        $r = ProcessManager\Factory::getPMSEObject('PMSERelatedModule');
        $res = $r->getRelatedBeans('Accounts', 'all');

        // Verify that we have the result we expect initially
        $this->assertArrayHasKey('result', $res);

        // Get one of each type to ensure that 'all' is working
        $o = $this->getRelatedModuleDef($res['result'], 'one');
        $m = $this->getRelatedModuleDef($res['result'], 'many');

        // Verify that we have at least one of each type
        $this->assertNotEmpty($o);
        $this->assertNotEmpty($m);

        // Verify that the label decorator was added
        $this->assertRegexp('/[*:1]/', $o['text']);
        $this->assertRegexp('/[*:M]/', $m['text']);
    }

    /**
     * Gets a single def from the resultant fetch, by relationship type
     * @param array $res The result of the collection
     * @param string $type The type of def to get
     * @return array
     */
    protected function getRelatedModuleDef($res, $type)
    {
        foreach ($res as $v) {
            if (isset($v['type']) && $v['type'] === $type) {
                return $v;
            }
        }

        return [];
    }
}
