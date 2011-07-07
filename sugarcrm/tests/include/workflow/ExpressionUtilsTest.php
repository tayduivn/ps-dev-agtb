<?php
//FILE SUGARCRM flav=pro ONLY
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
 
require_once 'include/workflow/expression_utils.php';

/*
 * @group ExpressionUtilsTest
 */

class ExpressionUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{

	public function testGetExpression()
	{
		$express_type = '+';
		$first = 8329;
		$second = 2374;
		$output = get_expression($express_type, $first, $second);
		$this->assertEquals(10703, $output);
		
		$express_type = '-';
		$first = 8329;
		$second = 2374;
		$output = get_expression($express_type, $first, $second);
		$this->assertEquals(5955, $output);
		
		$express_type = '*';
		$first = 8329;
		$second = 2374;
		$output = get_expression($express_type, $first, $second);
		$this->assertEquals(19773046, $output);
		
		$express_type = '/';
		$first = 7122;
		$second = 2374;
		$output = get_expression($express_type, $first, $second);
		$this->assertEquals(3, $output);
	}

	public function testExpressAdd()
	{
		$first = 8329;
		$second = 2374;
		$output = express_add($first, $second);
		$this->assertEquals(10703, $output);
	}

	public function testExpressSubtract()
	{
		$first = 8329;
		$second = 2374;
		$output = express_subtract($first, $second);
		$this->assertEquals(5955, $output);
	}

	public function testExpressMultiple()
	{
		$first = 8329;
		$second = 2374;
		$output = express_multiple($first, $second);
		$this->assertEquals(19773046, $output);
	}

	public function testExpressDivide()
	{
		$first = 7122;
		$second = 2374;
		$output = express_divide($first, $second);
		$this->assertEquals(3, $output);
	}

}
