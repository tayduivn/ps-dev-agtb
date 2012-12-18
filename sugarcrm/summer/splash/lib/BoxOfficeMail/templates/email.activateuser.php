<?php
$subject = 'Activate Your Sugar by SugarCRM Account';

$message = <<<EOQ


<table background="{$config['top_url']}summer/splash/lib/BoxOfficeMail/templates/img/cubes.png" cellspacing="0" cellpadding="0" border="0" width="800" style="background-color: #F5F5F5; font-family: helvetica, arial, sans serif; padding: 40px 50px 10px 50px; margin: 0 auto;">
	<tbody>
		<tr>
			<td><img src="{$config['top_url']}summer/splash/lib/BoxOfficeMail/templates/img/SugarCRM_logo.png" /></td>
		</tr>
		<tr>
			<td><h1 style="color: #333; font-size: 18px; padding: 10px 0; border-bottom: 4px solid #E61718;">Welcome to Sugar!</h1></td>
		</tr>
		<tr>
			<td style="padding: 10px 0; font-size: 15px; color: #333; font-size: 15px;">
				<p>Hello {$user['first_name']} {$user['last_name']}, you are almost ready to start using Sugar.</p>
    			<p>Please click the link below to activate your account, and thank you for joining the Sugar community!</p>
			</td>
		</tr>
		<tr>
			<td>
				<center>
			        <a style="color:#333;text-decoration:none" href="{$config['top_url']}summer/splash/activate?email={$user['email']}&amp;hash={$guid};">
			            <span style="text-decoration:underline;font-weight:bold">Click here to activate your account:</span>
			        </a>
			    </center>
			</td>
		</tr>
		<tr>
			<td>
				<center>
			        <a style="color:#333;text-decoration:none" href="{$config['top_url']}summer/splash/activate?email={$user['email']}&amp;hash={$guid};">
			            <img style="padding-top:10px" src="{$config['top_url']}summer/splash/lib/BoxOfficeMail/templates/img/activate.png">
			        </a>
			    </center>
			</td>
		</tr>
		<tr>
			<td style="padding: 10px 0; font-size: 15px; color: #333; font-size: 15px;">
			    <p>If you have any questions or are having issues activating your account, feel free to contact us anytime at <a href="mailto:gettysburg@sugarcrm.com">gettysburg@sugarcrm.com</a></p>
			    <p>We hope you'll enjoy using Sugar!</p>
			    <p>-The SugarCRM Team</p>
			</td>
		</tr>
	</tbody>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="800" style="background-color: #333; font-family: helvetica, arial, sans serif; padding: 30px 50px 30px 50px; margin: 0 auto;">
  <tr>
    <td style="font-size: 11px; color: #FFF; text-align: center" colspan="3">
      <span>&copy; 2012 &middot; SugarCRM &middot; 10050 North Wolfe Rd, Cupertino, CA</span>
    </td>
  </tr>
</table>

EOQ;




$txt = <<<EOQ
Welcome to Sugar by SugarCRM

Hello {$user['first_name']} {$user['last_name']},

You are almost ready to start using Sugar. Please follow the link below to activate your account!

Thank you for joining the Sugar community!

Click to activate your account @ {$config['top_url']}summer/splash/?do=activate&email={$user['email']}&guid={$guid}

EOQ;
