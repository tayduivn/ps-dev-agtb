<!--
/*********************************************************************************
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
/*********************************************************************************
 * $Id: EditView.html 38464 2008-07-31 19:38:57Z Ajay Gupta $
 ********************************************************************************/
-->
<script type='text/javascript'>
var LBL_LOGIN_SUBMIT = '{sugar_translate module="Users" label="LBL_LOGIN_SUBMIT"}';
var LBL_REQUEST_SUBMIT = '{sugar_translate module="Users" label="LBL_REQUEST_SUBMIT"}';
</script>
<table cellpadding="0" align="center" width="100%" cellspacing="0" border="0">
	<tr>
		<td align="center">
		<div class="dashletPanelMenu" style="width: 460px;">
		<div class="hd"><div class="tl"></div><div class="hd-center"></div><div class="tr"></div></div>
		<div class="bd">
		<div class="ml"></div>
		<div class="bd-center">
			<div class="loginBox">
			<table cellpadding="0" cellspacing="0" border="0" align="center">
				<tr>
					<td align="left"><b>{sugar_translate module="Users" label="LBL_LOGIN_WELCOME_TO"}</b><br>
					    {* //BEGIN SUGARCRM flav=pro && flav!=ent ONLY *}
						<IMG src="include/images/sugar_md.png" alt="Sugar" width="340" height="25">
					    {* //END SUGARCRM flav=pro && flav!=ent ONLY *}
					    {* //BEGIN SUGARCRM flav=sales ONLY
						<IMG src="include/images/sugar_md_sales.png" alt="Sugar" width="340" height="25" style="margin: 5px 0;">
					    {* //END SUGARCRM flav=sales ONLY
					    {* //BEGIN SUGARCRM flav=dev ONLY
						<IMG src="include/images/sugar_md_dev.png" alt="Sugar" width="340" height="25">
					    {* //END SUGARCRM flav=dev ONLY
					    {* //BEGIN SUGARCRM flav=com && lic=sub && flav!=dev ONLY
						<IMG src="include/images/sugar_md_express.png" alt="Sugar" width="340" height="25" style="margin: 5px 0;">
					    {* //END SUGARCRM flav=com && lic=sub && flav!=dev ONLY
					    {* //BEGIN SUGARCRM flav=com && lic!=sub ONLY
						<IMG src="include/images/sugar_md_open.png" alt="Sugar" width="340" height="25" style="margin: 5px 0;">
					    {* //END SUGARCRM flav=com && lic!=sub ONLY
					    {* //BEGIN SUGARCRM flav=dce ONLY
						<IMG src="include/images/sugar_md_dce.png" alt="Sugar" width="340" height="25">
					    {* //END SUGARCRM flav=dce ONLY
					    {* //BEGIN SUGARCRM flav=ent && flav!=dev ONLY
						<IMG src="include/images/sugar_md_ent.png" alt="Sugar" width="340" height="25">
					    {* //END SUGARCRM flav=ent && flav!=dev ONLY *}
					</td>
				</tr>
				<tr>
					<td align="center">
						<div class="login">
							<form action="index.php" method="post" name="DetailView" id="form" onsubmit="return document.getElementById('cant_login').value == ''">
								<table cellpadding="0" cellspacing="2" border="0" align="center" width="100%">
									{if $LOGIN_ERROR !=''}
									<tr>
										<td scope="row" colspan="2"><span class="error">{$LOGIN_ERROR}</span></td>
						    	{if $WAITING_ERROR !=''}
							        <tr>
							            <td scope="row" colspan="2"><span class="error">{$WAITING_ERROR}</span></td>
									</tr>
								{/if}
									</tr>
								{else}
									<tr>
										<td scope="row" width='1%'></td>
										<td scope="row"><span id='post_error' class="error"></span></td>
									</tr>
								{/if}
									<tr>
										<td scope="row" colspan="2" width="100%" style="font-size: 12px; font-weight: normal; padding-bottom: 4px;">
										{sugar_translate label="NTC_LOGIN_MESSAGE"}
										<input type="hidden" name="module" value="Users">
										<input type="hidden" name="action" value="Authenticate">
										<input type="hidden" name="return_module" value="Users">
										<input type="hidden" name="return_action" value="Login">
										<input type="hidden" id="cant_login" name="cant_login" value="">
										<input type="hidden" name="login_module" value="{$LOGIN_MODULE}">
										<input type="hidden" name="login_action" value="{$LOGIN_ACTION}">
										<input type="hidden" name="login_record" value="{$LOGIN_RECORD}">
										</td>
									</tr>
									
                                    <tr><td>&nbsp;</td></tr>
									<tr>
										<td scope="row" width="30%">{sugar_translate module="Users" label="LBL_USER_NAME"}:</td>
										<td width="70%"><input type="text" size='35' tabindex="1" id="user_name" name="user_name"  value='{$LOGIN_USER_NAME}' /></td>
									</tr>
									<tr>
										<td scope="row">{sugar_translate module="Users" label="LBL_PASSWORD"}:</td>
										<td width="30%"><input type="password" size='26' tabindex="2" id="user_password" name="user_password" value='{$LOGIN_PASSWORD}' /></td>
									</tr>
									{if !empty($SELECT_LANGUAGE)}
									
									
									<tr>
									    <td scope="row">{sugar_translate module="Users" label="LBL_LANGUAGE"}:</td>
                                        <td><select style='width: 152px' name='login_language' onchange="switchLanguage(this.value)">{$SELECT_LANGUAGE}</select></td>
									</tr>
                                    <tr><td>&nbsp;</td></tr>
									{/if}
									<tr>
										<td>&nbsp;</td>
										<td><input title="{sugar_translate module="Users" label="LBL_LOGIN_BUTTON_TITLE"}" accessKey="{sugar_translate module="Users" label="LBL_LOGIN_BUTTON_TITLE"}" class="button primary" type="submit" tabindex="3" id="login_button" name="Login" value="{sugar_translate module="Users" label="LBL_LOGIN_BUTTON_LABEL"}"><br>&nbsp;</td>		
									</tr>
								</table>
							</form>
							<form action="index.php" method="post" name="fp_form" id="fp_form" >
								<table cellpadding="0" cellspacing="2" border="0" align="center" width="100%">
									<tr>
										<td colspan="2" class="login_more">
										<div  style="cursor: hand; cursor: pointer; display:{$DISPLAY_FORGOT_PASSWORD_FEATURE};" onclick='toggleDisplay("forgot_password_dialog");'>
											<IMG src="{sugar_getimagepath file='advanced_search.gif'}" border="0" alt="Hide Options" id="forgot_password_dialog_options">
											<a href='javascript:void(0)'>{sugar_translate module="Users" label="LBL_LOGIN_FORGOT_PASSWORD"}</a>
										</div>
											<div id="forgot_password_dialog" style="display:none" >
												<input type="hidden" name="entryPoint" value="GeneratePassword">
												<table cellpadding="0" cellspacing="2" border="0" align="center" width="100%" >
													<tr>
														<td colspan="2">
															<div id="generate_success" class='error' style="display:inline;"> </div>
														</td>
													</tr>
													<tr>
														<td scope="row" width="30%">{sugar_translate module="Users" label="LBL_USER_NAME"}:</td>
														<td width="70%"><input type="text" size='26' id="fp_user_name" name="fp_user_name"  value='{$LOGIN_USER_NAME}' /></td>
													</tr>
													<tr>
											            <td scope="row" width="30%">{sugar_translate module="Users" label="LBL_EMAIL"}:</td>
											            <td width="70%"><input type="text" size='26' id="fp_user_mail" name="fp_user_mail"  value='' ></td>
											     	</tr>
													{$CAPTCHA}
													<tr>
													    <td scope="row" width="30%"><div id='wait_pwd_generation'></div></td>
														<td width="70%"><input title="Email Temp Password" class="button" type="button" style="display:inline" onclick="validateAndSubmit(); return document.getElementById('cant_login').value == ''" id="generate_pwd_button" name="fp_login" value="{sugar_translate module="Users" label="LBL_LOGIN_SUBMIT"}"></td>
													</tr>
												</table>
											</div>
										</td>
									</tr>
								</table>
							</form>
						</div>


					</td>
				</tr>
			</table>
			</div>
			</div>
			<div class="mr"></div>
			</div>
<div class="ft"><div class="bl"></div><div class="ft-center"></div><div class="br"></div></div>
</div>
		</td>
	</tr>
</table>
<br>
<br>