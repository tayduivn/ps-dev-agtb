<?php
//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once("include/Expressions/Expression/Parser/Parser.php");

class Bug43551Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function testSubStr()
	{
	    $contact = new Contact();
            $contact->first_name = "Fabio";
            $contact->last_name = "Grande";

            // First 2 letters of first name - Fa
            $expr = 'subStr($first_name, 0, 2)';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("Fa", $result);

            // First 2 letters of last name - Gr
            $expr = 'subStr($last_name, 0, 2)';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("Gr", $result);

            // First 2 letters of last name concatenated to first 2 letters first name - GrFa
            $expr = 'concat(subStr($last_name, 0, 2), subStr($first_name, 0, 2))';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("GrFa", $result);

            // First 22 letters of last name concatenated to first 32 letters first name - GrandeFabio
            $expr = 'concat(subStr($last_name, 0, 22), subStr($first_name, 0, 32))';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("GrandeFabio", $result);

            // First 5 letters of last name concatenated to first name - Grand
            $expr = 'subStr(concat($last_name, $first_name), 0, 5)';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("Grand", $result);

            $contact->first_name = "";
            $contact->last_name = "Grande";

            // First 2 letters of first name - is empty....
            $expr = 'subStr($first_name, 0, 2)';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("", $result);

            // First 2 letters of last name concatenated to first 2 letters first name (empty...) - Gr
            $expr = 'concat(subStr($last_name, 0, 2), subStr($first_name, 0, 2))';
            $result = Parser::evaluate($expr, $contact)->evaluate();
            $this->assertEquals("Gr", $result);
	}

}
