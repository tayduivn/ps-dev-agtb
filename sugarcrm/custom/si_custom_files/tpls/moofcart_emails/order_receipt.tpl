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
  <p>Dear {account_name},</p>

  <p>Thank you for your recent order with SugarCRM. Your order below will be processed
  once payment is confirmed, in accordance with SugarCRM policies. 

  <table class="c10" cellspacing="0" cellpadding="0" width="575" border="0" color=
  "#000000">
    <tr>
      <td>
        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr>
            <td valign="top"><span class="c1"><b>ORDER</b></span></td><!-- // end Dee -->
            <td width="100%" align="right"></td><!-- // end Dee-->
          </tr>

          <tr>
            <td colspan="2">
              <table cellspacing="0" cellpadding="2" width="100%" border="0">
                <tr>
                  <td valign="top"><b>Date:</b> {order.date}<br />
                  <b>Order id:</b> #{order.order_id}<br />
                  <b>Order status:</b> {order.status}<br />
                  <b>Payment method:</b><br />
                  {order.payment_method}<br />
                  </td>

                  <td valign="top" align="right"><b>SugarCRM Inc.</b><br />
                  10050 N. Wolfe Rd., Suite SW2-130<br />
                  Cupertino, California, US, 95014<br />
                  Sales Phone: +1 408-454-6940<br />
                  Main Phone: +1 408-454-6900<br />
                  Accounting Fax: +1 408-608-1918<br />
                  Email: accounting@sugarcrm.com<br />
                  <br />
                  <br /></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr>
            <td>&nbsp;&nbsp;</td>
          </tr>

          <tr>
            <td>&nbsp;&nbsp;</td>
          </tr>

          <tr>
            <td>&nbsp;&nbsp;</td>
          </tr>
        </table><br />

        <table cellspacing="0" cellpadding="0" width="45%" border="0">
          <tr>
            <td><b>Company:</b></td>

            <td>{order.company}</td>
          </tr>

          <tr>
            <td nowrap="nowrap"><b>First Name:</b></td>

            <td>{order.first_name}</td>
          </tr>

          <tr>
            <td nowrap="nowrap"><b>Last Name:</b></td>

            <td>{order.last_name}</td>
          </tr>

          <tr>
            <td><b>Phone:</b></td>

            <td>{order.phone}</td>
          </tr>

          <tr>
            <td><b>Email:</b></td>

            <td>{order.email}</td>
          </tr>
        </table><br />

        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr>
            <td width="45%" height="25"><b>Billing Address</b></td>

            <td width="10%">&nbsp;</td>

            <td width="45%" height="25"><b>Shipping Address</b></td>
          </tr>

          <tr>
            <td>&nbsp;&nbsp;</td>

            <td>&nbsp;&nbsp;</td>

            <td>&nbsp;&nbsp;</td>
          </tr>

          <tr>
            <td>&nbsp;&nbsp;</td>
          </tr>

          <tr>
            <td>
              <table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                  <td><b>First Name:</b></td>

                  <td>{order.first_name}</td>
                </tr>

                <tr>
                  <td><b>Last Name:</b></td>

                  <td>{order.last_name}</td>
                </tr>

                <tr>
                  <td><b>Address:</b></td>

                  <td>{order.billing_address}<br /></td>
                </tr>

                <tr>
                  <td><b>City:</b></td>

                  <td>{order.billing_city}</td>
                </tr>

                <tr>
                  <td><b>State:</b></td>

                  <td>{order.billing_state}</td>
                </tr>

                <tr>
                  <td><b>Country:</b></td>

                  <td>{order.billing_country}</td>
                </tr>

                <tr>
                  <td><b>Zip/Postal code:</b></td>

                  <td>{order.billing_zip}</td>
                </tr>
              </table>
            </td>

            <td>&nbsp;</td>

            <td>
              <table cellspacing="0" cellpadding="0" width="100%" border="0">
                <tr>
                  <td><b>First Name:</b></td>

                  <td>{order.first_name}</td>
                </tr>

                <tr>
                  <td><b>Last Name:</b></td>

                  <td>{order.last_name}</td>
                </tr>

                <tr>
                  <td><b>Address:</b></td>

                  <td>{order.shipping_address}<br /></td>
                </tr>

                <tr>
                  <td><b>City:</b></td>

                  <td>{order.shipping_city}</td>
                </tr>

                <tr>
                  <td><b>State:</b></td>

                  <td>{order.shipping_state}</td>
                </tr>

                <tr>
                  <td><b>Country:</b></td>

                  <td>{order.shipping_country}</td>
                </tr>

                <tr>
                  <td><b>Zip/Postal code:</b></td>

                  <td>{order.shipping_zip}</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td colspan="3">&nbsp;</td>
          </tr>
        </table><br />
        <br />

        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr>
            <td align="center"><span class="c3">Products ordered</span></td>
          </tr>
        </table>

        <table cellspacing="0" cellpadding="3" width="100%" border="0" class="c6">
          <tr>
            <th class="c4">Product</th>
            <th class="c4" width="60">Quantity</th>
            <th class="c4" width="60">Total</th>
          </tr>
<!-- BEGIN: products -->
          <tr>
            <td><span class="c5">{product.name}</span></td>
            <td nowrap="nowrap" width="60">{product.quantity}</td>
            <td nowrap="nowrap" width="60" align='right'>${product.total_price}</td>
          </tr>
        <!-- END: products -->
        </table>

        <table cellspacing="0" cellpadding="0" width="100%" border="0">
          <tr>
            <td align="right" width="100%" height="20"><b>Subtotal:</b>&nbsp;</td>

            <td align="right">${order.subtotal}&nbsp;&nbsp;&nbsp;</td>
          </tr>

		  <!-- BEGIN: partner_margin -->
          <tr>
            <td align="right" width="100%" height="20"><b>Margin:</b>&nbsp;</td>

            <td align="right">${order.partner_margin_c}&nbsp;&nbsp;&nbsp;</td>
          </tr>
		  <!-- END: partner_margin -->
		  <!-- BEGIN: discount -->
          <tr>
            <td align="right" width="100%" height="20"><b>Discount:</b>&nbsp;</td>

            <td align="right">${order.discount}&nbsp;&nbsp;&nbsp;</td>
          </tr>
		  <!-- END: discount -->

		  <!-- BEGIN: tax -->
          <tr>
            <td align="right" width="100%" height="20"><b>Tax:</b>&nbsp;</td>

            <td align="right">${order.tax}&nbsp;&nbsp;&nbsp;</td>
          </tr>
		  <!-- END: tax -->		  
          <tr>
            <td class="c2" colspan="2">&nbsp;&nbsp;</td>
          </tr>

          <tr>
            <td class="c7" align="right" height="25"><b>Total:</b>&nbsp;</td>

            <td class="c7" align="right"><b>${order.total}</b>&nbsp;&nbsp;&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td>
        <p class='c8'>Upon submission to SugarCRM, this Order shall become legally
        binding and governed by the Subscription Agreement agreed upon by Company, unless
        it is rejected by SugarCRM. No other terms and conditions shall apply.</p>
      </td>
    </tr>

    <tr>
      <td align="center"><br />
      <br />
      <span class="c9">Thank you for your order!</span></td>
    </tr>
  </table>
  <hr size="1" noshade="noshade" />

  <p class="c11">SugarCRM Inc.<br />
  Phone: +1 408-454-6940<br />
  Fax: +1 408-877-1816<br />
  URL: <a href="http://www.sugarcrm.com/sugarshop/" target=
  "_new">www.sugarcrm.com</a></p>
  </td>
  </tr>
  </table>
</body>
</html>
<!-- END: main -->
