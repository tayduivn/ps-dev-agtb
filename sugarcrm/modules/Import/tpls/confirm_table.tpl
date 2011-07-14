<table border="0" cellpadding="0" width="100%" id="importTable" class="detail view noBorder">
    <tbody>
        {foreach from=$SAMPLE_ROWS item=row name=row}
            <tr>
                {foreach from=$row item=value}
                    {if $smarty.foreach.row.first}
                        {if $HAS_HEADER}
                            <td scope="row" style="text-align: left;">{$value}</td>
                        {else}
                            <td scope="row" style="text-align: left;" colspan="{$column_count}">{$MOD.LBL_MISSING_HEADER_ROW}</td>
                        {/if}
                     {else}
                        <td class="impSample">{$value}</td>
                     {/if}
                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>
