<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

/**
 * This file is here to provide a HTML template for the rest help api.
 */
?>
<!DOCTYPE HTML>
<html>
<head>
<title>SugarCRM Auto Generated API Help</title>
<style type="text/css">
    table#endpointList {
        width: 100%;
        border-collapse: collapse;
    }
    .hidden {
        display: none;
    }
    .endpointMain {
        background: #eeeeee;
    }
    code {
        white-space: pre;
        height: 150px;
        overflow-x: scroll;
        display: inline-block;
        background: #eeeeff;
    }
    .params table, .params table td, .params table th {
        border: 1px solid #000000;
        border-collapse: collapse;
    }
    .params table {
        empty-cells: show;
    }
    .params table th {
        background-color: #efefef;
    }
    .params table th, .params table td {
        padding: 2px 4px;
    }
    .codesample .note {
        background: #ffffff;
        font-style: italic;
    }
    .showHide {
        cursor: pointer;
        padding: 0 3px;
        text-align: center;
        width: 20px;
    }

</style>
<script type="text/javascript" src="<?php echo SugarConfig::get('site_url') ?>/include/javascript/jquery/jquery.js"></script>
</head>

<body>
<h1>SugarCRM API</h1>

<table id="endpointList" border="1" cellspacing="0" cellpadding="2">
<?php
  foreach ( $endpointList as $i => $endpoint ) {
      if ( empty($endpoint['shortHelp']) ) { continue; }
?>
  <tr id="endpoint_<?php echo $i ?>" class="endpointMain">
    <td class="showHide" id="showHide<?php echo $i ?>">+</td>
    <td class="reqType"><?php echo htmlspecialchars($endpoint['reqType']) ?></td>
    <td class="fullPath"><?php echo htmlspecialchars($endpoint['fullPath']) ?></td>
    <td class="shortHelp"><?php echo htmlspecialchars($endpoint['shortHelp']) ?></td>
    <td class="score"><?php echo sprintf("%.02f",$endpoint['score']) ?></td>
  </tr>
  <tr id="endpoint_<?php echo $i ?>_full" class="endpointExtra hidden">
    <td class="empty">&nbsp;</td>
    <td class="fullHelp" colspan="4">
      <?php
      if ( file_exists($endpoint['longHelp']) ) {
          echo file_get_contents($endpoint['longHelp']);
      } else if ( !empty($endpoint['longHelp']) ) {
          echo 'Long help file not found: ' . htmlspecialchars($endpoint['longHelp']);
      } else {
          echo 'No additional help.';
      }
      ?>
      <hr>
      <b>File:</b><?php echo $endpoint['file']; ?><br>
      <b>Method:</b><?php echo $endpoint['method']; ?><br>
    </td>
  </tr>
<?php
  }
?>
</table>
<script type="text/javascript">
    $(function() {
        $('.showHide').click(function() {
            var id = $(this).attr('id').replace('showHide', '');
            var currentSign = $(this).text();
            var newSign = currentSign == '+' ? '-' : '+';
            $('#endpoint_' + id + '_full').toggle();
            $(this).text(newSign);
        });
    });
</script>
</body> </html>
