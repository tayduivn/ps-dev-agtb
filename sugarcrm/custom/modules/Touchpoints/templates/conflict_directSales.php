<?php
$email_body = '<table style="border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; border-bottom: 1px solid #cccccc; font-size: 12px; font-family: arial,verdana,helvetica,sans-serif; line-height: 16px; width: 600px;" border="0" cellspacing="0" cellpadding="0" align="center"><tbody><tr><td><a style="color: #9D0C0B;" href="http://www.sugarcrm.com"><img src="http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt="SugarCRM" width="600" height="200" /></a></td></tr><tr><td style="padding: 20px 30px 40px 60px;" width="600">

<p>Dear Inside Sales Manager,</p>

<p>This email is to inform you that '.$account_name.', which had no open opportunity and was assigned to '.$inside_sales_assigned_user_name.' on the Inside Sales Team, has been reassigned to '.$new_assigned_to_user_name.' in association with a new Partner opportunity.</p>

<p>For your reference the Account can be found at:<br />
'.$link_to_account.'
</p>

<p>For your reference the Opportunity can be found at:<br />
'.$link_to_opportunity.'
</p>

<p>Thanks,</p>

<p>- Sugar Internal Admin</p>

</td></tr></tbody></table>';

