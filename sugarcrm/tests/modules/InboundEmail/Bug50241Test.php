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


require_once('modules/InboundEmail/InboundEmail.php');

/**
 * @ticket 50241
 */
class Bug50241Test extends Sugar_PHPUnit_Framework_TestCase
{

	protected $ie = null;

	public function setUp()
    {
		$this->ie = new InboundEmail();
	}

	function testEmailCleanup()
	{
	    $inStr=<<<EOS
<head>
<style><!--
.hmmessage P
{
margin:0px;
padding:0px
}
body.hmmessage
{
font-size: 10pt;
font-family:Tahoma
}
--></style></head>
<body class='hmmessage'><div dir='ltr'>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">hello, <o:p></o:p></SPAN><BR>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. <o:p></o:p></SPAN><BR>
<SPAN style="FONT-FAMILY: 'Tahoma','sans-serif'; FONT-SIZE: 10pt">what can i do, so that i can play catwoman?<o:p></o:p></SPAN><BR>
 <BR> </div></body>
</html>
EOS;

$outStr=<<<EOS
<div dir="ltr">
<span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">hello, </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">i recently got Batman Arkham City and tried to get catwoman as an add-on character but when i put the code in it said that my code had already been used. </span><p></p><br /><span style="font-family:Tahoma, 'sans-serif';font-size:10pt;">what can i do, so that i can play catwoman?</span><p></p><br /><br /></div>
EOS;

	    $actual = SugarCleaner::cleanHtml($inStr);

	    // Normalize the line endings - Bug #51227
	    $outStr = str_replace("\r\n", "\n", $outStr);
	    $actual = str_replace("\r\n", "\n", $actual);
        $this->assertEquals(trim($outStr),trim($actual));
	}
}