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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Bug #48826
 * Module Builder - Dependent multiselect fields are always displayed
 * Bug #49774
 * [IBM RTC 3020] XSS - Administration, Studio, Edit Fields, formula
 *
 * @ticket
 */

require_once ('modules/DynamicFields/FieldCases.php') ;

class Bug48826Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
    
    public function provider()
    {
        $types = array(
            'char','varchar','varchar2','text','textarea','double','float','decimal','int','date','bool','relate',
            'enum','multienum','radioenum','email','url','iframe','html','phone','currency','parent','parent_type',
            'currency_id','address','encrypt','id','datetimecombo','datetime','image','_other_'
        );
        $provider_array = array();
        foreach ( $types as $type )
        {
            // Bug #48826
            $provider_array[] = array($type, array('name' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,&quot;Analyst&quot;)');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
            // Bug #49775
            $provider_array[] = array($type, array('formula' => 'concat(&quot;<script>alert(1623651453416)</script>&quot;, &quot;<script>alert(1623651453416)</script>&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('formula' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('formula' => 'concat(&quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;, &quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat(&quot;<script>alert(1623651453416)</script>&quot;, &quot;<script>alert(1623651453416)</script>&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat(&quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;, &quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
        }
        
        return $provider_array;
    }
    
    /**
     * @group 48826, 49774
     * @dataProvider provider
     */
    public function testPopulateFromPost($type, $request_data, $expected)
    {
        $tested_key = null;
        foreach ( $request_data as $_key => $_data )
        {
            $_REQUEST[$_key] = $_data;
            $tested_key = $_key;
        }
        
        $field = get_widget($type) ;
        $field->populateFromPost();

        if ( isset($field->$tested_key) )
        {
            $this->assertEquals($expected, $field->$tested_key);
        } 
        else 
        {
            $this->markTestSkipped();
        }
    }
}
?>
