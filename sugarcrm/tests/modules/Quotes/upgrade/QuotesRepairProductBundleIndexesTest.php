<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Test upgrade script which fixes duplicate bundle indexes
 * @see Bug65573
 */
require_once "tests/upgrade/UpgradeTestCase.php";

class QuotesRepairProductBundleIndexesTest extends UpgradeTestCase
{
    /**
     * @var DBManager
     */
    protected $db;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->db = DBManagerFactory::getInstance();
    }

    public function tearDown()
    {
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        parent::tearDown();
    }

    /**
     * Test if upgrade script correctly reindexes product bundles
     */
    public function testFixQuotesAndProductBundles()
    {
        $productBundleOne = SugarTestProductBundleUtilities::createProductBundle();
        $productBundleTwo = SugarTestProductBundleUtilities::createProductBundle();
        $quote = SugarTestQuoteUtilities::createQuote();

        // Create 2 relationships, but with the same bundle_index
        $productBundleOne->set_productbundle_quote_relationship($quote->id, $productBundleOne->id, 1);
        $productBundleTwo->set_productbundle_quote_relationship($quote->id, $productBundleTwo->id, 1);

        $this->upgrader->setVersions('6.7.4', 'ent', '7.2.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', '2_RepairQuoteAndProductBundles');
        $script->fixProductBundleIndexes();

        $result = $this->db->query(
            "
            SELECT quote_id, count(quote_id) AS count
            FROM product_bundle_quote
            GROUP BY quote_id, bundle_index
            HAVING count > 1
            "
        );
        $row = $this->db->fetchByAssoc($result);

        $this->assertEmpty($row, 'There should be no bundles with duplicate indexes');
    }
}
