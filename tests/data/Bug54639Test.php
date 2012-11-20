<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/SugarBean.php');

/**
 * @ticket 47731
 * @ticket 54639
 */
class Bug54639Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $contact = null;

    /**
     *
     */
    public function setUp()
    {
        $this->contact = new Contact();
        $this->contact->field_defs["as_tetrispe_accounts_name"] = array (
            'name' => 'as_tetrispe_accounts_name',
            'type' => 'relate',
            'source' => 'non-db',
            'vname' => 'LBL_AS_TETRISPERSON_ACCOUNTS_FROM_ACCOUNTS_TITLE',
            'save' => true,
            'id_name' => 'as_tetrispac95ccounts_ida',
            'link' => 'as_tetrisperson_accounts',
            'table' => 'accounts',
            'module' => 'Accounts',
            'rname' => 'name',
        );

        $this->contact->field_defs["as_tetrispac95ccounts_ida"] = array (
            'name' => 'as_tetrispac95ccounts_ida',
            'type' => 'link',
            'relationship' => 'as_tetrisperson_accounts',
            'source' => 'non-db',
            'reportable' => false,
            'side' => 'right',
            'vname' => 'LBL_AS_TETRISPERSON_ACCOUNTS_FROM_AS_TETRISPERSON_TITLE',
        );
    }

    /**
     * Test getting import fields from a bean when a relationship has been defined and the id field is only defined as a link
     * and not a relate entry. The id field should be exposed so that users can select it from a list during the import process.
     *
     * @group bug54639
     * @return void
     */
    public function testGetImportableFields()
    {
        $c = new Contact();
        $importableFields = $c->get_importable_fields();
        $this->assertTrue(isset($importableFields['as_tetrispac95ccounts_ida']));
    }
}