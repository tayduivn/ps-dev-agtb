<!---------------  END WORKFLOW SHOWCASE ------------>
    {foreach from=$customButtons key='key' item='item'}
        {*<input name="{$item.name}" type="{$item.type}" value={$item.value} onclick="{$item.onclick}">*}
        {if $item.value=='Claim'}
            <a href="{$item.onclick}" title="{$item.value}"><span class="btn">{$item.value}</span></a>
        {else}
            <input name="{$item.name}" type="{$item.type}" value={$item.value} onclick="{$item.onclick}">
        {/if}
    {/foreach} 
</form>
<!---------------  END WORKFLOW SHOWCASE ------------>