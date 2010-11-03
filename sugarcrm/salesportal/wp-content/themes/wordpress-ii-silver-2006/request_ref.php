<script language="javascript" type="text/javascript">
<!--
function submitbutton() {
  var form = document.referenceform;
  var email_pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/i;

  if (form.yourname.value == "") {
				alert( "Please provide your name" );
				form.yourname.focus();
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
<table cellspacing="0" cellpadding="0" border=0 width="100%" >
<tr><td style="width:350px;">
<table cellspacing="3" cellpadding="2" border=0 width=""100%>
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
      $fromname =   $_POST[yourname];
      $opportunity = $_POST[opportunity];
      $deployment = $_POST[deployment];
      $users = $_POST[users];
      $industry = $_POST[industry];
      $competitor = $_POST[competitor];
      $sales_stage = $_POST[sales_stage];
      $timeframe = $_POST[timeframe];
      $comments = $_POST[comments];

      $message = "Opportunity: $opportunity";
      $message .= "\nIndustry: $industry";
      $message .= "\n# of users: $users";
      $message .= "\nDeployment Option: $deployment";
      $message .= "\nCompetitor: $competitor";
      $message .= "\nSales stage: $sales_stage";
      $message .= "\nTime frame: $timeframe";
      $message .= "\nComments: $comments";

  $mail->From = $fromemail;
  $mail->FromName = $fromname;
  $mail->AddAddress("cbeasty@sugarcrm.com", "Collin Beasty");
  $mail->AddAddress("charrick@sugarcrm.com", "Chris Harrick");
  $mail->AddCC("deepali@sugarcrm.com", "Deepali Mittal");
  $mail->AddCC($fromemail, $fromname);

  $mail->Subject = "Request for a Sales reference by $fromname";
  $mail->Body = $message;

      if($mail->Send())
      { echo "<tr><td colspan='2' style='text-align:center;color:red;padding-bottom:10px;'><b>Your request has been submitted.</b></td></tr>"; }
      else
      { echo "<tr><td colspan='2' style='text-align:center;color:red;padding-bottom:10px;'><b>Your request could not be submitted.</b></td></tr>"; }

}
?>
<form method="post" action="" name="referenceform" onsubmit="return submitbutton();">

  <tr><td colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc;padding-top:5px;padding-bottom:5px;background-color:#fafafa">&nbsp;<b>Your Information</b></td></tr>

  <tr>
    <td style="padding-top:10px;"><span style="color:red;">*</span> Name </td><td style="padding-top:10px;"><input type="text" name="yourname" size="20" /></td>
  </tr>
  <tr>
    <td style="padding-bottom:10px;"><span style="color:red;">*</span> Email </td><td style="padding-bottom:10px;"><input type="text" name="email" size="20" /></td>
  </tr>

  <tr><td colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc;padding-top:5px;padding-bottom:5px;background-color:#fafafa;">&nbsp;<b>Reference Information</b></td></tr>

  <tr>
    <td style="padding-top:10px;">Opportunity </td><td style="padding-top:10px;"><input type="text" name="opportunity" size="20" /></td>
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
    <td>Competitor </td><td><input type="text" name="competitor" size="20" /></td>
  </tr>
  <tr>
    <td>Sales stage </td><td><input type="text" name="sales_stage" size="20" /></td>
  </tr>
   <tr>
    <td>Time frame </td><td><input type="text" name="timeframe" size="20" /></td>
  </tr>
  <tr>
    <td valign="top">Comments</td><td><textarea name="comments" rows=3 cols=17></textarea></td>
  </tr>
  <tr><td>&nbsp;</td><td style="padding-top:10px;"><input type="submit" name="submit" value="Submit Request" /></td></tr>

</form>
</table>

</td>
<td valign="top" style="padding-right:80px;">
  <p valign="top" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc;padding-top:5px;padding-bottom:5px;background-color:#fafafa;">&nbsp;<b>About this feature </b></p>
  <ul>
  <li>Request a Reference is designed to help sales arrange reference calls </li>
  <li>References need to be used strategically (individual references should not be overused) </li>
  <li>Currently, we cannot guarantee that all reference requests will be met</li>
  <li>Preference will be given to reference requests that are larger in deal size and in an advanced sales stage </li>
  <li>When possible, try to use <a href="?page_id=11">reference collateral</a> to satisfy reference requests</li>
  <li>Please position with prospects accordingly</li>
  </ul>
</td>
</tr>
</table>
