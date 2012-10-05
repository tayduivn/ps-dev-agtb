<?php
$subject = 'Activate Your Sugar by SugarCRM Account';

$message = <<<EOQ


<table cellspacing="0" cellpadding="0" border="0" width="600" style="background-color: #333; font-family: helvetica, arial, sans serif; padding: 40px 50px 50px 50px">
<tr>
 <td>
   <h1 style="color: #fff; font-size: 18px; padding: 10px 0; border-bottom: 4px solid #2288B5;">Welcome to Sugar by SugarCRM</h1>
 </td>
</tr>
<tr>
 <td style="padding: 10px 0; font-size: 15px; color: #fff; font-size: 15px;">
     <p>Hello {$user['first_name']} {$user['last_name']},

         You are almost ready to start using Sugar. Please click the link below to activate your account!

     </P

     <p>
         Thank you for joining the Sugar community!

     </p>

     Click to activate your account @ <a style="font-weight: bold; color: #2288B5; text-decoration: none;" onmouseover="this.style.color='#ff9900'" onmouseout="this.style.color='#2288B5'" href="{$config['top_url']}summer/splash/activate?email={$user['email']}&hash={$guid}">http://sugarcrm.com/summer/splash/?do=activate&email={$user['email']}&guid={$guid}</a>
   </div>
 </td>
</table>

EOQ;




$txt = <<<EOQ
Welcome to Sugar by SugarCRM

Hello {$user['first_name']} {$user['last_name']},

You are almost ready to start using Sugar. Please follow the link below to activate your account!

Thank you for joining the Sugar community!

Click to activate your account @ {$config['top_url']}summer/splash/?do=activate&email={$user['email']}&guid={$guid}

EOQ;
