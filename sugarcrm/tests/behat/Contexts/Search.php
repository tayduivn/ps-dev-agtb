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

namespace Sugarcrm\SugarcrmTestsBehat\Contexts;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Sugarcrm\Sugarcrm\SearchEngine\Engine\Elastic;
use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use SugarTestHelper;
use SugarTestUserUtilities;

/**
 * Defines application features from the specific context.
 */
class Search implements Context
{
    /**
     * @Then the following queries should have the following results:
     *
     * @param TableNode $table
     *
     * @throws \SugarApiExceptionSearchRuntime
     */
    public function theFollowingQueriesShouldHaveTheFollowingResults(TableNode $table)
    {
        //It can take up to 1 second to index items that were previously created in this scenario
        $engine = SearchEngine::getInstance();
        $container = $engine->getContainer();
        $container->indexer->finishBatch();
        sleep(1);

        $globalsearchApi = new \GlobalSearchApi();
        $restService = new \RestService();
        $restService->user = $GLOBALS["current_user"];

        foreach ($table as $row) {
            $args = array (
                '__sugar_url' => 'v11_2/globalsearch',
                'tags' => 'true',
                'max_num' => '5',
                'q' => $row['terms'],
                'module_list' => $row['modules'],
            );

            $result = $globalsearchApi->globalSearch($restService, $args);
            Assert::assertEquals($row['total'], $result['total']);
            Assert::assertContains($row['value'], array_column($result['records'], $row['fieldName']));
        }
    }
}
