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
 
require_once 'include/Smarty/plugins/function.sugar_link.php';
require_once 'include/Sugar_Smarty.php';

class FunctionSugarLinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty;
    }
    
    public function testReturnModuleLinkOnly()
    {
        $string = 'my string';
        
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains(ajaxLink("index.php?module=Dog&action=index"),$output);
    }
    
    public function testReturnModuleLinkWithAction()
    {
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','action'=>'cat','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains(ajaxLink("index.php?module=Dog&action=cat"),$output);
    }
    
    public function testReturnModuleLinkWithActionAndExtraParams()
    {
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','action'=>'cat','extraparams'=>'foo=bar','link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains(ajaxLink("index.php?module=Dog&action=cat&foo=bar"),$output);
    }
    
    /**
     * @ticket 33909
     */
    public function testReturnLinkWhenPassingData()
    {
        $data = array(
            '63edeacd-6ba5-b658-5e2a-4af9a5d682be',
            'http://localhost',
            'all',
            'iFrames',
            'Foo',
            );

        
        $output = smarty_function_sugar_link(
            array('module'=>'Dog','data'=>$data,'link_only'=>'1'),
            $this->_smarty);
        
        $this->assertContains(ajaxLink("index.php?module=iFrames&action=index&record=63edeacd-6ba5-b658-5e2a-4af9a5d682be&tab=true"),$output);
    }
    
    public function testCreatingFullLink()
    {
        $output = smarty_function_sugar_link(
            array(
                'module'=>'Dog',
                'action'=>'cat',
                'id'=>'foo1',
                'class'=>'foo4',
                'style'=>'color:red;',
                'title'=>'foo2',
                'accesskey'=>'B',
                'options'=>'scope="row"',
                'label'=>'foo3',
                ),
            $this->_smarty);
        
        $this->assertContains(
            '<a href="' . ajaxLink('index.php?module=Dog&action=cat') . '" id="foo1" class="foo4" style="color:red;" scope="row" title="foo2" module="Dog">foo3</a>',$output);

    }
}
