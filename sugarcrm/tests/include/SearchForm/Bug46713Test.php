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

 
require_once 'modules/DynamicFields/templates/Fields/TemplateInt.php';
require_once 'modules/DynamicFields/templates/Fields/TemplateDate.php';
require_once 'include/SearchForm/SearchForm2.php';
require_once 'modules/Cases/Case.php';

class Bug46713Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $hasExistingCustomSearchFields = false;
    var $searchForm;
    var $originalDbType;
    var $smartyTestFile;
    
    public function setUp()
    {	
        if(file_exists('custom/modules/Cases/metadata/SearchFields.php'))
        {
            $this->hasExistingCustomSearchFields = true;
            copy('custom/modules/Cases/metadata/SearchFields.php', 'custom/modules/Cases/metadata/SearchFields.php.bak');
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        } else if(!file_exists('custom/modules/Cases/metadata')) {
            mkdir_recursive('custom/modules/Cases/metadata');
        }

        //Setup Opportunities module and date_closed field
        $_REQUEST['view_module'] = 'Cases';
        $_REQUEST['name'] = 'date_closed';
        $templateDate = new TemplateDate();
        $templateDate->enable_range_search = true;
        $templateDate->populateFromPost();
        include('custom/modules/Cases/metadata/SearchFields.php');

        //Prepare SearchForm
        $seed = new aCase();
        $module = 'Cases';
        $this->searchForm = new SearchForm($seed, $module);
        $this->searchForm->searchFields = array(
            'range_case_number' => array
            (
                'query_type' => 'default',
                'enable_range_search' => true
            ),
        );
        $this->originalDbType = $GLOBALS['db']->dbType;
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->dbType = $this->originalDbType;

        if(!$this->hasExistingCustomSearchFields)
        {
            unlink('custom/modules/Cases/metadata/SearchFields.php');
        }

        if(file_exists('custom/modules/Cases/metadata/SearchFields.php.bak')) {
            copy('custom/modules/Cases/metadata/SearchFields.php.bak', 'custom/modules/Cases/metadata/SearchFields.php');
            unlink('custom/modules/Cases/metadata/SearchFields.php.bak');
        }

        if(file_exists($this->smartyTestFile))
        {
            unlink($this->smartyTestFile);
        }

    }

    public function testRangeNumberSearches()
    {
    	$GLOBALS['db']->dbType = 'mysql';

        $this->searchForm->searchFields['range_case_number'] = array (
            'query_type' => 'default',
            'enable_range_search' => 1,
            'value' => '0',
            'operator' => '=',
        );

        $where_clauses = $this->searchForm->generateSearchWhere();
        $this->assertEquals($where_clauses[0], "cases.case_number >= -0.01 AND cases.case_number <= 0.01", 'Unexpected where clause');
    }
}
?>