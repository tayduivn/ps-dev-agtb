<?php

$subject = $user['first_name'] . ' ' . $user['last_name'] . ' has invited you to try Sugar by SugarCRM';

$message = <<<EOQ

<center>
<table background="{$url}/lib/BoxOfficeMail/templates/img/cubes.png" cellspacing="0" cellpadding="0" border="0" width="800" style="background-color: #F5F5F5; font-family: helvetica, arial, sans serif; padding: 40px 50px 10px 50px;">
<tr>
  <td>
    <img src="{$url}/lib/BoxOfficeMail/templates/img/SugarCRM_logo.png" />
  </td>
</tr>
<tr>
 <td colspan="3">
   <h1 style="color: #333; font-size: 18px; padding: 10px 0; border-bottom: 4px solid #E61718;">{$user['first_name']} {$user['last_name']} has invited you to try Sugar by SugarCRM.</h1>
 </td>
</tr>
<tr>
 <td style="padding: 10px 0; font-size: 15px; color: #333; font-size: 15px; width: 50%; vertical-align: top">
     <p style="margin-top: 0">SugarCRM is the modern productivity application that helps you manage your customers and deals.
     In short, it makes getting your job done <strong style="color: #333;">easier</strong>.</p>
    <center>
        <a style="color: #333; text-decoration: none; cursor:pointer;" href="{$url}">
            <span style="text-decoration: underline; font-weight: bold">Click here to get started</span>
            <img style="padding-top: 10px;" src="{$url}/lib/BoxOfficeMail/templates/img/getstarted.png"/>
        </a>
    </center>
 </td>
  <td style="padding-top: 10px; font-size: 15px; color: #333; font-size: 15px; padding-left: 20px; width: 50%; vertical-align: top">
     <span>SugarCRM offers:</span>
     <ul style="list-style: none; padding: 0; margin-top: 0">
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">
        <img style="position: relative; bottom: 10px; vertical-align:middle" src="{$url}/lib/BoxOfficeMail/templates/img/customer.png"/>
        <span style="margin-left: 5px;">Customer Management</span>
      </li>
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">
        <img style="position: relative; bottom: 10px; vertical-align:middle" src="{$url}/lib/BoxOfficeMail/templates/img/ui.png"/>
        <span style="margin-left: 5px; ">Snappy and Intuitive User Interface</span>
      </li>
       <li style="font-weight: bold; padding: 3px 0; margin: 0;">
        <img style="position: relative; bottom: 10px; vertical-align:middle" src="{$url}/lib/BoxOfficeMail/templates/img/collaboration.png"/>
        <span style="margin-left: 5px; ">Seamless Collaboration</span>
      </li>
     </ul>
 </td>
 </tr>
 <tr>
  <td style="font-size: 15px; color: #333;" colspan="2">
    <p>Please register/login with your email address, and select <strong>{$instance['name']}</strong> to begin using Sugar today!</p>
    <img style="margin-right: 7px; padding-bottom: 30px" src="{$url}/lib/BoxOfficeMail/templates/img/dashboard.png">
    <img style="margin-right: 7px; padding-bottom: 30px" src="{$url}/lib/BoxOfficeMail/templates/img/forecasts.png">
    <img style="padding-bottom: 30px" src="{$url}/lib/BoxOfficeMail/templates/img/contacts.png">
  </td>
 </tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="800" style="background-color: #333; font-family: helvetica, arial, sans serif; padding: 30px 50px 30px 50px; margin: 0 auto;">
  <tr>
    <td style="font-size: 11px; color: #FFF; text-align: center" colspan="2">
      <span>&copy; 2012 &middot; SugarCRM &middot; 10050 North Wolfe Rd, Cupertino, CA</span>
    </td>
  </tr>
</table>
</center>

EOQ;



$txt = <<<EOQ
{$user['first_name']} {$user['last_name']} has invited you to try Sugar by SugarCRM.

SugarCRM is the modern productivity application that helps you manage your customers and deals.

In short, it makes getting your job done easier.

Come check it out at {$url}.

Please register and login with this email address, and choose instance {$instance['name']}.



EOQ;
