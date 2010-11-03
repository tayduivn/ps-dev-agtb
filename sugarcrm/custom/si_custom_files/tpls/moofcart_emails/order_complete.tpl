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
  <table border="0" cellspacing="0" cellpadding="0" width="600" align="center" class=
  "c22">
    <tbody>
      <tr>
        <td colspan="2"><a class="c1" href="http://www.sugarcrm.com"><img src=
        "http://media.sugarcrm.com/newsletter/SugarCRMheader.jpg" border="0" alt=
        "SugarCRM" width="600" height="200" /></a></td>
      </tr>

      <tr>
        <td colspan="2" class="c21">
          <p>Thank you for your order with SugarCRM.</p>

          <h1 class="c2">Your Purchase</h1>
		  <!-- BEGIN: products -->
          <table border="0" cellspacing="0" cellpadding="0" class="c6">
            <tbody>
              <tr>
                <td valign="top" class="c4"><strong class="c3">Product:</strong></td>

                <td valign="top" class="c4"><strong class="c5">{product.name}</strong></td>
              </tr>

              <tr>
                <td valign="top" class="c4"><strong class="c3">Quantity:</strong></td>

                <td valign="top" class="c4">{product.quantity}</td>
              </tr>

              <tr>
                <td valign="top" class="c4"><strong class="c3">Product expiration
                date:<br /></strong></td>

                <td valign="top" class="c4">{product.date_support_expires}</td>
              </tr>
            </tbody>
          </table>
		  <!-- END: products -->
          <p>An invoice for this purchase has been emailed to you.</p>

          <h1 class="c7">Accessing Your Product</h1>

          <p>You can use your Sugar product either On-Demand (managed on our servers) or
          download the product onto your servers. Below are instructions for accessing
          the product On-Demand or On-Site, as well as instructions for downloading
          <a class="c1" href="#plugin">Sugar Plug-Ins for Microsoft Office</a>.</p>

          <div class="c16">
            <h2 class="c8">On-Demand Access</h2>

            <table border="0" cellspacing="0" cellpadding="0" class="c6">
              <tbody>
                <tr>
                  <td valign="top" class="c4"><strong class="c3">Unique
                  URL:</strong></td>

                  <td valign="top" class="c4"><a href=
                  "http://{order.ondemand_instance_name_c}.sugarondemand.com">http://{order.ondemand_instance_name_c}.sugarondemand.com</a><br />
                  </td>
                </tr>
	<!--
                <tr>
                  <td valign="top" class="c4"><strong class="c3">Admin user
                  name:</strong></td>

                  <td valign="top" class="c4">{on_demand.admin_user}</td>
                </tr>

                <tr>
                  <td valign="top" class="c9"><strong class="c3">Admin
                  password:<br /></strong></td>

                  <td valign="top" class="c9">{on_demand.password}</td>
                </tr>
	-->
              </tbody>
            </table>

            <h2 class="c8">On-Site Access</h2>

            <p>You can download Sugar onto your servers through the SugarCRM Support
            Portal.</p>

            <table border="0" cellspacing="0" cellpadding="0" class="c15">
              <tbody>
                <tr>
                  <td valign="top" class="c11"><strong class="c10">STEP 1</strong></td>

                  <td valign="top" class="c12">Go to the SugarCRM Support Portal:
                  <a class="c1" href=
                  "http://support.sugarcrm.com">http://support.sugarcrm.com</a></td>
                </tr>

                <tr>
                  <td valign="top" class="c11"><strong class="c10">STEP 2</strong></td>

                  <td valign="top" class="c13">Login using your www.sugarcrm.com user
                  name and password.<br />
                  Username: {contact.portal_name} (<a class="c1" href=
                  "http://www.sugarcrm.com/crm/user/password">Forgotten your
                  password?</a>)</td>
                </tr>

                <tr>
                  <td valign="top" class="c11"><strong class="c10">STEP 3</strong></td>

                  <td valign="top" class="c13">Click on the <em>Download Purchased
                  Software</em> link in the Support Portal or<br />
                  Go directly to: <a class="c1" href=
                  "http://www.sugarcrm.com/sugarshop/download">http://www.sugarcrm.com/sugarshop/download</a></td>
                </tr>

                <tr>
                  <td valign="top" class="c11"><strong class="c10">STEP 5</strong></td>

                  <td valign="top" class="c13">Click on the zip file which you want to
                  access.</td>
                </tr>
              </tbody>
            </table>

            <p>If you wish to allow someone else to access the Sugar files you have
            purchased, submit a Support Case. Please be sure to include the SugarCRM.com
            username in your request.</p>
          </div><a name="plugin" title="plugin" id="plugin"></a>

          <h1 class="c7">Sugar Plug-Ins for Microsoft Office</h1>

          <p>You can access your purchased plug-ins, such as the Outlook Plug-in through
          the SugarCRM Support Portal.</p>

          <table border="0" cellspacing="0" cellpadding="0" class="c6">
            <tbody>
              <tr>
                <td valign="top" class="c11"><strong class="c10">STEP 1</strong></td>

                <td valign="top" class="c12">Go to the SugarCRM Support Portal: <a class=
                "c1" href=
                "http://support.sugarcrm.com">http://support.sugarcrm.com</a></td>
              </tr>

              <tr>
                <td valign="top" class="c11"><strong class="c10">STEP 2</strong></td>

                <td valign="top" class="c13">Login using your www.sugarcrm.com user name
                and password.<br />
                Username: {contact.portal_name} (<a class="c1" href=
                "http://www.sugarcrm.com/crm/user/password">Forgotten your
                password?</a>)</td>
              </tr>

              <tr>
                <td valign="top" class="c11"><strong class="c10">STEP 3</strong></td>

                <td valign="top" class="c13">Click on the <em>Download Purchased
                Software</em> link in the Support Portal or<br />
                Go directly to: <a class="c1" href=
                "http://www.sugarcrm.com/sugarshop/downloads.php">http://www.sugarcrm.com/sugarshop/downloads.php</a></td>
              </tr>

              <tr>
                <td valign="top" class="c11"><strong class="c10">STEP 4</strong></td>

                <td valign="top" class="c13">
                  Enter the download key below into the <em>Download Key</em> form field.
                  <em>(This field won't be displayed if you have purchased one
                  product.)</em>

                  <p class="c17"><strong class="c3">Download key:</strong> &nbsp;
                  {product.download_key}</p>Click on <em>Submit</em>.
                </td>
              </tr>

              <tr>
                <td valign="top" class="c11"><strong class="c10">STEP 5</strong></td>

                <td valign="top" class="c13">Click on the zip file which you want to
                access.</td>
              </tr>
            </tbody>
          </table>

          <p>If you wish to allow someone else to access the Sugar files you have
          purchased, submit a Support Case. Please be sure to include the SugarCRM.com
          username in your request.</p>

          <h1 class="c7">Resources</h1>

          <ul>
            <li class="c18"><a class="c1" href="http://support.sugarcrm.com">Support
            Portal</a> is where you can submit support cases.</li>

            <li class="c18"><a class="c1" href=
            "http://www.sugarcrm.com/crm/user_documentation">Sugar Documentation</a>
            helps users and administrators get up to speed.</li>

            <li class="c18"><a class="c1" href=
            "http://www.sugarcrm.com/crm/university">Sugar University</a> provides online
            instruction for end users and administrators.</li>

            <li class="c18"><a class="c1" href=
            "http://www.sugarcrm.com/wiki/index.php?title=Sugar_Support_Wiki">Support
            Wiki</a> is a knowledgebase containing tips and tricks, troubleshooting steps
            and other information.</li>

            <li class="c18"><a class="c1" href=
            "http://www.sugarcrm.com/wiki/index.php?title=Sugar_Developer_Wiki">Developer
            Wiki</a> is a knowledgebase for extending and customizing Sugar.</li>
          </ul>

          <div class="c19">
            <br />
          </div>

          <p>This Order is governed by the Subscription Agreement agreed upon by Company,
          unless it is rejected by SugarCRM. No other terms and conditions shall apply.
          Please keep this email for future reference.</p>

          <p>Again, thank you for becoming a SugarCRM customer.</p>

          <p><strong>SugarCRM Inc.</strong></p>

          <p><strong class="c20">Tel:</strong> +1 408-454-6940<br />
          <strong class="c20">Fax:</strong> +1 408-877-1816<br />
          <strong class="c20">URL:</strong> <a class="c1" href=
          "http://www.sugarcrm.com">www.sugarcrm.com</a><br />
          <strong class="c20">E-mail:</strong> <a class="c1" href=
          "mailto:sales@sugarcrm.com">sales@sugarcrm.com</a></p>
        </td>
      </tr>
    </tbody>
  </table>
</body>
</html>
<!-- END: main -->
