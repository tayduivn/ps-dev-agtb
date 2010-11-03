<?php
$contactemail_body_html = '<table style="border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; border-bottom: 1px solid #cccccc; font-size: 12px; font-family: arial,verdana,helvetica,sans-serif; line-height: 16px; width: 600px;" border="0" cellspacing="0" cellpadding="0" align="center"><tbody><tr><td><a style="color: #9D0C0B;" href="http://www.sugarcrm.com"><img src="http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt="SugarCRM" width="600" height="200" /></a></td></tr><tr><td style="padding: 20px 30px 40px 60px;" width="600">

<p>Dear $contact_first_name,</p>

<p>Thanks for getting in contact with SugarCRM.  You have joined the many thousands of companies today that recognize the choices are few when it comes to an economical CRM solution that can address your unique business needs now, and can also grow and adapt with your business over time.  I am confident that, with SugarCRM, you will find exactly this type of solution.</p>

<p>We understand that evaluating and selecting a business critical application such as CRM can become a challenging task.  It is critical that you identify the solution with the right fit in terms of cost, features, deployment flexibility, etc.  </p>
<p>That is why I have asked one of our proven partners, $partner_assigned_to_first_name&nbsp;$partner_assigned_to_last_name from $partner_assigned_to_account_name  to reach out to you and offer you assistance through this process.  They will simply answer questions you may have or help you to spec out the total cost of implementing a SugarCRM solution.  Either way, I think you will be extremely pleased with the professionalism and knowledge that $partner_assigned_to_account_name has to offer.</p>

<p><u>Contact Information:</u> </p>
<p>
$partner_assigned_to_account_name<br />
$partner_assigned_to_first_name&nbsp;$partner_assigned_to_last_name<br />
$partner_assigned_to_title<br />
<a href="mailto:$partner_assigned_to_email">$partner_assigned_to_email</a><br />
$partner_assigned_to_phone_work
</p>

<p>Of course, if I can help in any way, please do not hesitate to contact me directly via email or via my office phone number.</p>
<p>We look forward to earning your business and introducing you into the SugarCRM Commercial Community.</p>

<p>Sincerely, </p><p>'.$current_user->full_name.'<br /> '.$current_user->title.'<br /> '.$current_user->phone_work.'<br /> <a style="color: #9D0C0B;" href="mailto:'.$current_user->email1.'">'.$current_user->email1.'</a><br /> SugarCRM Inc.</p>
</td></tr></tbody></table>';
?>
