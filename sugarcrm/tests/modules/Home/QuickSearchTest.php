<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
class QuickSearchTest extends Sugar_PHPUnit_Framework_OutputTestCase
{
	private $quickSearch;
	
	public function setUp() 
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, 1));
    }
    
    public function tearDown() 
    {
        unset($_REQUEST['data']);
        unset($_REQUEST['query']);
        $q = "delete from product_templates where name = 'MasonUnitTest'";
        $GLOBALS['db']->query($q);
        SugarTestHelper::tearDown();
    }
	
    public function testFormatResults()
    {
    	$tempPT = new ProductTemplate();
    	$tempPT->name = 'MasonUnitTest';
    	$tempPT->description = "Unit'test";
    	$tempPT->cost_price = 1000;
    	$tempPT->discount_price = 800;
    	$tempPT->list_price = 1100;
    	$tempPT->save();
    	
    	$_REQUEST['data'] = '{"conditions":[{"end":"%","name":"name","op":"like_custom","value":""}],"field_list":["name","id","type_id","mft_part_num","cost_price","list_price","discount_price","pricing_factor","description","cost_usdollar","list_usdollar","discount_usdollar","tax_class_name"],"form":"EditView","group":"or","id":"EditView_product_name[1]","limit":"30","method":"query","modules":["ProductTemplates"],"no_match_text":"No Match","order":"name","populate_list":["name_1","product_template_id_1"],"post_onblur_function":"set_after_sqs"}';
        $_REQUEST['query'] = 'MasonUnitTest';
        require('modules/home/quicksearchQuery.php');
        
        $json = getJSONobj();
		$data = $json->decode(html_entity_decode($_REQUEST['data']));
		if(isset($_REQUEST['query']) && !empty($_REQUEST['query'])){
    		foreach($data['conditions'] as $k=>$v){
    			if(empty($data['conditions'][$k]['value'])){
       				$data['conditions'][$k]['value']=$_REQUEST['query'];
    			}
    		}
		}
 		$this->quickSearch = new quicksearchQuery();
		$result = $this->quickSearch->query($data);
		$resultBean = $json->decodeReal($result);
		$this->assertEquals($resultBean['fields'][0]['description'], $tempPT->description);
    }
}
