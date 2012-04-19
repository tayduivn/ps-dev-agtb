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
border-spacing: 0px;
}
</style>
<script type="text/javascript" src="../../include/javascript/jquery/jquery.js"></script>
</head>

<body>
<h1>SugarCRM API</h1>

<table id="endpointList" border=1 cellspacing=0 cellpadding=2>
<?php
  foreach ( $endpointList as $i => $endpoint ) {
      if ( empty($endpoint['shortHelp']) ) { continue; }
?>
  <tr id="endpoint_<?= $i ?>" class="endpointMain">
    <td class="showHide"><a onclick="showHideAction(this); return false;">+</a></td>
    <td class="reqType"><?= htmlspecialchars($endpoint['reqType']) ?></td>
    <td class="fullPath"><?= htmlspecialchars($endpoint['fullPath']) ?></td>
    <td class="shortHelp"><?= htmlspecialchars($endpoint['shortHelp']) ?></td>
    <td class="score"><?= sprintf("%.02f",$endpoint['score']) ?></td>
  </tr>
  <tr id="endpoint_<?= $i ?>_full" class="endpointExtra hidden">
    <td class="empty">&nbsp;</td>
    <td class="fullHelp" colspan=4>
      <?php if ( file_exists($endpoint['longHelp']) ) { ?>
          <?= file_get_contents($endpoint['longHelp']) ?>
      <? } else if ( !empty($endpoint['longHelp']) ) { ?>
          Long help file not found: <?= htmlspecialchars($endpoint['longHelp']) ?>
      <? } else { ?>
          No additional help.
      <? } ?>
      <hr>
      <b>File:</b><?= $endpoint['file'] ?><br>
      <b>Method:</b><?= $endpoint['method'] ?><br>
    </td>
  </tr>
<?php
  }
?>
</table>
<script type="text/javascript">
function showHideAction(elem) {
var showHideParent = $(elem).closest('.endpointMain')[0];
var elementId = showHideParent.id.split("_")[1];

$("#endpoint_"+elementId+"_full").toggle();
}
</script>
</body> </html>
