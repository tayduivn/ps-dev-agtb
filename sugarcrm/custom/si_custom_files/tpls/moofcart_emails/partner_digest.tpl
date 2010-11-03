<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta name="generator" content=
  "HTML Tidy for Linux/x86 (vers 11 February 2007), see www.w3.org" />
  <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />

  <title></title>
  <style type="text/css">
/*<![CDATA[*/
  table.c22 {background-color: #FFFFFF; border-bottom: 1px solid #cccccc; border-left: 1px solid #cccccc; border-right: 1px solid #cccccc; font-family: arial,verdana,helvetica,sans-serif; font-size: 12px; line-height: 16px}
  td.c21 {padding: 2px 20px 20px 40px}
  strong.c20 {color: #333333}
  div.c19 {border-bottom: 1px solid #cccccc; width: 200px}
  li.c18 {margin-bottom: 6px}
  p.c17 {border-top: 1px solid #e0e0e0; padding: 5px 5px 8px 15px}
  div.c16 {margin-left: 40px}
  table.c15 {font-family: Arial,Verdana,Helvetica,sans-serif; font-size: 12px; line-height: 16px}
  p.c14 {padding: 5px 5px 8px 15px}
  td.c13 {border-bottom: 1px solid #e0e0e0; padding: 5px 5px 8px}
  td.c12 {border-top: 1px solid #e0e0e0; border-bottom: 1px solid #e0e0e0; padding: 5px 5px 8px}
  td.c11 {padding: 5px 8px 8px 0pt; white-space: nowrap}
  strong.c10 {color: #444444}
  td.c9 {padding: 5px 10px 8px 5px}
  h2.c8 {border-top: 1px solid #dddddd; font-size: 14px; color: #444444; padding-top: 6px}
  h1.c7 {border-bottom: 3px solid #cccccc; font-size: 18px; font-weight: normal; padding-bottom: 5px; color: #333333; margin-top: 40px}
  table.c6 {font-family: Arial,Verdana,Helvetica,sans-serif; font-size: 12px; line-height: 16px; margin-left: 40px}
  strong.c5 {color:#333333}
  td.c4 {border-bottom: 1px solid #e0e0e0; padding: 5px 10px 3px 5px}
  strong.c3 {color: #666666}
  h1.c2 {border-bottom: 3px solid #cccccc; font-size: 18px; font-weight: normal; padding-bottom: 5px; color: #333333; margin-top: 30px}
  a.c1 {color: #9d0c0b}
  /*]]>*/
  </style>
</head>

<body>
  <table border="0" cellspacing="0" cellpadding="0" width="800" align="center" class=
  "c22">
    <tbody>
      <tr>
        <td colspan="2"><a class="c1" href="http://www.sugarcrm.com"><img src=
        "http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt=
        "SugarCRM" width="800" height="200" /></a></td>
      </tr>

      <tr>
        <td colspan="2" class="c21">

          <h1 class="c2">Welcome to SugarCRM! </h1>
		  <br/>
			<p>
			Dear {contact.first_name} {contact.last_name},
			Below are the updates for your opportunities this past week:
			<br/><br/>
			Total Opportunities: {v.total_past_week}
			<br/><br/>
			Opportunities Pending: {v.pending}
			<br/><br/>
			<strong>Opportunity Details:</strong><br/><br/>
			<!-- BEGIN: opportunities -->
			<table border='0' cellpadding='0' cellspacing='0' width='100%' align='center' style="font-family: Arial,Verdana,Helvetica,sans-serif; font-size: 12px;">
				<tr>
					<td><strong>Account/Type</strong></td>
					<td><strong>Location</strong></td>
					<td><strong>Decision Date</strong></td>
					<td><strong>Amount</strong></td>
					<td><strong>Users</strong></td>
					<td><strong>Status</strong></td>
				</tr>
				<!-- BEGIN: opportunity -->
				<tr>
					<td><a href="http://www.sugarcrm.com/crm/partners/partner_portal/sales?task=opportunity_detail&id={o.id}">{a.name}</a><br/>{o.partner_product}</td>
					<td>{a.location}</td>
					<td>{o.date_closed}</td>
					<td>${o.amount}</td>
					<td>{o.users}</td>
					<td>{o.partner_status}</td>
				</tr>
				<!-- END: opportunity -->
			</table>
			<br/><br/>
			<!-- END: opportunities -->
			Thank you,
			<br/><br/>
	          <p><strong>SugarCRM Inc.</strong></p>
	
	          <p><strong class="c20">Tel:</strong> +1 408-454-6940<br />
	          <strong class="c20">Fax:</strong> +1 408-877-1816<br />
	          <strong class="c20">URL:</strong> <a class="c1" href=
	          "http://www.sugarcrm.com">www.sugarcrm.com</a><br />
	          <strong class="c20">E-mail:</strong> <a class="c1" href=
	          "mailto:sales@sugarcrm.com">sales@sugarcrm.com</a></p>
			</p>
        </td>
      </tr>
    </tbody>
  </table>
</body>
</html>
<!-- END: main -->
