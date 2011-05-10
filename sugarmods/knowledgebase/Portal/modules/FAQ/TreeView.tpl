{literal}
<style type="text/css">
table.smplTable {
	margin: 0px;
	padding: 0px;
	background-color: #ffffff;
	}

table.smplTable th {
	font-family: Arial, Verdana, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: bold;
	height: 18px;
	line-height: 18px;
	text-align: left;
	white-space: nowrap;
	vertical-align : top;
	}

table.smplTable td {
	font-family: Arial, Verdana, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	padding-left: 0px;
	padding-right: 4px;
	padding-bottom: 4px;
	padding-top: 4px;
	vertical-align : top;
	border-bottom: solid 1px #dddddd;
	}
	
a.smplTable:link, a.smplTable:visited, a.smplTable:link, a.smplTable:visited {color: #9D0C0B; font-family: Arial, Verdana, Helvetica, sans-serif;
	text-decoration: underline;
	}

table.smplTablee {
	margin: 0px;
	padding: 0px;
	background-color: #ffffff;
	}

table.smplTablee td {
	font-family: Arial, Verdana, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	line-height: 15px;
	padding-left: 4px;
	padding-right: 4px;
	vertical-align : top;
	border: solid 0px #FFFFFF;
	}
</style>
{/literal}
<a name="top"></a>
<table width="600" cellpadding="0" cellspacing="0" class="smplTable">
<tr align="left" valign="top">
<td>
{assign var="section" value="0"}
{foreach name=outer key=outerkey item=outeritem from=$tree}
    {* Do Not Print Heading for root 'FAQs' item *}
    <a name="{$outerkey}"></a>
    {if $outerkey neq 'FAQs'}
    <h4>{$outerkey}</h4>
    {/if}
    
    {* Now loop through each category and display list of documents *}
	<ul>
	{foreach key=key item=item from=$outeritem}
    <li><a href="#{$section}_{$item.doc_id}">{$item.doc_name}</a>
	{/foreach}
	</ul>
	{assign var="section" value="`$section+1`"}
{/foreach}

</td>
</tr>

{assign var="section" value="0"}
{foreach name=outer key=outerkey item=outeritem from=$tree}
    {* Do Not Print Heading for root 'FAQs' item *}
    {if $outerkey neq 'FAQs'}
    <tr align="left" valign="top"><th>{$outerkey}</th></tr>
    {/if}
    
    {* Now loop through each category and display list of documents *}
	{foreach item=item from=$outeritem}
	<tr align="left" valign="top">
    <td>
    <a name="{$section}_{$item.doc_id}"></a><h5>{$item.doc_name}</h5>
    {$document_contents[$item.doc_id]}
    
    {if $document_attachments[$item.doc_id]}
    <p style="margin-bottom: 0px; text-align: left;">
       {foreach item="attachments" from="$document_attachments[$item.doc_id]}
           {if isset($attachments.name_value_list[0].value)}
	       <img src='themes/{$theme}/images/attachment.gif' width='16' height='16' border="0" alt="attachment">
	       <a href="index.php?module=KBDocuments&action=GetAttachment&id={$attachments.name_value_list[0].value}&ext={$attachments.name_value_list[4].value}&to_pdf=1">
	       {$attachments.name_value_list[3].value}
	       </a>
	       <br>
	       {/if}
       {/foreach}
    </p>
    {/if}
    
    <p style="margin-bottom: 0px; text-align: right;">
    <a href="#{$outerkey}">{$mod_strings.LBL_BACK_TO_TOP} ></a>
    </p>
    </td>
	</tr>
	{/foreach}
    {assign var="section" value="`$section+1`"}
{/foreach}

</table>
<p>
<p>