<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'modules/Forecasts/ForecastUtils.php';


class syncTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $product;
    private $lineItem;
    private $product_bundle;
    private $quote;
    private $opp;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $this->product = SugarTestProductUtilities::createProduct();

        $this->lineItem = SugarTestOppLineItemUtilities::createLine();

        $this->product_bundle = SugarTestProductBundleUtilities::createProductBundle();

        $this->quote = SugarTestQuoteUtilities::createQuote();

        $this->opp = SugarTestOpportunityUtilities::createOpportunity();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestOppLineItemUtilities::removeAllCreatedLines();
        SugarTestOppLineBundleUtilities::removeAllCreatedLineBundles();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
    }

    public function testSyncQuoteWithOpportunity()
    {
        //case #1: Quote has a bundle with a product. Opp has no bundles. Sync quote with opp.
        $this->product_bundle->set_productbundle_quote_relationship($this->quote->id, '', '');
        $this->product_bundle->set_productbundle_product_relationship($this->product->id, '1', '');

        $result = syncQuoteWithOpportunity($this->quote->id, $this->opp->id, 'to_opportunity');

        $bundleId[0] = $result['opportunity']['bundles'][0]['id'];
        $itemId[0] = $result['opportunity']['bundles'][0]['products'][0]['id'];

        $line = new OpportunityLine();
        $line->retrieve($itemId[0]);
        $this->assertTrue($this->product->id == $line->product_id);


        //case #2: Quote has a bundle with a product. Opp has a bundle with the same name but it doesn't contain any item
        $oppLineBundle = new OpportunityLineBundle();
        $oppLineBundle->retrieve($bundleId[0]);
        $oppLineBundle->clear_line_linebundle_relationship($itemId[0]);

        $result = syncQuoteWithOpportunity($this->quote->id, $this->opp->id, 'to_opportunity');

        $itemId[1] = $result['opportunity']['bundles'][0]['products'][0]['id'];

        $line = new OpportunityLine();
        $line->retrieve($itemId[1]);
        $this->assertTrue($this->product->id == $line->product_id);

        //case #3: Quote has a bundle with a product. Opp has a bundle with different name
        $oppLineBundle->name = 'OppBundle_' . $oppLineBundle->name;
        $oppLineBundle->save();
        $oppLineBundle->clear_line_linebundle_relationship($itemId[1]);

        $result = syncQuoteWithOpportunity($this->quote->id, $this->opp->id, 'to_opportunity');

        $bundleId[2] = $result['opportunity']['bundles'][0]['id'];
        $itemId[2] = $result['opportunity']['bundles'][0]['products'][0]['id'];

        $line = new OpportunityLine();
        $line->retrieve($itemId[2]);
        $this->assertTrue($this->product->id == $line->product_id);

        //case #4: Quote has a bundle with a product. Opp has the same bundle with an item refered to same product
        $result = syncQuoteWithOpportunity($this->quote->id, $this->opp->id, 'to_opportunity');
        $this->assertArrayNotHasKey('bundles', $result['opportunity']);

        SugarTestOppLineBundleUtilities::setCreatedLineBundle($bundleId);
        SugarTestOppLineItemUtilities::setCreatedLine($itemId);
    }
}