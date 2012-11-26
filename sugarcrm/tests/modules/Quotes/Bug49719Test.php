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


require_once 'modules/Quotes/Quote.php';

class Bug49719Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $quote;
    private $contact1;
    private $contact2;

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->quote = SugarTestQuoteUtilities::createQuote();
        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact2 = SugarTestContactUtilities::createContact();

    }

    public function tearDown()
    {
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestContactUtilities::removeAllCreatedContacts();
        unset($this->quote, $this->contact1, $this->contact2);
    }

    public function testQuoteShipContact()
    {
        $this->quote->shipping_contact_name = $this->contact1->name;
        $this->quote->shipping_contact_id = $this->contact1->id;
        $this->quote->billing_contact_name = $this->contact1->name;
        $this->quote->billing_contact_id = $this->contact1->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $this->quote->shipping_contact_name = $this->contact2->name;
        $this->quote->shipping_contact_id = $this->contact2->id;
        $this->quote->billing_contact_name = $this->contact2->name;
        $this->quote->billing_contact_id = $this->contact2->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $this->quote->shipping_contact_name = $this->contact1->name;
        $this->quote->shipping_contact_id = $this->contact1->id;
        $this->quote->billing_contact_name = $this->contact1->name;
        $this->quote->billing_contact_id = $this->contact1->id;
        $this->quote->save();

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Ship To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);

        $query = "SELECT count(*) as cnt FROM quotes_contacts WHERE quote_id = '{$this->quote->id}' AND deleted = 0 AND contact_role = 'Bill To'";
        $result = $GLOBALS['db']->fetchOne($query);
        $this->assertEquals(1, $result['cnt']);
    }
}
