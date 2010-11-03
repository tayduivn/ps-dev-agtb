<script language="javascript" type="text/javascript">
<!--
function submitbutton() {
  var form = document.referenceform;
  var email_pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/i;

  if (form.fname.value == "") {
				alert( "Please provide your first name" );
				form.fname.focus();
				return false;
  }
  if (form.lname.value == "") {
				alert( "Please provide your last name" );
				form.lname.focus();
				return false;
  }
  if (!email_pattern.test(form.email.value)) {
				alert( "Please enter a properly formatted email address" );
				form.email.focus();
				return false;
  }
}
// -->
</script>
<table cellspacing="3" cellpadding="2" border=0 width="350px;" >
<?php
if(isset($_POST[submit]))
{
include_once 'phpmailer/class.phpmailer.php';
$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host = "mail.sugarcrm.com"; // SMTP server
$mail->SMTPAuth = true;
$mail->Username = "salesportal@sugarcrm.com";
$mail->Password = "bIhfxB1Kg";

      $fromemail = $_POST[email];
      $fromname =   $_POST[fname]." ".$_POST[lname];
      $account = $_POST[account];
      $contact = $_POST[contact];
      $deployment = $_POST[deployment];
      $users = $_POST[users];
      $industry = $_POST[industry];
      $comments = $_POST[comments];

      $message = "\nAccount: $account";
			$message .= "\nContact name: $contact";
      $message .= "\nIndustry: $industry";
      $message .= "\nNo. of users: $users";
      $message .= "\nDeployment Option: $deployment";
      $message .= "\nComments: $comments";

$mail->From = $fromemail;
$mail->FromName = $fromname;
$mail->AddAddress("cbeasty@sugarcrm.com", "Collin Beasty");
$mail->AddCC("deepali@sugarcrm.com", "Deepali Mittal");
$mail->AddCC($fromemail, $fromname);

$mail->Subject = "Sales reference submitted by $fromname";
$mail->Body = $message;

if($mail->Send())
{ echo "<tr><td colspan='2' style='text-align:center;color:red;padding-bottom:10px;'><b>Your reference has been submitted.</b></td></tr>"; }
else
{ echo "<tr><td colspan='2' style='text-align:center;color:red;padding-bottom:10px;'><b>Your reference could not be submitted.</b></td></tr>"; }

}
?>

<form method="post" action="" name="referenceform" onsubmit="return submitbutton();">

  <tr><td colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc;padding-top:5px;padding-bottom:5px;background-color:#fafafa">&nbsp;<b>Your Information</b></td></tr>

  <tr>
    <td style="padding-top:10px;"><span style="color:red;">*</span> First name </td><td style="padding-top:10px;"><input type="text" name="fname" size="20" /></td>
  </tr>
  <tr>
    <td><span style="color:red;">*</span> Last name </td><td><input type="text" name="lname" size="20" /></td>
  </tr>
  <tr>
    <td style="padding-bottom:10px;"><span style="color:red;">*</span> Email </td><td style="padding-bottom:10px;"><input type="text" name="email" size="20" /></td>
  </tr>

  <tr><td colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc;padding-top:5px;padding-bottom:5px;background-color:#fafafa;">&nbsp;<b>Reference Information</b></td></tr>
  <tr>
    <td style="padding-top:10px;">Account </td><td style="padding-top:10px;"><input type="text" name="account" size="20" /></td>
  </tr>
  <tr>
    <td>Contact name </td><td><input type="text" name="contact" size="20"  /></td>
  </tr>
  <tr>
    <td>Deployment option</td><td>
     <select name="deployment">
      <option value="">- Select one -</option>
      <option value="Professional On-Demand">Professional On-Demand</option>
      <option value="Professional On-Site">Professional On-Site</option>
      <option value="Enterprise On-Demand">Enterprise On-Demand</option>
      <option value="Enterprise On-Site">Enterprise On-Site</option>
      </select>
    </td>
  </tr>
  <tr>
    <td># of users</td><td>
    <select name="users">
      <option value="">- Select one -</option>
        <option value="1-4">1-4</option>
				<option value="5-10">5-10</option>
				<option value="11-25">11-25</option>
				<option value="26-49">26-49</option>
				<option value="50-99">50-99</option>
				<option value="100-499">100-499</option>
				<option value="500-999">500-999</option>
				<option value="more than 1000">more than 1000</option>
    </select>
    </td>
  </tr>
  <tr>
    <td>Industry</td><td>
    <select name="industry">
      <option value="">- Select one -</option>
      <option value="Technology">Technology</option>
		  <option value="Education">Education</option>
		  <option value="Financial Services">Financial Services</option>
		  <option value="Government and Public Sector">Government and Public Sector</option>
		  <option value="Healthcare">Healthcare</option>
    	<option value="Manufacturing">Manufacturing</option>
		  <option value="Media">Media</option>
		  <option value="Pharmaceutical">Pharmaceutical</option>
      <option value="Real Estate">Real Estate</option>
		  <option value="Retail and Consumer Goods">Retail and Consumer Goods</option>
		  <option value="Services">Services</option>
		  <option value="Shipping and Transportation">Shipping and Transportation</option>
		  <option value="Travel and Leisure">Travel and Leisure</option>
		  <option value="Telecommunications">Telecommunications</option>
		  <option value="Utilities">Utilities</option>
      <option value="High Tech Manufacturing">High Tech Manufacturing</option>
      <option value="Semiconductor">Semiconductor</option>
		  <option value="Other">Other</option>
    </select>
    </td>
  </tr>
  <tr>
    <td valign="top">Comments</td><td><textarea name="comments" rows=3 cols=17></textarea></td>
  </tr>
  <tr><td>&nbsp;</td><td style="padding-top:10px;"><input type="submit" name="submit" value="  Submit  " /></td></tr>
</form>

</table>
<p></p>
<p></p>
<p style="padding-top:10px;border-top:1px solid #ccc; padding-top:10px;padding-bottom:5px;width:80%;text-align:center;">&nbsp;<b>Q2 Reference Submissions</b></p>
<table cellspacing="0" cellpadding="0" border=0 width="80%" class="dataTable">
<tbody>

   <tr>
   <th></th><th style="text-align:center">Reference #1</th><th style="text-align:center">Reference #1</th><th style="text-align:center">Bonus Reference</th>
   </tr>
   <tr><td style="font-weight:bold;">Richard Baldwin</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Jeff Campbell</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;">Anil Chaudhry</td><td>ICAT</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Dan Cronin</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;">Drew Currie</td><td>PEC USA</td><td>Virtual Subsidiary</td><td>&nbsp;</td></tr>
   <!-- <tr><td style="font-weight:bold;" id="what2">David Djanikian</td><td id="what2">&nbsp;</td><td id="what2">Email Systems</td><td id="what2">&nbsp;</td></tr>
   -->
   <tr><td style="font-weight:bold;">Reggy Hardy</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Cameron Jackson</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <!-- <tr><td style="font-weight:bold;">Kevin Jordan</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>   -->
   <tr><td style="font-weight:bold;" id="what2">Shane Karlin</td><td id="what2">Venstar Exchange</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;">James Nakamura</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Craig Parker</td><td id="what2">HR Carolina</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;">Isabel Sarkis</td><td>Fundraiser Software</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Liz Smith</td><td id="what2">SACE</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" >Chris Spangler</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Dean Warshawsky</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Ursula Rhett-Hughes</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Vince Randazzo</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">David Gearhert</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Kelly Bagely</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
   <tr><td style="font-weight:bold;" id="what2">Maureen Jackson</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td><td id="what2">&nbsp;</td></tr>
</tbody>
</table>
