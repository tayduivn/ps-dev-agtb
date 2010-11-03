<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
/*******************************************************
 * Call Assistant entry point
 * 
 * Created on 30/03/2007
 * Created by: Jeremy Roberts
 * 
 * Modified by Felix Nilam
 * Modified on 21/08/2007
 */

require_once('include/entryPoint.php');
require_once('modules/Users/User.php');
$Config_loginTimeout = 1.5;

session_start();
// use Fonality cookie if it exist
/*if(!empty($_COOKIE['FonalityCA'])){
	session_id($_COOKIE['FonalityCA']);
}

// set Fonality cookie
setcookie("FonalityCA", session_id(), time()+3600);*/

global $current_user;
if (!isset($_SESSION['authenticated_user_id']) && empty($_REQUEST['user_name']) && empty($_REQUEST['user_password'])) { ?>	
	<style>
	body {
		font-family: Verdana,Arial;
		color: #808284;
		font-size: 15px;
		font-weight: normal;
		background-image: url(fonality/include/images/ca_bg.jpg);background-repeat: repeat;
	}
	table td {
		font-size: 15px;
	}
	</style>
	<table width="100%" style="margin-left: auto; margin-right: auto; margin-top: 50;">
	<tr><td>
	<form action='UAECallAssistant.php' method='post' name='inboundcalllogin'>
	<input type="hidden" name="phone" value="<?php echo $_REQUEST['phone']?>" />
	<input type="hidden" name="direction" value="<?php echo $_REQUEST['direction']?>" />
	<table style="width: 400px; background-color: white; border: 2px solid gray; background-image:url(fonality/include/images/login_background.gif)" align='center' style='margin-top:50px;'>
	    <?php
	    if (isset($_REQUEST['message']) && !empty($_REQUEST['message'])) {
	    ?>
	    <tr>
	    	<td width="15%">&nbsp;</td>
	    	<td style="text-align:center; color:#FF6500; font-weight:bold; font-size:15px; margin-top:50px" colspan="3"><?php echo urldecode($_REQUEST['message']);?></td>
	    	<td width="15%">&nbsp;</td>
	    </tr>
	    <?php
	    }
	    ?>
	    <tr>
	    	<td width="15%">&nbsp;</td>
	    	<td align='center' colspan='3'><img src="fonality/include/images/fonality.gif" /></td>
	    	<td width="15%">&nbsp;</td>
	    </tr>
	    <tr>
	    	<td>&nbsp;</td>
            <td style='text-align:center;' colspan='3'><h4>Call Assistant Login</h4></td>
            <td>&nbsp;</td>
	    </tr>
	    <tr id='user_name'>
	    	<td>&nbsp;</td>
            <td><img src="fonality/include/images/login_username.gif"></td>
            <td><input type='text' name='user_name' value='<?=$_REQUEST['user'];?>' size='13'/></td>
	   		<td><input type='submit' name='login' value='Login' /></td>
	   		<td>&nbsp;</td>
	    </tr>
	    <tr id='user_password'>
            <td>&nbsp;</td>
            <td><img src="fonality/include/images/login_password.gif"></td>
            <td><input type='password' name='user_password' value='<?=$_REQUEST['pass'];?>' size='13'/></td>
	    	<td>&nbsp;</td>
	    	<td>&nbsp;</td>
	    </tr>
	    <tr>
	    	<td colspan="5">&nbsp;</td>
	    </tr>
	</table>
	</form>
	</td></tr>
	</table>

<?php
} else {
	$_REQUEST['login_module'] = 'Calls';
	$_REQUEST['login_action'] = 'UAECallAssistant&phone=' .urlencode($_REQUEST['phone']). '&direction=' .$_REQUEST['direction'];
	$authController = new AuthenticationController();
	
	// set the session unique key
	$_SESSION['unique_key'] = $sugar_config['unique_key'];
	include('modules/Users/Authenticate.php');
}
?>
