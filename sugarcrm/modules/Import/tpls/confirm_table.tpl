<table border="0" cellpadding="0" width="100%" id="importTable" class="detail view">
    <tbody>
        {foreach from=$SAMPLE_ROWS item=row name=row}
            <tr>
                {foreach from=$row item=value}
                    {if $smarty.foreach.row.first}
                        <td scope="row" style="text-align: left;">{$value}</td>
                     {else}
                        <td>{$value}</td>
                     {/if}

                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>
