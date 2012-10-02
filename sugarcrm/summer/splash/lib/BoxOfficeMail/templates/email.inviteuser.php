<?php

$subject = 'You\'ve been invited to Sugar by SugarCRM';


$message = <<<EOQ


<table cellspacing="0" cellpadding="0" border="0" width="600" style="background-color: #333; font-family: helvetica, arial, sans serif; padding: 40px 50px 50px 50px">
<tr>
 <td>
   <h1 style="color: #fff; font-size: 18px; padding: 10px 0; border-bottom: 4px solid #2288B5;">{$user['first_name']} {$user['last_name']} has invited to try out the SugarCRM.</h1>
 </td>
</tr>
<tr>
 <td style="padding: 10px 0; font-size: 15px; color: #fff; font-size: 15px;">
     <p>SugarCRM is the modern productivity application that helps you manage your customers and deals.
     In short, it makes getting your job done <strong style="color: #ff9900;">easier</strong>.</p>

     <p>
       SugarCRM offers:
     </p>
     <ul style="list-style: none; padding: 0;">
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">Customer Management</li>
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">Snappy and Intuitive User Interface</li>
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">Seamless Collaboration</li>
     </ul>

     Come check it out @ <a style="font-weight: bold; color: #2288B5; text-decoration: none;" onmouseover="this.style.color='#ff9900'" onmouseout="this.style.color='#2288B5'" href="{$url}">{$url}</a>.
     <br>
     Please register and login with this email address and choose instance <strong style="color: #ff9900;">{$instance['name']}</strong>.

   </div>
 </td>
</table>

EOQ;



$txt = <<<EOQ
{$user['first_name']} {$user['last_name']} has invited to try out the Sugar by SugarCRM.

SugarCRM is the modern productivity application that helps you manage your customers and deals.

In short, it makes getting your job done easier.

Come check it out at {$url}.

Please register and login with this email address and choose instance {$instance['name']}.



EOQ;
