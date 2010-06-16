{*

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */



*}
{literal}
<style type="text/css">
<!--

.title {
	font-family: Georgia, "Times New Roman", Times, serif;
	font-size: 16px;
	font-weight: bold;
	color: #333333;
}
.colHdr{
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #FFFFFF;
	background-color: #999999;	
}
table {
	padding: 3px;
	border: thin dashed #CCCCCC;
}

div {
	padding: 4px;
}
-->
</style>
{/literal}
<p>
<span id='{$MOD.LBL_SALES_REPORT_NONE}'>
<div id='emailBody'>
	<div id='emailWelcome'>
	{$MOD.LBL_SALES_REPORT_HELLO} {$USRNAME}, <br>
	
	<p>{$MOD.LBL_SALES_REPORT_BLURB1}</p>
	</div>

	<div id='notUsed30'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_NOT_USED_IN_30}</span>		
			<table width='100%'>
				<tr class='colHdr'>
					<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
		    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
		    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
		    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
		    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
		    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
		    	</tr>					
		{if !empty($notUsed30)}
				{foreach from=$notUsed30 key='key' item='value'}
			    	<tr >
			    		<td>{$value.name}</td>
			    		<td>{$value.account}</td>
			    		<td>{$value.expires}</td>
			    		<td>{$value.version}</td>
			    		<td>{$value.users}</td>
			    		<td>{$value.last_used}</td>
			    	</tr>						
			    {/foreach}
	    {else}	    
		   <tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
		{/if}
		 </table>
	</div>

	<div id='expiredPaid'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_EXPIRED_PAID}	</span>	
		<table width='100%'>
			<tr class='colHdr'>
				<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
	    	</tr>			
		{if !empty($expiredPaid)}
			{foreach from=$expiredPaid  item='value'}
		    	<tr>
		    		<td>{$value.name}</td>
		    		<td>{$value.account}</td>
		    		<td>{$value.expires}</td>
		    		<td>{$value.version}</td>
		    		<td>{$value.users}</td>
		    		<td>{$value.last_used}</td>
		    	</tr>			
			{/foreach}
	    {else}	    
	    <tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
		{/if}
	</table>
	</div>	

	<div id='expired30'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_EXPIRES_IN_30}		</span>
		<table width='100%'>
			<tr class='colHdr'>
				<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
	    	</tr>			
		{if !empty($expired30)}
			{foreach from=$expired30 key='key' item='value'}
		    	<tr>
		    		<td>{$value.name}</td>
		    		<td>{$value.account}</td>
		    		<td>{$value.expires}</td>
		    		<td>{$value.version}</td>
		    		<td>{$value.users}</td>
		    		<td>{$value.last_used}</td>
		    	</tr>			
			{/foreach}
	    {else}	    
			<tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
		{/if}
	</table>
	</div>
	
	<div id='expired90'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_EXPIRED_IN_90}	</span>
		<table width='100%'>
			<tr class='colHdr'>
				<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
	    	</tr>					
		{if !empty($expired90)}
			{foreach from=$expired90 key='key' item='value'}
		    	<tr>
		    		<td>{$value.name}</td>
		    		<td>{$value.account}</td>
		    		<td>{$value.expires}</td>
		    		<td>{$value.version}</td>
		    		<td>{$value.users}</td>
		    		<td>{$value.last_used}</td>
		    	</tr>			
			{/foreach}
	    {else}	    
		   <tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
		{/if}
	</table>
	</div>

	<div id='expiredEvals'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_EXPIRED_EVALS}</span>	
		<table width='100%'>
			<tr class='colHdr'>
				<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
	    	</tr>					
		{if !empty($expiredEvals)}
			{foreach from=$expiredEvals key='key' item='value'}
		    	<tr>
		    		<td>{$value.name}</td>
		    		<td>{$value.account}</td>
		    		<td>{$value.expires}</td>
		    		<td>{$value.version}</td>
		    		<td>{$value.users}</td>
		    		<td>{$value.last_used}</td>
		    	</tr>						
			{/foreach}
	    {else}	    
		   <tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
		{/if}
	</div>
	</table>
	<div id='expiredEvals7'>
		<span class='title'>{$MOD.LBL_SALES_REPORT_TITLE_EXPIRED_EVALS_IN_7}	</span>
		<table width='100%'>
			<tr class='colHdr'>
				<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_NAME}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_ACC}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_EXP}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_VER}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_USR}</span></td>
	    		<td><span class='colHdr'>{$MOD.LBL_SALES_REPORT_COL_LAST}</span></td>
	    	</tr>					
			{if !empty($expiredEvals7)}
					{foreach from=$expiredEvals7 key='key' item='value'}
				    	<tr>
				    		<td>{$value.name}</td>
				    		<td>{$value.account}</td>
				    		<td>{$value.expires}</td>
				    		<td>{$value.version}</td>
				    		<td>{$value.users}</td>
				    		<td>{$value.last_used}</td>
				    	</tr>						
					{/foreach}
		    {else}	    
			   <tr><td colspan='6'>{$MOD.LBL_SALES_REPORT_NONE}</td></tr>
			{/if}
		</table>		    
	</div>

	<div id='emailBye'>	</div>	
<span id='{$MOD.LBL_SALES_REPORT_NONE}'>









</div>