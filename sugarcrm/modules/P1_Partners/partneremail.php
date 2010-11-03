<?php
$body_html = '
                        <table style="border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; border-bottom: 1px solid #cccccc; font-size: 12px; font-family: arial,verdana,helvetica,sans-serif; line-height: 16px; width: 600px;" border="0" cellspacing="0" cellpadding="0" align="center"><tbody><tr><td><a style="color: #9D0C0B;" href="http://www.sugarcrm.com"><img src="http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt="SugarCRM" width="600" height="200" /></a></td></tr><tr><td style="padding: 20px 30px 40px 60px;" width="600">
                ';
$body_html .= '
                        <p>Dear $contact_first_name,</p>
<p>I have assigned you new opportunities. Please review these opportunities and accept or decline within 24 hours.</p>
<p>You can review immediately by following these simple steps:</p>
<ol>
<li style="margin-bottom: 10px;"><a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal">Login to the Partner Portal</a> using your SugarCRM.com account credentials. </li>
<li style="margin-bottom: 10px;">Once logged in, click on the "Sales" link.</li>
<li style="margin-bottom: 10px;">If you are already logged into the SugarCRM Partner Portal in your browser, you can simply <a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal/sales">follow this link</a> to review your opportunities.</li>
<li style="margin-bottom: 10px;">If the above link does not work, please copy and paste the following URL in your browser - <a style="color: #9D0C0B;" href="http://www.sugarcrm.com/crm/partners/partner_portal/sales">http://www.sugarcrm.com/crm/partners/partner_portal/sales</a>.</li>
</ol>
<p>Best regards, </p><p>'.$current_user->full_name.'<br /> '.$current_user->title.'<br /> '.$current_user->phone_work.'<br /> <a style="color: #9D0C0B;" href="mailto:'.$current_user->email1.'">'.$current_user->email1.'</a><br /> SugarCRM Inc.</p>
                ';
$body_html .= '</td></tr></tbody></table>';
?>
