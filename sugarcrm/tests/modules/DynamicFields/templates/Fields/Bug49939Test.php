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

/**
 * Bug49939Test.php
 * @author Collin Lee
 *
 * This is a simple test to assert that we can correctly remove the XSS attack strings set in the help field
 * via Studio.
 *
 */

class Bug49939Test extends Sugar_PHPUnit_Framework_TestCase {

/**
 * xssFields
 * This is the provider function for testPopulateFromPostWithXSSHelpField
 *
 */
public function xssFields() {
   return array(
       array(htmlentities('<script>alert(50);</script>'), ''),
       array(htmlentities('This is some help text'), 'This is some help text'),
       array(htmlentities('???'), '???'),
       array(htmlentities('Foo Foo<script type="text/javascript">alert(50);</script>Bar Bar'), 'Foo FooBar Bar'),
       array(htmlentities('I am trying to <b>Bold</b> this!'), 'I am trying to &lt;b&gt;Bold&lt;/b&gt; this!'),
       array(htmlentities(''), ''),
   );
}


/**
 * testPopulateFromPostWithXSSHelpField
 * @dataProvider xssFields
 * @param string $badXSS The bad XSS script
 * @param string $expectedValue The expected output
 */
public function testPopulateFromPostWithXSSHelpField($badXSS, $expectedValue)
{
    $tf = new Bug49939TemplateFieldMock();
    $_REQUEST['help'] = $badXSS;
    $tf->vardef_map = array('help'=>'help');
    $tf->populateFromPost();
    $this->assertEquals($expectedValue, $tf->help, 'Unable to remove XSS from help field');
}


}


require_once('modules/DynamicFields/templates/Fields/TemplateField.php');
class Bug49939TemplateFieldMock extends TemplateField {

public function applyVardefRules()
{
    //no-opt function called at the end of populateFromPost method in TemplateField
}

}

?>